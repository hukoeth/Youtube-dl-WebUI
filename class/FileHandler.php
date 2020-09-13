<?php

class FileHandler
{
    private $videos_ext = "avi|mp4|flv|webm|3gp|mkv";
    private $musics_ext = "mp3|ogg|m4a";
    private $config = [];

    public function __construct()
    {
          $this->config = $GLOBALS['config'];
    }

    public function listVideos()
    {
        $videos = [];

        if(!$this->outuput_folder_exists()) {
            return;
        }

        $folder = $this->get_downloads_folder().'/';

        $Directory = new RecursiveDirectoryIterator($folder, FilesystemIterator::FOLLOW_SYMLINKS);
        $Iterator = new RecursiveIteratorIterator($Directory);
        $Regex = new RegexIterator($Iterator, '/^.*\.('.$this->videos_ext.')$/i', RecursiveRegexIterator::GET_MATCH);

        foreach($Regex as $name => $file){
            $video=[];
            $video["name"] = str_replace($folder, "", $file[0]);
            $video["size"] = $this->to_human_filesize(filesize($file[0]), 1);
            $videos[]=$video;
        }

        usort($videos, function($a, $b) {
            return $a['name'] <=> $b['name'];
        });

        return $videos;
    }

    public function listMusics()
    {
        $musics = [];

        if(!$this->outuput_folder_exists()) {
            return;
        }

        $folder = $this->get_downloads_folder().'/';

        $Directory = new RecursiveDirectoryIterator($folder, FilesystemIterator::FOLLOW_SYMLINKS);
        $Iterator = new RecursiveIteratorIterator($Directory);
        $Regex = new RegexIterator($Iterator, '/^.*\.('.$this->musics_ext.')$/i', RecursiveRegexIterator::GET_MATCH);

        foreach($Regex as $name => $file){
            $music=[];
            $music["name"] = str_replace($folder, "", $file[0]);
            $music["size"] = $this->to_human_filesize(filesize($file[0]), 1);
            $musics[]=$music;
        }

        usort($musics, function($a, $b) {
            return $a['name'] <=> $b['name'];
        });

        return $musics;
    }


    public function delete($id)
    {
        $file = $this->get_downloads_folder().'/'.$id;
        if (file_exists($file)) {
            unlink($file);
        } else {
            $_SESSION['errors'] = "File does not exist";
        }
    }

    private function outuput_folder_exists()
    {
        if(!is_dir($this->get_downloads_folder())) {
            //Folder doesn't exist
            if(!mkdir($this->get_downloads_folder(), 0777)) {
                return false; //No folder and creation failed
            }
        }
        
        return true;
    }

    public function to_human_filesize($bytes, $decimals = 0)
    {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }

    public function free_space()
    {
        return $this->to_human_filesize(disk_free_space($this->get_downloads_folder()));
    }

    public function get_downloads_folder()
    {
        $path = $this->config["outputFolder"];
        if(strpos($path, "/") !== 0) {
            $path = dirname(__DIR__).'/' . $path;
        }
        return $path;
    }

    public function get_downloads_link()
    {
        $path = $this->config["downloadPath"];
        return $path;
    }
}

?>
