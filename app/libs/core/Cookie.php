<?php
/*
 * Cookies
 * ----
 * This will create, delete and do other things with cookies
 */
class Cookie
{
    static public function set($name, $value, $time = '', $directory = '/', $domain = NULL, $ssl = NULL)
    {
        setcookie($name, $value, $time, $directory, $domain, $ssl, true);
    }

    static public function update($key, $value)
    {
        setcookie($key, $value);
    }

    static public function delete($key)
    {
        unset($_COOKIE[$key]);
        @setcookie($key, '', time()-1);
    }
}