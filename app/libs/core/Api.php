<?php
class Api
{
    private $allowedTypes = array('auth', 'users', 'friends', 'search', 'posts');

    /*
     * Auth:Register
     */
    private $RRrequiredFields = array('firstname', 'lastname', 'username', 'email', 'password');

    /*
     * Auth:Activate
     */
    private $RArequiredFields = array('code', 'email');

    /*
     * Auth:Login
     */
    private $lrequiredFields = array('email', 'password');

    /*
     * Users:Initiate Forgot Password
     */
    private $FPIrequiredFields = array('email');

    /*
     * Users:Recover Password
     */
    private $FPRPrequiredFields = array('email', 'code', 'password1', 'password2');

    /*
     * Search:Recover Password
     */
    private $SArequiredFields = array('searchUser');

    /*
     * Posts:Create Text
     */
    private $PCTrequiredFields = array('userTo', 'postBody', 'privacy');
    
    /*
     * Posts:Like/Unlike
     */
    private $PLUrequiredFields = array('pid', 'tag');

    /*
     * Posts:Making Comments
     */
    private $PMCrequiredFields = array('id', 'body');


    /*
     * __construct
     * ----
     * This will initialize what type of api call it is, what method to call and include other options
     */
    public function __construct($type, $method, $data, $options = '')
    {
       if(!empty($type) && !empty($method) && !empty($data))
       {
            if(in_array($type, $this->allowedTypes))
            {
                $this->$type($method, $data);
                return false;
            }else{
                echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
                http_response_code(200);
            }
       }else{
           echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
           http_response_code(200);
       }
    }
    
    /*
     * Auth
     * ----
     * This will hold the system for api calls when it comes to authorization:
     * - Login
     * - Logout
     * - Register
     * - Activating
     */
    protected function auth($method, $data = array())
    {
        if(!empty($method))
        {
            // Now run through the different methods
            switch($method)
            {
                case 'register':
                    if($data != "")
                    {
                        // Make sure the right fields are present
                        $count = 0;
                        $methodData = array();

                        foreach ($data as $info=>$val)
                        {
                            if(in_array($info, $this->RRrequiredFields))
                            {
                                // Method Data
                                $methodData[$info] = $val;
                                $count++;
                            }
                        }

                        if($count == 5)
                        {
                            // Now call the function
                            $register = new SignupSystem();
                            $register->$method($methodData);
                        }else{
                            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
                            http_response_code(200);
                        }
                    }
                    break;
                case 'activate':
                    if($data != "")
                    {
                        // Make sure the right fields are present
                        $count = 0;
                        $methodData = array();

                        foreach ($data as $info=>$val)
                        {
                            if(in_array($info, $this->RArequiredFields))
                            {
                                // Method Data
                                $methodData[$info] = $val;
                                $count++;
                            }
                        }

                        if($count == 2)
                        {
                            // Now call the function
                            $register = new SignupSystem();
                            $register->$method($methodData);
                        }else{
                            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
                            http_response_code(200);
                        }
                    }
                    break;
                case 'login':
                    if($data != "")
                    {
                        // Make sure the right fields are present
                        $count = 0;
                        $methodData = array();

                        foreach ($data as $info=>$val)
                        {
                            if(in_array($info, $this->lrequiredFields))
                            {
                                // Method Data
                                $methodData[$info] = $val;
                                $count++;
                            }
                        }


                        if($count == 2)
                        {
                            // Now call the function
                            $login = new LoginSystem();
                            $login->$method($methodData);
                        }else{
                            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
                            http_response_code(200);
                        }
                    }
                    break;
                default:
                    echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
                    http_response_code(200);
                    break;
            }
        }else{
            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
            http_response_code(200);
        }
    }

    /*
     * Users
     * ----
     * This will be a pretty big method but it will handle everything with users
     * - Initiating Password Recovery
     */
    protected function users($method, $data = array())
    {
        if(!empty($method))
        {
            // Now run through the different methods
            switch($method)
            {
                case 'initiatePasswordRecovery':
                    if($data != "")
                    {
                        // Make sure the right fields are present
                        $count = 0;
                        $methodData = array();

                        foreach ($data as $info=>$val)
                        {
                            if(in_array($info, $this->FPIrequiredFields))
                            {
                                // Method Data
                                $methodData[$info] = $val;
                                $count++;
                            }
                        }


                        if($count == 1)
                        {
                            // Now call the function
                            $fp = new ForgotPasswordSystem();
                            $fp->$method($methodData);
                        }else{
                            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
                            http_response_code(200);
                        }
                    }
                    break;
                case 'recoverPassword':
                    if($data != "")
                    {
                        // Make sure the right fields are present
                        $count = 0;
                        $methodData = array();

                        foreach ($data as $info=>$val)
                        {
                            if(in_array($info, $this->FPRPrequiredFields))
                            {
                                // Method Data
                                $methodData[$info] = $val;
                                $count++;
                            }
                        }


                        if($count == 4)
                        {
                            // Now call the function
                            $fp = new ForgotPasswordSystem();
                            $fp->$method($methodData);
                        }else{
                            echo json_encode(array('status' => 'Invalid Request1', 'code' => 0));
                            http_response_code(200);
                        }
                    }
                    break;
            }
        }else{
            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
            http_response_code(200);
        }
    }

