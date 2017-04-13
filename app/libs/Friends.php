<?php
/*
 * Friends
 * ----
 * This will serve as our main "Friends" system that does everything when it comes to friends
 */
class Friends extends Database
{
    static public function sendRequest($data)
    {

    }

    static public function renderFriendshipBtnForMod($usr_id, $user_two)
    {
        if ($usr_id != Sessions::get('salt')) {
            if (Users::checkExists($usr_id) == 1) {
                // Render the persons relationship
                $array1 = array('person1' => $usr_id, 'person2' => $user_two, 'check' => 'request-status');
                $array2 = array('person1' => $usr_id, 'person2' => $user_two, 'check' => 'friendship');
                $rid = Friends::getRequestId($usr_id, $user_two);
                if (Friends::checkFriendshipStatus($array1) == 1) {
                    // Means the person can add or delete as friend
                    if (Friends::checkFriendshipStatus($array2) == 0) {
                        ?>
                        <a href='#' class='friendListener not-friends' id='friendRBTN-<?php echo $usr_id; ?>'
                           ftype='sendFRequest' uid='<?php echo $usr_id; ?>' from="<?php echo $user_two; ?>">Add
                            Friend</a>
                        <a href='#' class='friendListener request-pending request-canceler hidden'
                           id='friendBTN-<?php echo $usr_id; ?>' request_id='' ftype='cancelFRequest'
                           user_to='<?php echo $usr_id; ?>'>Delete request</a>
                        <?php
                    } else if (Friends::checkFriendshipStatus($array2) == 1) {
                        ?>
                        <a href='#' class='friendListener friends' id='friendBTN-<?php echo $usr_id; ?>'
                           ftype='cancelFRequest' request_id='<?php echo $rid; ?>'>Unfriend</a>
                        <a href='#' class='friendListener not-friends hidden' id='friendRBTN-<?php echo $usr_id; ?>'
                           ftype='sendFRequest' uid='<?php echo $usr_id; ?>' from="<?php echo $user_two; ?>">Add
                            Friend</a>
                        <?php
                    }
                } else {
                    // means the logged person has already sent this person a request
                    // Get request data to let the person cancel a request

                    ?>
                    <a href='#' class='friendListener request-pending request-canceler'
                       id='friendBTN-<?php echo $usr_id; ?>' ftype='cancelFRequest' request_id='<?php echo $rid; ?>'
                       user_to='<?php echo $usr_id; ?>'>Delete request</a>
                    <a href='#' class='friendListener not-friends hidden' id='friendRBTN-<?php echo $usr_id; ?>'
                       ftype='sendFRequest' uid='<?php echo $usr_id; ?>' from="<?php echo $user_two; ?>">Add Friend</a>
                    <?php
                }
            }
        }
    }

    static public function sendRelationshipRequest($data)
    {
        if ($data != "" && is_array($data)) {
            $user_to = Validation::santitize($data['user_to_id']);
            $from = Validation::santitize($data['from_id']);
            $type = Validation::santitize($data['type']);
            $types = array('friend', 'bestfriend', 'girlfriend', 'boyfriend', 'married', 'sister', 'brother', 'mom', 'dad', 'uncle', 'auntie', 'cousin');

            if (!empty($user_to) && !empty($from) && is_numeric($user_to) && is_numeric($from) && !empty($type) && in_array($type, $types)) {
                // Make the request
                $date = date("y:m:d h:i:s");
                $db = new Database;

                // Now lets render stuff
                // Make sure there isnt another request pending between these two
                $query = $db->prepare("SELECT * FROM " . FRIEND_REQUESTS . " WHERE user_to_id='" . $user_to . "' AND user_from_id='" . $from . "' OR user_to_id='" . $from . "' AND user_from_id='" . $user_to . "'");
                $query->execute();

                // Fetch the data
                $fetcher = $query->fetch(PDO::FETCH_ASSOC);
                $rid = $fetcher["id"];
                $user_to_db = $fetcher["user_to_id"];
                $user_from = $fetcher['user_from_id'];
                $type1 = $fetcher['rel_type'];

                // Make the algorithem
                if ($user_to_db == $from && $user_from == $user_to) {
                    // Means the person thats logged in already has a pending friend request from the user
                    $response = array();
                    $response['code'] = "0";
                    $response['status'] = "Looks like this person wants to be your " . $type1 . " also! Accept their request!";
                    echo json_encode($response);
                    return false;
                } else if ($user_to_db == $user_to && $user_from == $from) {
                    // Means the person thats logged in has already made the attempt to add someone as a friend before(This if statement should never be called)
                    $response = array();
                    $response['code'] = "0";
                    $response['status'] = "Looks like you have already made an attempt to make this person your " . $type1;
                    echo json_encode($response);
                    return false;
                } else {
                    // If all those other if statements fail call this one, because its the if statement we really want to call
                    $insert = $db->prepare("INSERT INTO " . FRIEND_REQUESTS . " VALUES('','" . $user_to . "','" . $from . "','" . $type . "','" . $date . "')");
                    $insert->execute();

                    $response = array();
                    $response['code'] = "1";
                    $response['status'] = "Request Sent";
                    echo json_encode($response);
                }
            }
        }
        $db = null;
    }

