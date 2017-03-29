<?php
/*
 * Main sesison handle
 * ----
 * This will manage our sessions
 */
class Sessions
{
    static public function initialize()
    {
        session_start();
    }

    static public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    static public function get($key)
    {
        return $_SESSION[$key];
    }

    static public function unset($key)
    {
        unset($_SESSION[$key]);
    }

    static public function unsetAll()
    {
        unset($_SESSION);
    }
}