<?php if (!isset($GLOBALS['config'])) { die("No direct script access");
} ?>
<div class="container" style="margin-bottom: 50px;">
  <ul id="mainnav" class="nav nav-pills">
    <li class="active"><a id="home_link" href="#home" data-toggle="tab" aria-expanded="true">Home</a></li>
    <li><a id="dl_link" href="#downloads" data-toggle="tab" aria-expanded="false">Downloading</a></li>
    <li><a id="vid_link" href="#videos" data-toggle="tab" aria-expanded="false">Videos</a></li>
    <li><a id="music_link" href="#music" data-toggle="tab" aria-expanded="false">Music</a></li>
  </ul>
  <div id="myTabContent" class="tab-content">
    <div class="tab-pane fade active in" id="home">
      <div class="row">
        <br />
        <h1 style="text-align: center;"><?php echo($config['siteName']); ?></h1><br />
<?php if(isset($_SESSION['errors']) && $_SESSION['errors'] > 0) : ?>
    <?php foreach ($_SESSION['errors'] as $e): ?>
    <div class="alert alert-warning" role="alert"><?php echo ($e); ?></div>
    <?php endforeach; ?>
<?php endif; ?>
        <form id="download-form" class="form-horizontal" action="index.php" method="post">

          <div class="form-group">

            <div class="col-md-12">
              <input class="form-control" id="url" name="urls"<?php echo($urlvalue); ?> placeholder="Enter the URL to the video you want to download. If you want to enter more that one please separate with a comma." type="text">
            </div>

            <div class="col-md-12">
              <div style="text-align: center;" class="checkbox">
                <button style="width: 300px;" type="submit" class="btn btn-primary">Download</button><br />

                <span <?php echo($config['disableExtraction'] ? " style=\"display: none;\"" : ""); ?>>
                  <input id="audio_convert" onclick="checkControls();"<?php echo($audio_check); ?> type="checkbox" name="audio"> Convert to Audio</label>
                </span>
              </div>
            </div>

            <div class="col-md-12">
              <div<?php echo($config['disableExtraction'] ? " style=\"display: none;\"" : ""); ?>>

                  <div class="form -group" id="audio_group"  <?php echo($audio_form_style); ?>>
                    <h6>
                    <label class="control-label col-sm-6" for="audio_format">Audio Format:</label>
                    <div class="col-sm-2">
	              <select class="form-control input-sm" name="audio_format" id="audio_format">
	                <option value="mp3-high"<?php echo($_GET["audio_format"]=="mp3-high" ? " selected=\"selected\"" : ""); ?>>mp3 High Quality</option>
	                <option value="mp3"<?php echo($_GET["audio_format"]=="mp3" ? " selected=\"selected\"" : ""); ?>>mp3</option>
	                <option value="aac"<?php echo($_GET["audio_format"]=="aac" ? " selected=\"selected\"" : ""); ?>>aac</option>
	                <option value="vorbis"<?php echo($_GET["audio_format"]=="vorbis" ? " selected=\"selected\"" : ""); ?>>vorbis</option>
	                <option value="m4a"<?php echo($_GET["audio_format"]=="m4a" ? " selected=\"selected\"" : ""); ?>>m4a</option>
                        <option value="opus"<?php echo($_GET["audio_format"]=="opus" ? " selected=\"selected\"" : ""); ?>>opus</option>
                        <option value="wav"<?php echo($_GET["audio_format"]=="wav" ? " selected=\"selected\"" : ""); ?>>wav</option>
	              </select>
                    </div>
                    </h6>
                  </div>

                  <div class="form -group" id="video_group" <?php echo($video_form_style); ?>>
                    <h6>
                    <label class="control-label col-sm-6 text-small" for="video_format">Video Format:</label>
                    <div class="col-sm-2">
                      <select class="form-control input-sm" name="video_format" id="video_format">
                        <option value="best"<?php echo($_GET["format"]=="best" ? " selected=\"selected\"" : ""); ?>>Best</option>
                        <option value="worst"<?php echo($_GET["format"]=="worst" ? " selected=\"selected\"" : ""); ?>>Smallest</option>
                      </select>
                    </div>
                    </h6>
                  </div>
               </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div class="tab-pane fade" id="downloads">
      <div style="text-align: center;" class="row">
        <br /><br />
        <h4>Currently Downloading</h4>
        <table style="text-align: left;" class="table table-striped table-hover ">
          <thead>
            <tr>
              <th style="width: 10%; height:35px;">Site/Type</th>
              <th>File</th>
              <th style="width: 25%;">Status</th>
              <th style="width: 120px;">Actions</th>
            </tr>
          </thead>
          <tbody id="dlprogress">
            <tr>
              <td colspan="4">Getting downloads please wait...</td>
            </tr>
          </tbody>
        </table>
        <br /><br />
