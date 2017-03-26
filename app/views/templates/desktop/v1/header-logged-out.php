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
        <div class="leftNavArea col-lg-3 clearfix">
            <div class="leftIconHold">
                <span id="barHold" class="sidebarOpen"><i class="fa fa-bars" aria-hidden="true"></i></span>
                <span id="barHold" class="sidebarClose hidden"><i class="fa fa-times" aria-hidden="true"></i></span>
            </div>
            <div class="rightLogoHold">
                <a href='<?php echo APP_URL; ?>'><img src="<?php echo IMAGES; ?>logos/<?php echo SITE_LOGO; ?>" /></a>
                <h3><a href='<?php echo APP_URL; ?>'><?php echo SITE_NAME; ?></a></h3>
            </div>
        </div>
        <div class="middleNav col-lg-6">
            <div class="searchBarHold col-lg-12">
                <form action="" method="post">
                    <input type="search" id="searchMainField" placeholder="Search" />
                </form>
            </div>
        </div>
        <div class="rightNavArea col-lg-3">
            <ul>
                <li><a href="<?php echo APP_URL; ?>signup">Signup</a></li>
                <li><a href="<?php echo APP_URL; ?>login">Login</a></li>
            </ul>
        </div>
    </div>
</header>
<div class="website-overlay"></div>

