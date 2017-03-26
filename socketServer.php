<?php
// Require socket & Config
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Frindse\Socket;

require __DIR__ . '/vendor/autoload.php';
require 'app/config/Config.php';

// Load database
require LIBS_CORE . 'Database.php';
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