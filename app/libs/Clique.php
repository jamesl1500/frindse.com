<?php

class Clique extends Database
{
    // For checking to see if this person has been invited before
    static public function checkInviteStatus($invited, $clique_id)
    {
        if(!empty($invited) && !empty($clique_id))
        {
            $db = new Database;

            $query = $db->prepare("SELECT * FROM clique_invites WHERE inviter='".$_SESSION['uid']."' AND invited='".$invited."' AND clique_id='".$clique_id."'");
            $query->execute();

            return $query->rowCount();
        }
    }

    // For making decisions
    static public function d($invite_id, $d)
    {
        if(!empty($invite_id) && !empty($d))
        {
            // Make sure this invite is valid
            $db = new Database;

            $query = $db->prepare("SELECT * FROM ".CLIQUE_INVITES." WHERE invite_token=:invite");
            $query->execute(array(':invite'=>$invite_id));

            if($query->rowCount())
            {
                $fetch = $query->fetch(PDO::FETCH_ASSOC);

                switch ($d)
                {
                    case 'yes':
                        // make sure this person isnt in this chat
                        $check = $db->prepare("SELECT * FROM " . CLIQUE_MEMBERS . " WHERE c_main_unique_id=:c_id AND c_m_user_id=:uid");
                        $check->execute(array(':c_id' => $fetch['clique_id'], ':uid' => $fetch['invited']));

                        if($check->rowCount() == 0)
                        {
                            try {
                                // Now delete all of the invites
                                $delete = $db->prepare("DELETE FROM clique_invites WHERE invite_token=:id AND invited=:invited OR clique_id='" . $fetch['clique_id'] . "' AND invited=:invited");
                                if ($delete->execute(array(':id' => $invite_id, ':invited' => $fetch['invited'])))
                                {
                                    // Now add this person to the clique even if its a paid clique
                                    Notifications::makeTimelineActivityPost($fetch['invited'], 'cliqueJoin', 'has joined a <a href="' . APP_URL . 'clique/' . $fetch['clique_id'] . '">clique!</a>', $fetch['invited'], $fetch['clique_id']);
                                    // Now add the person
                                    $insert = $db->prepare("INSERT INTO " . CLIQUE_MEMBERS . " VALUES('','" . $fetch['invited'] . "','" . self::getCliqueId($fetch['clique_id']) . "', '" . $fetch['clique_id'] . "','member',now())");
                                    $insert->execute();

                                    echo Response::make("Cool, you've joined this clique!", 'JSON', 1);
                                    break;
                                }
                            }catch(PDOException $e)
                            {
                                echo Response::make("Error has occurred", 'JSON', 0);
                                break;
                            }
                        }else{
                            echo Response::make("You're already in this chat!", 'JSON', 0);
                            break;
                        }
                        break;
                    case 'no':
                        // Just delete the invite
                        $delete = $db->prepare("DELETE FROM clique_invites WHERE invite_token=:id");
                        $delete->execute(array(':id' => $invite_id));

                        echo Response::make("Invite has been ignored!", 'JSON', 1);
                        break;
                    default:
                        echo Response::make("Invalid request!", 'JSON', 0);
                        return false;
                        break;
                }
            }else{
                echo Response::make("This invite is invalid", 'JSON', 0);
                return false;
            }
        }
    }

    // Invite to clique
    static public function invite($user_id_to_invited, $clique_id)
    {
        $token = md5(Validation::randomHash());

        if(!empty($user_id_to_invited) && !empty($clique_id))
        {
            if(Users::checkExists($user_id_to_invited) == 1)
            {
                // Now lets make sure they havent invited this person before
                if (self::checkInviteStatus($user_id_to_invited, $clique_id) == 0)
                {
                    // Now make sure this person isnt in the clique already
                    $db = new Database;

                    $query = $db->prepare("SELECT * FROM " . CLIQUE_MEMBERS . " WHERE c_main_unique_id=:c_id AND c_m_user_id=:uid");
                    $query->execute(array(':c_id' => $clique_id, ':uid' => $user_id_to_invited));

                    if($query->rowCount() == 0)
                    {
                        // Now insert the invite
                        $invite = $db->prepare("INSERT INTO clique_invites VALUES('', '".$_SESSION['uid']."', '".$user_id_to_invited."', '".$clique_id."', '". $token."')");
                        if($invite->execute())
                        {
                            // get the name of the clique
                            $get = $db->prepare("SELECT c_name, c_username FROM " . CLIQUES . " WHERE c_unique_id='".$clique_id."'");
                            $get->execute();

                            $fetch = $get->fetch(PDO::FETCH_ASSOC);

                            Notifications::makeNote('clique_invite', $user_id_to_invited, "has invited you to join <a href='" . APP_URL . "clique/".$fetch['c_username']."'>".ucwords($fetch['c_name'])."</a>", $token);

                            echo Response::make("Invited sent!", 'JSON', 1);
                            return false;
                        }
                    }else{
                        echo Response::make("This person is already a member :)", 'JSON', 0);
                        return false;
                    }
                } else {
                    echo Response::make("You've already invited this person :)", 'JSON', 0);
                    return false;
                }
            }else{
                echo Response::make("This person dosent exist!", 'JSON', 0);
                return false;
            }
        }
    }

    /* For changing clique colors */
    static public function editCliqueColor($color, $clique_id)
    {
        if(!empty($color) && !empty($clique_id))
        {
            // Make sure that this clique exist and that the logged user is author
            if(self::checkExists($clique_id))
            {
                $db = new Database;

                // Check if the person is author
                $update = $db->prepare("UPDATE " . CLIQUES . " SET c_color='".$color."' WHERE c_unique_id='".$clique_id."'");
                $update->execute();

                echo Response::make('Clique color has been updated!', 'JSON', 1);
                return false;
            }
        }
    }

    /* Render clique color */
    static public function renderCliqueColor($clique_id)
    {
        if(!empty($clique_id) && self::checkExists($clique_id) == 1)
        {
            $db = new Database();

            $query = $db->prepare("SELECT c_color FROM " . CLIQUES . " WHERE c_unique_id='".$clique_id."'");
            $query->execute();

            if($query->rowCount() == 1)
            {
                $fetch = $query->fetch(PDO::FETCH_ASSOC);

                return $fetch['c_color'];
            }
        }
    }

    /* For making decisions */
    static public function makeTheMoveHomie($cid, $type, $request_id)
    {
        // Make sure this clique exists
        if (self::checkExists($cid) == 1) {
            // Make sure we have a valid type
            $types = array('accept', 'ignore');

            if (in_array($type, $types)) {
                // Now make sure this request id is valid
                if (self::checkRequestExists($cid, $request_id) == 1) {
                    // Now get the data for this request
                    $db = new Database;

                    $query = $db->prepare("SELECT * FROM " . CLIQUE_REQUESTS . " WHERE c_main_unique_id='" . $cid . "' AND r_id='" . $request_id . "'");
                    $query->execute();

                    if ($query->rowCount() > 0) {
                        $fetch = $query->fetch(PDO::FETCH_ASSOC);
                        $r_id = $fetch['r_id'];
                        $user_id = $fetch['user_id'];
                        $c_main_unique_id = $fetch['c_main_unique_id'];
                        $date_sent = $fetch['date_sent'];

                        // Now render the types
                        switch ($type) {
                            case 'accept':
                                // Add the person to the clique and let everyone know
                                Notifications::makeNote("c_join_accept", $user_id, "has accepted your request to join their <a href='" . APP_URL . "cliques/" . $cid . "'></a>");
                                Notifications::makeTimelineActivityPost($user_id, 'cliqueJoin', 'has joined a <a href="' . APP_URL . 'cliques/' . $cid . '">clique!</a>', $user_id, $cid);
                                // Now add the person
                                $insert = $db->prepare("INSERT INTO " . CLIQUE_MEMBERS . " VALUES('','" . $user_id . "','" . self::getCliqueId($cid) . "', '" . $cid . "','member',now())");
                                $insert->execute();
                                // Now delete the request
                                $delete = $db->prepare("DELETE FROM " . CLIQUE_REQUESTS . " WHERE c_main_unique_id='" . $cid . "' AND r_id='" . $request_id . "' AND user_id='" . $user_id . "'");
                                $delete->execute();
                                echo json_encode(array('code' => 1));
                                return false;
                                break;
                            case 'ignore':
                                // Now just delete the request and tell the person he was rejected
                                Notifications::makeNote("clique_join_rejection", $user_id, "has rejected your request to join their <a href='" . APP_URL . "cliques/" . $cid . "'>clique</a>");
                                $delete = $db->prepare("DELETE FROM " . CLIQUE_REQUESTS . " WHERE c_main_unique_id='" . $cid . "' AND r_id='" . $request_id . "' AND user_id='" . $user_id . "'");
                                $delete->execute();

                                echo json_encode(array('code' => 1));
                                return false;
                                break;
                        }
                    } else {
                        echo json_encode(array('code' => 0, 'string' => 'Sorry but this request is not valid'));
                        return false;
                    }
                } else {
                    echo json_encode(array('code' => 0, 'string' => 'Sorry but this request is not valid'));
                    return false;
                }
            } else {
                echo json_encode(array('code' => 0, 'string' => 'Ahh i see your trying to hack my site. Haha nice try'));
                return false;
            }
        } else {
            echo json_encode(array('code' => 0, 'string' => 'Sorry but this clique dose not exists! Please refresh the page'));
            return false;
        }
    }

    static public function getCliqueId($clique_id)
    {
        if (!empty($clique_id)) {
            $db = new Database;

            $check = $db->prepare("SELECT * FROM " . CLIQUES . " WHERE c_unique_id='" . $clique_id . "'");
            $check->execute();

            $fetch = $check->fetch(PDO::FETCH_ASSOC);

            return $fetch['c_id'];
        }
    }

