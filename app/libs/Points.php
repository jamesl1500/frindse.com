<?php

class Points extends Database
{
    public static function addPoints($amount, $userId)
    {
        if (empty($amount) != true && is_numeric($amount) == true && empty($userId) != true && is_numeric($userId) == true)
        {
            $database = new Database;

            $query = $database->prepare("SELECT points FROM ".USERS." WHERE user_salt='" . $userId . "'");
            $query->execute();

            $fetch = $query->fetch(PDO::FETCH_ASSOC);
            $points = $fetch['points'];

            $total = $points + $amount;

            $query2 = $database->prepare("UPDATE ".USERS." SET points='" . $total . "' WHERE user_id='" . $userId . "'");
            $query2->execute();
        }
    }

    public static function subtractPoints($amount, $userId)
    {
        if (empty($amount) != true && is_numeric($amount) == true && empty($userId) != true && is_numeric($userId) == true)
        {
            $database = new Database;

            $query = $database->prepare("SELECT points FROM ".USERS." WHERE user_salt='" . $userId . "'");
            $query->execute();

            $fetch = $query->fetch(PDO::FETCH_ASSOC);
            $points = $fetch['points'];

            $total = $points - $amount;

            $query2 = $database->prepare("UPDATE ".USERS." SET points='" . $total . "' WHERE user_id='" . $userId . "'");
            $query2->execute();
        }
    }

    public static function getPoints($userId)
    {
        if (empty($userId) != true && is_numeric($userId) == true)
        {
            $database = new Database;
            $query = $database->prepare("SELECT points FROM ".USERS." WHERE user_salt='" . $userId . "'");
            $query->execute();

            $fetch = $query->fetch(PDO::FETCH_ASSOC);
            $points = $fetch['points'];

            return $points;
        }
    }

    public static function givePoints($userTo, $amount)
    {
        if (empty($userTo) != true && is_numeric($userTo) == true && empty($amount) != true && is_numeric($amount) == true)
        {
            // Make sure that the logged user has enough points to give to this person
            if (self::getPoints(Sessions::get("uid")) > $amount)
            {
                self::addPoints($amount, $userTo);
                self::subtractPoints($amount, Sessions::get("uid"));

                echo "Points Given";
            } else {
                echo "Insufficiant Points";
            }
        }
    }
}