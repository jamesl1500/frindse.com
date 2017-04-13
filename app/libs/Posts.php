<?php
/*
 * Posts
 * ----
 * This will handle everything when it comes to posts
 */
class Posts
{
    public $privacy = array('1','2', '3', '4');
    public $likeTypes = array('like', 'unlike');

    /*
     * countComments
     * ----
     * This will send back the number of comments for a specific post
     */
    static public function countComments($unique_id)
    {
        if(!empty($unique_id))
        {
            $db = new Database;

            $commentGetNum = $db->prepare("SELECT * FROM " . TIMELINE_POST_COMMENTS . " WHERE timeline_post_unique_id=:unique_id");
            $commentGetNum->execute(array(":unique_id"=>$unique_id));

            return $commentGetNum->rowCount();
        }
    }

    /*
     * displayComments
     * ----
     * This will display all of the comments
     */
    static public function displayComments($timelineId, $limit = 5, $displayViewAll = "yes")
    {

    }

    /*
     * makeComment
     * ----
     * This will display all of the comments
     */
    static public function makeComment($data)
    {
        if(!empty($data))
        {
            
        }
    }

    /*
     * countPostLikes
     * ----
     * This will count the number of likes a post has
     */
    static public function countPostLikes($unique_id)
    {
        if(!empty($unique_id))
        {
            $db = new Database;

            $likesNumber = $db->prepare("SELECT * FROM " . TIMELINE_POST_LIKES . " WHERE unique_id=:unique_id");
            $likesNumber->execute(array(':unique_id'=>$unique_id));

            return $likesNumber->rowCount();
        }
    }

    /*
     * getPostAuthor
     * ----
     * This will get the author of the specified post
     */
    static public function getPostAuthor($pid)
    {
        if (!empty($pid))
        {
            if ($pid != "")
            {
                // Call
                $db = new Database;

                // Fetch
                $get = $db->prepare("SELECT * FROM " . TIMELINE_ITEM . " WHERE unique_id=:pid");
                $get->execute(array(':pid'=>$pid));

                // Fetch
                $fetch = $get->fetch(PDO::FETCH_ASSOC);

                return $fetch['user_by'];
            }
        }
    }

    /*
     * likePosts
     * ----
     * This will handle the post likings
     */
    public function likePost($data)
    {
        if(!empty($data))
        {
            $id = $data['pid'];
            $tag = $data['tag'];

            if(!empty($id) && !empty($tag) && in_array($tag, $this->likeTypes))
            {
                $db = new Database;

                // Amount
                $number = self::countPostLikes($id);

                if (self::checkPostLikeStatus($id, Sessions::get('salt')) == 0)
                {
                    $insert = $db->prepare("INSERT INTO " . TIMELINE_POST_LIKES . " VALUES('', :uid, :unique_id, now())");
                    $insert->execute(array(':uid'=> Sessions::get('salt'), ':unique_id'=>$id));

                    $u = self::getPostAuthor($id);

                    // Send Note
                    if ($u != Sessions::get('salt')) {
                        Notifications::makeNote('post-like', $u, "has liked your <a href=''" . APP_URL . "posts/" . $id . "''>post</a>");
                        $firstname = Users::get(USERS, $u, "firstname");
                        $email = Users::get(USERS, $u, "email");
                        $username2 = Users::get(USERS, $u, "username");

                        $f = Users::get(USERS, Sessions::get('salt'), "firstname");
                        $l = Users::get(USERS, Sessions::get('salt'), "lastname");
                        $username = Users::get(USERS, Sessions::get('salt'), "username");

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
											<p style='text-align: center; color: #ccc; font-weight: 300; font-size: 20px;'><a style='text-decoration: none; color: #4aaee7;' href='" . APP_URL . "profile/" . $username . "'>" . ucwords($f) . " " . ucwords($l) . "</a> has liked your post</p>
											<br /><br />
											<center><a style='color: white; background:#2ecc71;border-radius: 5px; border: 1px solid transparent; padding-right: 20px; font-size: 24px; padding-left: 20px; padding-top: 15px; padding-bottom: 15px;height: 90px;text-decoration: none;text-align: center;' href='" . APP_URL . "posts/" . $id . "'>View the post here</a></center>
										</div>
									</div>
								</body>
								</html>
								";
                        Notifications::emailNotification(Sessions::get('salt'), $u, 'Someone has liked your post', 'post_like', $body_e);
                        Notifications::makeTimelineActivityPost(Sessions::get('salt'), 'postLike', 'has liked <a href="' . APP_URL . 'profile/' . $username2 . '">' . $username2 . '</a> <a href="' . APP_URL . 'posts/' . $id . '">post</a>', $u, $id);

                        Achievement::newMinorAchievementEvent($u, 'like', '5', '', false);
                        $logged_achievements = json_decode(Achievement::newMinorAchievementEvent(Sessions::get('salt'), 'like', '5', 'You have earned 5 points!', true));

                        $response = array(
                            'code' => 1,
                            'status' => $number + 1,
                            'isThereAchievement' => 1,
                            'achievements' => $logged_achievements,
                            'sendTo'=>$u,
                            'sendToSessionId'=>Sessions::get('sess_token'),
                            'fromSalt'=>Sessions::get('salt'),
                            'fromFirstname'=>$f,
                            'fromLastname'=>$l,
                            'fromUsername'=>$username
                        );
                        echo json_encode($response);
                        return false;
                    } else {
                        $response = array(
                            'code' => 1,
                            'status' => $number + 1,
                            'isThereAchievement' => 0,
                            'achievements' => ''
                        );
                        echo json_encode($response);
                        return false;
                    }
                } else {
                    $response = array('code' => 0,'status' => 'You have already liked this post!');
                    echo json_encode($response);
                    return false;
                }
            }else{
                echo Response::make("Invalid Request");
                return false;
            }
        }
    }

    /*
     * unlikePosts
     * ----
     * This will handle the post likings (Unlikes)
     */
    public function unlikePost($data)
    {
        if(!empty($data))
        {
            $id = $data['pid'];
            $tag = $data['tag'];

            if(!empty($id) && !empty($tag) && in_array($tag, $this->likeTypes))
            {
                $db = new Database;

                // Amount
                $number = self::countPostLikes($id);

                $delete = $db->prepare("DELETE FROM " . TIMELINE_POST_LIKES . " WHERE unique_id=:unique_id AND user_id=:uid");
                $delete->execute(array(':unique_id'=>$id, ':uid'=>Sessions::get('salt')));

                $response = array(
                    'code' => 1,
                    'status' => $number - 1,
                    'isThereAchievement' => 0,
                );
                Points::subtractPoints('5', Sessions::get('salt'));
                echo json_encode($response);
            }else{
                echo Response::make("Invalid Request");
                return false;
            }
        }
    }

    /*
     * checkPostLikeStatus
     * ----
     * This will check to see if the suer has reposted this post before
     */
    static public function checkPostLikeStatus($unique_id, $uid)
    {
        if(!empty($unique_id) && !empty($uid))
        {
            $db = new Database();

            // Do the query thing
            $query = $db->prepare("SELECT * FROM " . TIMELINE_POST_LIKES . " WHERE unique_id=:unique_id AND user_id=:uid");
            $query->execute(array(':unique_id'=>$unique_id, ':uid'=>$uid));

            return $query->rowCount();
        }
    }

    /*
     * getReportedPosts
     * ----
     * This will return all of the posts the specific user reported
     */
    static public function getReportedPosts($uid)
    {
        if(!empty($uid))
        {
            // Make array
            $reported = array();

            // Do stuff
            $db = new Database;

            $query = $db->prepare("SELECT * FROM " . POST_REPORTS. " WHERE user_id=:user_id");
            $query->execute(array(':user_id'=>$uid));
            
            if($query->rowCount() > 0)
            {
                while($fetch = $query->fetch(PDO::FETCH_ASSOC))
                {
                    $reported[] = $fetch['post_unique_id'];
                }
            }
            
            return $reported;
        }
    }