    static public function checkFriendshipStatus($data)
    {
        if ($data != "" && is_array($data)) {
            $person1 = Validation::santitize($data['person1']);
            $person2 = Validation::santitize($data['person2']); // Logged in person always
            $check = Validation::santitize($data['check']);

            // Lets check stuff here with a switch statement
            if (!empty($person1) && !empty($person2) && !empty($check)) {
                // If everything isnt empty
                switch ($check) {
                    case 'request-status':
                        // Call the database
                        $db = new Database;

                        // Check rather the logged in person has sent the person a request before
                        $query = $db->prepare("SELECT * FROM " . FRIEND_REQUESTS . " WHERE user_to_id='" . $person1 . "' AND user_from_id='" . $person2 . "' AND rel_type='friend'");
                        $query->execute();

                        // Render
                        if ($query->rowCount() == 1) {
                            // Means the logged person has already made an attempt to send the other persona requests so display request pending
                            return 0;
                        } else {
                            return 1;
                        }
                        break;
                    case 'friendship':
                        // Call the database
                        $db = new Database;
                        // Checking rather the logged in person is friends with someone
                        $check = $db->prepare("SELECT * FROM " . RELATIONSHIPS . " WHERE user_one='" . $person1 . "' AND user_two='" . $person2 . "' AND friendship_official='1' OR user_one='" . $person2 . "' AND user_two='" . $person1 . "' AND friendship_official='1'");
                        $check->execute();

                        if ($check->rowCount() == 1) {
                            // Means they have a relationship of some sort
                            return 1;
                        } else {
                            return 0;
                        }
                        break;
                }
            }
        }
        $db = null;
    }


    static public function getRequestId($uid, $logged, $response = "return")
    {
        if (!empty($uid) && !empty($logged)) {
            $uid = Validation::santitize($uid);
            $logged = Validation::santitize($logged);

            if (is_numeric($uid) && is_numeric($logged)) {
                // Call the database
                $db = new Database;

                // Query
                $query = $db->prepare("SELECT * FROM " . FRIEND_REQUESTS . " WHERE user_to_id='" . $uid . "' && user_from_id='" . $logged . "'");
                $query->execute();

                // Fetch
                $fetch = $query->fetch(PDO::FETCH_ASSOC);
                $rid = $fetch['id'];

                // Render
                if ($query->rowCount() == 1) {
                    // Means the logged person has already made an attempt to send the other persona requests so display request pending
                    if ($response == "return") {
                        return $rid;
                    } else if ($response == "echo") {
                        echo $rid;
                    }
                }
            }
        }
        $db = null;
    }

    static public function deleteRequest($rid)
    {
        if (!empty($rid) && is_numeric($rid)) {
            $rid = Validation::santitize($rid);

            // Make call to database
            $db = new Database;

            // Query
            $delete = $db->prepare("DELETE FROM " . FRIEND_REQUESTS . " WHERE id='" . $rid . "'");
            $delete->execute();

            // Success
            echo "request-deletion-success";

            $db = null;
            return false;
        }
    }

