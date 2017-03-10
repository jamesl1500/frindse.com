<?php
/*
 * Redirect
 * ----
 * This will serve as our main redirect class, for redirecting users
 */
class Redirect
{
    static public function to($type, $path)
    {
        if(!empty($type) && !empty($path))
        {
            self::$type($path);
        }
    }

    private function errors($name)
    {
        header("location: " . APP_URL . 'error/' . $name);

    }

    private function location($path = "")
    {
        header("location: " . APP_URL . $path);
    }
}