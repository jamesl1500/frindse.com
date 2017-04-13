<?php

class Achievement extends Database
{
    private $_max_point_ammount = 50;
    private $_min_point_ammount = 5;

    private $_achievement_types = array('like', 'comment', 'friend', 'personLike');

    /*// ----
        This will display to the user when he has done something that earns them points
    //*/
    static public function newMinorAchievementEvent($uid, $type, $pointAmount, $text = "", $return = true)
    {
        if (!empty($uid) && !empty($pointAmount))
        {
            if (Users::checkExists($uid) == 1)
            {
                if (self::checkAchievementIsValid($type, $pointAmount) == 1)
                {
                    Points::addPoints($pointAmount, Sessions::get('salt'));
                    switch ($type) {
                        case 'personLike':
                            return json_encode(array(
                                'code' => 1,
                                'points' => $pointAmount,
                                'text' => $text,
                                'type' => $type,
                                'icon' => "<i class='fa fa-thumbs-up'></i>"
                            ));
                            break;
                        case 'comment':
                            return json_encode(array(
                                'code' => 1,
                                'points' => $pointAmount,
                                'text' => $text,
                                'type' => $type,
                                'icon' => "<i class='fa fa-comment''></i>"
                            ));
                            break;
                        case 'friend':
                            return json_encode(array(
                                'code' => 1,
                                'points' => $pointAmount,
                                'text' => $text,
                                'type' => $type,
                                'icon' => "<i class='fa fa-user-plus'></i>"
                            ));
                            break;
                        case 'like':
                            return json_encode(array(
                                'code' => 1,
                                'points' => $pointAmount,
                                'text' => $text,
                                'type' => $type,
                                'icon' => "<i class='fa fa-heart'></i>"
                            ));
                            break;
                    }

                    return false;
                } else {
                    if ($return == true)
                    {
                        echo json_encode(array('code' => 0, 'status' => 'There was a problem handling your achievements'));
                        return false;
                    }
                }
            }
        }
    }

    /*//---
        This will check to see if the current achievement is valid
    ----//*/
    static public function checkAchievementIsValid($type, $points)
    {
        if (!empty($type) && !empty($points))
        {
            $db = new Database;

            $query = $db->prepare("SELECT * FROM " . ACHIEVEMENTS_TYPES . " WHERE ach_type=:type");
            $query->execute(array(':type'=>$type));

            if ($query->rowCount() == 1)
            {
                $fetch = $query->fetch(PDO::FETCH_ASSOC);

                if ($type == $fetch['ach_type'])
                {
                    if ($points >= $fetch['min_point_amount'] && $points <= $fetch['max_point_amount']) {
                        return 1;
                    } else {
                        return 0;
                    }
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        }
    }
}

?>