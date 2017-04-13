<?php
class FileReader extends Database
{
    private static $FileUrl;
    private static $FileKey;

    static public function FileExist($url)
    {
        self::$FileUrl = $url;

        if(!empty($url))
        {
            return file_exists(self::$FileUrl);
        }else{
            return false;
        }
    }

    static public function photo($path)
    {
        if(self::FileExist($path))
        {
            // Now just display the image
            header('Content-type: image/jpeg');
            echo file_get_contents($path);
        }else{
            // Means there is no file
            return false;
        }
    }

    static public function video()
    {

    }

    static public function file()
    {

    }

}