    static public function getOnlineFriends($uid, $display = 6)
    {
        if (!empty($uid) && is_numeric($uid)) {
            $uid = Validation::santitize($uid);

            if ($display != "all") {
                $d = "ORDER BY id LIMIT " . $display;
            } else {
                $d = "";
            }

            // Call the database
            $db = new Database;

            // Query
            $query = $db->prepare("SELECT * FROM " . RELATIONSHIPS . " WHERE user_one='" . $uid . "' AND friendship_official='1' OR user_two='" . $uid . "' AND friendship_official='1' ORDER BY id LIMIT 5");
            $query->execute();

            if ($query->rowCount() > 0) {
                // Fetch
                while ($fetch = $query->fetch(PDO::FETCH_ASSOC)) {
                    // Get data
                    $userone = $fetch['user_one'];
                    $usertwo = $fetch['user_two'];

                    // Render
                    if ($userone != $_SESSION['uid'] && $usertwo == $_SESSION['uid']) {
                        // If user one dosent equal the logged user
                        $id = $userone;
                    } else if ($userone == $_SESSION['uid'] && $usertwo != $_SESSION['uid']) {
                        // Means usertwo dosent equal the logged user
                        $id = $usertwo;
                    }

                    // Now time to get data for the user
                    $get = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $id . "' AND activated='1'");
                    $get->execute();

                    if ($get->rowCount() > 0) {
                        // Fetch user
                        $f = $get->fetch(PDO::FETCH_ASSOC);
                        $user_id = $f['user_id'];
                        
                        // Display
                        if ($as == "unlocked" && User::checkStatusNumber($user_id) >= 1) {
                            Users::makeUserMod($user_id);
                        }
                    } else {

                    }
                }
            } else {
                echo "<center><h3 style='paddign: 0;margin: 0;font-size: 20px;'>You have no friends, add some!</h3></center>";
            }
        } else {
            echo "error";
        }
        $db = null;
    }

    /*
     * getFriendsArray
     * ----
     * This will get the supplied uid friends into an array
     */
    static public function getFriendsArray($uid)
    {
        if(!empty($uid))
        {
            $db = new Database;
            $ids = array();

            // Make the query
            $query = $db->prepare("SELECT * FROM " . RELATIONSHIPS . " WHERE user_one=:uid AND friendship_official='1' OR user_two=:uid AND friendship_official='1'");
            if($query->execute(array(':uid'=>$uid)))
            {
                // Now get all the users
                while($fetch = $query->fetch(PDO::FETCH_ASSOC))
                {
                    // Get data
                    $userone = $fetch['user_one'];
                    $usertwo = $fetch['user_two'];

                    // Render
                    if ($userone != $uid && $usertwo == $uid) {
                        // If user one dosent equal the logged user
                        array_push($ids, $userone);
                    } else if ($userone == $uid && $usertwo != $uid) {
                        // Means usertwo dosent equal the logged user
                        array_push($ids, $usertwo);
                    }
                }
            }
            return $ids;
        }
    }

    static public function getFriendsNumber($uid)
    {
        if (!empty($uid)) {
            // Call the database
            $db = new Database;

            // Query
            $query = $db->prepare("SELECT * FROM " . RELATIONSHIPS . " WHERE user_one='" . $uid . "' AND friendship_official='1' OR user_two='" . $uid . "' AND friendship_official='1'");
            $query->execute();

            return $query->rowCount();
        }
    }

    static public function getFriendsRequestsNum($uid)
    {
        if (!empty($uid) && is_numeric($uid)) {
            // Call DB
            $db = new Database;

            // Query
            $getFriendNum = $db->prepare("SELECT * FROM " . FRIEND_REQUESTS . " WHERE user_to_id='" . $uid . "'");
            $getFriendNum->execute();

            return $getFriendNum->rowCount();
        }
    }

    static public function getFriendsRequests($uid)
    {
        if (!empty($uid) && is_numeric($uid)) {
            // Call DB
            $db = new Database;

            // Query
            $getFriendNum = $db->prepare("SELECT * FROM " . FRIEND_REQUESTS . " WHERE user_to_id='" . $uid . "' ORDER BY id DESC");
            $getFriendNum->execute();

            if ($getFriendNum->rowCount() > 0) {
                // Fetch
                while ($fetch = $getFriendNum->fetch(PDO::FETCH_ASSOC)) {
                    $id = $fetch['id'];
                    $userFrom = $fetch['user_from_id'];
                    $date = $fetch['date'];
                    $type = $fetch['rel_type'];

                    // Display & get the userfrom person
                    $query = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $userFrom . "'");
                    $query->execute();

                    // Fetch user
                    $u = $query->fetch(PDO::FETCH_ASSOC);
                    $firstname = $u['first_name'];
                    $lastname = $u['last_name'];
                    $pic = $u['profile_pic'];
                    $status = $u['account_locked'];

                    if ($status == 'unlocked' or $status == 'locked') {
                        if ($type == "friend") {
                            ?>
                            <div class='sPersonContainer clearfix' id='friendRequestContainer<?php echo $id; ?>'
                                 style='height: auto;width: 100%; padding: 10px;margin: 0px auto; background: white;border-bottom: 1px solid #ddd;padding-bottom: 7px;'>
                                <div style='float: left;height: 40px;width: 40px;float: left;background-image: url(<?php echo User::renderProfilePic($u['user_id']); ?>);background-size: cover;border-radius: 5px;'></div>
                                <div class='rightSPersonContainer' style='margin-left:50px;'>
                                    <a href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'
                                       style='font-size: 18px;font-weight: 300;padding: 0px;'><?php echo ucwords($u['first_name']); ?> <?php echo ucwords($u['last_name']); ?></a>

                                    <p style="margin: 0px;padding: 2px;">Wants you to be their friend</p>

                                    <div class='actions' style='padding-top: 3px;margin-top: 3px;'>
                                        <a class='friend-accept'
                                           style='cursor: pointer;font-size: 16px;margin: 0;background: white;color: #2ecc71;'
                                           data-frienderid='<?php echo $userFrom; ?>'
                                           data-requestid='<?php echo $id; ?>'
                                           data-type='friend-accept'>Accept</a> &middot;
                                        <a class='friend-ignore'
                                           style='cursor: pointer;font-size: 16px;margin: 0;background: white;color: #e74c3c;'
                                           data-frienderid='<?php echo $userFrom; ?>'
                                           data-requestid='<?php echo $id; ?>' data-type='friend-ignore'>Ignore</a>
                                    </div>
                                </div>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class='sPersonContainer clearfix' id='friendRequestContainer<?php echo $id; ?>'
                                 style='height: auto;width: 100%; padding: 4px;margin: 0px auto; border-bottom: 1px solid #ddd;padding-bottom: 7px;'>
                                <img
                                    src='<?php echo APP_URL; ?>user_data/<?php echo $u['unique_salt_id']; ?>/profile_pictures/<?php echo $pic; ?>'
                                    style='height: 45px; width: 45px; float: left;'/>

                                <div class='rightSPersonContainer' style='float: right;'>
                                    <a href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'
                                       style='font-size: 18px;font-weight: 300;padding: 0px;'><?php echo ucwords($u['first_name']); ?> <?php echo ucwords($u['last_name']); ?></a>

                                    <p style="margin: 0px;padding: 2px;">Wants to add you as
                                        their <?php echo $type; ?></p>

                                    <div class='actions' style='padding-top: 3px;margin-top: 3px;'>
                                        <a class='relationship-accept'
                                           style='cursor: pointer;font-size: 16px;margin: 0;background: white;color: #2ecc71;'
                                           data-frienderid='<?php echo $userFrom; ?>'
                                           data-requestid='<?php echo $id; ?>'
                                           data-type='relationship-accept'>Accept</a> &middot;
                                        <a class='relationship-ignore'
                                           style='cursor: pointer;font-size: 16px;margin: 0;background: white;color: #e74c3c;'
                                           data-frienderid='<?php echo $userFrom; ?>'
                                           data-requestid='<?php echo $id; ?>'
                                           data-type='relationship-ignore'>Ignore</a>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                }
            } else {
                echo "<center><h3 style='padding: 5px;'>No friend requests</h3></center>";
            }
        }
        $db = null;
    }

    static public function makeDecision($requestID, $type, $frienderID, $sub = "")
    {
        if (!empty($requestID) && !empty($type) && !empty($frienderID)) {
            if ($type == "friend-accept") {
                // Call database
                $db = new Database;

                /* Insertation and deletion */

                // Delete all requests
                $delete = $db->prepare("DELETE FROM " . FRIEND_REQUESTS . " WHERE id='" . $requestID . "'");
                $delete->execute();

                $delete2 = $db->prepare("DELETE FROM " . FRIEND_REQUESTS . " WHERE user_to_id='" . $frienderID . "' AND user_from_id='" . $_SESSION['uid'] . "' OR user_to_id='" . $_SESSION['uid'] . "' AND user_from_id='" . $frienderID . "'");
                $delete2->execute();
                // Insert
                if ($sub == "") {
                    $sub = "friends";
                } else {
                    $sub = $sub;
                }

                // Make insert
                $insert = $db->prepare("INSERT INTO " . RELATIONSHIPS . " VALUES('','" . $_SESSION['uid'] . "','" . $frienderID . "','friends','" . $sub . "','1','" . date("y:m:d h:i:s") . "')");
                $insert->execute();

                $lastId = $db->lastInsertId();

                // User data
                $username = User::get("users", $frienderID, "username");
                $firstname = ucwords(User::get("users", $frienderID, "first_name"));
                $lastname = ucwords(User::get("users", $frienderID, "last_name"));

                $username2 = User::get("users", $_SESSION['uid'], "username");
                $lastname2 = ucwords(User::get("users", $_SESSION['uid'], "last_name"));
                $firstname2 = ucwords(User::get("users", $_SESSION['uid'], "first_name"));
                // For logged user
                Notifications::makeTimelineActivityPost($_SESSION['uid'], 'friends', 'became friends with <a href="' . APP_URL . 'profile/' . $username . '">' . $firstname . ' ' . $lastname . '</a>', $_SESSION['uid'], $requestID);
                Notifications::makeTimelineActivityPost($frienderID, 'friends', 'became friends with <a href="' . APP_URL . 'profile/' . $username2 . '">' . $firstname2 . ' ' . $lastname2 . '</a>', $frienderID, $requestID);
                echo "success";
            } else if ($type == "friend-ignore") {
                $db = new Database;

                // Delete
                $query = $db->prepare("DELETE FROM " . FRIEND_REQUESTS . " WHERE id='" . $requestID . "'");
                $query->execute();

                echo "ignore-success";
            } else if ($type == "relationship-accept") {
                // Call database
                $db = new Database;

                /* Insertation and deletion */
                // Get data
                $query = $db->prepare("SELECT * FROM " . FRIEND_REQUESTS . " WHERE id='" . $requestID . "'");
                $query->execute();

                if ($query->rowCount() > 0) {
                    $fetch = $query->fetch(PDO::FETCH_ASSOC);
                    $sub = $fetch['rel_type'];

                    // Make sure this person has not been entered in to the user_froms index
                    $check = $db->prepare("SELECT * FROM " . FAMILY_BLOCK . " WHERE user_id='" . $frienderID . "'");
                    $check->execute();

                    $check2 = $db->prepare("SELECT * FROM " . FAMILY_BLOCK . " WHERE user_id='" . $_SESSION['uid'] . "'");
                    $check2->execute();

                    if ($check->rowCount() > 0) {
                        $cf = $check->fetch(PDO::FETCH_ASSOC);
                        $index = json_decode($cf[$sub]);

                        $cf2 = $check2->fetch(PDO::FETCH_ASSOC);
                        $index2 = json_decode($cf2[$sub]);

                        if (!in_array($_SESSION['uid'], $index)) {
                            // Delete all requests
                            $delete = $db->prepare("DELETE FROM " . FRIEND_REQUESTS . " WHERE id='" . $requestID . "'");
                            $delete->execute();

                            // Update neccessary
                            if ($sub != "mom" && $sub != "dad" && $sub != "girlfriend" && $sub != "boyfriend") {
                                array_push($index, $_SESSION['uid']);
                            } else {
                                $index = array($_SESSION['uid']);
                            }
                            $update = $db->prepare("UPDATE " . FAMILY_BLOCK . " SET " . $sub . "='" . json_encode($index) . "' WHERE user_id='" . $frienderID . "'");
                            $update->execute();

                            $update2 = $db->prepare("UPDATE " . FAMILY_BLOCK . " SET " . $sub . "='" . json_encode($index2) . "' WHERE user_id='" . $_SESSION['uid'] . "'");
                            $update2->execute();

                            $update3 = $db->prepare("UPDATE " . RELATIONSHIPS . " SET sub-type='" . $sub . "' WHERE user_one='" . $frienderID . "' AND user_two='" . $_SESSION['uid'] . "' AND friendship_official='1' OR user_one='" . $_SESSION['uid'] . "' AND user_two='" . $frienderID . "' AND friendship_official='1'");
                            $update3->execute();

                            $response = array();
                            $response['code'] = 1;
                            echo json_encode($response);
                            return false;
                        } else {
                            // Delete post
                            $delete = $db->prepare("DELETE FROM " . FRIEND_REQUESTS . " WHERE id='" . $requestID . "'");
                            $delete->execute();

                            $response = array();
                            $response['code'] = 0;
                            $response['status'] = "Looks like this person already have you as their " . $sub;
                            echo json_encode($response);
                            return false;
                        }
                    }
                }
            }
        }
        $db = null;
    }

    static public function unfriend($friendId)
    {
        if (!empty($friendId)) {
            // Db
            $db = new Database;

            // Delete
            $delete = $db->prepare("DELETE FROM " . RELATIONSHIPS . " WHERE id='" . $friendId . "'");
            $delete->execute();

            // R
            return false;
        } else {
            return false;
        }
        $db = null;
    }

    static public function mutualFriends($user_one, $user_two)
    {
        if (!empty($user_one) && !empty($user_two)) {
            $array1 = self::getFriendsArray($user_one, 'array', '');
            $array2 = self::getFriendsArray($user_two, 'array', '');

            $mutual = array_intersect($array1, $array2);
            return $mutual;
        }
    }

    static public function getFriendshipId($data = array())
    {
        if ($data != "") {
            $person1 = $data['person1'];
            $person2 = $data['person2'];

            if ($person1 != "" && $person2 != "") {
                $db = new Database;

                $get = $db->prepare("SELECT * FROM " . RELATIONSHIPS . " WHERE user_one='" . $person1 . "' AND user_two='" . $person2 . "' AND friendship_official='1' OR user_one='" . $person2 . "' AND user_two='" . $person1 . "' AND friendship_official='1'");
                $get->execute();

                if ($get->rowCount() > 0) {
                    return $get->fetch(PDO::FETCH_OBJ)->id;
                } else {
                    echo "";
                    return false;
                }
            }
        }
    }

    static public function searchFriends($uid, $searchQuery, $returnType = 'num', $limit = "5")
    {
        /*if(!empty($uid))
        {
            // Call the database
            $db = new Database;
            if($limit == 0){
                $l = "";
            }else{
                $l = "ORDER BY id LIMIT ".$limit."";
            }

            // Query
            $search = Validation::santitize($searchQuery);
            $query = $db->prepare("SELECT relationships.user_one, relationships.user_two, relationships.friendship_official FROM ".RELATIONSHIPS." INNER JOIN ".USERS_TABLE." ON relationships.user_one='".$uid."' AND user_two='".."' AND friendship_official='1' OR user_two='".$uid."' AND user_one LIKE '".$search."%' AND friendship_official='1' ".$l."");
            $query->execute();

            // Make the array
            $ids = array();

            if($query->rowCount() > 0)
            {
                // Fetch
                while($fetch = $query->fetch(PDO::FETCH_ASSOC))
                {
                    // Get data
                    $userone = $fetch['user_one'];
                    $usertwo = $fetch['user_two'];

                    // Render
                    if($userone != $uid && $usertwo == $uid)
                    {
                        // If user one dosent equal the logged user
                        array_push($ids, $userone);
                    } else if($userone == $uid && $usertwo != $uid)
                    {
                        // Means usertwo dosent equal the logged user
                        array_push($ids, $usertwo);
                    }
                }
            }else{
                if($returnType == 'num')
                {
                    return 0;
                }else
                {
                    return $ids;
                }

            }
            $db = null;
            return $ids;
        }*/
    }

    static public function getFriendActivity($uid, $postnumbers = "20", $offset = "0", $request = 'default')
    {
        $db = new Database;

        $u = $_SESSION['uid'];
        $sql = "SELECT COUNT(id) FROM " . RELATIONSHIPS . " WHERE user_one='$u' AND friendship_official='1' OR user_two='$u' AND friendship_official='1'";
        $query = $db->prepare($sql);
        $query->execute();
        $query_count = $query->rowCount();
        $friend_count = $query_count[0];

        /* Get All Friends */
        $all_friends = array();
        $sql = "SELECT user_one, user_two FROM " . RELATIONSHIPS . " WHERE (user_two='$u' OR user_one='$u') AND friendship_official='1'";
        $query = $db->prepare($sql);
        $query->execute();

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            if ($row["user_one"] != $u) {
                array_push($all_friends, $row["user_one"]);
            }
            if ($row["user_two"] != $u) {
                array_push($all_friends, $row["user_two"]);
            }
        }

        $me = $_SESSION['uid'];


        $everyone = implode("','", $all_friends);
        $getPosts = $db->prepare("SELECT * FROM " . TIMELINE_ITEM . " WHERE user_by IN ('$everyone') AND type='activity' ORDER BY id DESC LIMIT " . $postnumbers . " OFFSET " . $offset);
        $getPosts->execute();

        $posts = array();

        if ($getPosts->rowCount() != 0) {
            while ($pf = $getPosts->fetch(PDO::FETCH_ASSOC)) {
                $i = 0;

                $id = $pf['id'];
                $unique_id = $pf['unique_id'];
                $user_by = $pf['user_by'];
                $user_posted_to = $pf['user_posted_to'];
                $type = $pf['type'];
                $date = $pf['date'];

                // Get the user_by persons data
                $user_get = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $user_by . "' AND account_locked='unlocked' AND activated='1'");
                $user_get->execute();
                $u = $user_get->fetch(PDO::FETCH_ASSOC);

                $commentGetNum = $db->prepare("SELECT * FROM " . TIMELINE_POST_COMMENTS . " WHERE timeline_post_unique_id='" . $unique_id . "'");
                $commentGetNum->execute();

                $likesNumber = $db->prepare("SELECT * FROM " . TIMELINE_POST_LIKES . " WHERE unique_id='" . $unique_id . "'");
                $likesNumber->execute();

                // Display the different posts
                if ($request == 'default-ajax') {
                    $posts[$unique_id]['user_data'] = array(
                        'first_name' => ucwords($u['first_name']),
                        'last_name' => ucwords($u['last_name']),
                        'user_name' => $u['username'],
                        'profile_pic' => $u['profile_pic'],
                        'salt' => $u['unique_salt_id']
                    );
                    $posts[$unique_id]['post_data'] = array();

                }
                switch ($type) {
                    case 'activity':
                        $get_data_query = $db->prepare("SELECT id, user_by, second_user, type, what_happened, external_id FROM " . TIMELINE_ITEM_ACTIVITY . " WHERE unique_id='" . $unique_id . "'");
                        $get_data_query->execute();

                        // Fetch
                        $df = $get_data_query->fetch(PDO::FETCH_ASSOC);
                        $did = $df['id'];
                        $user_by = $df['user_by'];
                        $second_user = $df['second_user'];
                        $atype = $df['type'];
                        $what_happened = $df['what_happened'];
                        $external_id = $df['external_id'];

                        // RENDER DIFFERENT TYPES
                        switch ($atype) {
                            case 'postLike':
                                if ($request == 'default-ajax') {
                                    $posts[$unique_id]['post_data'] = array(
                                        'what_happened' => $what_happened,
                                        'font_icon' => 'fa fa-heart',
                                        'font_color' => '#e74c3c',
                                        'date' => Convert::convert_time($date)
                                    );
                                } else {
                                    ?>
                                    <div class="activityTimelinePost clearfix postLike" style="border-bottom: 1px solid #ddd;">
                                        <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                        <div class="rightActivity" style="width: 82%;">
                                            <h5><a style=''
                                                   href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['first_name']); ?> <?php echo ucwords($u['last_name']); ?></a> <?php echo $what_happened; ?>
                                            </h5>
                                            <h5 style="font-size: 14px;padding-top: 3px;"><font color="#e74c3c"><i
                                                        class="fa fa-heart"></i></font> &middot; <font
                                                    color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                        </div>
                                    </div>
                                    <?php
                                }
                                break;
                            case 'personLike':
                                if ($request == 'default-ajax') {
                                    $posts[$unique_id]['post_data'] = array(
                                        'what_happened' => $what_happened,
                                        'font_icon' => 'fa fa-heart',
                                        'font_color' => '#e74c3c',
                                        'date' => Convert::convert_time($date)
                                    );
                                } else {
                                    ?>
                                    <div class="activityTimelinePost clearfix postLike" style="border-bottom: 1px solid #ddd;">
                                        <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                        <div class="rightActivity" style="width: 83%;">
                                            <h5><a style=''
                                                   href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['first_name']); ?> <?php echo ucwords($u['last_name']); ?></a> <?php echo $what_happened; ?>
                                            </h5>
                                            <h5 style="font-size: 14px;padding-top: 3px;"><font color="#e74c3c"><i
                                                        class="fa fa-heart"></i></font> &middot; <font
                                                    color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                        </div>
                                    </div>
                                    <?php
                                }
                                break;
                            case 'newQuestion':
                                if ($request == 'default-ajax') {
                                    $posts[$unique_id]['post_data'] = array(
                                        'what_happened' => $what_happened,
                                        'font_icon' => 'fa fa-question-circle',
                                        'font_color' => '#e74c3c',
                                        'date' => Convert::convert_time($date)
                                    );
                                } else {
                                    ?>
                                    <div class="activityTimelinePost clearfix postLike" style="border-bottom: 1px solid #ddd;">
                                        <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                        <div class="rightActivity" style="width: 83%;">
                                            <h5><a style=''
                                                   href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['first_name']); ?> <?php echo ucwords($u['last_name']); ?></a> <?php echo $what_happened; ?>
                                            </h5>
                                            <h5 style="font-size: 14px;padding-top: 3px;"><font color="#e74c3c"><i
                                                        class="fa fa-heart"></i></font> &middot; <font
                                                    color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                        </div>
                                    </div>
                                    <?php
                                }
                                break;
                            case 'postComment':
                                if ($request == 'default-ajax') {
                                    $posts[$unique_id]['post_data'] = array(
                                        'what_happened' => $what_happened,
                                        'font_icon' => 'fa fa-comment',
                                        'font_color' => '#2ecc71',
                                        'date' => Convert::convert_time($date)
                                    );
                                } else {
                                    ?>
                                    <div class="activityTimelinePost clearfix postComment" style="border-bottom: 1px solid #ddd;">
                                        <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                        <div class="rightActivity" style="width: 83%;">
                                            <h5><a style=''
                                                   href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['first_name']); ?> <?php echo ucwords($u['last_name']); ?></a> <?php echo $what_happened; ?>
                                            </h5>
                                            <h5 style="font-size: 14px;padding-top: 3px;"><font color="#2ecc71"><i
                                                        class="fa fa-comment"></i></font> &middot; <font
                                                    color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                        </div>
                                    </div>
                                    <?php
                                }
                                break;
                            case 'friends':
                                if ($request == 'default-ajax') {
                                    $posts[$unique_id]['post_data'] = array(
                                        'what_happened' => $what_happened,
                                        'font_icon' => 'fa fa-user-plus',
                                        'font_color' => '#34495e',
                                        'date' => Convert::convert_time($date)
                                    );
                                } else {
                                    ?>
                                    <div class="activityTimelinePost clearfix postComment" id='post-<?php echo $unique_id; ?>' style="border-bottom: 1px solid #ddd;">
                                        <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                        <div class="rightActivity" style="width: 83%;">
                                            <h5><a style=''
                                                   href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['first_name']); ?> <?php echo ucwords($u['last_name']); ?></a> <?php echo $what_happened; ?>
                                            </h5>
                                            <h5 style="font-size: 14px;padding-top: 3px;"><font color="#34495e"><i
                                                        class="fa fa-user-plus"></i></font> &middot; <font
                                                    color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                        </div>
                                    </div>
                                    <?php
                                }
                                break;
                            case 'postToUserProfile':
                                if ($request == 'default-ajax') {
                                    $posts[$unique_id]['post_data'] = array(
                                        'what_happened' => $what_happened,
                                        'font_icon' => 'fa fa-reply',
                                        'font_color' => '#34495e',
                                        'date' => Convert::convert_time($date)
                                    );
                                } else {
                                    ?>
                                    <div class="activityTimelinePost clearfix postComment" id='post-<?php echo $unique_id; ?>' style="border-bottom: 1px solid #ddd;">
                                        <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                        <div class="rightActivity" style="width: 83%;">
                                            <h5><a style=''
                                                   href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['first_name']); ?> <?php echo ucwords($u['last_name']); ?></a> <?php echo $what_happened; ?>
                                            </h5>
                                            <h5 style="font-size: 14px;padding-top: 3px;"><font color="#34495e"><i
                                                        class="fa fa-reply"></i></font> &middot; <font
                                                    color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                        </div>
                                    </div>
                                    <?php
                                }
                                break;
                            case 'newProfilePic':
                                if ($request == 'default-ajax') {
                                    $posts[$unique_id]['post_data'] = array(
                                        'what_happened' => $what_happened,
                                        'font_icon' => 'fa fa-picture-o',
                                        'font_color' => '#9b59b6',
                                        'date' => Convert::convert_time($date),
                                        'external' => APP_URL . 'user_data/' . $u['unique_salt_id'] . '/profile_pictures/' . $external_id
                                    );
                                } else {
                                    ?>
                                    <div class="activityTimelinePost clearfix postComment" id='post-<?php echo $unique_id; ?>' style="border-bottom: 1px solid #ddd;">
                                        <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                        <div class="rightActivity" style="width: 83%;">
                                            <h5><a style=''
                                                   href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['first_name']); ?> <?php echo ucwords($u['last_name']); ?></a> <?php echo $what_happened; ?>
                                            </h5>
                                            <img src="<?php echo APP_URL; ?>user_data/<?php echo $u['unique_salt_id']; ?>/profile_pictures/<?php echo $external_id; ?>"
                                                 style="height: 110px;width: 100px;margin: 10px;margin-left: 0px;border-radius: 5px !important;"/>
                                            <h5 style="font-size: 14px;padding-top: 3px;"><font color="#9b59b6"><i
                                                        class="fa fa-picture-o"></i></font> &middot; <font
                                                    color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                        </div>
                                    </div>
                                    <?php
                                }
                                break;
                            case 'newBannerPic':
                                if ($request == 'default-ajax') {
                                    $posts[$unique_id]['post_data'] = array(
                                        'what_happened' => $what_happened,
                                        'font_icon' => 'fa fa-picture-o',
                                        'font_color' => '#9b59b6',
                                        'date' => Convert::convert_time($date),
                                        'external' => APP_URL . 'user_data/' . $u['unique_salt_id'] . '/banners/' . $external_id
                                    );
                                } else {
                                    ?>
                                    <div class="activityTimelinePost clearfix postComment" id='post-<?php echo $unique_id; ?>' style="border-bottom: 1px solid #ddd;">
                                        <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                        <div class="rightActivity" style="width: 83%;">
                                            <h5><a style=''
                                                   href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['first_name']); ?> <?php echo ucwords($u['last_name']); ?></a> <?php echo $what_happened; ?>
                                            </h5>
                                            <img src="<?php echo APP_URL; ?>user_data/<?php echo $u['unique_salt_id']; ?>/banners/<?php echo $external_id; ?>"
                                                 style="height: 200px;width: 100%;margin: 10px;margin-left: 0px;border-radius: 5px !important;"/>
                                            <h5 style="font-size: 14px;padding-top: 3px;"><font color="#9b59b6"><i
                                                        class="fa fa-picture-o"></i></font> &middot; <font
                                                    color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                        </div>
                                    </div>
                                    <?php
                                }
                                break;
                            case 'newClique':
                                if ($request == 'default-ajax') {
                                    $posts[$unique_id]['post_data'] = array(
                                        'what_happened' => $what_happened,
                                        'font_icon' => 'fa fa-users',
                                        'font_color' => '#aaa',
                                        'date' => Convert::convert_time($date)
                                    );
                                } else {
                                    ?>
                                    <div class="activityTimelinePost clearfix postComment" id='post-<?php echo $unique_id; ?>' style="border-bottom: 1px solid #ddd;">
                                        <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                        <div class="rightActivity" style="width: 83%;">
                                            <h5><a style=''
                                                   href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['first_name']); ?> <?php echo ucwords($u['last_name']); ?></a> <?php echo $what_happened; ?>
                                            </h5>
                                            <h5 style="font-size: 14px;padding-top: 3px;"><font color="#aaa"><i
                                                        class="fa fa-users"></i> &middot; <?php echo Convert::convert_time($date); ?>
                                                </font></h5>
                                        </div>
                                    </div>
                                    <?php
                                }
                                break;
                            case 'cliqueJoin':
                                if ($request == 'default-ajax') {
                                    $posts[$unique_id]['post_data'] = array(
                                        'what_happened' => $what_happened,
                                        'font_icon' => 'fa fa-users',
                                        'font_color' => '#aaa',
                                        'date' => Convert::convert_time($date)
                                    );
                                } else {
                                    ?>
                                    <div class="activityTimelinePost clearfix postComment" id='post-<?php echo $unique_id; ?>' style="border-bottom: 1px solid #ddd;">
                                        <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                        <div class="rightActivity" style="width: 83%;">
                                            <h5><a style=''
                                                   href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['first_name']); ?> <?php echo ucwords($u['last_name']); ?></a> <?php echo $what_happened; ?>
                                            </h5>
                                            <h5 style="font-size: 14px;padding-top: 3px;"><font color="#aaa"><i
                                                        class="fa fa-users"></i> &middot; <?php echo Convert::convert_time($date); ?>
                                                </font></h5>
                                        </div>
                                    </div>
                                    <?php
                                }
                                break;
                        }
                        break;
                }
            }
        }else{
            ?>
            <div style="padding: 15px;">
                <h3 style="text-align: center;color: #ddd;font-weight: 400;font-size: 3.2em;"><i class="fa fa-frown-o"></i></h3>
                <p style="text-align: center;padding: 10px;font-size: 1.1em;">Aw! There is no activity to show</p>
            </div>
            <?php
        }
        if ($request == 'default-ajax') {
            echo json_encode($posts);
            return false;
        }
    }
}

?>