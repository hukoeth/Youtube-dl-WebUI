<?php if (!isset($GLOBALS['config'])) { die("No direct script access");
} ?>
    <footer class="footer" style="max-height: 100%; overflow-y: auto; position: fixed;bottom: 0;padding: 0;margin: 0;width: 100%;">
            <div class="well text-center" style="padding: 0;margin: 0;">

    <div class="panel panel-info" style="margin: 0;">
      <div onclick="helpPanel()" style="cursor: pointer;" class="panel-heading">
        <h3 id="helplink" class="panel-title">Click here for Info, Help and to add a Download Bookmarklet to your Browser</h3>
      </div>
      <div id="helppanel" class="panel-body panel-collapsed" style="display: none;">
        <table class="table table-striped table-hover" style="text-align: left;">
          <tr>
            <td>
              <b>Free space:</b>
            </td>
            <td>
                <?php echo $file->free_space(); ?>iB
            </td>
          </tr>
          <tr>
            <td>
              <b>Youtube-dl version:</b>
            </td>
            <td>
                <?php $cmd = $GLOBALS['config']['youtubedlExe'];
                $cmd .= " --version";
                passthru($cmd); ?>
            </td>
          </tr>
          <tr>
            <td>
              <b>Download folder:</b>
            </td>
            <td>
                <?php echo $file->get_downloads_folder(); ?>
            </td>
          </tr>
          <tr>
            <td>
              <b>How does it work?</b>
            </td>
            <td>
              Simply paste your video link(s) into the URL field, select the quality or audio format and click "Download". You can also drag the following links to your bookmarks bar and simply click on the bookmark while watching the video you want to download.<br />
              For multiple URL in one paste, URL must be separated by a double pipe: URL1||URL2||URL3<br />
              <a href="<?php echo($bookmarkletvideo); ?>" class="btn btn-default btn-xs">Download Video</a>, <a href="<?php echo($bookmarkletmusic); ?>" class="btn btn-default btn-xs">Download Audio</a> or 
              <button data-toggle="modal" data-target="#custom_bookmarklet" class="btn btn-default btn-xs">Click here to create a custom bookmarklet</button></td>
            </td>
          </tr>
          <tr>
            <td>
              <b>Which sites are supported?</b>
            </td>
            <td>
              <a href="http://rg3.github.io/youtube-dl/supportedsites.html" targe="_blank">Here's</a> a list of all supported sites
            </td>
          </tr>
          <tr>
            <td>
              <b>How can I download the video/audio to my computer?</b>
            </td>
            <td>
              Go to <a href="#vidlist" onclick="$('#vid_link').click()" data-toggle="tab" aria-expanded="false">List of videos</a> or <a href="#songlist" onclick="$('#music_link').click()" data-toggle="tab" aria-expanded="false">List of songs</a> >> choose one >> right click on the link >> "Save target as ..."
            </td>
          </tr>
          <tr>
            <td>
              <b>Where can I get this software?</b>
            </td>
            <td>
              You can download this software for free from <a href="https://github.com/hukoeth/Youtube-dl-WebUI" target="_blank">https://github.com/hukoeth/Youtube-dl-WebUI</a>
            </td>
          </tr>
        </table>
      </div>
    </div>
            </div>
        </footer>
    </body>
</html>
