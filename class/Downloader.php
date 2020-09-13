<?php
class Downloader
{
    private $dl_list = [];
    private $errors = [];
    private $download_path = "";
    private $config = [];
    
    public function __construct($dl_list)
    {
        $this->download_path = (new FileHandler())->get_downloads_folder();
        $this->config = $GLOBALS['config'];
        $this->dl_list = $dl_list;
      
        if(!$this->check_requirements()) {
            return;
        }
      
        if (count($dl_list) > 0) {
            foreach($dl_list as $onedownload) {
                foreach(explode('||', $onedownload['url']) as $url) {
            if(!$this->is_valid_url($url)) {
                $this->errors[] = "\"".$url."\" is not a valid url !";
            }
        }
            }
      
        if(isset($this->errors) && count($this->errors) > 0) {
            $_SESSION['errors'] = $this->errors;
            return;
        }
                $this->do_download();
        }
    }
    
    public static function background_jobs()
    {
        return shell_exec("ps aux | grep -v grep | grep -v \"youtube-dl -U\" | grep youtube-dl | wc -l");
    }
    
    public static function max_background_jobs()
    {
        return $this->config["max_dl"];
    }
    
    public static function get_current_background_jobs()
    {
        $bjs = [];
        $dir = new DirectoryIterator($GLOBALS['config']['logPath']);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot() && $fileinfo->isFile() && strpos($fileinfo->getFilename(), "pid_")===0) {
                $outfile = $GLOBALS['config']['logPath']."/".str_replace("pid_", "job_", $fileinfo->getFilename());
                $completefile = $GLOBALS['config']['logPath']."/".str_replace("pid_", "ytdl_", $fileinfo->getFilename());
                if (!file_exists($outfile)) {
                    //No output file exists for job
                    unlink($fileinfo->getPathname());
                    continue;
                }
                $jpid = trim(file_get_contents($fileinfo->getPathname()));
                $ytcmd = explode("\n", $jpid)[1];
                $urltext = explode("\n", $jpid)[2];
                $jpid = explode("\n", $jpid)[0];

                if (!file_exists("/proc/".$jpid)) {
                    // The job has terminated
                    unlink($fileinfo->getPathname());
                    rename($outfile, $completefile);
                    file_put_contents($completefile, "[ytcmd] ". $ytcmd."\n", FILE_APPEND);
                    file_put_contents($completefile, "[yturl] ". $urltext."\n", FILE_APPEND);
                    continue;
                }
                $pidcmd = trim(file_get_contents('/proc/'.$jpid.'/cmdline'));
                // Check that this really is a youtube-dl process and not a process with the same PID as an old job
                if (strpos($pidcmd, $GLOBALS['config']['youtubedlExe']) === false) {
                    // The job has terminated
                    unlink($fileinfo->getPathname());
                    rename($outfile, $completefile);
                    file_put_contents($completefile, "[ytcmd] ". $ytcmd."\n", FILE_APPEND);
                    file_put_contents($completefile, "[yturl] ". $urltext."\n", FILE_APPEND);
                    continue;
                }
                $handle = fopen($outfile, "r");
                $lastline = "";
                $verylastline = "";
                $filename = "TBC";
                $site = "TBC";
                $siteset = false;
                $isaudio = false;
                $listpos = "";
                $playlist = "";
                if (strpos($fileinfo->getFilename(), "_a") !== false) {
                    $isaudio = true;
                }
                if ($handle) {
                    while (($line = fgets($handle)) !== false) {
                        if (strpos($line, '[debug] ') === false) {
                            if (strpos($line, '[download] Downloading') !== false) {
                                $listpos = "(".substr($line, 29).")";
                            }
                            if (strpos($line, '[download] Downloading playlist:') !== false) {
                                $playlist = substr($line, 33)."<br />";
                            }
                            if (trim($line) != "") {
                                $lastline = $line;
                            }
                            $verylastline = $line;
                            if ( !$siteset) {
                                $siteset = true;
                                $site = explode(" ", $line)[0];
                                $site = str_replace("[", "", $site);
                                $site = str_replace("]", "", $site);
                                $site = ucfirst($site);
                            }
                            if (strpos($line, 'Destination') !== false) {
                                $pos = strrpos($line, '/');
                                $filename = $pos === false ? $line : substr($line, $pos + 1);
                            }
                        }
                    }
                    fclose($handle);
                    if ($filename == "TBC") {
                        $lastline = "Getting file information from website"; 
                    } else {
                        $pos = strrpos($lastline, '[download]');
                        $lastline = $pos === false ? "" : trim(substr($lastline, $pos + 11));
                        $filename = urlencode($filename." ".$listpos);
                        $filename = str_replace("%0A", "", $filename);
                        $filename = urldecode($filename);
                        if ($isaudio && strpos($verylastline, '[ffmpeg]') !== false) {
                            $lastline = "Converting to audio. This might take a while.";
                        }
                    }
                    if (strpos($lastline, '100%') !== false || $lastline=="") {
                        $lastline = "In Progress...";
                    }
                    $type = "video";
                    if ($isaudio) {
                        $type = "audio";
                    }

                    $bjs[] = array(
                    'file' => json_encode($playlist.$filename),
                    'site' => json_encode($site),
                    'status' => str_replace("\\n", "", json_encode($lastline)),
                    'type' => json_encode($type),
                    'pid' => json_encode($fileinfo->getFilename()),
                    'url' => json_encode($urltext)
                    );
                }
            }
        }
        return $bjs;
    }
    
    public static function get_queued_jobs()
    {
        $qjs = [];
        $queue_file = $GLOBALS['config']['logPath']."/dl_queue";
        if (!file_exists($queue_file)) {
            return $qjs;
        }
        $corrupt_queue = false;
        $handle = fopen($queue_file, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (trim($line) === "") {
                    continue;
                }
                if (substr($line, 0, 7) !== "queueid") {
                    $corrupt_queue = true;
                    $break;
                }
                $url = substr($line, strpos($line,"=")+1);
                $pid = substr($line, 0, strpos($line,"="));
                $audio_only = true;
                if (!explode(">", $url)[2] || trim(explode(">", $url)[2]) === "") {
                    $audio_only = false;
                }
                
                $qjs[] = array(
                'pid' => json_encode($pid),
                'url' => json_encode(explode(">", $url)[0]),
                'dl_format' => json_encode(explode(">", $url)[1]),
                'audio_only' => $audio_only,
                'audio_format' => json_encode(explode(">", $url)[3])
                );
            }
        }
        fclose($handle);
        if ($corrupt_queue) {
            unlink($queue_file);
            return [];
        }
        return $qjs;
    }
    
    public static function get_finished_background_jobs()
    {
        $bjs = [];
        $dir = new DirectoryIterator($GLOBALS['config']['logPath']);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot() && $fileinfo->isFile() && strpos($fileinfo->getFilename(), "ytdl_")===0) {
                $handle = fopen($fileinfo->getPathname(), "r");
                $lastline = "";
                $verylastline = "";
                $filename = "N/A";
                $site = "N/A";
                $siteset = false;
                $isaudio = false;
                $listpos = "";
                $playlist = "";
                $urltext = "";
                if (strpos($fileinfo->getFilename(), "_a") !== false) {
                    $isaudio = true;
                }
                if ($handle) {
                    $jobstatus = "Completed";
                    while (($line = fgets($handle)) !== false) {
                        if (strpos($line, '[debug] ') === false) {

                            if (strpos($line, '[download] Downloading') !== false) {
                                $listpos = substr($line, 29);
                                $listpos = trim(substr($listpos, strpos($listpos, " of")+3));
                            }
                            if (strpos($line, '[download] Downloading playlist:') !== false) {
                                $playlist = substr($line, 33);
                            }
                            $verylastline = $line;
                            if (!$siteset) {
                                $siteset = true;
                                $site = explode(" ", $line)[0];
                                $site = str_replace("[", "", $site);
                                $site = str_replace("]", "", $site);
                                $site = ucfirst($site);
                            }
                            if (strpos($line, '[yturl]') !== false) {
                                $urltext = substr($line, 8);
                            }
                            if (strpos($line, 'Destination') !== false) {
                                $pos = strrpos($line, '/');
                                $filename = $pos === false ? $line : substr($line, $pos + 1);
                            }
                            if (strpos($line, 'has already been downloaded') !==false) {
                                $posEnd   = strpos($line, 'has already been downloaded');
                                $posStart = strrpos($line, '/');
                                $filename = $posStart === false ? $line : substr($line, $posStart + 1, $posEnd - 22);
                                $jobstatus = "Cancelled (Already Downloaded)";
                            }
                        }
                    }
                    fclose($handle);
                    $type = "video";
                    if ($isaudio) {
                        $type = "audio";
                    }
                    if (strpos($fileinfo->getFilename(), "_cancelled")!==false) {
                        $jobstatus = "Cancelled";
                    }
                    if ($playlist != "") {
                        $filename = $playlist." (".$listpos." files)";
                    }
                    if ($filename == "N/A") {
                        $type = "unknown";
                        $jobstatus = "Error";
                    }
                    $bjs[] = array(
                    'file' => json_encode($filename),
                    'site' => json_encode($site),
                    'status' => str_replace("\\n", "", json_encode($jobstatus)),
                    'type' => json_encode($type),
                    'pid' => json_encode($fileinfo->getFilename()),
                    'url' => json_encode($urltext)
                    );
                }
            }
        }
        return $bjs;
    }
    
    public static function kill_one_of_them($fpid)
    {
        $file = $GLOBALS['config']['logPath'].'/'.$fpid;
        if (!file_exists($file)) {
            return;
        }
        $outfile = $GLOBALS['config']['logPath']."/".str_replace("pid_", "job_", $fpid);
        $completed = $GLOBALS['config']['logPath']."/".str_replace("pid_", "ytdl_", $fpid)."_cancelled";
        $jpid = trim(file_get_contents($file));
        $ytcmd = explode("\n", $jpid)[1];
        $urltext = explode("\n", $jpid)[2];
        $jpid = explode("\n", $jpid)[0];

        $pidcmd = trim(file_get_contents('/proc/'.$jpid.'/cmdline'));
        // Check that this really is a youtube-dl process and not a process with the same PID as an old job
        if (strpos($pidcmd, $GLOBALS['config']['youtubedlExe']) !== false) {
            shell_exec("kill ".$jpid);
        }
        rename($outfile, $completed);
        file_put_contents($completed, "[ytcmd] ". $ytcmd."\n", FILE_APPEND);
        file_put_contents($completed, "[yturl] ". $urltext."\n", FILE_APPEND);
        unlink($file);
    }
    
    public static function kill_them_all()
    {
        foreach(glob($GLOBALS['config']['logPath'].'/pid_*') as $file) {
            $jobfile = str_replace("pid_", "job_", $file);
            $pos = strrpos($jobfile, "job_");
            $completed = substr_replace($jobfile, "ytdl_", $pos, strlen("job_"))."_cancelled";
            rename($jobfile, $completed);
            $jpid = trim(file_get_contents($file));
            $ytcmd = explode("\n", $jpid)[1];
            $jpid = explode("\n", $jpid)[0];
            file_put_contents($completed, "[ytcmd] ". $ytcmd."\n", FILE_APPEND);
            file_put_contents($completed, "[yturl] ". $urltext."\n", FILE_APPEND);
            unlink($file);
        }
      
        exec("ps -A -o pid,comm | grep -v grep | grep youtube-dl | awk '{print $1}'", $output);
      
        if(count($output) <= 0) {
            return;
        }
      
        foreach($output as $p) {
            shell_exec("kill ".$p);
        }
      
        $folder = $GLOBALS['config']['outputFolder'];
      
        if (!$GLOBALS['config']['keepPartialFiles']) {
            foreach(glob($folder.'/*.part') as $file) {
                unlink($file);
            }
        }
    }

    public static function restart_download($fpid) 
    {
        $handle = fopen($GLOBALS['config']['logPath'].'/'.$fpid, "r");
        $ytcmd = "";
        $urltext = "";
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (strpos($line, '[ytcmd]') !== false) {
                    $ytcmd = substr($line, 8);
                }
                if (strpos($line, '[yturl]') !== false) {
                    $urltext = substr($line, 8);
                }
            }
            fclose($handle);
        }
        if ($ytcmd == "") {
            $_SESSION['errors'] = "Could not restart. Log file corrupt.";
            return;
        }
        $suffix = "";
        if (strpos($fpid, "_a") !== false) {
            $suffix = "_a";
        }

        do {
            $fno = "job_".uniqid().$suffix;
        } while (file_exists($GLOBALS['config']['logPath']."/".$fno));

        $fnp = str_replace("job_", "pid_", $fno);
        $ytcmd = trim($ytcmd);
        $cmd = $ytcmd." >> ".$GLOBALS['config']['logPath']."/".$fno." & echo $! > ".$GLOBALS['config']['logPath']."/".$fnp;
        passthru($cmd);
        file_put_contents($GLOBALS['config']['logPath']."/".$fnp, $ytcmd."\n", FILE_APPEND);
        file_put_contents($GLOBALS['config']['logPath']."/".$fnp, $urltext."\n", FILE_APPEND);
    }

    public static function clear_one_finished($fpid)
    {
        unlink($GLOBALS['config']['logPath'].'/'.$fpid);
    }
    
    public static function clear_finished()
    {
        foreach(glob($GLOBALS['config']['logPath'].'/ytdl_*') as $file) {
            unlink($file);
        }
    }
    
    private function check_requirements()
    {
        $this->check_outuput_folder();
      
        if(isset($this->errors) && count($this->errors) > 0) {
            $_SESSION['errors'] = $this->errors;
            return false;
        }
      
        return true;
    }
    
    private function is_valid_url($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
    
    private function check_outuput_folder()
    {
        if(!is_dir($this->download_path)) {
            //Folder doesn't exist
            if(!mkdir($this->download_path, 0775)) {
                $this->errors[] = "Output folder doesn't exist and creation failed !";
            }
        } else {
            //Exists but can I write ?
            if(!is_writable($this->download_path)) {
                $this->errors[] = "Output folder isn't writable !";
            }
        }
    }

    private function getUniqueFileName($prefix, $suffix, $path)
    {
        do {
            $uid = $prefix.uniqid().$suffix;
        } while (file_exists($path.$uid));
        return $uid;
    }
    
    private function do_download()
    {
        foreach ($this->dl_list as $onedownload) {
            if($this->config["max_dl"] == -1) {
                $this->addOneDownload($onedownload);
            }
            elseif($this->config["max_dl"] > 0) {
                if($this->background_jobs() >= 0 && $this->background_jobs() < $this->config["max_dl"]) {
                    $this->addOneDownload($onedownload);
                } else {
                    if ($this->config["disableQueue"]) {
                        $this->errors[] = "Simultaneous downloads limit reached. ".$onedownload['url']." will not be downloaded.";
                    } else {
                        $this->addToQueue($onedownload);
                    }
                }
            } else {
                $this->errors[] = "The max_dl value in config.php is invalid.";
            }
        }
    }
    
    public function addOneDownload($onedownload)
    {
        $suffix = "";
        $cmd = $this->config['youtubedlExe'];
        $cmd .= " ".$this->config['youtubedlParameters'];
        $cmd .= " -o ".$this->download_path."/";
        $cmd .= escapeshellarg($this->config['outputSubfolder']."/".$this->config['downloadFileName'].".%(ext)s");
        $cmd .= " ".$onedownload['dl_format'];
      
        if($onedownload['audio_only']) {
            $cmd .= " -x";
            $cmd .= " ".$onedownload['audio_format'];
            $suffix = "_a";
        }
        $fno = $this->getUniqueFileName("job_", $suffix, $this->config['logPath']."/");
        $fnp = str_replace("job_", "pid_", $fno);
        $urltext = "";
        foreach(explode("||", $onedownload['url']) as $url) {
            $cmd .= " ".escapeshellarg($url);
            $urltext .= $url .",";
        }
        $urltext = trim($urltext, ",");
        $cmd .= " --restrict-filenames"; // --restrict-filenames is for specials chars
        $cmd .= " --ignore-errors";
        $cmd .= " ".$this->config["youteubedlParameters"];
        $logcmd = $cmd;
        $cmd .= " >> ".$this->config['logPath']."/".$fno." 2>&1 & echo $! > ".$this->config['logPath']."/".$fnp;

        // setting locale encoding to allow accented caracters into metadata
        $locale = $this->config['encoding'];
        setlocale(LC_ALL, $locale);
        putenv('LC_ALL='.$locale);
        // executing extraction
        passthru($cmd);

        file_put_contents($this->config['logPath']."/".$fnp, $logcmd."\n", FILE_APPEND);
        file_put_contents($this->config['logPath']."/".$fnp, $urltext."\n", FILE_APPEND);
    }
    
    public function process_queue()
    {
        $queue_file = $this->config['logPath']."/dl_queue";
        $currently_running = $this->background_jobs();
        if (!file_exists($queue_file)) {
            return;
        }
        if ($this->background_jobs() >= 0 && $this->background_jobs() >= $this->config["max_dl"]) {
            return;
        }
        $remaining_urls = [];
        $corrupt_queue = false;
        $handle = fopen($queue_file, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (trim($line) === "") {
                    continue;
                }
                if (substr($line, 0, 7) !== "queueid") {
                    $corrupt_queue = true;
                    break;
                }
                $url = substr($line, strpos($line,"=")+1);
                if (!$this->is_valid_url(explode(">", $url)[0])) {
                    $this->errors[] = $url." is not a valid URL and was removed from the queue.";
                } else {
                    if ($currently_running >= 0 && $currently_running < $this->config["max_dl"]) {
                        $this->dl_list[] =  array(
                        'url' => explode(">", $url)[0],
                        'dl_format' => explode(">", $url)[1],
                        'audio_only' => explode(">", $url)[2],
                        'audio_format' => explode(">", $url)[3]
                        );
                        $currently_running++;
                    } else {
                        $remaining_urls[] = $line;
                    }
                }
            }
        } else {
            $this->errors[] = "Could not open queue file. Please check permissions.";
        }
        fclose($handle);
        if ($corrupt_queue) {
            unlink($queue_file);
            $this->errors[] = "The queue file is corrupt and was deleted.";
        }
        $string_contents = "";
        foreach ($remaining_urls as $oneline) {
            $string_contents .= $oneline."\n";
        }
        file_put_contents($queue_file, $string_contents, LOCK_EX);
        $this->do_download();
        $_SESSION['errors'] = $this->errors;
    }

    public function addToQueue($onedownload)
    {
        $queue_file = $this->config['logPath']."/dl_queue";
        $fcontent = "queueid".uniqid()."=".$onedownload['url'].">".$onedownload['dl_format'].">".$onedownload['audio_only'].">".$onedownload['audio_format']."\n";
        if (file_exists($queue_file)) {
            file_put_contents($queue_file, $fcontent, FILE_APPEND | LOCK_EX);
        } else {
            file_put_contents($queue_file, $fcontent, LOCK_EX);
        }
    }
    
    public function remove_queued_job($qid) {
        $queue_file = $GLOBALS['config']['logPath']."/dl_queue";
        if (!file_exists($queue_file)) {
            return;
        }
        $remaining_urls = [];
        $corrupt_queue = false;
        $handle = fopen($queue_file, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (trim($line) === "") {
                    continue;
                }
                if (substr($line, 0, 7) !== "queueid") {
                    $corrupt_queue = true;
                    break;
                }
                $pid = substr($line, 0, strpos($line,"="));
                if ($pid !== $qid) {
                    $remaining_urls[] = $line;
                }
            }
        } else {
            $_SESSION['errors'] = "Could not open queue file. Please check permissions.";
            return;
        }
        fclose($handle);
        if ($corrupt_queue) {
            unlink($queue_file);
            $_SESSION['errors'] = "The queue file is corrupt and was deleted.";
            return;
        }
        if (count($remaining_urls) === 0) {
            unlink($queue_file);
            return;
        }
        $string_content = "";
        foreach ($remaining_urls as $oneline) {
            $string_content .= $oneline."\n";
        }
        file_put_contents($queue_file, $string_content, LOCK_EX);
    }
    
    public function remove_all_queued_jobs() {
        $queue_file = $GLOBALS['config']['logPath']."/dl_queue";
        if (!file_exists($queue_file)) {
            return;
        }
        unlink($queue_file);
    }

}
?>