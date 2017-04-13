<?php
/*
 * Users
 * ----
 * YThis will get user info and everything
 */
class Users extends Database
{
    /*
     * Get
     * ----
     * This will get a specific peice of info for a user by its id
     */
    static public function get($table, $salt, $column, $useID = false)
    {
        $db = new Database();

        if ($table != "" && $salt != "" && $column != "") {
            // Fetch whatever

            if ($useID == false){
                $query = $db->prepare("SELECT " . $column . " FROM " . $table . " WHERE user_salt='" . $salt . "'");
                $query->execute();
            }else {
                $query = $db->prepare("SELECT " . $column . " FROM " . $table . " WHERE user_id='" . $salt . "'");
                $query->execute();
            }

            // Check
            return $query->fetch(PDO::FETCH_OBJ)->$column;
        } else {
            die('Missing Data');
        }
    }

    /*
     * Check Exists
     * ----
     * This will check to see if the user exists
     */
    static public function checkExists($uid, $useSalt = false)
    {

        if ($uid != "") {
            $db = new Database;

            // Check status
            if($useSalt == false) 
            {
                $query = $db->prepare("SELECT * FROM " . USERS . " WHERE user_id=:uid");
                $query->execute(array(':uid' => $uid));
            }else{
                $query = $db->prepare("SELECT * FROM " . USERS . " WHERE user_salt=:uid");
                $query->execute(array(':uid' => $uid));
            }

            return $query->rowCount();
        }
    }

    /*
     * Render Profile Pic
     * ----
     * This will help render a persons profile picture
     */
    static public function renderProfilePic($salt, $useThumbnail = 0)
    {
        $db = new Database();

        if(!empty($salt))
        {
            $profilepic = Users::get('users', $salt, 'profile_pic');

            if(file_exists(SITE_ROOT . DS . 'data' . DS . 'user_data' . DS . $salt . DS . 'profile_pictures' . DS . $profilepic)) {
                if($useThumbnail == 0) {
                    return SITE_ROOT . DS . 'data/user_data/' . $salt . '/profile_pictures/' . $profilepic;
                }else{
                    if(file_exists(SITE_ROOT . DS . 'data' . DS . 'user_data' . DS . $salt . DS . 'profile_pictures/thumb' . $useThumbnail . '.' . $profilepic)){
                        return SITE_ROOT . DS . 'data/user_data/' . $salt . '/profile_pictures/thumb' . $useThumbnail . '.' . $profilepic;
                    }else{
                        return SITE_ROOT . DS . 'data/user_data/' . $salt . '/profile_pictures/' . $profilepic;
                    }
                }
            }else{
                return APP_URL . 'user_data/default_pic.jpg';
            }
        }else{
            return APP_URL . 'user_data/default_pic.jpg';
        }
    }

    /*
     * Render Banner Pic
     * ----
     * This will help render a persons profile picture
     */
    static public function renderBannerPic($salt, $useThumbnail = 0)
    {
        $db = new Database();

        if(!empty($salt))
        {
            $profilepic = Users::get('users', $salt, 'banner_pic');

            if(file_exists(SITE_ROOT . DS . 'data' . DS . 'user_data' . DS . $salt . DS . 'banners' . DS . $profilepic)) {
                if($useThumbnail == 0) {
                    return SITE_ROOT . DS . 'data/user_data/' . $salt . '/banners/' . $profilepic;
                }else{
                    if(file_exists(SITE_ROOT . DS . 'data' . DS . 'user_data' . DS . $salt . DS . 'banners/thumb' . $useThumbnail . '.' . $profilepic)){
                        return SITE_ROOT . DS . 'data/user_data/' . $salt . '/banners/thumb' . $useThumbnail . '.' . $profilepic;
                    }else{
                        return SITE_ROOT . DS . 'data/user_data/' . $salt . '/banners/' . $profilepic;
                    }
                }
            }else{
                return APP_URL . 'user_data/default_banner.jpg';
            }
        }else{
            return APP_URL . 'user_data/default_banner.jpg';
        }
    }
}