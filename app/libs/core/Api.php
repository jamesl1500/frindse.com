<?php
class Api
{
    private $allowedTypes = array('auth', 'users');

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
                case 'changePassword':
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
}