<?php if(!$config['disableQueue']) : ?>
        <h4>Queued</h4>
        <table style="text-align: left;" class="table table-striped table-hover ">
          <thead>
            <tr>
              <th style="height:35px;">URL</th>
              <th style="width: 120px;">Format</th>
              <th style="width: 120px;">Actions</th>
            </tr>
          </thead>
          <tbody id="dlqueue">
            <tr>
              <td colspan="3">Getting queued downloads please wait...</td>
            </tr>
          </tbody>
        </table>
        <br /><br />
<?php endif; ?>
        <h4>Recently Completed</h4>
        <table style="text-align: left;" class="table table-striped table-hover ">
          <thead>
            <tr>
              <th style="width: 10%; height:35px;">Site/Type</th>
              <th>File/Playlist</th>
              <th style="width: 25%;">Status</th>
              <th style="width: 180px;">Actions</th>
            </tr>
          </thead>
          <tbody id="dlcompleted">
            <tr>
              <td colspan="4">Getting downloads please wait...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="tab-pane fade" id="videos">
      <br /><br />
      <h4 style="text-align: center;">Downloaded Videos</h4>
      <input class="form-control input-sm" id="searchDownloadedVideos" type="text" placeholder="Search...">
      <table style="text-align: left;" class="table table-striped table-hover " id="tableDownloadedVideos">
        <thead>
          <tr>
            <th style="min-width:800px; height:35px">File</th>
            <th style="min-width:80px">Size</th>
            <?php if ($config['allowFileDelete']) : ?>
              <th style="min-width:110px">Actions</th>
            <?php else: ?>
              <th></th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody id="videofiles">
          <tr>
            <td colspan="3">Getting videos please wait...</td>
          </tr>
        </tbody>
      </table>
      <br/>
      <br/>
    </div>
    <div class="tab-pane fade" id="music">
      <br /><br />
      <h4 style="text-align: center;">Downloaded Music</h4>
      <input class="form-control input-sm" id="searchDownloadedMusic" type="text" placeholder="Search...">
      <table style="text-align: left;" class="table table-striped table-hover " id="tableDownloadedMusic">
        <thead>
          <tr>
            <th style="min-width:800px; height:35px">File</th>
            <th style="min-width:80px">Size</th>
            <?php if ($config['allowFileDelete']) : ?>
              <th style="min-width:110px">Actions</th>
            <?php else: ?>
              <th></th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody id="musicfiles">
          <tr>
            <td colspan="3">Getting music please wait...</td>
          </tr>
        </tbody>
      </table>
      <br/>
      <br/>
    </div>
  </div>  
</div>
<script>
  $('#mainnav a').click(function(e) {
    e.preventDefault();
    var id = $(e.target).attr("href").substr(1);
    window.location.hash = id;
    $(this).tab('show');
  });

  var hash = window.location.hash;
  $('#mainnav a[href="' + hash + '"]').tab('show');

  $("#searchDownloadedMusic").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#tableDownloadedMusic tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });

  $("#searchDownloadedVideos").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#tableDownloadedVideos tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });

</script>
 