<div class="sidebar sidebar-closed col-lg-3">
    <?php
    if($check)
    {
        ?>
        <div class="topBannerHold">
            <div class="cover">
                <div class="topProfilePic">
                    <img src="<?php echo APP_URL; ?>users/data/<?php echo $_SESSION['salt']; ?>/profile_picture/<?php echo Sessions::get(CSRF_TOKEN_NAME); ?>" />
                </div>
                <div class="middleUserInfo">
                    <h3><?php echo Users::get('users', $_SESSION['salt'], 'firstname'); ?> <?php echo Users::get('users', $_SESSION['salt'], 'lastname'); ?></h3>
                </div>
            </div>
        </div>
        <div class="divide"></div>
        <div class="linkList">
            <ul>
                <a id="timeline" href="<?php echo APP_URL; ?>timeline"><li><span><i class="fa fa-fw fa-home" aria-hidden="true"></i>Timeline</span></li></a>
                <a id="conversations" href="<?php echo APP_URL; ?>conversations"><li><span><i class="fa fa-fw fa-envelope" aria-hidden="true"></i> Conversations</span></li></a>
                <a id="chats" href="<?php echo APP_URL; ?>chats"><li><span><i class="fa fa-fw fa-comments" aria-hidden="true"></i> Chats</span></li></a>
                <a id="cliques" href="<?php echo APP_URL; ?>cliques"><li><span><i class="fa fa-fw fa-users" aria-hidden="true"></i> Cliques</span></li></a>
                <a id="edit_profile" href="<?php echo APP_URL; ?>edit_profile"><li><span><i class="fa fa-pencil" aria-hidden="true"></i> Edit Profile</span></li></a>
                <a id="account_settings" href="<?php echo APP_URL; ?>account_settings"><li><span><i class="fa fa-cog" aria-hidden="true"></i> Account Settings</span></li></a>
            </ul>
        </div>
        <?php
    }else {
        ?>
        <div class="topBannerHoldLoggedOut">
            <div class="cover">
                
            </div>
        </div>
        <div class="divide"></div>
        <div class="linkList">
            <ul>
                <li><a href="<?php echo APP_URL; ?>login"><i class="fa fa-fw fa-sign-in" aria-hidden="true"></i> Login</a></li>
                <li><a href="<?php echo APP_URL; ?>signup"><i class="fa fa-fw fa-user-plus" aria-hidden="true"></i> Signup</a></li>
                <li><a href="<?php echo APP_URL; ?>forgot_password"><i class="fa fa-fw fa-lock" aria-hidden="true"></i> Forgot Password</a></li>
            </ul>
        </div>
        <?php
    }
    ?>
</div>