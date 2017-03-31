<?php
/*
 * Signup system
 * ----
 * This system will be used to process everything for the signup system
 */
class SignupSystem extends Database
{
    private $db;

    private $email;
    private $username;

    /*
     * Register
     * ----
     * This will process the entire registration system and create our user
     */
    public function __construct()
    {
        $this->db = new Database();
    }

    public function activate($data)
    {
        if(isset($data['code']) && isset($data['email']))
        {
            // Make sure this is valid
            $check = $this->db->prepare("SELECT * FROM users WHERE user_salt=:code AND email=:email");
            if($check->execute(array(':code'=>$data['code'], ':email'=>$data['email'])))
            {
                // Now update the account
                $update = $this->db->prepare("UPDATE users SET activated='1' WHERE user_salt=:code AND email=:email");
                if($update->execute(array(':code'=>$data['code'], ':email'=>$data['email'])))
                {
                    // Log user in on the backend

                    echo json_encode(array('status' => "Your account has been created", 'code' => 1));
                    http_response_code(200);
                }else{
                    echo json_encode(array('status' => MESSAGE_SQL_ERROR, 'code' => 0));
                    http_response_code(200);
                }
            }else{
                echo json_encode(array('status' => MESSAGE_SQL_ERROR, 'code' => 0));
                http_response_code(200);
            }
        }else{
            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
            http_response_code(200);
        }
    }

    public function register($data)
    {
        // Validate email
        if(Validation::isEmail($data['email']))
        {
            // Make sure this user does not exist
            if($this->checkExists($data['username'], $data['email']))
            {
                // Create salt
                $salt = Validation::encrypt(Validation::randomHash());

                // Validate values
                $firstname = Validation::santitize($data['firstname']);
                $lastname = Validation::santitize($data['lastname']);
                $username = Validation::santitize($data['username']);
                $password = Validation::santitize($data['password']);

                // Now insert into database
                $insert = $this->db->prepare("INSERT INTO users VALUES('', :salt, :firstname, :lastname, :username, :email, :password, '0', 'unlocked', now())");
                if($insert->execute(array(':salt'=>$salt, ':firstname'=>$firstname, ':lastname'=>$lastname, ':username'=>$username, ':email'=>$this->email, ':password'=>Validation::passwordEncrypt($password))))
                {
                    // Now create the users directories and give permissions
                    mkdir(SITE_ROOT . '/data/user_data/' . $salt, 0777, true);
                    mkdir(SITE_ROOT . '/data/user_data/' . $salt . '/profile_pictures', 0777, true); // Profile pictures
                    mkdir(SITE_ROOT . '/data/user_data/' . $salt . '/banners', 0777, true); // Banners
                    mkdir(SITE_ROOT . '/data/user_data/' . $salt . '/photos', 0777, true); // Photos
                    mkdir(SITE_ROOT . '/data/user_data/' . $salt . '/videos', 0777, true); // Videos
                    mkdir(SITE_ROOT . '/data/user_data/' . $salt . '/data', 0777, true); // Data

                    copy(SITE_ROOT . '/data/user_data/default_pic.jpg', SITE_ROOT . '/data/user_data/' . $salt . '/profile_pictures/default_pic.jpg'); // Default profile pic
                    copy(SITE_ROOT . '/data/user_data/default_banner.jpg', SITE_ROOT . '/data/user_data/' . $salt . '/banners/default_banner.jpg'); // Default banner pic

                    chmod(SITE_ROOT . '/data/user_data/' . $salt . '/profile_pictures/default_pic.jpg', 0777);
                    chmod(SITE_ROOT . '/data/user_data/' . $salt . '/banners/default_banner.jpg', 0777);

                    chmod(SITE_ROOT . '/data/user_data/' . $salt, 0777);
                    chmod(SITE_ROOT . '/data/user_data/' . $salt . '/profile_pictures', 0777);
                    chmod(SITE_ROOT . '/data/user_data/' . $salt . '/banners', 0777);
                    chmod(SITE_ROOT . '/data/user_data/' . $salt . '/photos', 0777);
                    chmod(SITE_ROOT . '/data/user_data/' . $salt . '/videos', 0777);
                    chmod(SITE_ROOT . '/data/user_data/' . $salt . '/data', 0777);

                    // Now send the email
                    $body = "
					<html>
					<head>
						<link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=Roboto:400,100,300,500,700,900' />
					</head>
					<body style='height: 500px;'>
						<div class='email-container'>
							<div class='email-head' style='border-bottom: 1px solid #ddd; width: 60%; margin: 0 auto;'>
								<center><h1 style='font-size: 32px;'>" . SITE_NAME . "</h1></center>
								<h2 style='font-weight: 100; font-size: 25px; text-align: center;'>Welcome " . $firstname . "</h2>
							</div>
							<div class='inner-email'>
								<p style='text-align: center; color: #ccc; font-weight: 300; font-size: 20px;'>Welcome to the " . SITE_NAME . " community, we are glad to have you on our site, all you need to do now is activate your account</p>
								<br /><br />
								<center><a style='color: white; background:#2ecc71;border-radius: 5px; border: 1px solid transparent; padding-right: 20px; font-size: 24px; padding-left: 20px; padding-top: 15px; padding-bottom: 15px;height: 90px;text-decoration: none;text-align: center;' href='" . APP_URL . "signup/activate/" . $this->email . "/" . $salt . "'>Activate your account</a></center>
							</div>
						</div>
					</body>
					</html>
				    ";
                    Emailer::email($data = array(
                        'to' => Validation::decrypt($this->email),
                        'subject' => SITE_NAME . ' Account Activation!',
                        'body' => $body
                    ));

                    echo json_encode(array('status' => 'Your account has been created! Please check your email to activate your account', 'code' => 1));
                    http_response_code(200);
                }else{
                    echo json_encode(array('status' => MESSAGE_SQL_ERROR, 'code' => 0));
                    http_response_code(200);
                }
            }else{
                echo json_encode(array('status' => MESSAGE_ACCOUNT_EXIST, 'code' => 0));
                http_response_code(200);
            }
        }else{
            echo json_encode(array('status' => MESSAGE_INVALID_EMAIL, 'code' => 0));
            http_response_code(200);
        }
    }

    /*
     * Check Exist
     * ----
     * This will check to see if this user already exists
     */
    private function checkExists($username, $email)
    {
        if(!empty($username) && !empty($email))
        {
            $this->email = Validation::encrypt($email);

            $check = $this->db->prepare("SELECT * FROM users WHERE username=:username AND email=:email");
            if($check->execute(array(':username' => $username, ':email' => $this->email)))
            {
                if($check->rowCount() > 0)
                {
                    return false;
                }else{
                    return true;
                }
            }else{
                echo json_encode(array('status' => MESSAGE_SQL_ERROR, 'code' => 0));
                http_response_code(200);
            }
        }else{
            echo json_encode(array('status' => 'Please enter your Username and Email', 'code' => 0));
            http_response_code(200);
        }
    }
}