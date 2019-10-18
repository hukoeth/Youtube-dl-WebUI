<?php
return array(
  /**
   * The name of your site. You can specify the name that will be displayed
   * at the top of the website.
   *
   * 'siteName' => 'Youtube-dl WebUI'
   */
  'siteName' => 'Youtube-dl WebUI',
  
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
  'siteTheme' => 'yeti',
  
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
   * Specify the tab to redirect to after submitting a download URL.
   * allowed values are: 'downloads','home','videos','music'
   *
   * 'redirectAfterSubmit' => 'downloads'
   */
  'redirectAfterSubmit' => 'downloads',

  /**
   * The full absolute path where downloads will be saved to
   * without trailing slash.
   * Make sure that the user running your webserver has write
   * access to this folder
   * 
   * e.g.
   * 'outputFolder' => '/var/www/tubedl/download'
   */
  'outputFolder' => '/var/www/tubedl/download',
  
  /**
   * The web accessible path to you download folder. This has to be a
   * relative path to the installation of Youtube-dl-webui.
   * If your download folder is not accessible through the web, leave
   * this blank and Youtube-dl-webui will not offer download links.
   * This can be useful if you are running the software on a NAS type device.
   *
   * 'downloadPath' => 'download'
   */
  'downloadPath' => 'download',
  
  /**
   * Specify the tab to redirect to after submitting a download URL.
   * allowed values are: 'downloads','home','videos','music'
   *
   * 'redirectAfterSubmit' => 'downloads'
   */
  'redirectAfterSubmit' => 'downloads',
  
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
  'logPath' => '/var/www/tubedl/tmp',
  
  /**
   * If you the path you have set with logPath is accessible through your webserver,
   * you can specify the relative path without a trailing slash. This will be used
   * to create the links to the logs.
   * If you don't wish to expose the logs, leave this empty
   *
   * 'logURL' => 'logs'
   */
  'logURL' => 'logs',
  
  /**
   * Specify the command to run youtube-dl. This has to be the full
   * absolute path to youtube-dl executable. If you are not sure
   * where it is located on your system you can try to run 'which youtube-dl'
   * on the command line. If it is properly installed it should give you back
   * the path where the executable is installed.
   *
   * 'youtubedlExe' => '/usr/bin/youtube-dl'
   */
  'youtubedlExe' => '/usr/bin/youtube-dl',

  /**
   * Specify if .part files should be kept when cliking on Stop All on
   * the download status page.
   *
   * 'keepPartialFiles' => false
   */
  'keepPartialFiles' => false,

  /**
   * When the simultaneous download limit is reaches, new downloads
   * will be queued. The queued downloads will be processed each
   * time you access the website or if you setup a conjob calling
   * index.php?cron.
   * You can disable this by setting the following option to true.
   * In that case you will get an error when trying to add more
   * downloads after the simultaneous download limit has been reached.
   *
   * 'disableQueue' => false
   */
  'disableQueue' => false,

  /**
   * Specify if users can delete downloaded music and video files 
   *
   * 'allowFileDelete' => true
   */
  'allowFileDelete' => true,

  /**
   * If set to true, the script will output all errors. 
   * DO NOT USE THIS IN PRODUCTION ON OUTSIDE FACING WEBSITES
   *
   * 'debug' => false
   */
  'debug' => false 
  );
?>
