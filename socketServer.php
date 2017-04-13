<?php
// Require socket & Config
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require __DIR__ . '/vendor/autoload.php';
require 'app/config/Config.php';

// Load all required files
require LIBS_CORE . 'Database.php';

require LIBS_CORE . 'Validation.php';
require LIBS_CORE . 'Response.php';

require LIBS_CORE . 'Cookie.php';
require LIBS_CORE . 'Session.php';

require LIBS_CORE . 'Mobile.php';
require LIBS_CORE . 'Redirect.php';

require LIBS_CORE . 'View.php';
require LIBS_CORE . 'Model.php';
require LIBS_CORE . 'Controller.php';

require LIBS_CORE . 'Router.php';
require LIBS_CORE . 'Api.php';

require LIBS_CORE . 'Emailer.php';
require LIBS_CORE . 'FileReader.php';

require LIBS . 'Convert.php';

require LIBS . 'Users.php';
require LIBS . 'Friends.php';
require LIBS . 'Tagging.php';
require LIBS . 'Notifications.php';
require LIBS . 'Block.php';

require LIBS . 'Suggestions.php';

require LIBS . 'Clique.php';
require LIBS . 'Search.php';

require LIBS . 'LoginSystem.php';
require LIBS . 'SignupSystem.php';
require LIBS . 'ForgotPasswordSystem.php';

require LIBS . 'Points.php';
require LIBS . 'Achievement.php';

require LIBS . 'Posts.php';

// Load database
$dbh = new Database();

// Load socket file
require LIBS_CORE . 'Socket.php';

// Make configs for server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Socket()
        )
    ),
    8083
);

// Run the server
$server->run();
?>