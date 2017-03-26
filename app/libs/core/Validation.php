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
        return strip_tags($value);
    }

    static public function isEmail($value)
    {
        return FILTER_VALIDATE_EMAIL($value);
    }

    static public function passwordEncrypt($value, $type = PASSWORD_HASH_METHOD)
    {
        return password_hash($value, $type);
    }
}