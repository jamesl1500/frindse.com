<?php
/*
 * Sockets
 * --------------
 * This will serve as our main logic for all of the socket applications we will have
 */
namespace Frindse;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Socket implements MessageComponentInterface
{
    protected $clients;
    private $subscriptions;
    private $users;

    private $dbh;

    public function __construct()
    {
        global $dbh;

        // Initiate things
        $this->clients = new \SplObjectStorage();
        $this->subscriptions = [];
        $this->users = [];

        if(isset($dbh))
        {
            $this->dbh = $dbh;
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        echo 'new client';
    }

    public function onMessage(ConnectionInterface $from, $msg) {
    }

    public function onClose(ConnectionInterface $conn) {
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}