    /*
     * Search
     * ----
     * This will handle the search system
     * - Searching cliques and users
     */
    protected function search($method, $data = array())
    {
        if(!empty($method))
        {
            // Now run through the different methods
            switch($method)
            {
                case 'main':
                    if($data != "")
                    {
                        // Make sure the right fields are present
                        $count = 0;
                        $methodData = array();

                        foreach ($data as $info=>$val)
                        {
                            if(in_array($info, $this->SArequiredFields))
                            {
                                // Method Data
                                $methodData[$info] = $val;
                                $count++;
                            }
                        }

                        if($count == 1)
                        {
                            // Now call the function
                            $fp = new Search();
                            $fp->$method($methodData);
                        }else{
                            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
                            http_response_code(200);
                        }
                    }
                    break;
            }
        }else{
            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
            http_response_code(200);
        }
    }

    /*
     * Friends
     * ----
     * This will handle the friends system
     * - Sending friends requests
     * - Unfriending people
     * - Making friend request decisions
     * - Gathering friend requests (ajax)
     */
    protected function friends($method, $data = array())
    {
        if(!empty($method))
        {
            // Now run through the different methods
            switch($method)
            {
                case 'sendFriendRequest':
                    if($data != "")
                    {
                        // Make sure the right fields are present
                        $count = 0;
                        $methodData = array();

                        foreach ($data as $info=>$val)
                        {
                            if(in_array($info, $this->FPIrequiredFields))
                            {
                                // Method Data
                                $methodData[$info] = $val;
                                $count++;
                            }
                        }

                        if($count == 1)
                        {
                            // Now call the function
                            $fp = new ForgotPasswordSystem();
                            $fp->$method($methodData);
                        }else{
                            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
                            http_response_code(200);
                        }
                    }
                    break;
            }
        }else{
            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
            http_response_code(200);
        }
    }

    /*
     * Posts
     * ----
     * This will handle the posts system
     * - Creating Posts
     * - Editing Posts
     * - Deleting posts
     * - Liking/Unliking posts
     * - Resharing Posts
     * - Reporting posts
     */
    protected function posts($method, $data = array())
    {
        if(!empty($method))
        {
            // Now run through the different methods
            switch($method)
            {
                case 'makeTextPost':
                    if($data != "")
                    {
                        // Make sure the right fields are present
                        $count = 0;
                        $methodData = array();

                        foreach ($data as $info=>$val)
                        {
                            if(in_array($info, $this->PCTrequiredFields))
                            {
                                // Method Data
                                $methodData[$info] = $val;
                                $count++;
                            }
                        }

                        if($count == 3)
                        {
                            // Now call the function
                            $fp = new Posts();
                            $fp->$method($methodData);
                        }else{
                            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
                            http_response_code(200);
                        }
                    }
                    break;

                // Liking Posts
                case 'likePost':
                    if($data != "")
                    {
                        // Make sure the right fields are present
                        $count = 0;
                        $methodData = array();

                        foreach ($data as $info=>$val)
                        {
                            if(in_array($info, $this->PLUrequiredFields))
                            {
                                // Method Data
                                $methodData[$info] = $val;
                                $count++;
                            }
                        }

                        if($count == 2)
                        {
                            // Now call the function
                            $fp = new Posts();
                            $fp->$method($methodData);
                        }else{
                            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
                            http_response_code(200);
                        }
                    }
                    break;

                // Unliking posts
                case 'unlikePost':
                    if($data != "")
                    {
                        // Make sure the right fields are present
                        $count = 0;
                        $methodData = array();

                        foreach ($data as $info=>$val)
                        {
                            if(in_array($info, $this->PLUrequiredFields))
                            {
                                // Method Data
                                $methodData[$info] = $val;
                                $count++;
                            }
                        }

                        if($count == 2)
                        {
                            // Now call the function
                            $fp = new Posts();
                            $fp->$method($methodData);
                        }else{
                            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
                            http_response_code(200);
                        }
                    }
                    break;
                
                // Making comments
                case 'makeComment':
                    if($data != "")
                    {
                        // Make sure the right fields are present
                        $count = 0;
                        $methodData = array();

                        foreach ($data as $info=>$val)
                        {
                            if(in_array($info, $this->PMCrequiredFields))
                            {
                                // Method Data
                                $methodData[$info] = $val;
                                $count++;
                            }
                        }

                        if($count == 2)
                        {
                            // Now call the function
                            $fp = new Posts();
                            $fp->$method($methodData);
                        }else{
                            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
                            http_response_code(200);
                        }
                    }
                    break;
                
            }
        }else{
            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
            http_response_code(200);
        }
    }
}