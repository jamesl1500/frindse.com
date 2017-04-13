<?php

class Notifications extends Database
{
    public static function makeNote($type, $userTo, $reason, $ext_id = "")
    {
        $db = new Database();

        if ($type != "" && $userTo != "" && $reason != "" && is_numeric($userTo) == true)
        {
            $insert = $db->prepare("INSERT INTO " . NOTES . " VALUES('', :loggeduser, :userto, :reason, '1', :type, :ext_id, now())");
            $insert->execute(array(':loggeduser' => Sessions::get('salt'), ':userto' => $userTo, ':type' => $type, ':ext_id'=>$ext_id, ':reason' => $reason));
        }
    }

    public static function getAllNotes($user, $postnumbers = "10", $offset = "0", $load = false)
    {
        $db = new Database();

        if (empty($user) == false) 
        {
            $query = $db->prepare("SELECT * FROM " . NOTES . " WHERE user_to=:user ORDER BY id DESC LIMIT ".$postnumbers." OFFSET " . $offset);
            $query->execute(array(':user'=>$user));

            if ($query->rowCount() != 0) 
            {
                while ($fetch = $query->fetch(PDO::FETCH_ASSOC)) 
                {
                    $id = $fetch['id'];
                    $user_from = $fetch['user_from'];
                    $new = $fetch['is_new'];
                    $type = $fetch['type'];
                    $date = $fetch['date'];
                    $what_happened = $fetch['what_happened'];
                    $ext = $fetch['ext_id'];

                    $get = $db->prepare("SELECT * FROM " . USERS . " WHERE user_salt='" . $user_from . "'");
                    $get->execute();

                    if ($get->rowCount() == 1) 
                    {
                        $u = $get->fetch(PDO::FETCH_ASSOC);

                        // RENDER DIFFERENT TYPES
                        switch ($type) {
                            case 'personSaidCool':
                                ?>
                                <div class="activityTimelinePost clearfix postLike" style="box-shadow: 0px 1px 2px rgba(0,0,0,0.2);">
                                    <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                    <div class="rightActivity" style="width: 87%;">
                                        <h5><a style='' href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['firstname']); ?> <?php echo ucwords($u['lastname']); ?></a> <?php echo $what_happened; ?>
                                        </h5>
                                        <h5 style="font-size: 14px;padding-top: 3px;"><font color="#e74c3c"><i
                                                    class="fa fa-thumbs-up"></i></font> &middot; <font
                                                color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'profile-like':
                                ?>
                                <div class="activityTimelinePost clearfix postLike" style="box-shadow: 0px 1px 2px rgba(0,0,0,0.2);">
                                    <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                    <div class="rightActivity" style="width: 87%;">
                                        <h5><a style=''
                                               href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['firstname']); ?> <?php echo ucwords($u['lastname']); ?></a> <?php echo $what_happened; ?>
                                        </h5>
                                        <h5 style="font-size: 14px;padding-top: 3px;"><font color="#e74c3c"><i
                                                    class="fa fa-thumbs-up"></i></font> &middot; <font
                                                color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'chat-person-notify':
                                ?>
                                <div class="activityTimelinePost clearfix postComment" style="box-shadow: 0px 1px 2px rgba(0,0,0,0.2);">
                                    <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                    <div class="rightActivity" style="width: 87%;">
                                        <h5><a style=''
                                               href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['firstname']); ?> <?php echo ucwords($u['lastname']); ?></a> <?php echo $what_happened; ?>
                                        </h5>
                                        <h5 style="font-size: 14px;padding-top: 3px;"><font color="#2ecc71"><i
                                                    class="fa fa-comment"></i></font> &middot; <font
                                                color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'newQuestion':
                                ?>
                                <div class="activityTimelinePost clearfix postComment" style="box-shadow: 0px 1px 2px rgba(0,0,0,0.2);">
                                    <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                    <div class="rightActivity" style="width: 87%;">
                                        <h5><a style=''
                                               href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['firstname']); ?> <?php echo ucwords($u['lastname']); ?></a> <?php echo $what_happened; ?>
                                        </h5>
                                        <h5 style="font-size: 14px;padding-top: 3px;"><font color="#2ecc71"><i
                                                    class="fa fa-comment"></i></font> &middot; <font
                                                color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'profile-post-to-user':
                                ?>
                                <div class="activityTimelinePost clearfix postComment" style="box-shadow: 0px 1px 2px rgba(0,0,0,0.2);">
                                    <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                    <div class="rightActivity" style="width: 87%;">
                                        <h5><a style=''
                                               href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['firstname']); ?> <?php echo ucwords($u['lastname']); ?></a> <?php echo $what_happened; ?>
                                        </h5>
                                        <h5 style="font-size: 14px;padding-top: 3px;"><font color="#34495e"><i
                                                    class="fa fa-reply"></i></font> &middot; <font
                                                color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'post-comment-like':
                                ?>
                                <div class="activityTimelinePost clearfix postComment" style="box-shadow: 0px 1px 2px rgba(0,0,0,0.2);">
                                    <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                    <div class="rightActivity" style="width: 87%;">
                                        <h5><a style=''
                                               href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['firstname']); ?> <?php echo ucwords($u['lastname']); ?></a> <?php echo $what_happened; ?>
                                        </h5>
                                        <h5 style="font-size: 14px;padding-top: 3px;"><font color="#34495e"><i
                                                    class="fa fa-heart"></i></font> &middot; <font
                                                color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'post-comment':
                                ?>
                                <div class="activityTimelinePost clearfix postComment" style="background-image: url();box-shadow: 0px 1px 2px rgba(0,0,0,0.2);">
                                    <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                    <div class="rightActivity" style="width: 87%;">
                                        <h5><a style='' href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['firstname']); ?> <?php echo ucwords($u['lastname']); ?></a> <?php echo $what_happened; ?></h5>
                                        <h5 style="font-size: 14px;padding-top: 3px;"><font color="#9b59b6"><i class="fa fa-comment"></i></font> &middot; <font color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'post-like':
                                ?>
                                <div class="activityTimelinePost clearfix postComment" style="">
                                    <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo APP_URL; ?>users/data/<?php echo $u['user_salt']; ?>/profile_picture);background-size: cover;border-radius: 50%;'></div>
                                    <div class="rightActivity" style="width: 87%;">
                                        <h5><a style='' href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['firstname']); ?> <?php echo ucwords($u['lastname']); ?></a> <?php echo $what_happened; ?>
                                        </h5>
                                        <h5 style="font-size: 14px;padding-top: 3px;"><font color="#9b59b6"><i class="fa fa-heart"></i></font> &middot; <font color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'newClique':
                                ?>
                                <div class="activityTimelinePost clearfix postComment" style="box-shadow: 0px 1px 2px rgba(0,0,0,0.2);">
                                    <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                    <div class="rightActivity" style="width: 87%;">
                                        <h5><a style=''
                                               href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['firstname']); ?> <?php echo ucwords($u['lastname']); ?></a> <?php echo $what_happened; ?>
                                        </h5>
                                        <h5 style="font-size: 14px;padding-top: 3px;"><font color="#aaa"><i
                                                    class="fa fa-users"></i> &middot; <?php echo Convert::convert_time($date); ?>
                                            </font></h5>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'cliqueJoin':
                                ?>
                                <div class="activityTimelinePost clearfix postComment" id='post-<?php echo $unique_id; ?>' style="box-shadow: 0px 1px 2px rgba(0,0,0,0.2);">
                                    <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                    <div class="rightActivity" style="width: 83%;">
                                        <h5><a style=''
                                               href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['firstname']); ?> <?php echo ucwords($u['lastname']); ?></a> <?php echo $what_happened; ?>
                                        </h5>
                                        <h5 style="font-size: 14px;padding-top: 3px;"><font color="#aaa"><i
                                                    class="fa fa-users"></i> &middot; <?php echo Convert::convert_time($date); ?>
                                            </font></h5>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'tagging':
                                ?>
                                <div class="activityTimelinePost clearfix postComment" style="box-shadow: 0px 1px 2px rgba(0,0,0,0.2);">
                                    <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                    <div class="rightActivity" style="width: 87%;">
                                        <h5><a style=''
                                               href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['firstname']); ?> <?php echo ucwords($u['lastname']); ?></a> <?php echo $what_happened; ?>
                                        </h5>
                                        <h5 style="font-size: 14px;padding-top: 3px;"><font color="#9b59b6"><i
                                                    class="fa fa-comment"></i></font> &middot; <font
                                                color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'clique_invite':

                                $get = $database->prepare("SELECT * FROM clique_invites WHERE invite_token='".$ext."'");
                                $get->execute();

                                if($get->rowCount() == 1)
                                {
                                    $tfetch = $get->fetch(PDO::FETCH_ASSOC);
                                    // Now get the clique data
                                    $c_data = $database->prepare("SELECT * FROM " . CLIQUES . " WHERE c_unique_id='".$tfetch['clique_id']."'");
                                    $c_data->execute();

                                    if($c_data->rowCount())
                                    {
                                        $cfetch = $c_data->fetch(PDO::FETCH_ASSOC);

                                        // Get the data
                                        ?>
                                        <div id="cliqueJoinStuff<?php echo $ext; ?>" class="activityTimelinePost clearfix postComment" style="box-shadow: 0px 1px 2px rgba(0,0,0,0.2);">
                                            <div style='float: left;height: 35px;width: 35px;background-image: url(<?php echo User::renderProfilePic($u['user_id'], 80); ?>);background-size: cover;border-radius: 5px;'></div>
                                            <div class="rightActivity" style="width: 87%;">
                                                <h5><a style='' href='<?php echo APP_URL; ?>profile/<?php echo $u['username']; ?>'><?php echo ucwords($u['firstname']); ?> <?php echo ucwords($u['lastname']); ?></a> <?php echo $what_happened; ?></h5>
                                                <div class="cliqueData" style="background: url(<?php echo APP_URL; ?>clique_data/<?php echo $tfetch['clique_id']; ?>/clique_banners/<?php echo $cfetch['c_banner_pic']; ?>);height: 100px;margin: 5px;border-radius: 3px;">
                                                    <div style="background: rgba(0,0,0,.7);width: 100%;height: 100%;border-radius: 3px;" class="cov">
                                                        <div class="topInvite" style="padding: 10px;">
                                                            <img src="<?php echo APP_URL; ?>clique_data/<?php echo $tfetch['clique_id']; ?>/clique_profile_pic/<?php echo $cfetch['c_profile_pic']; ?>" " style="float: left;height: 50px;width: 50px;border: 2px solid white;border-radius: 50%;"/>
                                                            <div class="cliqueInfo" style="margin-left: 55px;">
                                                                <h3 style=""><a href="<?php echo APP_URL; ?>clique/<?php echo $cfetch['c_username']; ?>"><?php echo ucwords($cfetch['c_name']); ?></a></h3>
                                                                <h4 style="color: white;font-weight: 400;"><?php echo Clique::numberOfMembers($tfetch['clique_id']); ?> members &middot; <?php echo $cfetch['c_privacy']; ?></h4>
                                                                <div style="padding-top: 5px;border-top: 1px solid #eee;margin: 5px;margin-left: 0px;" class="decisionmaker">
                                                                    <button class="btn cliqueInviteListener" data-invite_id="<?php echo $ext; ?>" data-d="yes" data-tko="<?php echo $_SESSION['token']; ?>" style="background: #14C79A;padding: 5px;padding-right: 7px;padding-left:7px;" data-tooltip="Join this clique!">Join</button>
                                                                    <button class="btn cliqueInviteListener" data-invite_id="<?php echo $ext; ?>" data-d="no" data-tko="<?php echo $_SESSION['token']; ?>" style="background: #e74c3c;padding: 5px;padding-right: 7px;padding-left:7px;" data-tooltip="Ignore this request">Ignore</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <h5 style="font-size: 14px;padding-top: 3px;"><font color="#34495e"><i class="fa fa-envelope"></i></font> &middot; <font color="#aaa"><?php echo Convert::convert_time($date); ?></font></h5>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                break;
                        }
                    }
                }
            } else {
                if($load == false) {
                    echo "<center><h3 style='padding: 5px;'>No Notifications</h3></center>";
                }
            }
        }
    }

    public static function emailNotification($from, $to, $subject, $type, $body)
    {
        if (!empty($from) && !empty($to) && !empty($subject) && !empty($type) && !empty($body)) {
            // Check to see if everyone exist
            $one = Users::checkExists($from);
            $two = Users::checkExists($to);

            if ($one == 1 && $two == 1) {
                $subject = Validation::santitize($subject);
                $types = array('post_comment', 'post_like', 'post_tags', 'messages_send', 'messages_photo', 'chat_started', 'chat_added', 'friend_request', 'friend_accept');

                if (in_array($type, $types)) {
                    // Now check the "to" persons email notifications settings
                    $db = new Database;

                    $query = $db->prepare("SELECT * FROM " . EMAIL_NOTIFICATIONS . " WHERE user_id=:to");
                    $query->execute(array(':to'=>$to));

                    if ($query->rowCount() == 1) {
                        $f = $query->fetch(PDO::FETCH_ASSOC);
                        $check = $f[$type];

                        if ($check == 1) {
                            // Means the person allows this kind of email
                            $data = array('to' => Users::get(USERS, $to, "email"), 'subject' => $subject, 'body' => $body);
                            Emailer::Email($data);
                            return false;
                        }
                    }
                }
            } else {
                return false;
            }
        }
    }

    public static function makeTimelineActivityPost($user_by, $type, $what_happened, $second_user = "", $exid = "")
    {
        if (!empty($user_by) && !empty($type) && !empty($what_happened))
        {
            $ub = (int)$user_by;
            $types = array('postLike', 'postComment', 'friends', 'personLike', 'postToUserProfile', 'newProfilePic', 'newBannerPic', 'newClique', 'cliqueJoin', 'cliquePost');

            if (in_array($type, $types)) {
                // Make connection
                if (Users::checkExists($user_by) == 1) {
                    $db = new Database;

                    // Create unique id
                    $encryption = md5(Validation::randomHash());

                    // Timeline main insert
                    $insert = $db->prepare("INSERT INTO " . TIMELINE_ITEM . " VALUES('', :encryption, :ub, :ub, 'activity', '2','','', :date,'0')");
                    $insert->execute(array(':encryption'=>$encryption, ':ub'=>$ub, ':data'=>date('y-m-d H:i:s')));

                    // Insert2
                    $insert2 = $db->prepare("INSERT INTO timeline_item_activity VALUES('', :encryption, :ub, :second_user, :type, :reason, :exid)");
                    $insert2->execute(array(':encryption'=>$encryption, ':ub'=>$ub, ':second_user'=>$second_user, ':reason' => $what_happened, ':type'=>$type, ':exid'=>$exid));

                    return false;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function markAllRead()
    {
        // Call db
        $db = new Database;

        // mark read
        $update = $db->prepare("UPDATE " . NOTES . " SET is_new='0' WHERE user_to=:user");
        $update->execute(array(':user'=>Sessions::get('salt')));

        return self::getNumber(Sessions::get('salt'));
    }

    public static function getNumber($user)
    {
        $database = new Database();

        $query = $database->prepare("SELECT * FROM " . NOTES . " WHERE user_to=:user AND is_new='1' ORDER BY id DESC");
        $query->execute(array(':user'=>$user));

        return $query->rowCount();
    }
}

?>