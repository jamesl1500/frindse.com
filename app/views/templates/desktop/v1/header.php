<?php
$login = new LoginSystem();
$check = $login->isLoggedIn();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $this->title; ?></title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <meta name="description" content="<?php echo SITE_DESC; ?>">
    <meta name="keywords" content="frindse, social network, social media, network" />
    <meta name="robots" content="index,follow">

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo IMAGES; ?>logos/<?php echo SITE_LOGO; ?>" type="image/png">

    <!-- Main css file -->
    <link rel="stylesheet" type="text/css" href="<?php echo CSS; ?><?php echo $this->device; ?>/v<?php echo $this->version; ?>/<?php echo ucwords($this->stylesheet); ?>.css"/>
</head>
<body>
<div class="website-main col-lg-12">
    <header class="header header-fixed header-logged-out header-shadow-1">
        <div class="header-container col-lg-12">
            <div class="leftNavArea col-lg-3 col-md-3 col-sm-3 clearfix">
                <div class="leftIconHold">
                    <span id="barHold" class="sidebarOpen"><i class="fa fa-bars" aria-hidden="true"></i></span>
                    <span id="barHold" class="sidebarClose hidden"><i class="fa fa-times" aria-hidden="true"></i></span>
                </div>
                <div class="rightLogoHold">
                    <a href='<?php echo APP_URL; ?>'><img src="<?php echo IMAGES; ?>logos/<?php echo SITE_LOGO; ?>" /></a>
                    <h3><a href='<?php echo APP_URL; ?>'><?php echo SITE_NAME; ?></a></h3>
                </div>
            </div>
            <div class="middleNav col-lg-6 col-md-6 col-sm-5">
                <div class="searchBarHold col-lg-12 col-sm-12">
                    <form action="" method="post">
                        <input type="search" id="searchMainField" placeholder="Search" />
                    </form>
                    <div class="search_results" style="background: #eee;">
                        <div class="resultsUpper" style="padding: 15px;color: #4aaee7;background: white;">
                            <h3>Searching for: <span id="name"></span></h3>
                        </div>
                        <div class="StickySearchStopper"></div>
                        <div class="filterResultsNav">
                            <ul>
                                <li class="searchTabbing filterResultsNavActive" data-close="cliques_results" data-open="users_results"> <a href="">Users</a></li>
                                <li class="searchTabbing" data-close="users_results" data-open="cliques_results"><a href="">Cliques</a></li>
                            </ul>
                        </div>
                        <div class="results" style="padding: 0px;margin-top: 0px;">
                            <div class="inner_results_container">
                                <div class="searchRBlocks users_results">
                                    <div class="asideRight">
                                        <ul class="user_search_list" style="height: 250px;overflow-y: auto;">

                                        </ul>
                                        <div class="searchAllResults searchAllUsersResults" style="padding: 10px;background: white;">
                                            <a class="searchAllResultsHrefUsers" href="">See all Results</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="searchRBlocks cliques_results hidden">
                                    <div class="asideRight">
                                        <ul class="cliques_search_list" style="height: 250px;overflow-y: auto;">

                                        </ul>
                                        <div class="searchAllResults searchAllCliqueResults" style="padding: 10px;background: white;">
                                            <a class="searchAllResultsHrefCliques" href="">See all Results</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="rightNavArea logged col-lg-3 col-md-3 col-sm-4">
                <div class="rightProfileIcon">
                    <a href="<?php echo APP_URL; ?>profile/<?php echo Users::get(USERS, Sessions::get('salt'), 'username'); ?>"><img src="<?php echo APP_URL; ?>users/data/<?php echo $_SESSION['salt']; ?>/profile_picture" /></a>
                </div>
                <div class="leftIcons">
                    <div class="innerLeftIcons">
                        <ul>
                            <li class="navOpen" data-open="friendRequests"><span><i class="fa fa-fw fa-user" aria-hidden="true"></i></span></li>
                            <li class="navOpen" data-open="messages"><span><i class="fa fa-fw fa-envelope" aria-hidden="true"></i></span></li>
                            <li class="navOpen" data-open="notifications"><span><i class="fa fa-fw fa-bell" aria-hidden="true"></i></span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="navBoxes col-lg-4">
            <div class="navBox notifications hidden">
                <div class="topHead">
                    <h3>Notifications</h3>    
                </div>
                <div class="innerHold" id="innerNotesHold">
                    <?php Notifications::getAllNotes(Sessions::get('salt')); ?>
                </div>
            </div>
            <div class="navBox friendRequests hidden">
                <div class="topHead">
                    <h3>Friend Requests</h3>
                </div>
                <div class="innerHold" id="innerFriendRequestHold">

                </div>
            </div>
            <div class="navBox messages hidden">
                <div class="topHead">
                    <h3>Messages</h3>
                </div>
                <div class="innerHold" id="innerMessagesHold">

                </div>
            </div>
        </div>
        <div class="a_holder" style="position: fixed;bottom: 10px;right: 10px;z-index: 9999;">

        </div>
        <div class="n_holder" style="position: fixed;bottom: 10px;right: 10px;z-index: 9999;">
            
        </div>
        <div class="global_response_holder animated hidden">

        </div>
    </header>
    <div class="website-overlay"></div>

