<?php
$config = include_once 'config/config.php';
if ($config['debug']) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL); 
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0); 
}
$siteTheme = $config['siteTheme'];
if (isset($_GET['theme'])) {
    $siteTheme = $_GET['theme'];
}

require_once 'class/Session.php';
require_once 'class/Downloader.php';
require_once 'class/FileHandler.php';
  
$session = Session::getInstance();
$file = new FileHandler;
  
// Process the download queue unless it's disabled
if (!$config['disableQueue']) {
    $downloader = new Downloader([]);
    $downloader->process_queue();
}

// Return JSON with all jobs currently running and jobs history
if(isset($_GET['jobs'])) {
    $videofiles = $file->listVideos();
    $musicfiles = $file->listMusics();
    $jsonString = "{ \"jobs\": [";
    foreach(Downloader::get_current_background_jobs() as $key) {
        $jsonString .= "{ \"file\": ".$key['file'].", ";
        $jsonString .= "\"status\": ".$key['status'].", ";
        $jsonString .= "\"site\": ".$key['site'].", ";
        $jsonString .= "\"type\": ".$key['type'].", ";
        $jsonString .= "\"pid\": ".$key['pid'].", ";
        $jsonString .= "\"url\": ".$key['url'];
        $jsonString .= "},";
    }
    $jsonString = trim($jsonString, ",");
    $jsonString .= "],";
    
    $jsonString .= "\"queue\": [";
    if (!$config['disableQueue']) {
        foreach(Downloader::get_queued_jobs() as $key) {
            $dl_type = "video";
            $dl_format = str_replace("-f ", "Format: ", $key['dl_format']);
            if ($key['audio_only']) {
              $dl_type = "audio";
              $dl_format = str_replace("--audio-format ", "Format: ", $key['audio_format']);
              $dl_format = str_replace(" --audio-quality ", ", Quality: ", $dl_format);
            }
            $jsonString .= "{ \"pid\": ".$key['pid'].", ";
            $jsonString .= "\"url\": ".$key['url'].", ";
            $jsonString .= "\"dl_format\": ".$dl_format.", ";
            $jsonString .= "\"type\": \"".$dl_type."\"";
            $jsonString .= "},";
        }
        $jsonString = trim($jsonString, ",");
    }
    $jsonString .= "],";
    $jsonString .= "\"finished\": [";
    foreach(Downloader::get_finished_background_jobs() as $key) {
        $jsonString .= "{ \"file\": ".$key['file'].", ";
        $jsonString .= "\"status\": ".$key['status'].", ";
        $jsonString .= "\"site\": ".$key['site'].", ";
        $jsonString .= "\"type\": ".$key['type'].", ";
        $jsonString .= "\"pid\": ".$key['pid'].", ";
        $jsonString .= "\"url\": ".$key['url'];
        $jsonString .= "},";
    }
    $jsonString = trim($jsonString, ",");
    $jsonString .= "],";

    $jsonString .= "\"videos\": [";
    foreach($videofiles as $f) {
        $deleteurl = "";
        if (array_key_exists('allowFileDelete', $config) && $config['allowFileDelete']) {
            $deleteurl = '<a data-href="?delete='.urlencode($f["name"]).'&type=v" data-toggle="modal" data-target="#confirm-delete" class="btn btn-danger btn-xs">Delete</a>';
        }
        $fileurl = $f["name"];
        if ($config['downloadPath'] != "") {
            $fileurl = '<a href="'.$file->get_downloads_link().'/'.$f["name"].'" download>'.$f["name"].'</a>';
        }
        $jsonString .= "{ \"file\": ".json_encode($fileurl).", ";
        $jsonString .= "\"size\": ".json_encode($f["size"]).", ";
        $jsonString .= "\"deleteurl\": ".json_encode($deleteurl);
        $jsonString .= "},";
    }
            
    $jsonString = trim($jsonString, ",");
    $jsonString .= "],";

    $jsonString .= "\"music\": [";
    foreach($musicfiles as $f) {
        $deleteurl = "";
        if (array_key_exists('allowFileDelete', $config) && $config['allowFileDelete']) {
            $deleteurl = '<a data-href="?delete='.urlencode($f["name"]).'&type=m" data-toggle="modal" data-target="#confirm-delete" class="btn btn-danger btn-xs">Delete</a>';
        }
        $fileurl = $f["name"];
        if ($config['downloadPath'] != "") {
            $fileurl = '<a href="'.$file->get_downloads_link().'/'.$f["name"].'" download>'.$f["name"].'</a>';
        }
        $jsonString .= "{ \"file\": ".json_encode($fileurl).", ";
        $jsonString .= "\"size\": ".json_encode($f["size"]).", ";
        $jsonString .= "\"deleteurl\": ".json_encode($deleteurl);
        $jsonString .= "},";
    }
    $jsonString = trim($jsonString, ",");
    $jsonString .= "],";

    $jsonString .= "\"logURL\": ".json_encode($config['logURL'])." }";
    // If the paramter "cron" is set we don't want to do any output unless something's wrong
    if(!isset($_GET['cron'])) {
    echo $jsonString;
    }
    die();
}

if(isset($_GET['cron'])) {
    // If the paramter "cron" is set we don't want to do any output unless something's wrong
    die();
}
  
// Remove a queued item
if(isset($_GET["removeQueued"])) {
    Downloader::remove_queued_job($_GET["removeQueued"]);
    header("Location: index.php#downloads");
    die();
}
  
