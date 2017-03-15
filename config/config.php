<?php
return array(
  /**
   * The name of your site. You can specify the name that will be displayed
   * at the top of the website.
   *
   * 'siteName' => 'Youtube-dl-WebUI'
   */
  'siteName' => 'kiot.eu Tube Downloader',
  
  /**
   * The bootswatch theme to be used. You can visit https://bootswatch.com/
   * for more information.
   * Allowed values:
   * 'cerulean','cosmo','cyborg','darkly','flatly','journal',
   * 'lumen','paper','readable','sandstone','simplex','slate',
   * 'spacelab','superhero','united','yeti'
   *
   * 'siteTheme' => 'yeti'
   */
  'siteTheme' => 'flatly',
  
  /**
   * youtube-dl can convert the downloaded videos to audio only.
   * This requires that you have either ffmpeg or avconv installed.
   * If you don't have either of those tools available or you want to
   * disable this feature for performance reasons, set this to true.
   *
   * 'disableExtraction' => false
   */
	'disableExtraction' => false,
  
  /**
   * Set the maximum allowed simultaneous download (i.e. instances
   * of youtube-dl). Set to -1 if you want to disable the limit (not
   * recommended)
   *
   * 'max-dl' => 3
   */
	'max_dl' => 3,

  /**
   * The full absolute path where downloads will be saved to
   * without trailing slash.
   * Make sure that the user running your webserver has write
   * access to this folder
   * 
   * e.g.
   * 'outputFolder' => '/var/www/tubedl/Download'
   */
	'outputFolder' => '/share/Download',
  
  /**
   * The web accessible path to you download folder. This has to be a
   * relative path to the installation of Youtube-dl-webui.
   * If your download folder is not accessible through the web, leave
   * this blank and Youtube-dl-webui will not offer download links.
   *
   * 'downloadPath' => 'download'
   */
  'downloadPath' => 'Download',
   
  /**
   * Specify the tab to redirect to after submitting a download URL.
   * allowed values are: 'downloading','home','vid','music'
   *
   * 'redirectAfterSubmit' => 'downloading'
   */
  'redirectAfterSubmit' => 'downloading',
  
  /**
   * Specify the directory where youtube should log it's output to.
   * This has to be a full absolute path without a trailing slash.
   * The files created by youtube-dl are used to display the progress on the
   * download page.
   * Make sure that the user who is running the webserver has write access
   * to this directory.
   *
   * 'logPath' => '/var/www/tubedl/tmp'
   */
  'logPath' => '/share/Web/tubedl/tmp',
  
  /**
   * Specify the command to run youtube-dl. This has to be the full
   * absolute path to youtube-dl executable. If you are not sure
   * where it is located on your system you can try to run 'which youtube-dl'
   * on the command line. If it is properly installed it should give you back
   * the path where the executable is installed.
   *
   * 'youtubedlExe' => '/usr/bin/youtube-dl'
   */
  'youtubedlExe' => '/opt/bin/youtube-dl'
  );
?>
