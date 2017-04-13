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
    
    static public function getSessionToken()
    {
        return session_id();
    }

    static public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    static public function get($key)
    {
        if(isset($_SESSION[$key])) 
        {
            return $_SESSION[$key];
        }else{
            return false;
        }
    }

    static public function unsetKey($key)
    {
        unset($_SESSION[$key]);
    }

    static public function unsetAll()
    {
        unset($_SESSION);
    }
}