    static public function getCliqueCreator($clique_id)
    {
        if (self::checkExists($clique_id) == 1) {
            $db = new Database;

            $get = $db->prepare("SELECT * FROM " . CLIQUE_MEMBERS . " WHERE c_main_unique_id='" . $clique_id . "' AND c_m_priv='founder'");
            $get->execute();

            if ($get->rowCount() > 0) {
                $fetch = $get->fetch(PDO::FETCH_ASSOC);

                return $fetch['c_m_user_id'];
            }
        }
    }

    static public function checkRequestExists($clique_id, $request_id)
    {
        if (!empty($clique_id) && !empty($request_id)) {
            $db = new Database;

            $check = $db->prepare("SELECT * FROM " . CLIQUE_REQUESTS . " WHERE r_id='" . $request_id . "' AND c_main_unique_id='" . $clique_id . "'");
            $check->execute();

            return $check->rowCount();
        }
    }

    /* Get the requests for this persons cliques*/
    static public function viewCliqueRequests($uid)
    {
        if (!empty($uid) && User::checkExists($uid) == 1) {
            $db = new Database;
            $count = 0;
            // Get all the cliques where he is founder
            $query = $db->prepare("SELECT * FROM " . CLIQUE_MEMBERS . " WHERE c_m_user_id='" . $uid . "' AND c_m_priv='founder'");
            $query->execute();

            if ($query->rowCount() > 0) {
                while ($fetch = $query->fetch(PDO::FETCH_ASSOC)) {
                    $unique_id = $fetch['c_main_unique_id'];

                    if ($unique_id != "") {
                        $cliqueGet = $db->prepare("SELECT * FROM " . CLIQUES . " WHERE c_unique_id='" . $unique_id . "'");
                        $cliqueGet->execute();

                        if ($cliqueGet->rowCount() > 0) {
                            $cfetch = $cliqueGet->fetch(PDO::FETCH_ASSOC);
                            // Now get all the clique requests
                            $getCliques = $db->prepare("SELECT * FROM " . CLIQUE_REQUESTS . " WHERE c_main_unique_id='" . $unique_id . "'");
                            $getCliques->execute();

                            if ($getCliques->rowCount() > 0) {
                                $count++;
                                ?>
                                <section class="cliqueSection" style="margin: 5px;">
                                    <div class="topCliqueSection clearfix"
                                         style="padding: 5px;width: 100%;border-bottom: 1px solid #eee;">
                                        <img
                                            src="<?php echo APP_URL; ?>clique_data/<?php echo $unique_id; ?>/clique_profile_pic/<?php echo $cfetch['c_profile_pic']; ?>"
                                            style="height: 30px;width: 30px;float: left;"/> <a
                                            href='<?php echo APP_URL; ?>clique/<?php echo $cfetch['c_username']; ?>'
                                            style="padding:10px;margin-top: 10px;vertical-align: bottom;"
                                            href=""><?php echo $cfetch['c_name']; ?></a>
                                        <span
                                            style="float: right;padding: 10px;padding-bottom: 0px;padding-top: 6px;color: #e74c3c;"><?php echo $getCliques->rowCount(); ?>
                                            <i class="fa fa-user-plus"></i></span>
                                    </div>
                                    <div class="cliqueRequestsMain">
                                        <?php
                                        while ($rfetch = $getCliques->fetch(PDO::FETCH_ASSOC)) {
                                            $r_id = $rfetch['r_id'];
                                            $usr_id = $rfetch['user_id'];
                                            $type = $rfetch['type'];
                                            $date = $rfetch['date_sent'];

                                            // Now get the users data
                                            $getUserData = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $usr_id . "'");
                                            $getUserData->execute();
                                            $getUserDataFetch = $getUserData->fetch(PDO::FETCH_ASSOC);
                                            $firstname = $getUserDataFetch['first_name'];
                                            $lastname = $getUserDataFetch['last_name'];
                                            $username = $getUserDataFetch['username'];
                                            $pic = $getUserDataFetch['profile_pic'];
                                            $bio = $getUserDataFetch['bio'];
                                            $salt = $getUserDataFetch['unique_salt_id'];
                                            ?>
                                            <div class="userLister cliqueRequestHolder clearfix"
                                                 id="cliqueRequestHolder<?php echo $unique_id; ?><?php echo $r_id; ?>"
                                                 style="padding: 5px;border-bottom: 1px solid #eee;">
                                                <div class="profilePicLeft" style="float: left;">
                                                    <img style="height: 50px;width: 50px;"
                                                         src="<?php echo APP_URL; ?>user_data/<?php echo $salt; ?>/profile_pictures/<?php echo $pic; ?>"/>
                                                </div>
                                                <div class="rightPerson"
                                                     style="margin-left: 60px;font-size: 14px;">
                                                    <div class="" style="float: left;width: 60%;">
                                                        <h3 style="font-size: 14px;"><a
                                                                href="<?php echo APP_URL; ?>profile/<?php echo $username; ?>"><?php echo ucwords($firstname); ?> <?php echo ucwords($lastname); ?></a>
                                                            <font color="#66757f">@<?php echo $username; ?></font></h3>

                                                        <p style="margin-top: 0px;padding-top: 2px;margin-bottom: 2px;"><?php echo $bio; ?></p>
                                                        <?php if (isset($_SESSION['uid']) && $_SESSION['uid'] != $usr_id) { ?>
                                                            <?php
                                                            // Render the persons relationship
                                                            $array1 = array('person1' => $usr_id, 'person2' => $_SESSION['uid'], 'check' => 'request-status');
                                                            $array2 = array('person1' => $usr_id, 'person2' => $_SESSION['uid'], 'check' => 'friendship');
                                                            $rid = Friends::getRequestId($usr_id, $_SESSION['uid']);
                                                            if (Friends::checkFriendshipStatus($array1) == 1) {
                                                                // Means the person can add or delete as friend
                                                                if (Friends::checkFriendshipStatus($array2) == 0) {
                                                                    ?>
                                                                    <a href='#' class='friendListener not-friends'
                                                                       id='friendRBTN-<?php echo $usr_id; ?>'
                                                                       ftype='sendFRequest' uid='<?php echo $usr_id; ?>'
                                                                       from="<?php echo $_SESSION['uid']; ?>">Add
                                                                        Friend</a>
                                                                    <a href='#'
                                                                       class='friendListener request-pending request-canceler hidden'
                                                                       id='friendBTN-<?php echo $usr_id; ?>'
                                                                       request_id='' ftype='cancelFRequest'
                                                                       user_to='<?php echo $usr_id; ?>'>Delete
                                                                        request</a>
                                                                    <?php
                                                                } else if (Friends::checkFriendshipStatus($array2) == 1) {
                                                                    ?>
                                                                    <a href='#' class='friendListener friends'
                                                                       id='friendBTN-<?php echo $usr_id; ?>'
                                                                       ftype='cancelFRequest'
                                                                       request_id='<?php echo $rid; ?>'>Unfriend</a>
                                                                    <a href='#'
                                                                       class='friendListener not-friends hidden'
                                                                       id='friendRBTN-<?php echo $usr_id; ?>'
                                                                       ftype='sendFRequest' uid='<?php echo $usr_id; ?>'
                                                                       from="<?php echo $_SESSION['uid']; ?>">Add
                                                                        Friend</a>
                                                                    <?php
                                                                }
                                                            } else {
                                                                // means the logged person has already sent this person a request
                                                                // Get request data to let the person cancel a request

                                                                ?>
                                                                <a href='#'
                                                                   class='friendListener request-pending request-canceler'
                                                                   id='friendBTN-<?php echo $usr_id; ?>'
                                                                   ftype='cancelFRequest'
                                                                   request_id='<?php echo $rid; ?>'
                                                                   user_to='<?php echo $usr_id; ?>'>Delete request</a>
                                                                <a href='#' class='friendListener not-friends hidden'
                                                                   id='friendRBTN-<?php echo $usr_id; ?>'
                                                                   ftype='sendFRequest' uid='<?php echo $usr_id; ?>'
                                                                   from="<?php echo $_SESSION['uid']; ?>">Add Friend</a>
                                                                <?php
                                                            }
                                                            ?>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </div><br />
                                                <div class="rightBtns"
                                                     style="padding: 10px;width: 300px;margin-top: 35px;padding-left: 0px;">
                                                    <button title="accept their request to join your clique"
                                                            style="width: auto;background: #2ecc71;padding: 9px 12px 9px 12px;"
                                                            class="cliqueBtn cliqueJoinDBtn"
                                                            data-x="<?php echo $unique_id; ?>" data-type="accept"
                                                            data-requestid="<?php echo $r_id; ?>" data-tko="<?php echo $_SESSION['token'] ?>"><i
                                                            class="fa fa-plus-square"></i> Accept
                                                    </button>
                                                    <button title="ignore their request to join your clique"
                                                            style="width: auto;padding: 9px 12px 9px 12px;" class="cliqueBtn cliqueJoinDBtn"
                                                            data-x="<?php echo $unique_id; ?>" data-type="ignore"
                                                            data-requestid="<?php echo $r_id; ?>" data-tko="<?php echo $_SESSION['token'] ?>"><i
                                                            class="fa fa-minus-square"></i> Ignore
                                                    </button>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </section>
                                <?php
                            } else {
                                $count--;
                            }
                        }
                    }
                }
            } else {
                echo "<div class'response success'>Sorry you dont have any cliques</div>";
                return false;
            }
        }
    }

    static public function seachQuery($search)
    {
        if (!empty($search) && $search != "") {
            // Strip the string
            $search = Validation::santitize($search);

            if ($search != "") {
                $db = new Database;

                $query = $db->prepare("SELECT * FROM " . CLIQUES . " WHERE c_name LIKE '%" . $search . "%' OR c_username LIKE '%" . $search . "%' OR c_bio LIKE '%" . $search . "%'");
                $query->execute();

                if ($query->rowCount() > 0) {
                    while ($fetch = $query->fetch(PDO::FETCH_ASSOC)) {
                        $c_id = $fetch['c_id'];
                        $c_name = $fetch['c_name'];
                        $c_username = $fetch['c_username'];
                        $c_bio = $fetch['c_bio'];
                        $c_profile_pic = $fetch['c_profile_pic'];
                        $c_banner_pic = $fetch['c_banner_pic'];
                        $c_privacy = $fetch['c_privacy'];
                        $c_created_date = $fetch['c_created_date'];
                        $c_active = $fetch['c_active'];
                        $c_unique_id = $fetch['c_unique_id'];

                        $numGet = $get = $db->prepare("SELECT * FROM " . CLIQUE_MEMBERS . " WHERE c_main_unique_id='" . $c_unique_id . "' AND c_main_id='" . $c_id . "'");
                        $numGet->execute();

                        // Get the founders information
                        $get = $db->prepare("SELECT * FROM " . CLIQUE_MEMBERS . " WHERE c_main_unique_id='" . $c_unique_id . "' AND c_main_id='" . $c_id . "' AND c_m_priv='founder'");
                        $get->execute();
                        $gf = $get->fetch(PDO::FETCH_ASSOC);
                        $user_id = $gf['c_m_user_id'];

                        ?>
                        <div class="cliqueDisplay clearfix" style="min-height: 100px;border-bottom: 1px solid #eee;">
                            <div class="innerClique">
                                <div class="farLeft" style="float: left;">
                                    <img
                                        src='<?php echo APP_URL; ?>clique_data/<?php echo $c_unique_id; ?>/clique_profile_pic/<?php echo $c_profile_pic; ?>'
                                        style="height: 100%;width: 100px;"/>
                                </div>
                                <div class="allRight" style="float: right;width: 88%;padding: 5px;">
                                    <div class="top" style="">
                                        <a href="<?php echo APP_URL; ?>clique/<?php echo $c_username; ?>"><h3
                                                style="padding: 0px;margin: 0px;"><?php echo ucwords($c_name); ?> &middot;
                                                <span style="font-size: 16px;color:#aaa;"><i
                                                        class="fa fa-users"></i> <?php echo $numGet->rowCount(); ?></span>
                                            </h3></a>
                                    </div>
                                    <div class="middleEverything">
                                        <p style="padding-top: 2px;margin: 0px;width: 300px;"><?php echo $c_bio; ?></p>
                                    </div>
                                    <div class="bottomEverything" style="padding-top: 7px;">
                                        <a style="color: #aaa;"
                                           href="<?php echo APP_URL; ?>profile/<?php echo User::get('users', $user_id, 'username'); ?>"><img
                                                src="<?php echo APP_URL; ?>user_data/<?php echo User::get('users', $user_id, 'username'); ?>/profile_pictures/<?php echo User::get('users', $user_id, 'profile_pic'); ?>"
                                                style="height: 20px;width: 20px;"/> <span
                                                style="position: relative;top: -5px;"><?php echo User::get('users', $user_id, 'username'); ?>
                                                (Founder) &middot; Type: <?php echo $c_privacy; ?></span></a>
                                    </div>
                                    <?php
                                    $request_status = Clique::joinStatus($c_unique_id, $_SESSION['uid'], 'request-status');
                                    $member_status = Clique::joinStatus($c_unique_id, $_SESSION['uid'], 'member');
                                    ?>
                                    <?php if ($_SESSION['uid'] != $user_id) { ?>
                                        <span
                                            style="float: right;position: relative;top: -65px;height: 0px;right: 50px;">
                                    <?php Clique::renderBtn($c_unique_id, $c_privacy); ?>
                                </span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<div class='response success'>No cliques matching: " . $search . "</div>";
                    return false;
                }
            }
        }
    }

    static public function changeBannerPic($clique_id, $profilePic)
    {
        if (!empty($clique_id) && !empty($profilePic)) {
            if (self::checkExists($clique_id) == 1) {
                $types = array('image/jpg', 'image/jpeg', 'image/png', 'image/ttf');

                if (in_array($profilePic['type'], $types)) {
                    $upload = new Upload($profilePic, 'photo', array(
                        'photoRootLocation' => SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_banners/',
                        'photoPublicLocation' => APP_URL . 'clique_data/' . $clique_id . '/clique_banners/'),
                        'regular');

                    // Now quickly update photo just to see if this works
                    $db = new Database;

                    $update = $db->prepare("UPDATE " . CLIQUES . " SET c_banner_pic='" . $upload->encryptedFileName . "' WHERE c_unique_id='" . $clique_id . "'");
                    $update->execute();

                    $response = array();
                    $response['string'] = "Clique profile picture has been updated!";
                    $response['code'] = 1;
                    $response['type'] = "profilePic";
                    $response['x'] = $clique_id;
                    $response['link'] = $upload->filePublicPath;
                    echo json_encode($response);
                    return false;
                } else {
                    $response = array();
                    $response['string'] = "Photo has to be either jpg, jpeg, or png";
                    $response['code'] = 0;
                    echo json_encode($response);
                    return false;
                }
            } else {
                $response = array();
                $response['string'] = "This clique does not exist!";
                $response['code'] = 0;
                echo json_encode($response);
                return false;
            }
        }
    }

    static public function changeProfilePic($clique_id, $profilePic)
    {
        if (!empty($clique_id) && !empty($profilePic)) {
            if (self::checkExists($clique_id) == 1) {
                $types = array('image/jpg', 'image/jpeg', 'image/png', 'image/ttf');

                if (in_array($profilePic['type'], $types)) {
                    $upload = new Upload($profilePic, 'photo', array(
                        'photoRootLocation' => SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_profile_pic/',
                        'photoPublicLocation' => APP_URL . 'clique_data/' . $clique_id . '/clique_profile_pic/'),
                        'regular');

                    // Now quickly update photo just to see if this works
                    $db = new Database;

                    $update = $db->prepare("UPDATE " . CLIQUES . " SET c_profile_pic='" . $upload->encryptedFileName . "' WHERE c_unique_id='" . $clique_id . "'");
                    $update->execute();

                    $response = array();
                    $response['string'] = "Clique profile picture has been updated!";
                    $response['code'] = 1;
                    $response['type'] = "profilePic";
                    $response['x'] = $clique_id;
                    $response['link'] = $upload->filePublicPath;
                    echo json_encode($response);
                    return false;
                } else {
                    $response = array();
                    $response['string'] = "Photo has to be either jpg, jpeg, or png";
                    $response['code'] = 0;
                    echo json_encode($response);
                    return false;
                }
            } else {
                $response = array();
                $response['string'] = "This clique does not exist!";
                $response['code'] = 0;
                echo json_encode($response);
                return false;
            }
        }
    }

    static public function checkExists($clique_id)
    {
        if (!empty($clique_id)) {
            $db = new Database;

            $check = $db->prepare("SELECT * FROM " . CLIQUES . " WHERE c_unique_id='" . $clique_id . "'");
            $check->execute();

            return $check->rowCount();
        }
    }

    static public function changePrivacySetting($setting, $clique_id)
    {
        if (!empty($setting) && self::checkExists($clique_id) == 1) {
            $s = Validation::santitize($setting);

            if ($s != "") {
                $db = new Database;

                // Query
                $query = $db->prepare("UPDATE " . CLIQUES . " SET c_privacy='" . $s . "' WHERE c_unique_id='" . $clique_id . "'");
                $query->execute();

                $response = array();
                $response['code'] = 1;
                $response['string'] = "Changes has been saved!";
                echo json_encode($response);
                return false;
            }
        }
    }

    static public function changeBasicInfo($clique_name, $clique_username, $clique_bio, $clique_id)
    {
        if (!empty($clique_name) && !empty($clique_username) && !empty($clique_bio) && !empty($clique_id)) {
            $db = new Database;

            // Make sure the clique exists
            if (self::checkExists($clique_id) == 1) {
                // Now do work
                $name = Validation::santitize($clique_name);
                $username = Validation::santitize($clique_username);
                $bio = Validation::santitize($clique_bio);

                $update = $db->prepare("UPDATE " . CLIQUES . " SET c_name='" . $name . "',c_username='" . $username . "',c_bio='" . $bio . "' WHERE c_unique_id='" . $clique_id . "'");
                $update->execute();

                $response = array();
                $response['code'] = 1;
                $response['string'] = "Changes has been saved!";
                echo json_encode($response);
                return false;
            } else {
                $response = array();
                $response['code'] = 0;
                $response['string'] = "This clique does not exist!";
                echo json_encode($response);
                return false;
            }
        } else {
            $response = array();
            $response['code'] = 0;
            $response['string'] = "Your clique name, bio and username cant be empty silly!";
            echo json_encode($response);
            return false;
        }
    }

    static public function displayPSOptions($clique_id)
    {
        if (!empty($clique_id)) {
            // Call db
            $db = new Database;

            // Get status
            $query = $db->prepare("SELECT * FROM " . CLIQUES . " WHERE c_unique_id='" . $clique_id . "'");
            $query->execute();

            // Fetch
            $fetch = $query->fetch(PDO::FETCH_ASSOC);
            $rs = $fetch['c_privacy'];
            // Render
            $types = array('<i class="fa fa-unlock"></i> Public' => 'public', '<i class="fa fa-lock"></i>  Private' => 'private'); //,'<i class="fa fa-lock"></i>  Invite-only' => 'invite-only'
            ?>
            <div class='sellecters'>
                <?php
                $the_key = $rs; // or whatever you want
                foreach ($types as $key => $val) {
                    ?>
                    <div class='inputField'>
                        <p style='padding: 0;margin: 0;'><?php echo $key; ?>:</p>

                        <p style='color: #ccc;margin: 0;padding-left: 3px;padding: 5px;'></p>
                        <input type="radio" name="radioSetting" id='setting_<?php echo $val; ?>' class=''
                               value="<?php echo $val; ?>" <?php if ($val == $the_key) echo 'checked'; ?>/>Set clique
                        to <?php echo $val; ?>
                    </div><br/>
                    <?php
                }
                ?>
            </div>
            <?php
        }
    }

    static public function inviteToClique()
    {

    }

    static public function displayPosts($clique_id, $clique_name, $names)
    {
        if (!empty($clique_id)) {
            $db = new Database;

            // Get all posts
            $query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM . " WHERE type='clique-post' ORDER BY id DESC");
            $query->execute();

            if ($query->rowCount() > 0) {
                while ($fetch = $query->fetch(PDO::FETCH_ASSOC)) {
                    $id = $fetch['id'];
                    $unique_id = $fetch['unique_id'];
                    $user_by = $fetch['user_by'];
                    $user_posted_to = $fetch['user_posted_to'];
                    $date = $fetch['date'];

                    // Now get all clique-post posts thingys
                    $get = $db->prepare("SELECT * FROM timeline_item_clique_post WHERE unique_id='" . $unique_id . "' AND clique_id='" . $clique_id . "'");
                    $get->execute();

                    if ($get->rowCount() > 0) {
                        // Now get the posts data
                        $cfetch = $get->fetch(PDO::FETCH_ASSOC);
                        $type = $cfetch['type'];

                        // Render Types
                        // Get the user_by persons data
                        $user_get = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $user_by . "' AND account_locked='unlocked' AND activated='1'");
                        $user_get->execute();
                        $u = $user_get->fetch(PDO::FETCH_ASSOC);

                        // Get the user_by persons data
                        $user_get = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $user_by . "' AND account_locked='unlocked' AND activated='1'");
                        $user_get->execute();
                        $u = $user_get->fetch(PDO::FETCH_ASSOC);

                        $commentGetNum = $db->prepare("SELECT * FROM " . TIMELINE_POST_COMMENTS . " WHERE timeline_post_unique_id='" . $unique_id . "'");
                        $commentGetNum->execute();

                        $likesNumber = $db->prepare("SELECT * FROM " . TIMELINE_POST_LIKES . " WHERE unique_id='" . $unique_id . "'");
                        $likesNumber->execute();

                        // Display the different posts
                        switch ($type) {
                            case 'text':
                                $get_data_query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM_TEXT . " WHERE unique_id='" . $unique_id . "'");
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
                                    $user_get2 = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $user_posted_to . "' AND account_locked='unlocked' AND activated='1'");
                                    $user_get2->execute();
                                    $u2 = $user_get2->fetch(PDO::FETCH_ASSOC);
                                    $pid = 1;
                                    $up = "<a style='' href='" . APP_URL . "profile/" . $u['username'] . "'>" . ucwords($u['first_name']) . " " . ucwords($u['last_name']) . "</a> » <a style='padding-top: 5px;' href='" . APP_URL . "profile/" . $u2['username'] . "'>" . ucwords($u2['first_name']) . " " . ucwords($u2['last_name']) . "</a>";
                                } else {
                                    $pid = 0;
                                    $up = "<a style='display: inline;' href='" . APP_URL . "profile/" . $u['username'] . "'>" . ucwords($u['first_name']) . " " . ucwords($u['last_name']) . "</a>";
                                }

                                //if (Report::checkStatus($_SESSION['uid'], $unique_id) == 0) {
                                ?>
                                <div class='timeline-item clearfix post animate bounceIn' id='post-<?php echo $unique_id; ?>' post-id='<?php echo $unique_id; ?>'>
                                    <div class="whoRepostedThis clearfix" style="">
                                        <p><font color="#34495e"><i class='fa fa-fw fa-users'></i></font>  Posted this in <a style='color:#34495e; padding-top: 5px;' href='<?php echo APP_URL; ?>clique/<?php echo $clique_name; ?>'><?php echo $names; ?></a></p>
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
                                    <div class="actionsHolder">
                                        <?php TimelinePostHandler::renderButtonsForPosts($unique_id, $u['user_id'], $likesNumber->rowCount(), $commentGetNum->rowCount()); ?>
                                    </div>
                                    <div class="extrasHolder">

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
                                //}
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
                                    $user_get2 = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $user_posted_to . "' AND account_locked='unlocked' AND activated='1'");
                                    $user_get2->execute();
                                    $u2 = $user_get2->fetch(PDO::FETCH_ASSOC);
                                    $pid = 1;
                                    $up = "<a style='' href='" . APP_URL . "profile/" . $u['username'] . "'>" . ucwords($u['first_name']) . " " . ucwords($u['last_name']) . "</a> » <a style='padding-top: 5px;' href='" . APP_URL . "profile/" . $u2['username'] . "'>" . ucwords($u2['first_name']) . " " . ucwords($u2['last_name']) . "</a>";
                                } else {
                                    $pid = 0;
                                    $up = "<a style='display: inline;' href='" . APP_URL . "profile/" . $u['username'] . "'>" . ucwords($u['first_name']) . " " . ucwords($u['last_name']) . "</a>";
                                }

                                //if (Report::checkStatus($_SESSION['uid'], $unique_id) == 0) {
                                ?>
                                <div class='timeline-item clearfix post bounceIn' id='post-<?php echo $unique_id; ?>' post-id='<?php echo $unique_id; ?>'>
                                    <div class="whoRepostedThis clearfix" style="">
                                        <p><font color="#34495e"><i class='fa fa-fw fa-users'></i></font>  Posted this in <a style='color:#34495e; padding-top: 5px;' href='<?php echo APP_URL; ?>clique/<?php echo $clique_name; ?>'><?php echo $names; ?></a></p>
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
                                // }
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

                                //if (Report::checkStatus($_SESSION['uid'], $unique_id) == 0) {
                                ?>
                                <div class='timeline-item clearfix post bounceIn' id='post-<?php echo $unique_id; ?>' post-id='<?php echo $unique_id; ?>'>
                                    <div class="whoRepostedThis clearfix" style="">
                                        <p><font color="#34495e"><i class='fa fa-fw fa-users'></i></font>  Posted this in <a style='color:#34495e; padding-top: 5px;' href='<?php echo APP_URL; ?>clique/<?php echo $clique_name; ?>'><?php echo $names; ?></a></p>
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
                                        <div class="photos_actual">
                                            <?php
                                            foreach ($photos as $p) {
                                                ?>
                                                <img class="item" data-x="<?php echo $unique_id; ?>" data-f="<?php echo $u['first_name']; ?>" src="<?php echo $p; ?>" style="cursor: pointer;max-height: 1100px;"/>
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
                                //}
                                break;

                        }
                    }
                }
            }
        }
    }

    static public function renderPost($unique_id, $showAll = "", $return = "html", $jsonReturn = "")
    {
        if (!empty($unique_id)) {
            $db = new Database;
            $park = $unique_id;

            // Get all posts
            $query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM . " WHERE unique_id='" . $unique_id . "' ORDER BY id DESC");
            $query->execute();

            if ($query->rowCount() > 0) {
                while ($fetch = $query->fetch(PDO::FETCH_ASSOC)) {
                    $id = $fetch['id'];
                    $unique_id = $fetch['unique_id'];
                    $user_by = $fetch['user_by'];
                    $user_posted_to = $fetch['user_posted_to'];
                    $date = $fetch['date'];

                    // Now get all clique-post posts thingys
                    $get = $db->prepare("SELECT * FROM timeline_item_clique_post WHERE unique_id='" . $unique_id . "'");
                    $get->execute();

                    if ($get->rowCount() > 0) {
                        // Now get the posts data
                        $cfetch = $get->fetch(PDO::FETCH_ASSOC);
                        $type = $cfetch['type'];
                        $clique_id = $cfetch['clique_id'];

                        $clique = $db->prepare("SELECT * FROM " . CLIQUES . " WHERE c_unique_id='" . $clique_id . "'");
                        $clique->execute();
                        $clique_fetch = $clique->fetch(PDO::FETCH_ASSOC);
                        $clique_name = $clique_fetch['c_username'];
                        $names = $clique_fetch['c_name'];
                        $npr = $clique_fetch['c_privacy'];

                        if ($npr == "public" or self::joinStatus($clique_id, $_SESSION['uid'], 'member') == 1) {

                            // Render Types
                            // Get the user_by persons data
                            $user_get = $db->prepare("SELECT * FROM " . USERS_TABLE . " WHERE user_id='" . $user_by . "' AND account_locked='unlocked' AND activated='1'");
                            $user_get->execute();
                            $u = $user_get->fetch(PDO::FETCH_ASSOC);
                            $salt2 = $u['unique_salt_id'];

                            $commentGetNum = $db->prepare("SELECT * FROM " . TIMELINE_POST_COMMENTS . " WHERE timeline_post_unique_id='" . $unique_id . "'");
                            $commentGetNum->execute();

                            $likesNumber = $db->prepare("SELECT * FROM " . TIMELINE_POST_LIKES . " WHERE unique_id='" . $unique_id . "'");
                            $likesNumber->execute();

                            // Display the different posts
                            switch ($type) {
                                case 'text':
                                    $get_data_query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM_TEXT . " WHERE unique_id='" . $unique_id . "'");
                                    $get_data_query->execute();

                                    if ($get_data_query->rowCount() == 1) {
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
                                        $priv = PrivacySystem::checkPostPrivacy($unique_id);
                                        if ($report == 0 && $priv == 1) {
                                            if ($return == "html") {
                                                ?>
                                                <div class='timeline-item clearfix post animate bounceIn' id='post-<?php echo $unique_id; ?>' post-id='<?php echo $unique_id; ?>'>
                                                    <div class="whoRepostedThis clearfix" style="">
                                                        <p><font color="#34495e"><i class='fa fa-fw fa-users'></i></font>  Posted this in <a style='color:#34495e; padding-top: 5px;' href='<?php echo APP_URL; ?>clique/<?php echo $clique_name; ?>'><?php echo $names; ?></a></p>
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
                                                    <div class="actionsHolder">
                                                        <?php TimelinePostHandler::renderButtonsForPosts($unique_id, $u['user_id'], $likesNumber->rowCount(), $commentGetNum->rowCount()); ?>
                                                    </div>
                                                    <div class="extrasHolder">

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
                                            } else if ($return == "json") {
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
                                                $jsonReturn['posts'][$unique_id]['post_data'] = array(
                                                    'postId' => $unique_id,
                                                    'postType' => $type,
                                                    'postSubType' => 'clique',
                                                    'postDate' => Convert::convert_time($date),
                                                    'postText' => Hashtags::parseText($body),
                                                    'postAuthor' => $u['user_id'],
                                                    'pid' => $pid,
                                                    'loggeduserProfile' => User::renderProfilePic($_SESSION['uid'], 80),
                                                    'loggedUserPost' => $_SESSION['uid'],
                                                    'likeCount' => $likesNumber->rowCount(),
                                                    'commentCount' => $commentGetNum->rowCount(),
                                                    'shareCount' => TimelinePostHandler::countReposts($unique_id),
                                                    'hasReposted' => Repost::CheckIfRepostedBefore($unique_id, $_SESSION['uid']),
                                                    'hasLiked' => TimelinePostHandler::checkLikeStatusMain($unique_id, $_SESSION['uid']),
                                                    'isRepost' => '0',
                                                    'comments' => Comments::displayCommentsInJson($unique_id),
                                                    'cliquename' => $names,
                                                    'cliqueusername' => $clique_name


                                                );

                                            }
                                        }
                                    }
                                    break;
                                case 'video':
                                    $get_data_query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM_VIDEO . " WHERE unique_id='" . $unique_id . "'");
                                    $get_data_query->execute();
                                    if ($get_data_query->rowCount() == 1) {

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
                                        $priv = PrivacySystem::checkPostPrivacy($unique_id);

                                        if($report == 0 && $priv == 1){
                                            if($return == "html") {
                                                ?>
                                                <div class='timeline-item clearfix post bounceIn' id='post-<?php echo $unique_id; ?>' post-id='<?php echo $unique_id; ?>'>
                                                    <div class="whoRepostedThis clearfix" style="">
                                                        <p><font color="#34495e"><i class='fa fa-fw fa-users'></i></font>  Posted this in <a style='color:#34495e; padding-top: 5px;' href='<?php echo APP_URL; ?>clique/<?php echo $clique_name; ?>'><?php echo $names; ?></a></p>
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
                                                    'postSubType' => 'clique',
                                                    'postDate' => Convert::convert_time($date),
                                                    'postText' => Hashtags::parseText($body),
                                                    'postAuthor' => $u['user_id'],
                                                    'pid' => $pid,
                                                    'loggeduserProfile' => User::renderProfilePic($_SESSION['uid'], 80),
                                                    'loggedUserPost' => $_SESSION['uid'],
                                                    'likeCount' => $likesNumber->rowCount(),
                                                    'commentCount' => $commentGetNum->rowCount(),
                                                    'shareCount' => TimelinePostHandler::countReposts($unique_id),
                                                    'hasReposted' => Repost::CheckIfRepostedBefore($unique_id, $_SESSION['uid']),
                                                    'hasLiked' => TimelinePostHandler::checkLikeStatusMain($unique_id, $_SESSION['uid']),
                                                    'isRepost' => '0',
                                                    'comments' => Comments::displayCommentsInJson($unique_id),
                                                    'postVideo' => $videoLink,
                                                    'cliquename' => $names,
                                                    'cliqueusername' => $clique_name

                                                );

                                            }
                                        }
                                    }
                                    break;
                                case 'photo':
                                    $get_data_query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM_PHOTO . " WHERE unique_id='" . $unique_id . "'");
                                    $get_data_query->execute();
                                    if ($get_data_query->rowCount() == 1) {

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
                                        if (isset($_SESSION['uid'])) {
                                            $report = Report::checkStatus($_SESSION['uid'], $unique_id);
                                        }
                                        $priv = PrivacySystem::checkPostPrivacy($unique_id);

                                        if ($report == 0 && $priv == 1) {
                                            if ($return == "html") {
                                                ?>
                                                <div class='timeline-item clearfix post bounceIn' id='post-<?php echo $unique_id; ?>' post-id='<?php echo $unique_id; ?>'>
                                                    <div class="whoRepostedThis clearfix" style="">
                                                        <p><font color="#34495e"><i class='fa fa-fw fa-users'></i></font>  Posted this in <a style='color:#34495e; padding-top: 5px;' href='<?php echo APP_URL; ?>clique/<?php echo $clique_name; ?>'><?php echo $names; ?></a></p>
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
                                                        <div class="photos_actual">
                                                            <?php
                                                            foreach ($photos as $p) {
                                                                ?>
                                                                <img class="item" data-x="<?php echo $unique_id; ?>" data-f="<?php echo $u['first_name']; ?>" src="<?php echo $p; ?>" style="cursor: pointer;max-height: 1100px;"/>
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
                                            } else if ($return == "json") {
                                                foreach ($photos as $p) {
                                                    $photosToSend[] = $p;
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
                                                    'postSubType' => 'clique',
                                                    'postDate' => Convert::convert_time($date),
                                                    'postText' => Hashtags::parseText($body),
                                                    'postAuthor' => $u['user_id'],
                                                    'pid' => $pid,
                                                    'loggeduserProfile' => User::renderProfilePic($_SESSION['uid'], 80),
                                                    'loggedUserPost' => $_SESSION['uid'],
                                                    'likeCount' => $likesNumber->rowCount(),
                                                    'commentCount' => $commentGetNum->rowCount(),
                                                    'shareCount' => TimelinePostHandler::countReposts($unique_id),
                                                    'hasReposted' => Repost::CheckIfRepostedBefore($unique_id, $_SESSION['uid']),
                                                    'hasLiked' => TimelinePostHandler::checkLikeStatusMain($unique_id, $_SESSION['uid']),
                                                    'isRepost' => '0',
                                                    'comments' => Comments::displayCommentsInJson($unique_id),
                                                    'postPhotos' => $photosToSend,
                                                    'cliquename' => $names,
                                                    'cliqueusername' => $clique_name

                                                );

                                            }
                                        }
                                    }
                                    break;

                            }
                        }
                    }
                }
                if($return == "json"){
                    return $jsonReturn;
                }
            }
        }
    }

    static public function displayPostsNumber($clique_id)
    {
        if (!empty($clique_id)) {
            $db = new Database;
            $count = 0;

            // Get all posts
            $query = $db->prepare("SELECT * FROM " . TIMELINE_ITEM . " WHERE type='clique-post'");
            $query->execute();

            if ($query->rowCount() > 0) {
                while ($fetch = $query->fetch(PDO::FETCH_ASSOC)) {
                    $unique_id = $fetch['unique_id'];

                    // Now get all clique-post posts thingys
                    $get = $db->prepare("SELECT * FROM timeline_item_clique_post WHERE unique_id='" . $unique_id . "' AND clique_id='" . $clique_id . "' ORDER BY id DESC");
                    $get->execute();

                    if ($get->rowCount() > 0) {
                        $count++;
                    }
                }
            }
        }
        return $count;
    }

    // Post something on their timeline asking all friends to join thier clique
    static public function shareCliquePost($body, $clique_id)
    {
        if (!empty($body) && !empty($clique_id)) {
            $b = Validation::santitize($body);

            if ($b != "") {
                ProfilePosting::makeCliqueSharePost($_SESSION['uid'], $_SESSION['uid'], $b, $clique_id, "Public");
                return false;
            }
        }
    }

    static public function makeCliqueTextPost($body, $clique_id, $clique_name, $privacy)
    {
        if (!empty($body) && !empty($clique_id)) {
            $b = Validation::santitize($body);

            if ($b != "") {
                ProfilePosting::makeCliqueTextPost($_SESSION['uid'], $_SESSION['uid'], $clique_id, $clique_name, $b, $privacy);
                return false;
            }
        }
    }

    static public function makeCliqueVideoPost($body, $video, $clique_id, $clique_name, $privacy)
    {
        if (!empty($body) && !empty($clique_id)) {
            $b = Validation::santitize($body);

            if ($b != "") {
                ProfilePosting::makeCliqueVideoPost($_SESSION['uid'], $_SESSION['uid'], $video, $clique_id, $clique_name, $b, $privacy);
                return false;
            }
        }
    }

    static public function makeCliquePhotoPost($body, $clique_id, $clique_name, $privacy, $photo)
    {
        if (!empty($body) && !empty($clique_id)) {
            $b = Validation::santitize($body);

            if ($b != "") {
                ProfilePosting::makeCliquePhotoPost($_SESSION['uid'], $_SESSION['uid'], $clique_id, $clique_name, $b, $privacy, $photo);
                return false;
            }
        }
    }

    static public function renderProfileBtn($clique_id, $clique_privacy)
    {
        $db = new Database;
        // Get the founders information
        $get = $db->prepare("SELECT * FROM " . CLIQUE_MEMBERS . " WHERE c_main_unique_id='" . $clique_id . "' AND c_m_priv='founder'");
        $get->execute();
        $gf = $get->fetch(PDO::FETCH_ASSOC);
        $user_id = $gf['c_m_user_id'];

        $request_status = Clique::joinStatus($clique_id, $_SESSION['uid'], 'request-status');
        $member_status = Clique::joinStatus($clique_id, $_SESSION['uid'], 'member');
        ?>
        <?php if ($_SESSION['uid'] != $user_id) { ?>

        <?php if ($clique_privacy == "public") { ?>
            <?php
            if ($member_status == 0) {
                ?>
                <button class="btn cliqueListener" data-type="straightJoinProfile"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-tko="<?php echo $_SESSION['token'] ?>" style="background: #2ecc71;" data-cliqueid="<?php echo $clique_id; ?>"><i
                        class="fa fa-user-plus"></i> Join this group
                </button>
                <button class="btn cliqueListener hidden" data-type="leaveStraightGroupProfile"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"
                        data-tko="<?php echo $_SESSION['token'] ?>" style="background: #e74c3c;color: #fff;"><i class="fa fa-user-times"></i> Leave Clique
                </button>
                <?php
            } else if ($member_status == 1) {
                ?>
                <button class="btn cliqueListener" data-type="leaveStraightGroupProfile"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"
                        data-tko="<?php echo $_SESSION['token'] ?>" style="background: #e74c3c;color: #fff;"><i class="fa fa-user-times"></i> Leave Clique
                </button>
                <button class="btn cliqueListener hidden joiner" data-type="joinRequest"
                        data-tko="<?php echo $_SESSION['token'] ?>" data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"><i
                        class="fa fa-user-plus"></i> Join this group
                </button>
                <?php
            }
            ?>
        <?php } else if ($clique_privacy == "private") { ?>
            <?php
            // First see if the person has sent a request then see member status
            if ($request_status == 0 && $member_status == 0) {
                ?>
                <button class="btn joiner cliqueListener" data-type="joinRequest"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"><i
                        data-tko="<?php echo $_SESSION['token'] ?>" class="fa fa-user-plus"></i> Ask to join
                </button>
                <button class="btn cliqueListener hidden" data-type="deleteRequest"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"
                        data-tko="<?php echo $_SESSION['token'] ?>" style="background: #e74c3c;color: #fff;"><i class="fa fa-trash-o"></i> Delete Request
                </button>
                <?php
            } else if ($request_status == 1 && $member_status == 0) {
                ?>
                <button class="btn cliqueListener" data-type="deleteRequest"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"
                        data-tko="<?php echo $_SESSION['token'] ?>" style="background: #e74c3c;color: #fff;"><i class="fa fa-trash-o"></i> Delete Request
                </button>
                <button class="btn cliqueListener joiner hidden" data-type="joinRequest"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-tko="<?php echo $_SESSION['token'] ?>" data-cliqueid="<?php echo $clique_id; ?>"><i class="fa fa-user-plus"></i> Ask to join
                </button>
                <?php
            } else if ($request_status == 0 && $member_status == 1) {
                ?>
                <button class="btn cliqueListener" data-type="leaveStraightGroupProfile"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"
                        data-tko="<?php echo $_SESSION['token'] ?>" style="background: #e74c3c;color: #fff;"><i class="fa fa-user-times"></i> Leave Clique
                </button>
                <span id="btnsHolder<?php echo $clique_id; ?>" class="hidden">
                    <button class="btn cliqueListener joiner" data-type="joinRequest"
                            data-uid="<?php echo $_SESSION['uid']; ?>" data-tko="<?php echo $_SESSION['token'] ?>" data-cliqueid="<?php echo $clique_id; ?>"><i class="fa fa-user-plus"></i> Ask to join
                    </button>
                    <button class="btn cliqueListener hidden" data-type="deleteRequest"
                            data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"
                            data-tko="<?php echo $_SESSION['token'] ?>" style="background: #e74c3c;color: #fff;"><i class="fa fa-trash-o"></i> Delete Request
                    </button>
                </span>
                <?php
            }
            ?>
        <?php } else { ?>
            <?php
            if ($member_status == 0) {
                ?>
                <button class="btn"><i class="fa fa-user-plus"></i> Invite Only</button>
                <?php
            } else if ($member_status == 1) {
                ?>
                <button class="btn cliqueListener" data-type="leaveStraightGroupProfile"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"
                        data-tko="<?php echo $_SESSION['token'] ?>" style="background: #e74c3c;color: #fff;"><i class="fa fa-user-times"></i> Leave Clique
                </button>
                <span id="btnsHolder<?php echo $clique_id; ?>" class="hidden">
                    <button class="btn"><i class="fa fa-user-plus"></i> Invite Only</button>
                </span>
                <?php
            }
        }
    }
    }

    static public function renderBtn($clique_id, $clique_privacy)
    {
        $db = new Database;
        // Get the founders information
        $get = $db->prepare("SELECT * FROM " . CLIQUE_MEMBERS . " WHERE c_main_unique_id='" . $clique_id . "' AND c_m_priv='founder'");
        $get->execute();
        $gf = $get->fetch(PDO::FETCH_ASSOC);
        $user_id = $gf['c_m_user_id'];

        $query = $db->prepare("SELECT c_join_cost FROM " .CLIQUES. " WHERE c_active='1' AND c_unique_id='".$clique_id."'");
        $query->execute();
        $fetch = $query->fetch(PDO::FETCH_ASSOC);

        $request_status = Clique::joinStatus($clique_id, $_SESSION['uid'], 'request-status');
        $member_status = Clique::joinStatus($clique_id, $_SESSION['uid'], 'member');
        ?>
        <?php if ($_SESSION['uid'] != $user_id) { ?>

        <?php if ($clique_privacy == "public") { ?>
            <?php
            if ($member_status == 0) {
                ?>
                <button class="btn cliqueListener" data-type="straightJoin"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>" data-tko="<?php echo $_SESSION['token'] ?>" style="background: #2ecc71;"><i
                        class="fa fa-user-plus"></i> Join this group
                </button>
                <button class="btn cliqueListener hidden" data-type="leaveStraightGroup"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"
                        data-tko="<?php echo $_SESSION['token'] ?>" style="background: #e74c3c;color: #fff;"><i class="fa fa-user-times"></i> Leave Clique
                </button>
                <?php
            } else if ($member_status == 1) {
                ?>
                <button class="btn cliqueListener" data-type="leaveStraightGroup"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"
                        data-tko="<?php echo $_SESSION['token'] ?>" style="background: #e74c3c;color: #fff;"><i class="fa fa-user-times"></i> Leave Clique
                </button>
                <button class="btn cliqueListener hidden joiner" data-type="joinRequest"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>" data-tko="<?php echo $_SESSION['token'] ?>"><i class="fa fa-user-plus"></i> Join this group
                </button>
                <?php
            }
            ?>
        <?php } else if ($clique_privacy == "private") { ?>
            <?php
            // First see if the person has sent a request then see member status
            if ($request_status == 0 && $member_status == 0) {
                ?>
                <button class="btn joiner cliqueListener" data-type="joinRequest"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>" data-tko="<?php echo $_SESSION['token'] ?>" style="background: #2ecc71;"><i
                        class="fa fa-user-plus"></i> Ask to join
                </button>
                <button class="btn cliqueListener hidden" data-type="deleteRequest"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"
                        data-tko="<?php echo $_SESSION['token'] ?>" style="background: #e74c3c;color: #fff;"><i class="fa fa-trash-o"></i> Delete Request
                </button>
                <?php
            } else if ($request_status == 1 && $member_status == 0) {
                ?>
                <button class="btn cliqueListener" data-type="deleteRequest"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"
                        data-tko="<?php echo $_SESSION['token'] ?>" style="background: #e74c3c;color: #fff;"><i class="fa fa-trash-o"></i> Delete Request
                </button>
                <button class="btn cliqueListener joiner hidden" data-type="joinRequest"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>" data-tko="<?php echo $_SESSION['token'] ?>" style="background: #2ecc71;"><i
                        class="fa fa-user-plus"></i> Ask to join
                </button>
                <?php
            } else if ($request_status == 0 && $member_status == 1) {
                ?>
                <button class="btn cliqueListener" data-type="leaveGroup"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"
                        data-tko="<?php echo $_SESSION['token'] ?>" style="background: #e74c3c;color: #fff;"><i class="fa fa-user-times"></i> Leave Clique
                </button>
                <span id="btnsHolder<?php echo $clique_id; ?>" class="hidden">
                    <button class="btn cliqueListener joiner" data-type="joinRequest"
                            data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>" data-tko="<?php echo $_SESSION['token'] ?>" style="background: #2ecc71;"><i
                            class="fa fa-user-plus"></i> Ask to join
                    </button>
                    <button class="btn cliqueListener hidden" data-type="deleteRequest"
                            data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"
                            data-tko="<?php echo $_SESSION['token'] ?>" style="background: #e74c3c;color: #fff;"><i class="fa fa-trash-o"></i> Delete Request
                    </button>
                </span>
                <?php
            }
            ?>
        <?php } else if($clique_privacy == "paid") { ?>
            <?php
            if ($member_status == 0) {
                ?>

                <button class="btn cliqueListener" data-type="joinCliqueWithCost" data-cliqueid="<?php echo $clique_id; ?>" data-tko="<?php echo $_SESSION['token'] ?>" data-cliquecost="<?php echo $fetch['c_join_cost']; ?>" style="background: #2ecc71;" title="Use <?php echo $fetch['c_join_cost']; ?> to join this chat"><i class="fa fa-money"></i> <?php echo $fetch['c_join_cost']; ?></button>
                <button class="btn cliqueListener hidden" data-type="leaveStraightGroup" data-uid="<?php echo $_SESSION['uid']; ?>" data-tko="<?php echo $_SESSION['token'] ?>" data-cliqueid="<?php echo $clique_id; ?>" style="background: #e74c3c;color: #fff;"><i class="fa fa-user-times"></i> Leave Clique
                </button>
                <?php
            } else if ($member_status == 1) {
                ?>
                <button class="btn cliqueListener" data-type="leaveGroup"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"
                        data-tko="<?php echo $_SESSION['token'] ?>" data-tko="<?php echo $_SESSION['token'] ?>" style="background: #e74c3c;color: #fff;"><i class="fa fa-user-times"></i> Leave Clique
                </button>
                <button class="btn cliqueListener hidden" data-tko="<?php echo $_SESSION['token'] ?>" data-type="joinCliqueWithCost" data-cliqueid="<?php echo $clique_id; ?>" data-tko="<?php echo $_SESSION['token'] ?>" data-cliquecost="<?php echo $fetch['c_join_cost']; ?>" style="background: #2ecc71;" title="Use <?php echo $fetch['c_join_cost']; ?> to join this chat"><i class="fa fa-money"></i> <?php echo $fetch['c_join_cost']; ?></button>
                <?php
            }
            ?>
        <?php } else { ?>
            <?php
            if ($member_status == 0) {
                ?>
                <button class="btn cliqueListener" ><i class="fa fa-user-plus"></i> Invite Only</button>
                <?php
            } else if ($member_status == 1) {
                ?>
                <button class="btn cliqueListener" data-type="leaveGroup"
                        data-uid="<?php echo $_SESSION['uid']; ?>" data-cliqueid="<?php echo $clique_id; ?>"
                        style="background: #e74c3c;color: #fff;"><i class="fa fa-user-times"></i> Leave Clique
                </button>
                <span id="btnsHolder<?php echo $clique_id; ?>" class="hidden">
                    <button class="btn"><i class="fa fa-user-plus"></i> Invite Only</button>
                </span>
                <?php
            }
        }
    }
    }

    static public function numberOfMembers($clique_id)
    {
        if (!empty($clique_id)) {
            $db = new Database;

            $get = $db->prepare("SELECT * FROM " . CLIQUE_MEMBERS . " WHERE c_main_unique_id='" . $clique_id . "'");
            $get->execute();

            return $get->rowCount();
        }
    }

    static public function displayMembersIcons($clique_id, $limit = 10)
    {
        if (!empty($clique_id)) {
            $db = new Database;

            $get = $db->prepare("SELECT * FROM " . CLIQUE_MEMBERS . " WHERE c_main_unique_id='" . $clique_id . "' LIMIT " . $limit);
            $get->execute();

            if ($get->rowCount() > 0) {
                while ($fetch = $get->fetch(PDO::FETCH_ASSOC)) {
                    $c_m_user_id = $fetch['c_m_user_id'];
                    $type = $fetch['c_m_priv'];

                    ?>
                    <a href="<?php echo APP_URL; ?>profile/<?php echo User::get('users', $c_m_user_id, 'username'); ?>">
                        <div data-tooltip="<?php echo User::get('users', $c_m_user_id, 'first_name'); ?> <?php echo User::get('users', $c_m_user_id, 'last_name'); ?>'s profile" class="" style="display: inline-block;height: 25px;width: 25px;border-radius: 3px;box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);background-image: url(<?php echo User::renderProfilePic($c_m_user_id, 80); ?>);background-size: cover;"></div>
                    </a>
                    <?php
                }
            }
        }
    }

    static public function CliqueSuggestionsBasedOnClique($clique_id)
    {
        if(!empty($clique_id))
        {
            $db = new Database;
            $c_ids = array();

            $query = $db->prepare("SELECT * FROM " . CLIQUES . " where c_unique_id='".$clique_id."'");
            $query->execute();

            if($query->rowCount() == 1)
            {
                $fetch = $query->fetch(PDO::FETCH_ASSOC);

                // Now get the data for this
                $c_name     = $fetch['c_name'];
                $sn = "";

                // Strip the name for every space
                $arr = explode(" ", $c_name);
                $arrc = count($arr);


                $sn = $c_name;

                // Now run a query to search for two cliques that are like the other
                $search = $db->prepare("SELECT * FROM " . CLIQUES . " WHERE c_name LIKE :name AND c_unique_id!=:except LIMIT 2");
                $search->execute(array(':name' => '%' . $sn . '%', ':except' => $clique_id));

                if($search->rowCount() > 0)
                {
                    while($sfetch = $search->fetch(PDO::FETCH_ASSOC)){
                        $c_ids[] = $sfetch['c_unique_id'];
                    }
                }
            }
            return $c_ids;
        }
    }

    static public function leaveGroup($clique_id, $uid)
    {
        if (!empty($clique_id) && !empty($uid)) {
            if (self::joinStatus($clique_id, $uid, 'member') == 1) {
                // Means the person is apart of this group
                $db = new Database;

                // Now make sure this clique exists!!!!!!!!!!!!
                $check = $db->prepare("SELECT * FROM " . CLIQUES . " WHERE c_unique_id='" . $clique_id . "'");
                $check->execute();
                if ($check->rowCount() == 1) {
                    // Now do work!
                    $delete = $db->prepare("DELETE FROM " . CLIQUE_MEMBERS . " WHERE c_main_unique_id='" . $clique_id . "' AND c_m_user_id='" . $uid . "'");
                    $delete->execute();

                    $response = array();
                    $response['code'] = 1;
                    echo json_encode($response);
                    return false;
                } else {
                    $response = array();
                    $response['code'] = 0;
                    $response['status'] = "This clique does not exists!";
                    echo json_encode($response);
                    return false;
                }
            } else {
                $response = array();
                $response['code'] = 0;
                $response['status'] = "Sorry but you were never apart of this Clique";
                echo json_encode($response);
                return false;
            }
        }
    }

    static public function deleteJoinRequest($clique_id, $user_id)
    {
        if (!empty($clique_id) && !empty($user_id)) {
            $cid = Validation::santitize($clique_id);

            if ($cid != "") {
                // Make sure this request exist
                $db = new Database;

                $check = $db->prepare("SELECT * FROM " . CLIQUE_REQUESTS . " WHERE user_id='" . $user_id . "' AND c_main_unique_id='" . $clique_id . "' AND type='join'");
                $check->execute();

                if ($check->rowCount() == 1) {
                    // Do work
                    $delete = $db->prepare("DELETE FROM " . CLIQUE_REQUESTS . " WHERE user_id='" . $user_id . "' AND c_main_unique_id='" . $cid . "' AND type='join' ");
                    $delete->execute();

                    $response = array();
                    $response['code'] = 1;
                    echo json_encode($response);
                    return false;
                } else {
                    $response = array();
                    $response['code'] = 0;
                    $response['status'] = "You havent requested to be in this chat yet!";
                    echo json_encode($response);
                    return false;
                }
            }
        }
    }

    /*
     * Will be used to render join status
     * And be able to determine all the join buttons
     */
    static public function joinStatus($clique_id, $user_id, $findType)
    {
        if (!empty($clique_id) && !empty($user_id) && !empty($findType)) {
            $types = array('request-status', 'member');

            if (in_array($findType, $types)) {
                $db = new Database;
                switch ($findType) {
                    // For requests
                    case 'request-status':
                        $check = $db->prepare("SELECT * FROM " . CLIQUE_REQUESTS . " WHERE user_id='" . $user_id . "' AND c_main_unique_id='" . $clique_id . "' AND type='join'");
                        $check->execute();
                        return $check->rowCount();
                        break;

                    // For member status
                    case 'member':
                        $check = $db->prepare("SELECT * FROM " . CLIQUE_MEMBERS . " WHERE c_m_user_id='" . $user_id . "' AND c_main_unique_id='" . $clique_id . "'");
                        $check->execute();
                        return $check->rowCount();
                        break;
                }
            }
        }
    }

    static public function joinClique($clique_id, $uid, $cost = 0)
    {
        if (!empty($clique_id) && !empty($uid) && is_numeric($uid)) {
            $cid = Validation::santitize($clique_id);
            $db = new Database;

            if ($cid != "") {
                // Check to see if this clique exists
                $check = $db->prepare("SELECT * FROM " . CLIQUES . " WHERE c_unique_id='" . $clique_id . "'");
                $check->execute();

                if ($check->rowCount() == 1) {
                    // Make sure this guy isnt a member of this clique already
                    $check2 = $db->prepare("SELECT * FROM " . CLIQUE_MEMBERS . " WHERE c_main_unique_id='" . $cid . "' AND c_m_user_id='" . $uid . "'");
                    $check2->execute();

                    if ($check2->rowCount() == 0) {
                        // Now add the person
                        $fetch = $check->fetch(PDO::FETCH_ASSOC);
                        $privacy = $fetch['c_privacy'];
                        $cmd = $fetch['c_id'];

                        // Render
                        if ($privacy == "public") {
                            // Now insert the first member(the founder)
                            $insert2 = $db->prepare("INSERT INTO " . CLIQUE_MEMBERS . " VALUES('','" . $uid . "','" . $cmd . "', '" . $clique_id . "','member',now())");
                            $insert2->execute();
                            //Notifications::makeTimelineActivityPost($_SESSION['uid'],'cliqueJoin','has joined a <a href="'.APP_URL.'clique/'.$clique_id.'">clique!</a>',$_SESSION['uid'],$clique_id);
                            $response = array();
                            $response['code'] = 1;
                            echo json_encode($response);
                            return false;
                        } else if ($privacy == "private") {
                            // Insert the person but dont make them official. Also send a request to the founder or leader of the clique
                            $insertRequest = $db->prepare("INSERT INTO " . CLIQUE_REQUESTS . " VALUES('','" . $uid . "','" . $cid . "','join',now())");
                            $insertRequest->execute();

                            $response = array();
                            $response['code'] = 1;
                            echo json_encode($response);
                            return false;
                        } else if ($privacy == "paid") {
                            // Do cost validation
                            if($cost != 0 && $cost >= 1 && $cost <= 500){
                                if(Points::getPoints($uid) >= $cost)
                                {
                                    // Make transaction
                                    Points::addPoints($cost, self::getCliqueCreator($clique_id));
                                    Points::subtractPoints($cost, $uid);

                                    $insert2 = $db->prepare("INSERT INTO " . CLIQUE_MEMBERS . " VALUES('','" . $uid . "','" . $cmd . "', '" . $clique_id . "','member',now())");
                                    $insert2->execute();

                                    echo Response::make('join-good', 'JSON', 1);
                                    return false;
                                }else{
                                    $a = $cost - Points::getPoints($uid);
                                    echo Response::make("You need " . $a . " points to join this clique!");
                                    return false;
                                }
                            }else{
                                echo Response::make('Invalid cost');
                                return false;
                            }
                        }
                    } else {
                        $response = array();
                        $response['code'] = 0;
                        $response['status'] = "You are already a member of this clique";
                        echo json_encode($response);
                        return false;
                    }
                } else {
                    $response = array();
                    $response['code'] = 0;
                    $response['status'] = "This clique does not exist!";
                    echo json_encode($response);
                    return false;
                }
            }
        }
    }

    static public function create_clique($cliqueName, $cliqueUsername, $cliqueDescription, $cliquePrivacy, $cliquePhoto = "", $cliqueJoinCost = 0)
    {
        if (!empty($cliqueName) && !empty($cliqueUsername) && !empty($cliqueDescription) && !empty($cliquePrivacy)) {
            $db = new Database;
            $name = Validation::santitize($cliqueName);
            $cusername = Validation::santitize($cliqueUsername);
            $description = Validation::santitize($cliqueDescription);
            $privacy = Validation::santitize($cliquePrivacy);
            $clique_id = md5(Encryption::randomHash());

            if (preg_match('/[\'^£$%&*()}{@#~?><>,|=+¬-]/', $cusername)) {
                // one or more of the 'special characters' found in $string
                $response = array();
                $response['code'] = 0;
                $response['string'] = "Your username cant have 'special characters'!";
                echo json_encode($response);
                return false;
            } else {
                // Render profile picture
                if (isset($cliquePhoto) && empty($cliquePhoto) != true) {
                    // Means we got a photo
                    if (isset($cliquePhoto)) {
                        // Then upload the photo
                        $types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/ttf');
                        if (in_array($cliquePhoto['type'], $types)) {
                            // First make a encrypted folder
                            $path = SITE_ROOT . "/public/clique_data/" . $clique_id;
                            if (!file_exists($path)) {
                                // Make all the directories
                                mkdir(SITE_ROOT . '/public/clique_data/' . $clique_id, 0777, true);
                                mkdir(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_profile_pic', 0777, true); // Profile pictures
                                mkdir(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_banners', 0777, true); // Banners
                                mkdir(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_photos', 0777, true); // Photos
                                mkdir(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_data', 0777, true); // Data

                                // Copy the defaults
                                copy(SITE_ROOT . '/public/clique_data/group_default_profile_pic.jpg', SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_profile_pic/default_pic.jpg'); // Default profile pic
                                copy(SITE_ROOT . '/public/clique_data/default_banner.jpg', SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_banners/default_banner.jpg'); // Default banner pic

                                //Give permissions for everything
                                // Copies
                                chmod(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_profile_pic/default_pic.jpg', 0777);
                                chmod(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_banners/default_banner.jpg', 0777);
                                // Folders
                                chmod(SITE_ROOT . '/public/clique_data/' . $clique_id, 0777);
                                chmod(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_profile_pic', 0777);
                                chmod(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_banners', 0777);
                                chmod(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_photos', 0777);
                                chmod(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_data', 0777);
                            } else {
                                $response = array();
                                $response['code'] = 0;
                                $response['string'] = "File directory already exists. Please resubmit the form";
                                echo json_encode($response);
                                return false;
                            }
                            $p = md5(Encryption::randomHash());
                            if (1 == 1) {
                                $upload = new Upload($cliquePhoto, 'photo', array(
                                    'photoRootLocation' => SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_profile_pic/',
                                    'photoPublicLocation' => APP_URL . 'clique_data/' . $clique_id . '/clique_profile_pic/'),
                                    'regular');

                                $publicFilePath = $upload->filePublicPath;

                                // Means we dont have one so just insert the stuff and members but do checking thou
                                $check = $db->prepare("SELECT * FROM " . CLIQUES . " WHERE c_username='" . $cusername . "'");
                                $check->execute();

                                if ($check->rowCount() == 0) {
                                    $r = array('!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '+', '=', '{', '}', '[', ']', ';', ':', '"', ',', '.', '<', '>', '/', '?', '|', 'create_clique', 'my_cliques', ' ');
                                    $cusername = str_replace($r, '', $cusername);

                                    // Means there isnt a clique with that username or unique id
                                    if($cliqueJoinCost != 0)
                                    {
                                        if($cliqueJoinCost >= 1 && $cliqueJoinCost <= 500){
                                            // Not going to say anything lol
                                        }else{
                                            $response = array();
                                            $response['code'] = 0;
                                            $response['status'] = "Your cost must be under 500 points!";
                                            echo json_encode($response);
                                            return false;
                                        }
                                    }
                                    $insert1 = $db->prepare("INSERT INTO " . CLIQUES . " VALUES('','" . $name . "','" . $cusername . "','" . $description . "','" . $upload->encryptedFileName . "','default_banner.jpg','','#ddd', 'quicksand','" . $privacy . "','".$cliqueJoinCost."',now(),'1','" . $clique_id . "')");
                                    $insert1->execute();

                                    $last_id = $db->lastInsertId();

                                    // Now insert the first member(the founder)
                                    $insert2 = $db->prepare("INSERT INTO " . CLIQUE_MEMBERS . " VALUES('','" . $_SESSION['uid'] . "','" . $last_id . "', '" . $clique_id . "','founder',now())");
                                    $insert2->execute();

                                    // Means we can inform people of the new group
                                    Notifications::makeTimelineActivityPost($_SESSION['uid'], 'newClique', 'has made a new <a href="' . APP_URL . 'clique/' . $cusername . '">clique</a> ', '', $clique_id);
                                    $response = array();
                                    $response['code'] = 1;
                                    $response['string'] = "Your clique has been created!";
                                    echo json_encode($response);
                                    return false;
                                } else {
                                    $response = array();
                                    $response['code'] = 0;
                                    $response['string'] = "There is already a clique with that username!";
                                    echo json_encode($response);
                                    return false;
                                }
                            } else {
                                $response = array();
                                $response['code'] = 0;
                                $response['string'] = "Error making folders";
                                echo json_encode($response);
                                return false;
                            }
                        } else {
                            $response = array();
                            $response['code'] = 0;
                            $response['string'] = "Please select a png, jpg, or gif type photo";
                            echo json_encode($response);
                            return false;
                        }
                    }
                } else if (empty($cliquePhoto)) {
                    // Means we dont have one so just insert the stuff and members but do checking thou
                    $check = $db->prepare("SELECT * FROM " . CLIQUES . " WHERE c_username='" . $cusername . "'");
                    $check->execute();

                    if ($check->rowCount() == 0) {
                        $r = array('!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '+', '=', '{', '}', '[', ']', ';', ':', '"', ',', '.', '<', '>', '/', '?', '|', 'create_clique', 'my_cliques', ' ');
                        $cusername = str_replace($r, '', $cusername);

                        if($cliqueJoinCost != 0)
                        {
                            if($cliqueJoinCost >= 1 && $cliqueJoinCost <= 500){
                                // Not going to say anything lol
                            }else{
                                $response = array();
                                $response['code'] = 0;
                                $response['string'] = "Your cost must be under 500 points!";
                                echo json_encode($response);
                                return false;
                            }
                        }
                        // Means there isnt a clique with that username or unique id
                        $insert1 = $db->prepare("INSERT INTO " . CLIQUES . " VALUES('','" . $name . "','" . $cusername . "','" . $description . "','default_pic.jpg','default_banner.jpg','','#ddd', 'quicksand', '" . $privacy . "','".$cliqueJoinCost."',now(),'1','" . $clique_id . "')");
                        $insert1->execute();

                        $last_id = $db->lastInsertId();

                        // Now insert the first member(the founder)
                        $insert2 = $db->prepare("INSERT INTO " . CLIQUE_MEMBERS . " VALUES('','" . $_SESSION['uid'] . "','" . $last_id . "', '" . $clique_id . "','founder',now())");
                        $insert2->execute();

                        // Means we can inform people of the new group
                        Notifications::makeTimelineActivityPost($_SESSION['uid'], 'newClique', 'has made a new <a href="' . APP_URL . 'clique/' . $cusername . '">clique</a> ', '', $clique_id);

                        $path = SITE_ROOT . "/public/clique_data/" . $clique_id;
                        if (!file_exists($path)) {
                            // Make all the directories
                            mkdir(SITE_ROOT . '/public/clique_data/' . $clique_id, 0777, true);
                            mkdir(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_profile_pic', 0777, true); // Profile pictures
                            mkdir(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_banners', 0777, true); // Banners
                            mkdir(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_photos', 0777, true); // Photos
                            mkdir(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_data', 0777, true); // Data

                            // Copy the defaults
                            copy(SITE_ROOT . '/public/clique_data/group_default_profile_pic.jpg', SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_profile_pic/default_pic.jpg'); // Default profile pic
                            copy(SITE_ROOT . '/public/clique_data/default_banner.jpg', SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_banners/default_banner.jpg'); // Default banner pic

                            //Give permissions for everything
                            // Copies
                            chmod(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_profile_pic/default_pic.jpg', 0777);
                            chmod(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_banners/default_banner.jpg', 0777);
                            // Folders
                            chmod(SITE_ROOT . '/public/clique_data/' . $clique_id, 0777);
                            chmod(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_profile_pic', 0777);
                            chmod(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_banners', 0777);
                            chmod(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_photos', 0777);
                            chmod(SITE_ROOT . '/public/clique_data/' . $clique_id . '/clique_data', 0777);
                        } else {
                            $response = array();
                            $response['code'] = 0;
                            $response['string'] = "File directory already exists. Please resubmit the form";
                            echo json_encode($response);
                            return false;
                        }
                        $response = array();
                        $response['code'] = 1;
                        $response['string'] = "Your clique has been created!";
                        echo json_encode($response);
                        return false;
                    } else {
                        $response = array();
                        $response['code'] = 0;
                        $response['string'] = "There is already a clique with that username!";
                        echo json_encode($response);
                        return false;
                    }
                }
            }
        } else {
            $response = array();
            $response['code'] = 0;
            $response['string'] = "Data was not recieved";
            echo json_encode($response);
            return false;
        }
    }
}

?>