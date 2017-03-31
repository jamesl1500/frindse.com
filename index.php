<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Require Bootstrap & Config
require __DIR__ . '/vendor/autoload.php';

require 'app/config/Config.php';
require 'app/libs/core/Bootstrap.php';

// Welcome to frindse
$frindse = new Bootstrap();
