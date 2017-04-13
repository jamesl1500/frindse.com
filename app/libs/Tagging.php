<?php
/*
 * Tagging
 * ----
 * This will handle everything with tagging and hastags
 */
class Tagging extends Database
{
    public $delimeter = "#";

    /*
     * Parse Text
     * ----
     * This will parse a specific piece of text and convert hashtags or user tags to HTML
     */
    static public function parseText($text)
    {
        $arr = explode(" ", $text);
        $arrc = count($arr);
        $i = 0;
        $tags = array();

        while ($i < $arrc) {
            if (substr($arr[$i], 0, 1) === "#") {
                $url = $arr[$i];
                $url = preg_replace("/<br\W*?\/>/", "", $arr[$i]);

                $vowels = array("#");
                $onlyconsonants = str_replace($vowels, "", $url);
                $onlyconsonants = preg_replace("/<br\W*?\/>/", " ", $onlyconsonants);
                $arr[$i] = "<a href='" . APP_URL . "search/" . $onlyconsonants . "'>" . $arr[$i] . "</a>";
            } else if (substr($arr[$i], 0, 1) === "@") {
                $url = $arr[$i];
                $url = preg_replace("/<br\W*?\/>/", "", $arr[$i]);

                $vowels = array("@");
                $onlyconsonants = str_replace($vowels, "", $url);
                $onlyconsonants = preg_replace("/<br\W*?\/>/", " ", $onlyconsonants);
                $arr[$i] = "<a href='" . APP_URL . "profile/" . $onlyconsonants . "'>" . $arr[$i] . "</a>";
            }
            $i++;
        }
        $text = implode(" ", $arr);
        return $text;
    }

    /*
     * getHashTags
     * ----
     * This will get all of the hashtags from a specific piece of text
     */
    static public function getHashTags($text)
    {
        $arr = explode(" ", $text);
        $arrc = count($arr);
        $i = 0;
        $tags = array();

        while ($i < $arrc) {
            if (substr($arr[$i], 0, 1) === "#") {
                $tags[] = $arr[$i];
            }
            $i++;
        }
        return $tags;
    }

    /*
     * getUserTags
     * ----
     * This will get all of the specific user tags from a piece of text
     */
    static public function getUserTags($text)
    {
        $arr = explode(" ", $text);
        $arrc = count($arr);
        $i = 0;
        $tags = array();

        while ($i < $arrc) {
            if (substr($arr[$i], 0, 1) === "@") {
                $url = $arr[$i];
                $vowels = array("@");
                $onlyconsonants = str_replace($vowels, "", $url);
                $tags[] = $onlyconsonants;
            }
            $i++;
        }
        return $tags;
    }

    /*
     * This will notify of people who got tagged
     */
    static public function notifyTaggedUsers($utd, $ut, $ub, $id)
    {
        $db = new Database;

        if (count($utd) > 0) {
            $arrc = count($utd);
            $i = 0;

            foreach ($utd as $ud) {
                $c = $db->prepare("SELECT * FROM " . USERS . " WHERE username=:username");
                $c->execute(array(':username'=>$ud));

                $v = $c->fetch(PDO::FETCH_ASSOC);
                $j = $v['user_id'];

                if ($c->rowCount() > 0 && $_SESSION['uid'] != $j) {
                    // Make the note
                    $g = 'has tagged you in a <a href="' . APP_URL . 'posts/' . $id . '">post</a>';
                    Notifications::makeNote('tagging', $j, $g);
                    $firstname = User::get("users", $j, "first_name");
                    $email = User::get("users", $j, "email");

                    $f = User::get("users", $ub, "first_name");
                    $l = User::get("users", $ub, "last_name");
                    $username = User::get("users", $ub, "username");

                    $body_e = "
					<html>
					<head>
						<link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=Roboto:400,100,300,500,700,900' />
					</head>
					<body style='height: 500px;'>
						<div class='email-container'>
							<div class='email-head' style='border-bottom: 1px solid #ddd; width: 60%; margin: 0 auto;'>
								<center><h1 style='font-size: 32px;'>" . SITE_NAME . "</h1> </center>
								<h2 style='font-weight: 100; font-size: 25px; text-align: center;'>Hey there " . $firstname . "</h2>
							</div>
							<div class='inner-email'>
								<p style='text-align: center; color: #ccc; font-weight: 300; font-size: 20px;'><a style='text-decoration: none; color: #4aaee7;' href='" . APP_URL . "profile/" . $username . "'>" . ucwords($f) . " " . ucwords($l) . "</a> has tagged you in a post</p>
								<br /><br />
								<center><a style='color: white; background:#2ecc71;border-radius: 5px; border: 1px solid transparent; padding-right: 20px; font-size: 24px; padding-left: 20px; padding-top: 15px; padding-bottom: 15px;height: 90px;text-decoration: none;text-align: center;' href='" . APP_URL . "posts/" . $id . "'>View the post here</a></center>
							</div>
						</div>
					</body>
					</html>
					";
                    Notifications::emailNotification($ub, $ut, 'Someone has tagged you in a post', 'post_tags', $body_e);
                }
            }
        }
    }

    /*
     * updateHastag
     * ----
     * @array $tagz - This must be an array of hashtags
     *
     * This will update a specific hashtag
     */
    static public function updateHashtag($tagz)
    {
        $db = new Database;

        if (count($tagz) > 0)
        {
            foreach ($tagz as $tag)
            {
                // Now see if you can insert the new hashtag, but if not just find that hashtag and add a number to it
                $check_hash = $db->prepare("SELECT * FROM " . HASHTAGS . " WHERE hashtag_name=:tag");
                if($check_hash->execute(array(':tag'=>$tag)))
                {
                    if ($check_hash->rowCount() > 0)
                    {
                        // means that hashtag already exist so just update it
                        $hf = $check_hash->fetch(PDO::FETCH_ASSOC);
                        $upd = $hf['used'] + 1;
                        $update_hash = $db->prepare("UPDATE " . HASHTAGS . " SET used=:used WHERE hashtag_name=:name");
                        $update_hash->execute(array(':used'=>$upd, ':name'=>$tag));
                    } else {
                        // Means it dosent exist, then insert it into there
                        $insert_hash = $db->prepare("INSERT INTO " . HASHTAGS . " VALUES('',:tag,'1')");
                        $insert_hash->execute(array(':tag'=>$tag));
                    }
                }
            }
        }
    }
}

?>