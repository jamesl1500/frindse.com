<?php
/*
 * Validation Class
 * ----
 * This class will hold the keys to verifying data and making sure its safe
 * and correct
 */
class Validation
{
    static public function santitize($value)
    {
        return $value;
    }

    public static function HumanReadableFileSizeConvert($bytes)
    {

        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }
        return $bytes;
    }

    public static function ComputerReadableFileSize($bytes)
    {

        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2);
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2);
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2);
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes;
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes;
        }
        else
        {
            $bytes = '0';
        }

    }

    public static function GetYoutubeId($data)
    {
        $url = str_replace('&amp;', '&', $data);
        if (strpos($url, 'http://www.youtube.com/embed/') !== false) // If Embed URL
        {
            return str_replace('http://www.youtube.com/embed/', '', $url);
        }
        parse_str(parse_url($url, PHP_URL_QUERY), $array_of_vars);
        $video_id = $array_of_vars['v'];

        if ($video_id != "") {
            // Now get other data
            $youtube = "http://www.youtube.com/oembed?url=" . $data . "&format=json";

            $curl = curl_init($youtube);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $return = curl_exec($curl);
            curl_close($curl);
            $videoData = json_decode($return, true);
        }

        return $video_id;
    }

    static public function RenderLinks($body)
    {
        $pattern = array(
            '/((?:[\w\d]+\:\/\/)?(?:[\w\-\d]+\.)+[\w\-\d]+(?:\/[\w\-\d]+)*(?:\/|\.[\w\-\d]+)?(?:\?[\w\-\d]+\=[\w\-\d]+\&?)?(?:\#[\w\-\d]*)?)/', # URL
            '/([\w\-\d]+\@[\w\-\d]+\.[\w\-\d]+)/', # Email
            '/\[([^\]]*)\]/', # Bold
            '/\{([^}]*)\}/', # Italics
            '/_([^_]*)_/', # Underline
            '/\s{2}/', # Linebreak
        );
        $replace = array(
            '<a href="$1">$1</a>',
            '<a href="mailto:$1">$1</a>',
            '<b>$1</b>',
            '<i>$1</i>',
            '<u>$1</u>',
            '<br />'
        );
        $body = preg_replace($pattern, $replace, $body);
        return $body;
    }

    public static function byte_convert($size) {
        # size smaller then 1kb
        if ($size < 1024) return $size . ' Byte';
        # size smaller then 1mb
        if ($size < 1048576) return sprintf("%4.2f KB", $size/1024);
        # size smaller then 1gb
        if ($size < 1073741824) return sprintf("%4.2f MB", $size/1048576);
        # size smaller then 1tb
        if ($size < 1099511627776) return sprintf("%4.2f GB", $size/1073741824);
        # size larger then 1tb
        else return sprintf("%4.2f TB", $size/1073741824);
    }

    public static function clean_file_name($name)
    {
        if (empty($name) != true) {
            return self::santitize(preg_replace("/(?![.=$'€%-])\p{P}/u", "", $name));
        }
    }

    static public function isEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    static public function passwordEncrypt($value, $type = PASSWORD_HASH_METHOD)
    {
        return password_hash($value, $type);
    }

    static public function randomHash()
    {
        return substr(uniqid(rand(), true), 0, 20);
    }

    static public function encrypt($data)
    {
        return @openssl_encrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, NULL, ENCRYPTION_KEY);
    }

    static public function decrypt($data)
    {
        return @openssl_decrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, NULL, ENCRYPTION_KEY);
    }
}