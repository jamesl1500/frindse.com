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
        $this->users = array();

        if(isset($dbh))
        {
            $this->dbh = $dbh;
        }
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // Figure out whats incoming
        $data = json_decode($msg, true);

        switch($data['type'])
        {
            case 'addNewlyLoggedUser':
                // Just call that function
                $this->userHandle('addNewlyLoggedUser', $data, $from->resourceId);
                break;
        }

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {

    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {

    }

    public function userHandle($type, $data, $resourceID)
    {
        if(!empty($type))
        {
            switch($type)
            {
                case 'addNewlyLoggedUser':
                    // Add this user to the global array
                    if(!in_array($data['sid'], $this->users)) {
                        $this->users[$data['sid']] = array(
                            'client_id' => $resourceID,
                            'user_id' => $data['sid'],
                            'user_salt' => $data['sid_s']
                        );
                    }

                    // In the future make it where you notifiy their friends of them logging in

                    // Now were good
                    foreach ($this->clients as $client) {
                        $client->send(print_r($this->users));
                    }
                    break;
            }
        }
    }
}