// Delete a downloaded file from disk if allowed
if(isset($_GET["delete"]) && array_key_exists('allowFileDelete', $config) && $config['allowFileDelete']) {
    $file->delete($_GET["delete"]);
    if ($_GET["type"] == "m") {
        header("Location: index.php#music");
    } else {
        header("Location: index.php#videos");
    }
    die();
}
  
// Kill one or all jobs
if(isset($_GET['kill']) && !empty($_GET['kill'])) {
    if ($_GET['kill'] === "all") {
        Downloader::kill_them_all();
    } else {
        Downloader::kill_one_of_them($_GET['kill']);
    }
    header("Location: index.php#downloads");
    die();
}
  
// Clear download history
if(isset($_GET['clear']) && !empty($_GET['clear'])) {
    switch ($_GET['clear']) {
        case "recent":
        Downloader::clear_finished();
        break;
        case "queue":
            Downloader::remove_all_queued_jobs();
        break;
        default:
        Downloader::clear_one_finished($_GET['clear']);
        break;
    }
    header("Location: index.php#downloads");
    die();
}
  
// Restart a job from history
if(isset($_GET['restart']) && !empty($_GET['restart'])) {
    Downloader::restart_download($_GET['restart']);
    header("Location: index.php#downloads");
    die();
}
  
// Download a video
if(isset($_POST['urls']) && !empty($_POST['urls'])) {
    $get_parms = "?";
    $audio_only = false;
    $audio_format = "--audio-format mp3 --audio-quality 0";
    //$dl_format = "-f best";
    $dl_format = "";
    
    if(isset($_POST['audio']) && !empty($_POST['audio'])) {
        $audio_only = true;
        $get_parms .= "audio=true&";
    }
    
    if(isset($_POST['audio_format']) && !empty($_POST['audio_format'])) {
        if($_POST['audio_format'] === "mp3-high") {
            $audio_format = "--audio-format mp3 --audio-quality 0";
        } else {
            $audio_format = "--audio-format " . $_POST['audio_format'];
        }
        $get_parms .= "audio_format=".$_POST['audio_format']."&";
    }
    
    if(isset($_POST['format']) && !empty($_POST['format'])) {
        if($_POST['format'] != "best") {
            $dl_format = "-f " . $_POST['format'];
            $get_parms .= "format=".$_POST['format']."&";
        }
    }
    
    $dl_list = [];
    $dl_list[] = array(
    'url' => $_POST['urls'],
    'audio_only' => $audio_only,
    'dl_format' => $dl_format,
    'audio_format' => $audio_format
    );
    $downloader = new Downloader($dl_list);
    
    if(!isset($_SESSION['errors']) || count($_SESSION['errors']) === 0) {
        header("Location: index.php".$get_params."#".$config['redirectAfterSubmit']);
        die();
    }
}
  
// Download or autopopulate from bookmarklet
$urlvalue = "";
if (isset($_GET['url'])) {
    $urlvalue = " value=\"".urldecode($_GET['url'])."\"";
    if (isset($_GET['auto_submit'])) {
        $audio_format = "--audio-format mp3 --audio-quality 0";
        //$dl_format = "-f best";
        $dl_format = "";
        if (isset($_GET["audio"]) && $_GET["audio"] == "true") {
            $audio_only = true;
        } else {
            $audio_only = false;
        }
      
        if(isset($_GET['audio_format']) && !empty($_GET['audio_format'])) {
            if($_POST['audio_format'] === "mp3-high") {
                $audio_format = "--audio-format mp3 --audio-quality 0";
            } else {
                $audio_format = "--audio-format " . $_POST['audio_format'];
            }
        }
    
        if(isset($_GET['format']) && !empty($_GET['format'])) {
            $dl_format = "-f " . $_GET['format'];
        }
      
        $dl_list = [];
        $dl_list[] = array(
        'url' => $_GET['url'],
        'audio_only' => $audio_only,
        'dl_format' => $dl_format,
        'audio_format' => $audio_format
        );
        $downloader = new Downloader($dl_list);
     
        if(isset($_SESSION['errors']) && $_SESSION['errors'] > 0) {
            header("Location: index.php?submitstatus=error");
        } else {
            header("Location: index.php?submitstatus=success");
        }
        die();
    }
}

// Show success or error if download was triggered through bookmarklet
if (isset($_GET['submitstatus'])) {
    include_once 'views/page.autosubmit.php';
    unset($_SESSION['errors']);
    die();
}
  
// Prepare for display of web page
if (@$_GET["audio"]=="true" && !$config['disableExtraction']) {
    $audio_check = " checked=\"checked\"";
    $video_form_style = " style=\"display: none;\"";
    $audio_form_style = "";
} else {
    $audio_check = "";
    $video_form_style = "";
    $audio_form_style = "style=\"display: none;\"";
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
$uri_parts = $uri_parts[0];
$uri_parts = explode('#', $uri_parts, 2);
$baseuri = $protocol . $_SERVER['HTTP_HOST'] . $uri_parts[0];
$bookmarkletvideo = "javascript:(function(){f='".$baseuri."?url='+encodeURIComponent(window.location.href);a=function(){if(!window.open(f))location.href=f};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})()";
$bookmarkletmusic = "javascript:(function(){f='".$baseuri."?audio=true&url='+encodeURIComponent(window.location.href);a=function(){if(!window.open(f))location.href=f};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})()";

// Show header
require_once 'views/part.header.php';
  
// Add Custom bookmarklet popup
require_once 'views/popup.bookmarklet.php';

// Add confirm popup
require_once 'views/popup.confirm.php';
  
// Main part of website
require_once 'views/part.main.php';

// Show footer with help panel
require_once 'views/part.footer.php';

unset($_SESSION['errors']);
?>
