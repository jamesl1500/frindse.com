<?php
/*
 * Sockets
 * --------------
 * This will serve as our main logic for all of the socket applications we will have
 */
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
        $this->clients = new \SplObjectStorage;
        $this->subscriptions = [];
        $this->users = array();

        if(isset($dbh))
        {
            $this->dbh = $dbh;
        }
    }

    /*
     * onOpen
     * ----
     * We need to see if there is a logged in user
     */
    public function onOpen(ConnectionInterface $conn)
    {
        // Lets first see if there is a session cookie
        $sessionId = $conn->WebSocket->request->getCookies()['PHPSESSID'];

        // Store the new connection to send messages to later
        if($sessionId !="" )
        {
            $conn->session = $sessionId;
        }

        $this->clients->attach($conn);

        //$this->users[$conn->resourceId] = $conn;
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // Figure out whats incoming
        $data = json_decode($msg, true);
        $data['session_id'] = $from->WebSocket->request->getCookies()['PHPSESSID'];

        switch($data['type'])
        {
            case 'addNewlyLoggedUser':
                // Just call that function
                $this->userHandle('addNewlyLoggedUser', $data, $from);

                foreach ($this->clients as $client) {
                    if ($from !== $client) {
                        // The sender is not the receiver, send to each client connected
                        $client->send(json_encode($data));
                    }
                }
                break;

            case 'newPostLike':
                // Just call that function
                $this->postsHandle('newPostLike', $data, $from);
                break;
        }
    }

    public function onClose(ConnectionInterface $conn)
    {

    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {

    }

    public function postsHandle($type, $data, $resourceID)
    {
        if(!empty($type))
        {
            switch($type)
            {
                case 'newPostLike':
                    // Now were good
                    foreach ($this->clients as $client)
                    {
                        // Check to see of their friends with user
                        if($client->session == $data['sendToSessionId']){
                            $client->send(json_encode(array('type'=>'newPostLike', 'fromSalt'=>$data['fromSalt'], 'fromFirstname'=>$data['fromFirstname'], 'fromLastname'=>$data['fromLastname'], 'fromUsername'=>$data['fromUsername'])));
                        }

                        // Now traverse through users
                        //foreach($this->users as $key=>$val)
                        //{

                        //}
                    }
                    break;
            }
        }
    }

    public function userHandle($type, $data, $resourceID)
    {
        if(!empty($type))
        {
            switch($type)
            {
                case 'addNewlyLoggedUser':
                    // Add this user to the global array
                    unset($this->users[$data['sid']]);
                    
                    if(!in_array($data['sid_s'], $this->users)) {
                        $this->users[$resourceID->resourceId] = array(
                            'client_id' => $resourceID->resourceId,
                            'client_conn' => $resourceID,
                            'user_id' => $data['sid'],
                            'user_salt' => $data['sid_s'],
                            'session_id' => $data['session_id']
                        );
                    }

                    // In the future make it where you notifiy their friends of them logging in
                    $friends = Friends::getFriendsArray($data['sid_s']);

                    // Now were good
                    foreach ($this->clients as $client)
                    {
                        // Now traverse through users
                        foreach($this->users as $key=>$val)
                        {
                            // Check to see of their friends with user
                            if(in_array($val['user_salt'], $friends)){
                                $client->send(json_encode(array('type'=>'newOnlineFriend', 'friendSalt'=>$data['sid_s'], 'friendFirstname'=>Users::get(USERS, $data['sid_s'], 'firstname'), 'last_name'=>Users::get(USERS, $data['sid_s'], 'lastname'))));
                            }
                        }
                    }
                    break;
            }
        }
    }
}