    /*
     * checkPostPrivacy
     * ----
     * This will check the privacy of a specific post
     */
    static public function checkPostPrivacy($unique_id)
    {
        if(!empty($unique_id))
        {
            // Instantiate
            $db = new Database;

            // Query
            $query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM . " WHERE unique_id=:unique_id");
            $query->execute(array(':unique_id'=>$unique_id));

            // Fetch
            $fetch = $query->fetch(PDO::FETCH_ASSOC);
            $privacy = $fetch['privacy'];
            $user_by = $fetch['user_by'];
            $posted_to = $fetch['user_posted_to'];

            // Render
            if (Sessions::get('salt') == false) {
                if ($privacy == 2 or $privacy == 3 or $privacy == 4) {
                    return 0;
                } else if ($privacy == 1) {
                    return 1;
                }
            } else if (Sessions::get('salt') != false) {
                if ($privacy == 1) {
                    return 1;
                } else if ($privacy == 2) {
                    if($user_by != Sessions::get('salt')) {
                        if ($posted_to == Sessions::get('salt')) {
                            return 1;
                        } else {
                            // Check to see if logged user is the profile persons friend
                            $check = Friends::checkFriendshipStatus(array('person1' => $user_by, 'person2' => Sessions::get('salt'), 'check' => 'friendship'));

                            // Render
                            if ($check == 1) {
                                // Means they are friends
                                return 1;
                            } else if ($check == 0) {
                                return 0;
                            }
                        }
                    }else if($user_by == Sessions::get('salt')){
                        return 1;
                    }
                }else if($privacy == 3)
                {
                    if($user_by != Sessions::get('salt')) {
                        return 0;
                    }else if($user_by == Sessions::get('salt') ){
                        return 1;
                    }
                }else if($privacy == 4)
                {
                    // Means this is between two people and only the user_by and user_to person
                    if($user_by == Sessions::get('salt') or $posted_to == Sessions::get('salt'))
                    {
                        return 1;
                    }else{
                        return 0;
                    }
                }
            }
        }else{
            return 1;
        }
    }

    /*
     * renderButtonsForPosts
     * ----
     * This will render all of the buttons for a post
     */
    static public function renderButtonsForPosts($unique_id, $user_by, $likecount, $commentCount)
    {
        if (Sessions::get('salt'))
        {
            $db = new Database;

            // Render post like button
            if(Sessions::get('salt') != false)
            {
                if (self::checkPostLikeStatus($unique_id, Sessions::get('salt')) == 1)
                {
                    ?>
                    <a style="color: #e74c3c;" class='likePostBtn<?php echo $unique_id; ?> unlikeBTN postLikeListener' data-type='unlike' data-pid='<?php echo $unique_id; ?>'><i class="fa fa-fw fa-heart"></i> Unlike</a>
                    <a style="color: #999;" class='likePostBtn<?php echo $unique_id; ?> postLikeListener hidden' data-type='like' data-pid='<?php echo $unique_id; ?>'><i class="fa fa-fw fa-heart"></i> Like</a>
                    <?php
                } else {
                    ?>
                    <a style="color: #999;" class='likePostBtn<?php echo $unique_id; ?> postLikeListener' data-type='like' data-pid='<?php echo $unique_id; ?>'><i class="fa fa-fw fa-heart"></i> Like</a>
                    <a style="color: #e74c3c;" class='likePostBtn<?php echo $unique_id; ?> unlikeBTN postLikeListener hidden' data-type='unlike' data-pid='<?php echo $unique_id; ?>'><i class="fa fa-fw fa-heart"></i> Unlike</a>
                    <?php
                }
            }
            
            // Render comment button
            if(Sessions::get('salt') != false)
            {
                ?>
                    &middot; <a class='commentMaker' data-id='<?php echo $unique_id; ?>'>Comment</a>
                <?php
            }

            // Render repost button
            if(Sessions::get('salt') != false)
            {
                if(self::getPostAuthor($unique_id) != Sessions::get('salt'))
                {
                    ?>
                    <span>
                    <?php
                    if (self::checkIfRepostedBefore($unique_id, Sessions::get('salt')) == 1)
                    {
                        ?>
                        &middot; <a class='repostMaker' data-type="undoRepost" data-id='<?php echo $unique_id; ?>' style="color: #2ecc71;"><i class="fa fa-retweet"></i> UnRepost</a>
                        <a class='repostMaker hidden' data-type="doRepost" data-id='<?php echo $unique_id; ?>'><i class="fa fa-retweet"></i> Repost</a>
                        <?php
                    } else {
                        ?>
                        &middot; <a class='repostMaker hidden' data-type="undoRepost" data-id='<?php echo $unique_id; ?>' style="color: #2ecc71;"><i class="fa fa-retweet"></i> UnRepost</a>
                        <a class='repostMaker' data-type="doRepost" data-id='<?php echo $unique_id; ?>'><i class="fa fa-retweet"></i> Repost</a>
                        <?php
                    }
                    ?>
                    </span>
                    <?php
                }
            }
        }
    }

    /*
     * checkIfRepostedBefore
     * ----
     * This will check to see if the suer has reposted this post before
     */
    static public function checkIfRepostedBefore($unique_id, $uid)
    {
        if(!empty($unique_id) && !empty($uid))
        {
            $db = new Database();

            // Do the query thing
            $query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM_SHARE . " WHERE original_id=:original_id AND reposter=:uid");
            $query->execute(array(':original_id'=>$unique_id, ':uid'=>$uid));

            return $query->rowCount();
        }
    }

    /*
     * makeTextPost
     * ----
     * This will only create text type posts
     */
    public function makeTextPost($data)
    {
        if(!empty($data))
        {
            $userTo = Validation::santitize($data['userTo']);
            $postBody = nl2br(Validation::santitize($data['postBody']));
            $privacy = $data['privacy'];
            
            $pd2 = 0;

            // Create the unique_id
            $encryption = md5(Validation::randomHash() . rand(100, 100000));

            // Make sure this user exists!
            if(Users::checkExists($userTo, true) == 1)
            {
                // Now get hashtags and user tags and then update the hashtags then notify the users thats been tagged
                $hashtags = Tagging::getHashTags($postBody);
                $tagged = Tagging::getUserTags($postBody);
                
                // Now update the hashtags and update the users
                Tagging::updateHashtag($hashtags);
                Tagging::notifyTaggedUsers($tagged, $userTo, Sessions::get('salt'), $encryption);

                // Make sure the post body has links
                $postBody = Validation::RenderLinks($postBody);

                // Make sure the correct privacy is there
                if(!empty($postBody) && in_array($privacy, $this->privacy))
                {
                    $db = new Database;

                    $insert = $db->prepare("INSERT INTO " . TIMELINE_ITEM . " VALUES('', :encryption, :userby, :userto, 'text', :privacy, :hashtags, :tagged, :date,'0')");
                    if($insert->execute(array(':encryption'=>$encryption, ':userby'=>Sessions::get('salt'), ':userto'=>$userTo, ':privacy'=>$privacy, ':hashtags'=>json_encode($hashtags), ':tagged'=>json_encode($tagged), ':date'=>date("y:m:d H:i:s"))))
                    {
                        $insert2 = $db->prepare("INSERT INTO " . TIMELINE_ITEM_TEXT . " VALUES('', :encryption, :body)");
                        $insert2->execute(array(':encryption'=>$encryption, ':body' => stripslashes(utf8_encode($postBody))));

                        if ($userTo != Sessions::get('salt')) {
                            $pd2 = 1;
                            $up = "&lt;div class=''&gt;&lt;a style='' href='" . APP_URL . "profile/" . Users::get(USERS, Sessions::get('salt'), 'username') . "'&gt;&lt;h3 style='float: left;font-size: 22px;'&gt;" . ucwords(Users::get(USERS, Sessions::get('salt'), 'firstname')) . " " . ucwords(Users::get(USERS, Sessions::get('salt'), 'firstname')) . "&lt;/h3&gt;&gt;/a&lt; &gt;span style='float: left;padding-left: 5px;padding-top: 3px;padding-right: 5px;'> >> </span> <a style='padding-top: 5px;' href='" . APP_URL . "profile/" . Users::get(USERS, $userTo, 'username') . "'><h3 style='padding-top: 2px;'>" . ucwords(Users::get(USERS, Sessions::get('salt'), 'firstname')) . " " . ucwords(Users::get(USERS, Sessions::get('salt'), 'lastname')) . "</h3></a></div>";
                            Notifications::makeTimelineActivityPost(Sessions::get('salt'), 'postToUserProfile', "has <a href='" . APP_URL . "posts/" . $encryption . "'>posted</a> something on <a href='" . APP_URL . "profile/" . Users::get(USERS, $userTo, 'firstname') . "'>" . Users::get(USERS, $userTo, 'firstname') . " " . Users::get(USERS, $userTo, 'firstname') . "''s profile</a>", Sessions::get('salt'), $encryption);
                        } else {
                            $pd2 = 0;
                            $up = "&lt;a style='font-size: 22px;' href='" . APP_URL . "profile/" . Users::get(USERS, Sessions::get('salt'), 'username') . "'&gt;&lt;h3&gt;" . ucwords(Users::get(USERS, Sessions::get('salt'), 'firstname')) . " " . ucwords(Users::get(USERS, Sessions::get('salt'), 'lastname')) . " &lt;/h3&gt;&lt;/a&gt;";
                        }

                        $response = array();
                        $response['code'] = 1;

                        $response['post_data'] = array('unique_id' => $encryption,'date_posted' => 'Just Now', 'postBody' => Tagging::parseText($postBody), 'postHead' => $pd2);
                        $response['user_data'] = array('user_by' => array('uid' => Sessions::get('salt'), 'first_name' => ucwords(Users::get(USERS, Sessions::get('salt'), 'firstname')), 'last_name' => ucwords(Users::get(USERS, Sessions::get('salt'), 'lastname')), 'username' => Users::get(USERS, Sessions::get('salt'), 'username'), 'profile_picture' => "" . APP_URL . "users/data/" . Users::get(USERS, Sessions::get('salt'), 'user_salt') . "/profile_picture"));
                        if ($pd2 == 1) {
                            $response['user_posted_to'] = array('posted_to' => array('uid' => $userTo,'first_name' => ucwords(Users::get(USERS, $userTo, 'firstname')), 'last_name' => ucwords(Users::get(USERS, $userTo, 'lastname')), 'username' => Users::get(USERS, $userTo, 'username'), 'salt' => Users::get(USERS, $userTo, 'user_salt'),));
                        }

                        echo json_encode($response);
                        return false;
                    }else
                    {
                        echo Response::make(MESSAGE_SQL_ERROR);
                        return false;
                    }
                }
            }else
            {
                echo Response::make(MESSAGE_USER_NO_EXIST);
                return false;
            }
        }else
        {
            echo Response::make("Oops, post cant be empty!");
            return false;
        }
    }

    public function loadTimeline($data)
    {
        $db = new Database;

        if (!empty($data))
        {
            // Initiate vars
            $uid = $data['uid'];
            $postnumbers = $data['postnumbers'];
            $offset = $data['offset'];
            $return = $data['return'];
            $u = Sessions::get('salt');

            if($return == "json"){
                $jsonReturn['posts'] = array();
            }

            // Get all of the logged users friends
            $all_friends = Friends::getFriendsArray($u);
            array_push($all_friends, Sessions::get('salt'));
            
            // Get reported posts
            $reported = self::getReportedPosts(Sessions::get('salt'));

            $items_load = 15;

            // Get the posts
            $getPosts = $db->prepare("SELECT * FROM " . TIMELINE_ITEM . " WHERE user_by IN ('".implode("','", $all_friends)."')  AND unique_id NOT IN ('".implode("','", $reported)."') AND type !='activity' ORDER BY id DESC LIMIT " . $postnumbers . " OFFSET " . $offset);
            $getPosts->execute();

            if ($getPosts->rowCount() != 0)
            {
                while ($pf = $getPosts->fetch(PDO::FETCH_ASSOC))
                {
                    $id = $pf['id'];
                    $unique_id = $pf['unique_id'];
                    $user_by = $pf['user_by'];
                    $user_posted_to = $pf['user_posted_to'];
                    $type = $pf['type'];
                    $date = $pf['date'];

                    // Get the user_by persons data
                    $user_get = $db->prepare("SELECT * FROM " . USERS . " WHERE user_salt=:user_by AND activated='1'");
                    $user_get->execute(array(':user_by'=>$user_by));

                    $u = $user_get->fetch(PDO::FETCH_ASSOC);
                    $salt2 = $u['user_salt'];

                    // Get the number of comments
                    $commentGetNum = self::countComments($unique_id);

                    // Get number of likes
                    $likesNumber = self::countPostLikes($unique_id);

                    // Display the different posts
                    if($type == "repost")
                    {
                        // Okay now get the info for this item
                        $get_data_query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM_SHARE . " WHERE unique_id='" . $unique_id . "'");
                        $get_data_query->execute();

                        // Fetch
                        $df = $get_data_query->fetch(PDO::FETCH_ASSOC);
                        $did = $df['id'];
                        $reposter = $df['reposter'];
                        $og_id = $df['original_id'];

                        // Now get actual data
                        $g = $db->prepare("SELECT * FROM " . TIMELINE_ITEM . " WHERE unique_id='".$og_id."'");
                        $g->execute();

                        if($reposter != $_SESSION['uid'])
                        {
                            $fetch = $g->fetch(PDO::FETCH_ASSOC);
                            $rtype = $fetch['type'];
                            $og_userby = $fetch['user_by'];

                            if($og_userby != $_SESSION['uid']) {

                                //print_r(Repost::GetFriendsWhoRepostedThisPost($og_id, $_SESSION['uid'], Repost::GetRepostUserIds($og_id)));

                                $user_get2 = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $fetch['user_by'] . "' AND account_locked='unlocked' AND activated='1'");
                                $user_get2->execute();
                                $u2 = $user_get2->fetch(PDO::FETCH_ASSOC);
                                $salt = $u2['unique_salt_id'];

                                $commentGetNum2 = $db->prepare("SELECT * FROM " . TIMELINE_POST_COMMENTS . " WHERE timeline_post_unique_id='" . $og_id . "'");
                                $commentGetNum2->execute();

                                $likesNumber2 = $db->prepare("SELECT * FROM " . TIMELINE_POST_LIKES . " WHERE unique_id='" . $og_id . "'");
                                $likesNumber2->execute();

                                switch ($rtype) {
                                    case 'clique-share':
                                        $get_data_query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM_CLIQUE_SHARE . " WHERE unique_id='" . $og_id . "'");
                                        $get_data_query->execute();

                                        // Fetch
                                        $df = $get_data_query->fetch(PDO::FETCH_ASSOC);
                                        $did = $df['id'];
                                        $body = $df['clique_unique_id'];
                                        $clique_id = $df['body'];

                                        $get_clique_data = $db->prepare("SELECT * FROM " . CLIQUES . " WHERE c_unique_id='" . $clique_id . "'");
                                        $get_clique_data->execute();

                                        if ($get_clique_data->rowCount() == 1) {
                                            // Display
                                            // See if the logged person is friends with the user by
                                            if (isset($_SESSION['uid'])) {
                                                $fi = Friends::checkFriendshipStatus(array(
                                                    'person1' => $user_by,
                                                    'person2' => $_SESSION['uid'],
                                                    'check' => 'friendship'
                                                ));

                                                if ($fi == 1) {
                                                    $fidd = "<font color='#2ecc71'>Friends</font>";
                                                } else {
                                                    $fidd = "<font color='#ccc'>Not Friends</font>";
                                                }
                                            } else {
                                                $fidd = "<font color='#ccc'>No logged in user</font>";
                                            }

                                            if ($user_by != $user_posted_to) {
                                                // If this was posted to a different user
                                                // Get the user_by persons data
                                                $user_get3 = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $user_posted_to . "' AND account_locked='unlocked' AND activated='1'");
                                                $user_get3->execute();
                                                $u3 = $user_get3->fetch(PDO::FETCH_ASSOC);
                                                $up = "<div class='clearfix'><h3 style='font-size: 16px;'><a style='' href='" . APP_URL . "profile/" . $u2['username'] . "'>" . ucwords($u2['first_name']) . " " . ucwords($u2['last_name']) . "</a> » <a style='padding-top: 5px;' href='" . APP_URL . "profile/" . $u3['username'] . "'>" . ucwords($u3['first_name']) . " " . ucwords($u3['last_name']) . "</a></h3></div>";
                                            } else {
                                                $up = "<a style='display: inline;' href='" . APP_URL . "profile/" . $u2['username'] . "'><h3 style='font-weight: 400;font-size: 18px;'>" . ucwords($u2['first_name']) . " " . ucwords($u2['last_name']) . " </h3></a>";
                                            }

                                            if (Report::checkStatus($_SESSION['uid'], $unique_id) == 0) {
                                                ?>
                                                <div class='timeline-item photo-post post'
                                                     id='post-<?php echo $og_id; ?>' post-id='<?php echo $id; ?>'>
                                                    <div class="wasRepostedBy"
                                                         style="padding: 20px;padding-bottom: 5px;">
                                                        <p style="margin: 0px;padding: 0px;color: #aaa;"><font
                                                                color="#2ecc71"><i class="fa fa-retweet"></i></font>
                                                            Reposted by <a
                                                                href="<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>"><?php echo $u['first_name']; ?> <?php echo $u['last_name']; ?></a>
                                                        </p>
                                                    </div>
                                                    <div class="top_timeline_photo_mass">
                                                        <div class="top_user_info">
                                                            <div class="ppic">
                                                                <img
                                                                    src='<?php echo APP_URL; ?>user_data/<?php echo $u2['username']; ?>/profile_pictures/<?php echo $u2['profile_pic']; ?>'/>
                                                            </div>
                                                            <div class="every_thing_other" style="font-weight: 400;">
                                                                <?php echo $up; ?>
                                                                <font color="#AAAAAA"><i class="fa fa-clock-o"></i>
                                                                    Posted <?php echo Convert::convert_time($date); ?>
                                                                </font>
                                                                <?php if ($_SESSION['uid'] == $user_by) { ?>
                                                                    &middot; <a href="" onClick="return false;"
                                                                                class="postDropDown"
                                                                                id="postDropDownMaker<?php echo $og_id; ?>"
                                                                                data-x="<?php echo $og_id; ?>"
                                                                                style="font-size: 20px;color: #aaa;padding-top: 8px;"
                                                                                title="Edit Post!"><i
                                                                            class="fa fa-caret-down"></i></a>
                                                                    <div class="postDropDownCont"
                                                                         id="postDropDownCont<?php echo $og_id; ?>"
                                                                         style="display: none;">
                                                                        <ul id="holderc<?php echo $og_id; ?>">
                                                                            <li class="startEditingMaker"
                                                                                id='liBtnAcess<?php echo $og_id; ?>'
                                                                                data-x="<?php echo $og_id; ?>"><i
                                                                                    class="fa fa-pencil-square-o"></i>
                                                                                Edit Post
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class='inner_timeline_post'>
                                                        <p class='postTextBody' id="postBody<?php echo $og_id; ?>"
                                                           style='padding-left: 5px;margin: 10px;margin-left: 0px;font-weight: 400;'><?php echo Hashtags::parseText($body); ?></p>
                                                    </div>
                                                    <?php
                                                    $fetch_clique_data = $get_clique_data->fetch(PDO::FETCH_ASSOC);
                                                    $c_id = $fetch_clique_data['c_id'];
                                                    $c_name = $fetch_clique_data['c_name'];
                                                    $c_username = $fetch_clique_data['c_username'];
                                                    $c_bio = $fetch_clique_data['c_bio'];
                                                    $c_profile_pic = $fetch_clique_data['c_profile_pic'];
                                                    $c_banner_pic = $fetch_clique_data['c_banner_pic'];
                                                    $c_privacy = $fetch_clique_data['c_privacy'];
                                                    $c_created_date = $fetch_clique_data['c_created_date'];
                                                    $c_active = $fetch_clique_data['c_active'];
                                                    $c_unique_id = $fetch_clique_data['c_unique_id'];

                                                    $get30 = $db->prepare("SELECT * FROM " . CLIQUE_MEMBERS . " WHERE c_main_unique_id='" . $c_unique_id . "' AND c_main_id='" . $c_id . "' AND c_m_priv='founder'");
                                                    $get30->execute();
                                                    $gf = $get30->fetch(PDO::FETCH_ASSOC);
                                                    $user_id3 = $gf['c_m_user_id'];
                                                    ?>
                                                    <div class="clique_view_access"
                                                         style="width: 100%;min-height: 70px;padding: 0px;background: url(<?php echo APP_URL; ?>clique_data/<?php echo $c_unique_id; ?>/clique_banners/<?php echo $c_banner_pic; ?>);">
                                                        <div class="container_wrap"
                                                             style="min-height: 70px;background: rgba(0,0,0,.5);padding: 5px;">
                                                            <div class="clique_pic_left"
                                                                 style="float: left;padding-right: 5px;">
                                                                <img
                                                                    src='<?php echo APP_URL; ?>clique_data/<?php echo $c_unique_id; ?>/clique_profile_pic/<?php echo $c_profile_pic; ?>'
                                                                    style="border-radius: 50%;height: 60px;width:60px;"/>
                                                            </div>
                                                            <div class="rightClique">
                                                                <h3 style="margin-left: 5px;"><a
                                                                        href="<?php echo APP_URL; ?>clique/<?php echo $c_username; ?>"><?php echo $c_name; ?></a>
                                                                </h3>

                                                                <p style="color: #fff;padding: 5px;margin: 0px;"><?php echo $c_bio; ?></p>
                                                                <a style="color: #4aaee7;padding-top: 2px;"
                                                                   href="<?php echo APP_URL; ?>profile/<?php echo ucwords(User::get('users', $user_id3, 'username')); ?>"><img
                                                                        src="<?php echo APP_URL; ?>user_data/<?php echo User::get('users', $user_id3, 'unique_salt_id'); ?>/profile_pictures/<?php echo User::get('users', $user_id3, 'profile_pic'); ?>"
                                                                        style="border-radius: 50%;height: 20px;width: 20px;"/>
                                                                <span
                                                                    style="position: relative;top: -5px;color: white;"><?php echo ucwords(User::get('users', $user_id3, 'username')); ?>
                                                                    (Founder)</span></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class='botton_timeline_post'>
                                                        <?php if (isset($_SESSION['uid'])) { ?>
                                                            <?php
                                                            // Render post like button
                                                            $postLikeCheck = $db->prepare("SELECT * FROM " . TIMELINE_POST_LIKES . " WHERE user_id='" . $_SESSION['uid'] . "' AND unique_id='" . $og_id . "'");
                                                            $postLikeCheck->execute();

                                                            if ($postLikeCheck->rowCount() == 1) {
                                                                // Means person has liked this post
                                                                ?>
                                                                <a style="color: #e74c3c;"
                                                                   class='likePostBtn<?php echo $og_id; ?> postLikeListener'
                                                                   data-type='unlike'
                                                                   data-pid='<?php echo $og_id; ?>'><i
                                                                        class="fa fa-heart"></i> <span
                                                                        class='countHolder<?php echo $og_id; ?>'><?php echo $likesNumber2->rowCount(); ?></span></a>
                                                                <a style="color: #999;"
                                                                   class='likePostBtn<?php echo $og_id; ?> postLikeListener hidden'
                                                                   data-type='like' data-pid='<?php echo $og_id; ?>'><i
                                                                        class="fa fa-heart"></i> <span
                                                                        class='countHolder<?php echo $og_id; ?>'><?php echo $likesNumber2->rowCount(); ?></span></a>
                                                                <?php
                                                            } else {
                                                                ?>
                                                                <a style="color: #999;"
                                                                   class='likePostBtn<?php echo $og_id; ?> postLikeListener'
                                                                   data-type='like' data-pid='<?php echo $og_id; ?>'><i
                                                                        class="fa fa-heart"></i> <span
                                                                        class='countHolder<?php echo $og_id; ?>'><?php echo $likesNumber2->rowCount(); ?></span></a>
                                                                <a style="color: #e74c3c;"
                                                                   class='likePostBtn<?php echo $og_id; ?> postLikeListener hidden'
                                                                   data-type='unlike'
                                                                   data-pid='<?php echo $og_id; ?>'><i
                                                                        class="fa fa-heart"></i> <span
                                                                        class='countHolder<?php echo $og_id; ?>'><?php echo $likesNumber2->rowCount(); ?></span></a>
                                                                <?php
                                                            }
                                                            ?> &middot; <a class='commentMaker'
                                                                           data-id='<?php echo $og_id; ?>'><i
                                                                    class="fa fa-comment"></i> <?php echo $commentGetNum->rowCount(); ?>
                                                            </a> &middot;

                                                            <span>
                                                            <?php if ($_SESSION['uid'] != $fetch['user_by']) { ?>
                                                                <?php if (Repost::CheckIfRepostedBefore($og_id, $_SESSION['uid']) == 1) { ?>
                                                                    <a class='repostMaker' data-type="undoRepost"
                                                                       data-id='<?php echo $og_id; ?>'
                                                                       style="color: #2ecc71;"><i
                                                                            class="fa fa-retweet"></i> <?php echo self::countReposts($og_id); ?>
                                                                    </a>
                                                                    <a class='repostMaker hidden' data-type="doRepost"
                                                                       data-id='<?php echo $og_id; ?>'><i
                                                                            class="fa fa-retweet"></i> <?php echo self::countReposts($og_id); ?>
                                                                    </a>
                                                                <?php } else { ?>
                                                                    <a class='repostMaker hidden' data-type="undoRepost"
                                                                       data-id='<?php echo $og_id; ?>'
                                                                       style="color: #2ecc71;"><i
                                                                            class="fa fa-retweet"></i> <?php echo self::countReposts($og_id); ?>
                                                                    </a>
                                                                    <a class='repostMaker' data-type="doRepost"
                                                                       data-id='<?php echo $og_id; ?>'><i
                                                                            class="fa fa-retweet"></i> <?php echo self::countReposts($og_id); ?>
                                                                    </a>
                                                                <?php } ?>
                                                            <?php } ?>
                                                </span>
                                                            <?php if ($user_by == $_SESSION['uid']) { ?> <a
                                                                style='float: right;' data-pid='<?php echo $og_id; ?>'
                                                                class='postRemove'><i class="fa fa-trash-o"></i>
                                                                Delete</a> <?php } else { ?> <a style='float: right;'
                                                                                                data-pid='<?php echo $og_id; ?>'
                                                                                                class='postReport'><i
                                                                    class="fa fa-flag"></i></a> <?php } ?>
                                                        <?php } else { ?>
                                                            <span
                                                                style='color: #ccc;'>Login to like, comment and enjoy this post</span>
                                                        <?php } ?>
                                                    </div>
                                                    <?php Comments::displayComments($og_id); ?>
                                                </div>
                                                <?php
                                            }
                                        }
                                        break;
                                    case 'text':
                                        $get_data_query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM_TEXT . " WHERE unique_id='" . $og_id . "'");
                                        $get_data_query->execute();

                                        // Fetch
                                        $df = $get_data_query->fetch(PDO::FETCH_ASSOC);
                                        $did = $df['id'];
                                        $body = $df['postBody'];

                                        // Display
                                        // See if the logged person is friends with the user by
                                        if (isset($_SESSION['uid'])) {
                                            $fi = Friends::checkFriendshipStatus(array(
                                                'person1' => $user_by,
                                                'person2' => $_SESSION['uid'],
                                                'check' => 'friendship'
                                            ));

                                            if ($fi == 1) {
                                                $fidd = "<font color='#2ecc71'>Friends</font>";
                                            } else {
                                                $fidd = "<font color='#ccc'>Not Friends</font>";
                                            }
                                        } else {
                                            $fidd = "<font color='#ccc'>No logged in user</font>";
                                        }

                                        if ($user_by != $user_posted_to) {
                                            // If this was posted to a different user
                                            // Get the user_by persons data
                                            $user_get3 = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $user_posted_to . "' AND account_locked='unlocked' AND activated='1'");
                                            $user_get3->execute();
                                            $u3 = $user_get3->fetch(PDO::FETCH_ASSOC);
                                            $pid = 1;
                                            $up = "<div class='clearfix'><h3 style='font-size: 16px;'><a style='' href='" . APP_URL . "profile/" . $u2['username'] . "'>" . ucwords($u2['first_name']) . " " . ucwords($u2['last_name']) . "</a> » <a style='padding-top: 5px;' href='" . APP_URL . "profile/" . $u3['username'] . "'>" . ucwords($u3['first_name']) . " " . ucwords($u3['last_name']) . "</a></h3></div>";
                                        } else {
                                            $pid = 0;
                                            $up = "<a style='display: inline;' href='" . APP_URL . "profile/" . $u2['username'] . "'><h3 style='font-weight: 400;font-size: 18px;'>" . ucwords($u2['first_name']) . " " . ucwords($u2['last_name']) . " </h3></a>";
                                        }

                                        $report = 0;
                                        if (isset($_SESSION['uid'])) {
                                            $report = Report::checkStatus($_SESSION['uid'], $unique_id);
                                        }
                                        if ($report == 0) {
                                            if($return == "html"){
                                                ?>
                                                <div class='timeline-item clearfix post animate bounceIn' id='post-<?php echo $og_id; ?>' post-id='<?php echo $og_id; ?>'>
                                                    <div class="whoRepostedThis clearfix" style="padding: 20px;padding-bottom: 5px;">
                                                        <p><font color="#2ecc71"><i class="fa fa-retweet"></i></font> Reposted by <a href="<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>"><?php echo $u['first_name']; ?> <?php echo $u['last_name']; ?></a></p>
                                                    </div>
                                                    <div class="topPostAlways clearfix">
                                                        <div class="topAuthorPortion">
                                                            <div class="authorProfilePic" style="background-image: url(<?php echo User::renderProfilePic($u2['user_id'], 80); ?>);"></div>
                                                            <div class="rightAuthorInfo">
                                                                <?php echo $up; ?>
                                                                <h3><?php echo Convert::convert_time($date); ?></h3>
                                                            </div>
                                                        </div>
                                                        <div class="postTextBody">
                                                            <p class='postTextBody' id="postBody<?php echo $og_id; ?>" style='padding-left: 5px;margin: 10px;margin-left: 0px;'><?php echo Hashtags::parseText($body); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="actionsHolder">
                                                        <?php TimelinePostHandler::renderButtonsForPosts($og_id, $u2['user_id'], $likesNumber2->rowCount(), $commentGetNum2->rowCount()); ?>
                                                    </div>
                                                    <div class="extrasHolder">

                                                    </div>
                                                    <div class="bottomPostAssets">
                                                        <div class="postStatsTop">
                                                            <ul>

                                                                <li><span class='fspan'><i class="fa fa-heart"></i></span> <span class='sspan'><?php echo $likesNumber2->rowCount(); ?></span></li>
                                                                <li><span class='fspan'><i class="fa fa-commenting"></i></span> <span class='sspan'><?php echo $commentGetNum2->rowCount(); ?></span></li>

                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }else if($return == "json") {
                                                $jsonReturn['posts'][$unique_id]['user_data'] = array(
                                                    'user_id' => $u2['user_id'],
                                                    'username' => $u2['username'],
                                                    'firstname' => $u2['first_name'],
                                                    'lastname' => $u2['last_name'],
                                                    'profilepic' => User::renderProfilePic($u2['user_id'], 80),
                                                );
                                                if ($pid == 1) {
                                                    $jsonReturn['posts'][$unique_id]['user_posted_to'] = array(
                                                        'username' => $u3['username'],
                                                        'firstname' => $u3['first_name'],
                                                        'lastname' => $u3['last_name'],
                                                    );
                                                }
                                                $jsonReturn['posts'][$unique_id]['user_reposted_by'] = array(
                                                    'username' => $u['username'],
                                                    'firstname' => $u['first_name'],
                                                    'lastname' => $u['last_name'],
                                                );
                                                $jsonReturn['posts'][$unique_id]['post_data'] = array(
                                                    'postId' => $unique_id,
                                                    'postType' => $type,
                                                    'postDate' => Convert::convert_time($date),
                                                    'postText' => Hashtags::parseText($body),
                                                    'postAuthor' => $u['user_id'],
                                                    'pid' => $pid,
                                                    'loggeduserProfile' => User::renderProfilePic($_SESSION['uid'], 80),
                                                    'loggedUserPost' => $_SESSION['uid'],
                                                    'likeCount' => $likesNumber->rowCount(),
                                                    'commentCount' => $commentGetNum->rowCount(),
                                                    'shareCount' => self::countReposts($unique_id),
                                                    'hasReposted' => Repost::CheckIfRepostedBefore($unique_id, $_SESSION['uid']),
                                                    'hasLiked' => self::checkLikeStatusMain($unique_id, $_SESSION['uid']),
                                                    'isRepost' => 1,
                                                    'comments' => Comments::displayCommentsInJson($unique_id)

                                                );
                                            }
                                        }
                                        break;
                                    case 'photo':
                                        $get_data_query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM_PHOTO . " WHERE unique_id='" . $og_id . "'");
                                        $get_data_query->execute();

                                        // Fetch
                                        $df = $get_data_query->fetch(PDO::FETCH_ASSOC);
                                        $did = $df['id'];
                                        $body = $df['photoTitle'];
                                        $photoLink = $df['photoFileLocation'];

                                        // Display
                                        // See if the logged person is friends with the user by
                                        $photos = json_decode($photoLink, true);

                                        if ($user_by != $user_posted_to) {
                                            // If this was posted to a different user
                                            // Get the user_by persons data
                                            $user_get3 = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $user_posted_to . "' AND account_locked='unlocked' AND activated='1'");
                                            $user_get3->execute();
                                            $u3 = $user_get3->fetch(PDO::FETCH_ASSOC);
                                            $up = "<div class='clearfix'><h3 style='font-size: 20px;'><a style='' href='" . APP_URL . "profile/" . $u2['username'] . "'>" . ucwords($u2['first_name']) . " " . ucwords($u2['last_name']) . "</a> » <a style='padding-top: 5px;' href='" . APP_URL . "profile/" . $u3['username'] . "'>" . ucwords($u3['first_name']) . " " . ucwords($u3['last_name']) . "</a></h3></div>";
                                        } else {
                                            $up = "<a style='' href='" . APP_URL . "profile/" . $u2['username'] . "'><h3 style='font-size: 22px;'>" . ucwords($u2['first_name']) . " " . ucwords($u2['last_name']) . " </h3></a>";
                                        }

                                        $report = 0;
                                        if (isset($_SESSION['uid'])) {
                                            $report = Report::checkStatus($_SESSION['uid'], $og_id);
                                        }
                                        if ($report == 0) {
                                            if($return == "html"){
                                                ?>
                                                <div class='timeline-item clearfix post animate bounceIn' id='post-<?php echo $og_id; ?>' post-id='<?php echo $og_id; ?>'>
                                                    <div class="whoRepostedThis clearfix" style="padding: 20px;padding-bottom: 5px;">
                                                        <p><font color="#2ecc71"><i class="fa fa-retweet"></i></font> Reposted by <a href="<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>"><?php echo $u['first_name']; ?> <?php echo $u['last_name']; ?></a></p>
                                                    </div>
                                                    <div class="topPostAlways clearfix">
                                                        <div class="topAuthorPortion">
                                                            <div class="authorProfilePic" style="background-image: url(<?php echo User::renderProfilePic($u2['user_id'], 80); ?>);"></div>
                                                            <div class="rightAuthorInfo">
                                                                <?php echo $up; ?>
                                                                <h3><?php echo Convert::convert_time($date); ?></h3>
                                                            </div>
                                                        </div>
                                                        <div class="postTextBody">
                                                            <p class='postTextBody' id="postBody<?php echo $og_id; ?>" style='padding-left: 5px;margin: 10px;margin-left: 0px;'><?php echo Hashtags::parseText($body); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="actionsHolder">
                                                        <?php TimelinePostHandler::renderButtonsForPosts($og_id, $u2['user_id'], $likesNumber2->rowCount(), $commentGetNum2->rowCount()); ?>
                                                    </div>
                                                    <div class="extrasHolder">
                                                        <div class="photos_actual">
                                                            <?php
                                                            foreach ($photos as $p) {
                                                                ?>
                                                                <img class="item" data-x="<?php echo $unique_id; ?>" data-f="<?php echo $u['first_name']; ?>" src="<?php echo APP_URL; ?>public/user_data/<?php echo $salt2; ?>/photos/<?php echo $p; ?>" style="cursor: pointer;max-height: 1100px;"/>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                    <div class="bottomPostAssets">
                                                        <div class="postStatsTop">
                                                            <ul>

                                                                <li><span class='fspan'><i class="fa fa-heart"></i></span> <span class='sspan'><?php echo $likesNumber2->rowCount(); ?></span></li>
                                                                <li><span class='fspan'><i class="fa fa-commenting"></i></span> <span class='sspan'><?php echo $commentGetNum2->rowCount(); ?></span></li>

                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="shade shader<?php echo $og_id; ?>"></div>
                                                <div class="modal" id="photoHolder<?php echo $og_id; ?>">
                                                    <div class="modal-container ui-widget-content draggable">
                                                        <div class="modal-inner">
                                                            <div class="modal-header clearfix">
                                                                <h3><img
                                                                        src='<?php echo APP_URL; ?>user_data/<?php echo $u2['unique_salt_id']; ?>/profile_pictures/<?php echo $u2['profile_pic']; ?>'/>
                                                                    <a href='<?php echo APP_URL; ?>profile/<?php echo $u2['username']; ?>'><?php echo ucwords($u2['first_name']); ?>
                                                                        's photo</a></h3> <span class="closeModal"
                                                                                                data-x="<?php echo $og_id; ?>"
                                                                                                style="cursor: pointer;float: right;color: white;font-weight:400;font-size:22px;padding:10px;">&times;</span>
                                                            </div>
                                                            <div class="modal-image-holder"
                                                                 id="holder<?php echo $og_id; ?>">
                                                                <img src=""/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php
                                            }else if($return == "json") {
                                                foreach ($photos as $p) {
                                                    $photosToSend[] = APP_URL . "public/user_data/" . $salt2 . "/photos/" . $p;
                                                }

                                                $jsonReturn['posts'][$unique_id]['user_data'] = array(
                                                    'user_id' => $u['user_id'],
                                                    'username' => $u['username'],
                                                    'firstname' => $u['first_name'],
                                                    'lastname' => $u['last_name'],
                                                    'profilepic' => User::renderProfilePic($u['user_id'], 80),
                                                );
                                                $jsonReturn['posts'][$unique_id]['user_reposted_by'] = array(
                                                    'username' => $u['username'],
                                                    'firstname' => $u['first_name'],
                                                    'lastname' => $u['last_name'],
                                                );
                                                if ($pid == 1) {
                                                    $jsonReturn['posts'][$unique_id]['user_posted_to'] = array(
                                                        'username' => $u2['username'],
                                                        'firstname' => $u2['first_name'],
                                                        'lastname' => $u2['last_name'],
                                                    );
                                                }
                                                $jsonReturn['posts'][$unique_id]['post_data'] = array(
                                                    'postId' => $unique_id,
                                                    'postType' => $type,
                                                    'postDate' => Convert::convert_time($date),
                                                    'postText' => Hashtags::parseText($body),
                                                    'postAuthor' => $u['user_id'],
                                                    'pid' => $pid,
                                                    'loggeduserProfile' => User::renderProfilePic($_SESSION['uid'], 80),
                                                    'loggedUserPost' => $_SESSION['uid'],
                                                    'likeCount' => $likesNumber->rowCount(),
                                                    'commentCount' => $commentGetNum->rowCount(),
                                                    'shareCount' => self::countReposts($unique_id),
                                                    'hasReposted' => Repost::CheckIfRepostedBefore($unique_id, $_SESSION['uid']),
                                                    'hasLiked' => self::checkLikeStatusMain($unique_id, $_SESSION['uid']),
                                                    'isRepost' => 1,
                                                    'comments' => Comments::displayCommentsInJson($unique_id),
                                                    'postPhotos' => $photosToSend


                                                );
                                            }
                                        }
                                        break;
                                    case 'video':
                                        $get_data_query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM_VIDEO . " WHERE unique_id='" . $og_id . "'");
                                        $get_data_query->execute();

                                        // Fetch
                                        $df = $get_data_query->fetch(PDO::FETCH_ASSOC);
                                        $did = $df['id'];
                                        $body = $df['body'];
                                        $videoLink = $df['videoLink'];

                                        // Display
                                        // See if the logged person is friends with the user by
                                        if (isset($_SESSION['uid'])) {
                                            $fi = Friends::checkFriendshipStatus(array(
                                                'person1' => $user_by,
                                                'person2' => $_SESSION['uid'],
                                                'check' => 'friendship'
                                            ));

                                            if ($fi == 1) {
                                                $fidd = "<font color='#2ecc71'>Friends</font>";
                                            } else {
                                                $fidd = "<font color='#ccc'>Not Friends</font>";
                                            }
                                        } else {
                                            $fidd = "<font color='#ccc'>No logged in user</font>";
                                        }

                                        if ($user_by != $user_posted_to) {
                                            // If this was posted to a different user
                                            // Get the user_by persons data
                                            $user_get3 = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $user_posted_to . "' AND account_locked='unlocked' AND activated='1'");
                                            $user_get3->execute();
                                            $u3 = $user_get3->fetch(PDO::FETCH_ASSOC);
                                            $up = "<div class='clearfix'><h3 style='font-size: 20px;font-weight: 400;'><a style='' href='" . APP_URL . "profile/" . $u2['username'] . "'>" . ucwords($u2['first_name']) . " " . ucwords($u2['last_name']) . "</a> » <a style='padding-top: 5px;' href='" . APP_URL . "profile/" . $u3['username'] . "'>" . ucwords($u3['first_name']) . " " . ucwords($u3['last_name']) . "</a></h3></div>";
                                        } else {
                                            $up = "<a style='' href='" . APP_URL . "profile/" . $u2['username'] . "'><h3 style='font-size: 22px;font-weight: 400;'>" . ucwords($u2['first_name']) . " " . ucwords($u2['last_name']) . " </h3></a>";
                                        }

                                        $report = 0;
                                        if (isset($_SESSION['uid'])) {
                                            $report = Report::checkStatus($_SESSION['uid'], $og_id);
                                        }
                                        if ($report == 0) {
                                            if($return == "html"){
                                                ?>
                                                <div class='timeline-item clearfix post animate bounceIn' id='post-<?php echo $og_id; ?>' post-id='<?php echo $og_id; ?>'>
                                                    <div class="whoRepostedThis clearfix" style="padding: 20px;padding-bottom: 5px;">
                                                        <p><font color="#2ecc71"><i class="fa fa-retweet"></i></font> Reposted by <a href="<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>"><?php echo $u['first_name']; ?> <?php echo $u['last_name']; ?></a></p>
                                                    </div>
                                                    <div class="topPostAlways clearfix">
                                                        <div class="topAuthorPortion">
                                                            <div class="authorProfilePic" style="background-image: url(<?php echo User::renderProfilePic($u2['user_id'], 80); ?>);"></div>
                                                            <div class="rightAuthorInfo">
                                                                <?php echo $up; ?>
                                                                <h3><?php echo Convert::convert_time($date); ?></h3>
                                                            </div>
                                                        </div>
                                                        <div class="postTextBody">
                                                            <p class='postTextBody' id="postBody<?php echo $og_id; ?>" style='padding-left: 5px;margin: 10px;margin-left: 0px;'><?php echo Hashtags::parseText($body); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="actionsHolder">
                                                        <?php TimelinePostHandler::renderButtonsForPosts($og_id, $u2['user_id'], $likesNumber2->rowCount(), $commentGetNum2->rowCount()); ?>
                                                    </div>
                                                    <div class="extrasHolder">
                                                        <div class="youtube" id="<?php echo $videoLink; ?>" style="width: 100%; height: 320px;border: none;"></div>
                                                        <script src="<?php echo APP_URL; ?>js/youtube.js"></script>
                                                    </div>
                                                    <div class="bottomPostAssets">
                                                        <div class="postStatsTop">
                                                            <ul>

                                                                <li><span class='fspan'><i class="fa fa-heart"></i></span> <span class='sspan'><?php echo $likesNumber2->rowCount(); ?></span></li>
                                                                <li><span class='fspan'><i class="fa fa-commenting"></i></span> <span class='sspan'><?php echo $commentGetNum2->rowCount(); ?></span></li>

                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }else if($return == "json") {
                                                $jsonReturn['posts'][$unique_id]['user_data'] = array(
                                                    'user_id' => $u['user_id'],
                                                    'username' => $u['username'],
                                                    'firstname' => $u['first_name'],
                                                    'lastname' => $u['last_name'],
                                                    'profilepic' => User::renderProfilePic($u['user_id'], 80),
                                                );
                                                if ($pid == 1) {
                                                    $jsonReturn['posts'][$unique_id]['user_posted_to'] = array(
                                                        'username' => $u2['username'],
                                                        'firstname' => $u2['first_name'],
                                                        'lastname' => $u2['last_name'],
                                                    );
                                                }
                                                $jsonReturn['posts'][$unique_id]['user_reposted_by'] = array(
                                                    'username' => $u['username'],
                                                    'firstname' => $u['first_name'],
                                                    'lastname' => $u['last_name'],
                                                );
                                                $jsonReturn['posts'][$unique_id]['post_data'] = array(
                                                    'postId' => $unique_id,
                                                    'postType' => $type,
                                                    'postDate' => Convert::convert_time($date),
                                                    'postText' => Hashtags::parseText($body),
                                                    'postAuthor' => $u['user_id'],
                                                    'pid' => $pid,
                                                    'loggedUserPost' => $_SESSION['uid'],
                                                    'likeCount' => $likesNumber->rowCount(),
                                                    'commentCount' => $commentGetNum->rowCount(),
                                                    'shareCount' => self::countReposts($unique_id),
                                                    'hasReposted' => Repost::CheckIfRepostedBefore($unique_id, $_SESSION['uid']),
                                                    'hasLiked' => self::checkLikeStatusMain($unique_id, $_SESSION['uid']),
                                                    'isRepost' => 1,
                                                    'comments' => Comments::displayCommentsInJson($unique_id),
                                                    'postVideo' => $videoLink


                                                );
                                            }
                                        }
                                        break;
                                }
                            }
                        }
                    }else{
                        switch ($type) {
                            case 'clique-post':
                                if($return == "html") {
                                    Clique::renderPost($unique_id, 'all', $return);
                                }else{
                                    $jsonReturn = Clique::renderPost($unique_id, 'all', $return, $jsonReturn);
                                }
                                break;
                            case 'clique-share':
                                $get_data_query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM_CLIQUE_SHARE . " WHERE unique_id='" . $unique_id . "'");
                                $get_data_query->execute();

                                // Fetch
                                $df = $get_data_query->fetch(PDO::FETCH_ASSOC);
                                $did = $df['id'];
                                $body = $df['clique_unique_id'];
                                $clique_id = $df['body'];

                                $get_clique_data = $db->prepare("SELECT * FROM " . CLIQUES . " WHERE c_unique_id='" . $clique_id . "'");
                                $get_clique_data->execute();

                                if ($get_clique_data->rowCount() == 1) {
                                    if ($user_by != $user_posted_to) {
                                        // If this was posted to a different user
                                        // Get the user_by persons data
                                        $user_get2 = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $user_posted_to . "' AND account_locked='unlocked' AND activated='1'");
                                        $user_get2->execute();
                                        $u2 = $user_get2->fetch(PDO::FETCH_ASSOC);
                                        $pid = 1;
                                        $up = "<a style='' href='" . APP_URL . "profile/" . $u['username'] . "'>" . ucwords($u['first_name']) . " " . ucwords($u['last_name']) . "</a> » <a style='padding-top: 5px;' href='" . APP_URL . "profile/" . $u2['username'] . "'>" . ucwords($u2['first_name']) . " " . ucwords($u2['last_name']) . "</a>";
                                    } else {
                                        $pid = 0;
                                        $up = "<a style='display: inline;' href='" . APP_URL . "profile/" . $u['username'] . "'>" . ucwords($u['first_name']) . " " . ucwords($u['last_name']) . "</a>";
                                    }

                                    if (Report::checkStatus($_SESSION['uid'], $unique_id) == 0) {
                                        if($return == "html") {
                                            ?>
                                            <div class='timeline-item clearfix post animate bounceIn' id='post-<?php echo $unique_id; ?>' post-id='<?php echo $unique_id; ?>'>
                                                <div class="whoRepostedThis clearfix" style="">
                                                    <p><font color="#2574A9"><i class="fa fa-users"></i></font> Invited you to join a clique</p>
                                                </div>
                                                <div class="topPostAlways clearfix">
                                                    <div class="topAuthorPortion">
                                                        <div class="authorProfilePic" style="background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);"></div>
                                                        <div class="rightAuthorInfo">
                                                            <?php echo $up; ?>
                                                            <h3><?php echo Convert::convert_time($date); ?></h3>
                                                        </div>
                                                    </div>
                                                    <div class="postTextBody">
                                                        <p class='postTextBody' id="postBody<?php echo $unique_id; ?>" style='padding-left: 5px;margin: 10px;margin-left: 0px;'><?php echo Hashtags::parseText($body); ?></p>
                                                    </div>
                                                </div>
                                                <div class="extrasHolder">
                                                    <?php
                                                    $fetch_clique_data = $get_clique_data->fetch(PDO::FETCH_ASSOC);
                                                    $c_id = $fetch_clique_data['c_id'];
                                                    $c_name = $fetch_clique_data['c_name'];
                                                    $c_username = $fetch_clique_data['c_username'];
                                                    $c_bio = $fetch_clique_data['c_bio'];
                                                    $c_profile_pic = $fetch_clique_data['c_profile_pic'];
                                                    $c_banner_pic = $fetch_clique_data['c_banner_pic'];
                                                    $c_privacy = $fetch_clique_data['c_privacy'];
                                                    $c_created_date = $fetch_clique_data['c_created_date'];
                                                    $c_active = $fetch_clique_data['c_active'];
                                                    $c_unique_id = $fetch_clique_data['c_unique_id'];

                                                    $get30 = $db->prepare("SELECT * FROM " . CLIQUE_MEMBERS . " WHERE c_main_unique_id='" . $c_unique_id . "' AND c_main_id='" . $c_id . "' AND c_m_priv='founder'");
                                                    $get30->execute();
                                                    $gf = $get30->fetch(PDO::FETCH_ASSOC);
                                                    $user_id3 = $gf['c_m_user_id'];
                                                    ?>
                                                    <div class="clique_view_access"
                                                         style="width: 100%;min-height: 100px;padding: 00px;background: url(<?php echo APP_URL; ?>clique_data/<?php echo $c_unique_id; ?>/clique_banners/<?php echo $c_banner_pic; ?>);">
                                                        <div class="container_wrap"
                                                             style="min-height: 70px;background: rgba(0,0,0,.5);padding: 20px;">
                                                            <div class="clique_pic_left"
                                                                 style="float: left;padding-right: 5px;">
                                                                <img
                                                                    src='<?php echo APP_URL; ?>clique_data/<?php echo $c_unique_id; ?>/clique_profile_pic/<?php echo $c_profile_pic; ?>'
                                                                    style="border-radius: 50%;height: 60px;width:60px;"/>
                                                            </div>
                                                            <div class="rightClique" style="margin-left: 10px;">
                                                                <h3 style="margin-left: 15px;"><a
                                                                        href="<?php echo APP_URL; ?>clique/<?php echo $c_username; ?>"><?php echo $c_name; ?></a>
                                                                </h3>

                                                                <p style="color: #fff;padding: 8px;margin: 0px;"><?php echo $c_bio; ?></p>
                                                                <a style="color: #4aaee7;padding-top: 2px;"
                                                                   href="<?php echo APP_URL; ?>profile/<?php echo ucwords(User::get('users', $user_id3, 'username')); ?>"><img
                                                                        src="<?php echo APP_URL; ?>user_data/<?php echo User::get('users', $user_id3, 'unique_salt_id'); ?>/profile_pictures/<?php echo User::get('users', $user_id3, 'profile_pic'); ?>"
                                                                        style="border-radius: 50%;height: 20px;width: 20px;"/>
                                                                <span
                                                                    style="position: relative;top: -5px;color: white;"><?php echo ucwords(User::get('users', $user_id3, 'username')); ?>
                                                                    (Founder)</span></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="actionsHolder" style="padding-top: 20px;">
                                                    <?php TimelinePostHandler::renderButtonsForPosts($unique_id, $u['user_id'], $likesNumber->rowCount(), $commentGetNum->rowCount()); ?>
                                                </div>
                                                <div class="bottomPostAssets">
                                                    <div class="postStatsTop">
                                                        <ul>

                                                            <li><span class='fspan likeCH'><i class="fa fa-heart"></i></span> <span class='sspan likeCH countHolder<?php echo $unique_id; ?>' id=""><font color="#e74c3c"><?php echo $likesNumber->rowCount(); ?></font></span></li>
                                                            <li><span class='fspan'><i class="fa fa-commenting"></i></span> <span class='sspan '><?php echo $commentGetNum->rowCount(); ?></span></li>

                                                        </ul>
                                                    </div>
                                                    <div class="commentArea">
                                                        <?php Comments::displayComments($unique_id); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }else if($return == "json"){
                                            $jsonReturn['posts'][$unique_id]['post_chat_data'] = array(

                                            );
                                            $jsonReturn['posts'][$unique_id]['user_data'] = array(
                                                'user_id' => $u['user_id'],
                                                'username' => $u['username'],
                                                'firstname' => $u['first_name'],
                                                'lastname' => $u['last_name'],
                                                'profilepic' => User::renderProfilePic($u['user_id'], 80),
                                            );
                                            if($pid == 1)
                                            {
                                                $jsonReturn['posts'][$unique_id]['user_posted_to'] = array(
                                                    'username' => $u2['username'],
                                                    'firstname' => $u2['first_name'],
                                                    'lastname' => $u2['last_name'],
                                                );
                                            }
                                            $jsonReturn['posts'][$unique_id]['post_data'] = array(
                                                'postId' => $unique_id,
                                                'postType' => $type,
                                                'postDate' => Convert::convert_time($date),
                                                'postText' => Hashtags::parseText($body),
                                                'postAuthor' => $u['user_id'],
                                                'pid' => $pid,
                                                'loggeduserProfile' => User::renderProfilePic($_SESSION['uid'], 80),
                                                'loggedUserPost' => $_SESSION['uid'],
                                                'likeCount' => $likesNumber->rowCount(),
                                                'commentCount' => $commentGetNum->rowCount(),
                                                'shareCount' => self::countReposts($unique_id),
                                                'hasReposted' => Repost::CheckIfRepostedBefore($unique_id, $_SESSION['uid']),
                                                'hasLiked' => self::checkLikeStatusMain($unique_id, $_SESSION['uid']),
                                                'isRepost' => '0',
                                                'comments' => Comments::displayCommentsInJson($unique_id)

                                            );

                                        }
                                    }
                                }
                                break;
                            case 'chat_invite':
                                $get_data_query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM_CHAT_INVITE . " WHERE unique_id='" . $unique_id . "'");
                                $get_data_query->execute();

                                // Fetch
                                $df = $get_data_query->fetch(PDO::FETCH_ASSOC);
                                $did = $df['id'];
                                $chat_hash = $df['chat_hash'];
                                $body = $df['post_body'];
                                $paid = $df['paid'];
                                $cost_to_join = $df['cost_to_join'];

                                if ($user_by != $user_posted_to) {
                                    // If this was posted to a different user
                                    // Get the user_by persons data
                                    $user_get2 = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $user_posted_to . "' AND account_locked='unlocked' AND activated='1'");
                                    $user_get2->execute();
                                    $u2 = $user_get2->fetch(PDO::FETCH_ASSOC);
                                    $pid = 1;
                                    $up = "<a style='' href='" . APP_URL . "profile/" . $u['username'] . "'>" . ucwords($u['first_name']) . " " . ucwords($u['last_name']) . "</a> » <a style='padding-top: 5px;' href='" . APP_URL . "profile/" . $u2['username'] . "'>" . ucwords($u2['first_name']) . " " . ucwords($u2['last_name']) . "</a>";
                                } else {
                                    $pid = 0;
                                    $up = "<a style='display: inline;' href='" . APP_URL . "profile/" . $u['username'] . "'>" . ucwords($u['first_name']) . " " . ucwords($u['last_name']) . "</a>";
                                }

                                $report = 0;
                                if(isset($_SESSION['uid']))
                                {
                                    $report = Report::checkStatus($_SESSION['uid'], $unique_id);
                                }
                                if($report == 0){
                                    $getChatData = $db->prepare("SELECT * FROM " . CHATS . " WHERE chat_hash='".$chat_hash."'");
                                    $getChatData->execute();

                                    if($getChatData->rowCount() == 1){
                                        $fetchChatData = $getChatData->fetch(PDO::FETCH_ASSOC);

                                        if($return == "html") {
                                            ?>
                                            <div class='timeline-item clearfix post animate bounceIn' id='post-<?php echo $unique_id; ?>' post-id='<?php echo $unique_id; ?>'>
                                                <div class="whoRepostedThis clearfix" style="">
                                                    <p><font color="#2574A9"><i class="fa fa-comments"></i></font> Invited you to join a chat</p>
                                                </div>
                                                <div class="topPostAlways clearfix">
                                                    <div class="topAuthorPortion">
                                                        <div class="authorProfilePic" style="background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);"></div>
                                                        <div class="rightAuthorInfo">
                                                            <?php echo $up; ?>
                                                            <h3><?php echo Convert::convert_time($date); ?></h3>
                                                        </div>
                                                    </div>
                                                    <div class="postTextBody">
                                                        <p class='postTextBody' id="postBody<?php echo $unique_id; ?>" style='padding-left: 5px;margin: 10px;margin-left: 0px;'><?php echo Hashtags::parseText($body); ?></p>
                                                    </div>
                                                </div>
                                                <div class="extrasHolder">
                                                    <div class="comeJoinChatCont" style="background-image: url(<?php echo APP_URL; ?><?php echo CHAT_DATA_PATH; ?>/<?php echo $chat_hash; ?>/ch_b/<?php echo $fetchChatData['chat_banner']; ?>);background-size: cover;height: 300px;">
                                                        <div class="innerComeJoinChat" style="border-top-left-radius:3px;border-top-right-radius: 3px;width: 100%;height: 100%;background: rgba(0,0,0,.4);">
                                                            <div class="comeJoinBottom" style="position: relative;top: 160px;padding: 20px;background: rgba(0,0,0,.5);">
                                                                <div class="topChatName">
                                                                    <h3 style="color: white;font-weight: 400;font-size: 20px;"><?php echo ucwords($fetchChatData['chat_name']); ?></h3>
                                                                </div>
                                                                <div class="middleChatMems" style="padding-top: 5px;">
                                                                    <?php
                                                                    // Fetch all chat members
                                                                    $getMembers = $db->prepare("SELECT * FROM " . CHAT_MEMBERS . " WHERE chat_hash='" . $chat_hash . "' ORDER BY user_id LIMIT 4");
                                                                    $getMembers->execute();

                                                                    while ($fetchMembers = $getMembers->fetch(PDO::FETCH_ASSOC)) {
                                                                        $user_id = $fetchMembers['user_id'];

                                                                        // Now get the persons firstname, lastname, and profile picture
                                                                        $fetchUserData = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $user_id . "' && account_locked='unlocked'");
                                                                        $fetchUserData->execute();

                                                                        $fetchUserDataFetch = $fetchUserData->fetch(PDO::FETCH_ASSOC);
                                                                        $id = $fetchUserDataFetch['user_id'];
                                                                        $firstname = $fetchUserDataFetch['first_name'];
                                                                        $lastname = $fetchUserDataFetch['last_name'];
                                                                        $picture = $fetchUserDataFetch['profile_pic'];
                                                                        $username = $fetchUserDataFetch['username'];
                                                                        ?>
                                                                        <a class="" href="<?php echo APP_URL; ?>profile/<?php echo $username; ?>">
                                                                            <img style="height: 30px;width: 30px;border-radius: 50%;border: 2px solid #fff;" src="<?php echo APP_URL; ?>user_data/<?php echo $fetchUserDataFetch['unique_salt_id']; ?>/profile_pictures/<?php echo $picture; ?>" />
                                                                        </a>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                </div>
                                                                <div class="bottomButton" style="padding-top: 5px;">
                                                                    <?php
                                                                    if($paid == "free")
                                                                    {
                                                                        ?>
                                                                        <button class="btn chatJoinInvite" data-date_in_number_not_important="<?php echo $user_by; ?>" data-type="free-just-join" data-chat_hash="<?php echo $chat_hash; ?>" style="padding: 10px;background: #2ecc71;padding-top: 6px;padding-bottom: 6px;">Join</button>
                                                                        <?php
                                                                    }else if($paid == "not-free")
                                                                    {
                                                                        ?>
                                                                        <button class="btn chatJoinInvite" data-date_in_number_not_important="<?php echo $user_by; ?>" data-type="not-free-join" data-chat_hash="<?php echo $chat_hash; ?>" data-cost='<?php echo $cost_to_join; ?>' style="padding: 10px;background: #2ecc71;padding-top: 6px;padding-bottom: 6px;" title="Use <?php echo $cost_to_join; ?> points to join this chat!"><i class="fa fa-money"></i> <?php echo $cost_to_join; ?></button>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="actionsHolder" style="padding-top: 20px;">
                                                    <?php TimelinePostHandler::renderButtonsForPosts($unique_id, $u['user_id'], $likesNumber->rowCount(), $commentGetNum->rowCount()); ?>
                                                </div>
                                                <div class="bottomPostAssets">
                                                    <div class="postStatsTop">
                                                        <ul>

                                                            <li><span class='fspan likeCH'><i class="fa fa-heart"></i></span> <span class='sspan likeCH countHolder<?php echo $unique_id; ?>' id=""><font color="#e74c3c"><?php echo $likesNumber->rowCount(); ?></font></span></li>
                                                            <li><span class='fspan'><i class="fa fa-commenting"></i></span> <span class='sspan '><?php echo $commentGetNum->rowCount(); ?></span></li>

                                                        </ul>
                                                    </div>
                                                    <div class="commentArea">
                                                        <?php Comments::displayComments($unique_id); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }else if($return == "json"){
                                            $jsonReturn['posts'][$unique_id]['post_chat_data'] = array(

                                            );
                                            $jsonReturn['posts'][$unique_id]['user_data'] = array(
                                                'user_id' => $u['user_id'],
                                                'username' => $u['username'],
                                                'firstname' => $u['first_name'],
                                                'lastname' => $u['last_name'],
                                                'profilepic' => User::renderProfilePic($u['user_id'], 80),
                                            );
                                            if($pid == 1)
                                            {
                                                $jsonReturn['posts'][$unique_id]['user_posted_to'] = array(
                                                    'username' => $u2['username'],
                                                    'firstname' => $u2['first_name'],
                                                    'lastname' => $u2['last_name'],
                                                );
                                            }
                                            $jsonReturn['posts'][$unique_id]['post_data'] = array(
                                                'postId' => $unique_id,
                                                'postType' => $type,
                                                'postDate' => Convert::convert_time($date),
                                                'postText' => Hashtags::parseText($body),
                                                'postAuthor' => $u['user_id'],
                                                'pid' => $pid,
                                                'loggeduserProfile' => User::renderProfilePic($_SESSION['uid'], 80),
                                                'loggedUserPost' => $_SESSION['uid'],
                                                'likeCount' => $likesNumber->rowCount(),
                                                'commentCount' => $commentGetNum->rowCount(),
                                                'shareCount' => self::countReposts($unique_id),
                                                'hasReposted' => Repost::CheckIfRepostedBefore($unique_id, $_SESSION['uid']),
                                                'hasLiked' => self::checkLikeStatusMain($unique_id, $_SESSION['uid']),
                                                'isRepost' => '0',
                                                'comments' => Comments::displayCommentsInJson($unique_id)

                                            );

                                        }
                                    }
                                }
                                break;
                            case 'text':
                                $get_data_query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM_TEXT . " WHERE unique_id=:unique_id");
                                $get_data_query->execute(array(':unique_id'=>$unique_id));

                                // Fetch
                                $df = $get_data_query->fetch(PDO::FETCH_ASSOC);
                                $did = $df['id'];
                                $body = $df['postBody'];

                                $pid = "";

                                if ($user_by != $user_posted_to) {
                                    // If this was posted to a different user
                                    // Get the user_by persons data
                                    $user_get2 = $db->prepare("SELECT * FROM " . USERS . " WHERE user_salt='" . $user_posted_to . "' AND activated='1'");
                                    $user_get2->execute(array(':user_posted_to'=>$user_posted_to));

                                    $u2 = $user_get2->fetch(PDO::FETCH_ASSOC);
                                    $pid = 1;
                                    $up = "<a style='' href='" . APP_URL . "profile/" . $u['username'] . "'>" . ucwords($u['firstname']) . " " . ucwords($u['lastname']) . "</a> » <a style='padding-top: 5px;' href='" . APP_URL . "profile/" . $u2['username'] . "'>" . ucwords($u2['firstname']) . " " . ucwords($u2['lastname']) . "</a>";
                                } else {
                                    $pid = 0;
                                    $up = "<a style='display: inline;' href='" . APP_URL . "profile/" . $u['username'] . "'>" . ucwords($u['firstname']) . " " . ucwords($u['lastname']) . "</a>";
                                }

                                $priv = self::checkPostPrivacy($unique_id);
                                if($priv == 1){
                                    if($return == "html") {
                                        ?>
                                        <div class='timeline-item clearfix post animate bounceIn' id='post-<?php echo $unique_id; ?>' post-id='<?php echo $unique_id; ?>'>
                                            <div class="topPostAlways clearfix">
                                                <div class="topAuthorPortion">
                                                    <div class="authorProfilePic" style="background-image: url(<?php echo APP_URL; ?>users/data/<?php echo $u['user_salt'];?>/profile_picture"></div>
                                                    <div class="rightAuthorInfo">
                                                        <?php echo $up; ?>
                                                        <h3><?php echo Convert::convert_time($date); ?></h3>
                                                    </div>
                                                </div>
                                                <div class="postTextBody">
                                                    <p class='postTextBody' id="postBody<?php echo $unique_id; ?>" style='padding-left: 5px;margin: 10px;margin-left: 0px;'><?php echo Tagging::parseText($body); ?></p>
                                                </div>
                                            </div>
                                            <div class="actionsHolder">
                                                <?php self::renderButtonsForPosts($unique_id, $u['user_salt'], $likesNumber, $commentGetNum); ?>
                                            </div>
                                            <div class="extrasHolder">

                                            </div>
                                            <div class="bottomPostAssets">
                                                <div class="postStatsTop">
                                                    <ul>

                                                        <li><span class='fspan likeCH'><i class="fa fa-heart"></i></span> <span class='sspan likeCH countHolder<?php echo $unique_id; ?>' id=""><font color="#e74c3c"><?php echo $likesNumber; ?></font></span></li>
                                                        <li><span class='fspan'><i class="fa fa-commenting"></i></span> <span class='sspan '><?php echo $commentGetNum; ?></span></li>

                                                    </ul>
                                                </div>
                                                <div class="commentArea">
                                                    <?php self::displayComments($unique_id); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }else if($return == "json"){
                                        $jsonReturn['posts'][$unique_id]['user_data'] = array(
                                            'user_id' => $u['user_id'],
                                            'username' => $u['username'],
                                            'firstname' => $u['first_name'],
                                            'lastname' => $u['last_name'],
                                            'profilepic' => Users::renderProfilePic($u['user_id'], 80),
                                        );
                                        if($pid == 1)
                                        {
                                            $jsonReturn['posts'][$unique_id]['user_posted_to'] = array(
                                                'username' => $u2['username'],
                                                'firstname' => $u2['first_name'],
                                                'lastname' => $u2['last_name'],
                                            );
                                        }
                                        $jsonReturn['posts'][$unique_id]['post_data'] = array(
                                            'postId' => $unique_id,
                                            'postType' => $type,
                                            'postDate' => Convert::convert_time($date),
                                            'postText' => Validation::parseText($body),
                                            'postAuthor' => $u['user_id'],
                                            'pid' => $pid,
                                            'loggeduserProfile' => User::renderProfilePic($_SESSION['uid'], 80),
                                            'loggedUserPost' => $_SESSION['uid'],
                                            'likeCount' => $likesNumber,
                                            'commentCount' => $commentGetNum,
                                            'shareCount' => self::countReposts($unique_id),
                                            'hasReposted' => self::CheckIfRepostedBefore($unique_id, $_SESSION['uid']),
                                            'hasLiked' => self::checkLikeStatusMain($unique_id, $_SESSION['uid']),
                                            'isRepost' => '0',
                                            'comments' => self::displayCommentsInJson($unique_id)

                                        );

                                    }
                                }
                                break;
                            case 'video':
                                $get_data_query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM_VIDEO . " WHERE unique_id='" . $unique_id . "'");
                                $get_data_query->execute();

                                // Fetch
                                $df = $get_data_query->fetch(PDO::FETCH_ASSOC);
                                $did = $df['id'];
                                $body = $df['body'];
                                $videoLink = $df['videoLink'];

                                // Display
                                // See if the logged person is friends with the user by
                                if ($user_by != $user_posted_to) {
                                    // If this was posted to a different user
                                    // Get the user_by persons data
                                    $user_get2 = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $user_posted_to . "' AND account_locked='unlocked' AND activated='1'");
                                    $user_get2->execute();
                                    $u2 = $user_get2->fetch(PDO::FETCH_ASSOC);
                                    $pid = 1;
                                    $up = "<a style='' href='" . APP_URL . "profile/" . $u['username'] . "'>" . ucwords($u['first_name']) . " " . ucwords($u['last_name']) . "</a> » <a style='padding-top: 5px;' href='" . APP_URL . "profile/" . $u2['username'] . "'>" . ucwords($u2['first_name']) . " " . ucwords($u2['last_name']) . "</a>";
                                } else {
                                    $pid = 0;
                                    $up = "<a style='display: inline;' href='" . APP_URL . "profile/" . $u['username'] . "'>" . ucwords($u['first_name']) . " " . ucwords($u['last_name']) . "</a>";
                                }

                                $report = 0;
                                if(isset($_SESSION['uid']))
                                {
                                    $report = Report::checkStatus($_SESSION['uid'], $unique_id);
                                }
                                $priv = PrivacySystem::checkPostPrivacy($unique_id, $_SESSION['uid']);
                                if($report == 0 && $priv == 1){
                                    if($return == "html") {
                                        ?>
                                        <div class='timeline-item clearfix post bounceIn' id='post-<?php echo $unique_id; ?>' post-id='<?php echo $unique_id; ?>'>
                                            <div class="topPostAlways clearfix">
                                                <div class="topAuthorPortion">
                                                    <div class="authorProfilePic" style="background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);"></div>
                                                    <div class="rightAuthorInfo">
                                                        <?php echo $up; ?>
                                                        <h3><?php echo Convert::convert_time($date); ?></h3>
                                                    </div>
                                                </div>
                                                <div class="postTextBody">
                                                    <p class='postTextBody' id="postBody<?php echo $unique_id; ?>" style='padding-left: 5px;margin: 10px;margin-left: 0px;'><?php echo Hashtags::parseText($body); ?></p>
                                                </div>
                                            </div>
                                            <div class="extrasHolder">
                                                <div class="youtube" id="<?php echo $videoLink; ?>" style="width: 100%; height: 320px;border: none;"></div>
                                                <script src="<?php echo APP_URL; ?>js/youtube.js"></script>
                                            </div>
                                            <div class="actionsHolder" style="padding-top: 20px;">
                                                <?php TimelinePostHandler::renderButtonsForPosts($unique_id, $u['user_id'], $likesNumber->rowCount(), $commentGetNum->rowCount()); ?>
                                            </div>
                                            <div class="bottomPostAssets">
                                                <div class="postStatsTop">
                                                    <ul>

                                                        <li><span class='fspan likeCH'><i class="fa fa-heart"></i></span> <span class='sspan likeCH countHolder<?php echo $unique_id; ?>' id=""><font color="#e74c3c"><?php echo $likesNumber->rowCount(); ?></font></span></li>
                                                        <li><span class='fspan'><i class="fa fa-commenting"></i></span> <span class='sspan '><?php echo $commentGetNum->rowCount(); ?></span></li>

                                                    </ul>
                                                </div>
                                                <div class="commentArea">
                                                    <?php Comments::displayComments($unique_id); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }else if($return == "json"){
                                        $jsonReturn['posts'][$unique_id]['user_data'] = array(
                                            'user_id' => $u['user_id'],
                                            'username' => $u['username'],
                                            'firstname' => $u['first_name'],
                                            'lastname' => $u['last_name'],
                                            'profilepic' => User::renderProfilePic($u['user_id'], 80),
                                        );
                                        if($pid == 1)
                                        {
                                            $jsonReturn['posts'][$unique_id]['user_posted_to'] = array(
                                                'username' => $u2['username'],
                                                'firstname' => $u2['first_name'],
                                                'lastname' => $u2['last_name'],
                                            );
                                        }
                                        $jsonReturn['posts'][$unique_id]['post_data'] = array(
                                            'postId' => $unique_id,
                                            'postType' => $type,
                                            'postDate' => Convert::convert_time($date),
                                            'postText' => Hashtags::parseText($body),
                                            'postAuthor' => $u['user_id'],
                                            'pid' => $pid,
                                            'loggeduserProfile' => User::renderProfilePic($_SESSION['uid'], 80),
                                            'loggedUserPost' => $_SESSION['uid'],
                                            'likeCount' => $likesNumber->rowCount(),
                                            'commentCount' => $commentGetNum->rowCount(),
                                            'shareCount' => self::countReposts($unique_id),
                                            'hasReposted' => Repost::CheckIfRepostedBefore($unique_id, $_SESSION['uid']),
                                            'hasLiked' => self::checkLikeStatusMain($unique_id, $_SESSION['uid']),
                                            'isRepost' => '0',
                                            'comments' => Comments::displayCommentsInJson($unique_id),
                                            'postVideo' => $videoLink

                                        );

                                    }
                                }
                                break;

                            case 'photo':
                                $get_data_query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM_PHOTO . " WHERE unique_id='" . $unique_id . "'");
                                $get_data_query->execute();

                                // Fetch
                                $df = $get_data_query->fetch(PDO::FETCH_ASSOC);
                                $did = $df['id'];
                                $body = $df['photoTitle'];
                                $photoLink = $df['photoFileLocation'];

                                // Display
                                // See if the logged person is friends with the user by
                                $photos = json_decode($photoLink, true);

                                if ($user_by != $user_posted_to) {
                                    // If this was posted to a different user
                                    // Get the user_by persons data
                                    $user_get2 = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $user_posted_to . "' AND account_locked='unlocked' AND activated='1'");
                                    $user_get2->execute();
                                    $u2 = $user_get2->fetch(PDO::FETCH_ASSOC);
                                    $pid = 1;
                                    $up = "<a style='' href='" . APP_URL . "profile/" . $u['username'] . "'>" . ucwords($u['first_name']) . " " . ucwords($u['last_name']) . "</a> » <a style='padding-top: 5px;' href='" . APP_URL . "profile/" . $u2['username'] . "'>" . ucwords($u2['first_name']) . " " . ucwords($u2['last_name']) . "</a>";
                                } else {
                                    $pid = 0;
                                    $up = "<a style='display: inline;' href='" . APP_URL . "profile/" . $u['username'] . "'>" . ucwords($u['first_name']) . " " . ucwords($u['last_name']) . "</a>";
                                }

                                $report = 0;
                                if(isset($_SESSION['uid']))
                                {
                                    $report = Report::checkStatus($_SESSION['uid'], $unique_id);
                                }
                                $priv = PrivacySystem::checkPostPrivacy($unique_id, $_SESSION['uid']);
                                if($report == 0 && $priv == 1){
                                    if($return == "html") {
                                        ?>
                                        <div class='timeline-item clearfix post bounceIn' id='post-<?php echo $unique_id; ?>' post-id='<?php echo $unique_id; ?>'>
                                            <div class="topPostAlways clearfix">
                                                <div class="topAuthorPortion">
                                                    <div class="authorProfilePic" style="background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);"></div>
                                                    <div class="rightAuthorInfo">
                                                        <?php echo $up; ?>
                                                        <h3><?php echo Convert::convert_time($date); ?></h3>
                                                    </div>
                                                </div>
                                                <div class="postTextBody">
                                                    <p class='postTextBody' id="postBody<?php echo $unique_id; ?>" style='padding-left: 5px;margin: 10px;margin-left: 0px;'><?php echo Hashtags::parseText($body); ?></p>
                                                </div>
                                            </div>
                                            <div class="extrasHolder">
                                                <div class="photos_actual">
                                                    <?php
                                                    foreach ($photos as $p) {
                                                        ?>
                                                        <img class="item" data-x="<?php echo $unique_id; ?>" data-f="<?php echo $u['first_name']; ?>" src="<?php echo APP_URL; ?>public/user_data/<?php echo $salt2; ?>/photos/<?php echo $p; ?>" style="cursor: pointer;max-height: 1100px;"/>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="actionsHolder">
                                                <?php TimelinePostHandler::renderButtonsForPosts($unique_id, $u['user_id'], $likesNumber->rowCount(), $commentGetNum->rowCount()); ?>
                                            </div>
                                            <div class="bottomPostAssets">
                                                <div class="postStatsTop">
                                                    <ul>

                                                        <li><span class='fspan likeCH'><i class="fa fa-heart"></i></span> <span class='sspan likeCH countHolder<?php echo $unique_id; ?>' id=""><font color="#e74c3c"><?php echo $likesNumber->rowCount(); ?></font></span></li>
                                                        <li><span class='fspan'><i class="fa fa-commenting"></i></span> <span class='sspan '><?php echo $commentGetNum->rowCount(); ?></span></li>

                                                    </ul>
                                                </div>
                                                <div class="commentArea">
                                                    <?php Comments::displayComments($unique_id); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }else if($return == "json"){
                                        foreach ($photos as $p) {
                                            $photosToSend[] = APP_URL . "public/user_data/" . $salt2 . "/photos/" . $p;
                                        }

                                        $jsonReturn['posts'][$unique_id]['user_data'] = array(
                                            'user_id' => $u['user_id'],
                                            'username' => $u['username'],
                                            'firstname' => $u['first_name'],
                                            'lastname' => $u['last_name'],
                                            'profilepic' => User::renderProfilePic($u['user_id'], 80),
                                            'salt' => $salt2
                                        );
                                        if($pid == 1)
                                        {
                                            $jsonReturn['posts'][$unique_id]['user_posted_to'] = array(
                                                'username' => $u2['username'],
                                                'firstname' => $u2['first_name'],
                                                'lastname' => $u2['last_name'],
                                            );
                                        }
                                        $jsonReturn['posts'][$unique_id]['post_data'] = array(
                                            'postId' => $unique_id,
                                            'postType' => $type,
                                            'postDate' => Convert::convert_time($date),
                                            'postText' => Hashtags::parseText($body),
                                            'postAuthor' => $u['user_id'],
                                            'pid' => $pid,
                                            'loggeduserProfile' => User::renderProfilePic($_SESSION['uid'], 80),
                                            'loggedUserPost' => $_SESSION['uid'],
                                            'likeCount' => $likesNumber->rowCount(),
                                            'commentCount' => $commentGetNum->rowCount(),
                                            'shareCount' => self::countReposts($unique_id),
                                            'hasReposted' => Repost::CheckIfRepostedBefore($unique_id, $_SESSION['uid']),
                                            'hasLiked' => self::checkLikeStatusMain($unique_id, $_SESSION['uid']),
                                            'isRepost' => '0',
                                            'comments' => Comments::displayCommentsInJson($unique_id),
                                            'postPhotos' => $photosToSend

                                        );

                                    }
                                }
                                break;
                            case 'original-video':
                                $get_data_query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM_ORIGINAL_VIDEOS . " WHERE unique_id='" . $unique_id . "'");
                                $get_data_query->execute();

                                // Fetch
                                $df = $get_data_query->fetch(PDO::FETCH_ASSOC);
                                $did = $df['id'];
                                $body = $df['body'];
                                $videolink = $df['video_encryption'];

                                // Display
                                // See if the logged person is friends with the user b
                                if ($user_by != $user_posted_to) {
                                    // If this was posted to a different user
                                    // Get the user_by persons data
                                    $user_get2 = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $user_posted_to . "' AND account_locked='unlocked' AND activated='1'");
                                    $user_get2->execute();
                                    $u2 = $user_get2->fetch(PDO::FETCH_ASSOC);
                                    $pid = 1;
                                    $up = "<div class='clearfix'><h3 style='font-size: 20px;'><a style='' href='" . APP_URL . "profile/" . $u['username'] . "'>" . ucwords($u['first_name']) . " " . ucwords($u['last_name']) . "</a> » <a style='padding-top: 5px;' href='" . APP_URL . "profile/" . $u2['username'] . "'>" . ucwords($u2['first_name']) . " " . ucwords($u2['last_name']) . "</a></h3></div>";
                                } else {
                                    $pid = 0;
                                    $up = "<a style='' href='" . APP_URL . "profile/" . $u['username'] . "'><h3 style='font-size: 22px;'>" . ucwords($u['first_name']) . " " . ucwords($u['last_name']) . " </h3></a>";
                                }

                                $report = 0;
                                if(isset($_SESSION['uid']))
                                {
                                    $report = Report::checkStatus($_SESSION['uid'], $unique_id);
                                }
                                $priv = PrivacySystem::checkPostPrivacy($unique_id, $_SESSION['uid']);
                                if($report == 0 && $priv == 1){
                                    if($return == "html") {
                                        ?>

                                        <?php
                                    }else if($return == "json"){
                                        $jsonReturn['posts'][$unique_id]['user_data'] = array(
                                            'user_id' => $u['user_id'],
                                            'username' => $u['username'],
                                            'firstname' => $u['first_name'],
                                            'lastname' => $u['last_name'],
                                            'profilepic' => User::renderProfilePic($u['user_id'], 80),
                                        );
                                        if($pid == 1)
                                        {
                                            $jsonReturn['posts'][$unique_id]['user_posted_to'] = array(
                                                'username' => $u2['username'],
                                                'firstname' => $u2['first_name'],
                                                'lastname' => $u2['last_name'],
                                            );
                                        }
                                        $jsonReturn['posts'][$unique_id]['post_data'] = array(
                                            'postId' => $unique_id,
                                            'postType' => $type,
                                            'postDate' => Convert::convert_time($date),
                                            'postText' => Hashtags::parseText($body),
                                            'postAuthor' => $u['user_id'],
                                            'pid' => $pid,
                                            'loggeduserProfile' => User::renderProfilePic($_SESSION['uid'], 80),
                                            'loggedUserPost' => $_SESSION['uid'],
                                            'likeCount' => $likesNumber->rowCount(),
                                            'commentCount' => $commentGetNum->rowCount(),
                                            'shareCount' => self::countReposts($unique_id),
                                            'hasReposted' => Repost::CheckIfRepostedBefore($unique_id, $_SESSION['uid']),
                                            'hasLiked' => self::checkLikeStatusMain($unique_id, $_SESSION['uid']),
                                            'isRepost' => '0',
                                            'comments' => Comments::displayCommentsInJson($unique_id),
                                            'postVideo' => $videolink

                                        );

                                    }
                                }
                                break;

                        }
                    }

                }

                if($return == "json"){
                    echo json_encode($jsonReturn);
                    return false;
                }
            }
        }
    }
}