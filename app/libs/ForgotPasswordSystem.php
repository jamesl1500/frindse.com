<?php
/*
 * Forgot Password system
 * ----
 * This system will be used to process everything for the forgot password system
 */
class ForgotPasswordSystem extends Database
{
    private $db;

    private $email;

    /*
     * Construct
     * ----
     * This will process the entire login system and create our user
     */
    public function __construct()
    {
        $this->db = new Database();
    }

    public function initiatePasswordRecovery($data)
    {
        if(!empty($data))
        {
           $this->email = Validation::encrypt($data['email']);

            // Check to see if the user exists
            $check = $this->db->prepare("SELECT * FROM users WHERE email=:email");
            if($check->execute(array(':email'=>$this->email)))
            {
                if($check->rowCount() == 1)
                {
                    $fetch = $check->fetch(PDO::FETCH_ASSOC);

                    // Means we got a hit, now make sure they havent asked for a new password recently
                    $check2 = $this->db->prepare("SELECT * FROM forgot_password WHERE user_id=:user_id");
                    $check2->execute(array(':user_id'=>$fetch['user_id']));

                    if($check2->rowCount() == 0)
                    {
                        @$code = bin2hex(openssl_random_pseudo_bytes(64, $cstrong = true));
                        
                        // Now insert stuff and send email
                        $insert = $this->db->prepare("INSERT INTO forgot_password VALUES('', :user_id, :code, now())");
                        if($insert->execute(array(':user_id'=>$fetch['user_id'], ':code'=>$code)))
                        {
                            // Send the email
                            // Now send the email
                            $body = "
                            <html>
								<head>
									<link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=Roboto:400,100,300,500,700,900' />
								</head>
								<body style='height: 500px;'>
									<div class='email-container'>
										<div class='email-head' style='border-bottom: 1px solid #ddd; width: 60%; margin: 0 auto;'>
											<center><h1 style='font-size: 32px;'>".SITE_NAME."</h1></center>
											<h2 style='font-weight: 100; font-size: 25px; text-align: center;'>Hello " . $fetch['firstname'] . "</h2>
										</div>
										<div class='inner-email'>
											<p style='text-align: center; color: #ccc; font-weight: 300; font-size: 20px;'>You have asked to reset your password for your ".SITE_NAME." account, please click the button below to reset your password and unlock your account. <br />
											Question: Why do we lock your account when you ask to reset your password?<br />
											Answer: Because we want the most security for your account, it makes it where nobody can get into your account after its locked.</p>
											<br /><br />
											<center><a style='color: white; background:#2ecc71;border-radius: 5px; border: 1px solid transparent; padding-right: 20px; font-size: 24px; padding-left: 20px; padding-top: 15px; padding-bottom: 15px;height: 90px;text-decoration: none;text-align: center;' href='" . APP_URL . "forgot_password/reset/" . $this->email . "/" . $code . "'>Reset your password!</a></center>
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

                            echo json_encode(array('status' => 'Please check your inbox to reset your password!', 'code' => 1));
                            http_response_code(200);
                        }else{
                            echo json_encode(array('status' => MESSAGE_SQL_ERROR, 'code' => 0));
                            http_response_code(200);
                        }
                    }else{
                        echo json_encode(array('status' => "You've already asked to have your password reset!", 'code' => 0));
                        http_response_code(200);
                    }
                }else{
                    echo json_encode(array('status' => MESSAGE_NO_ACCOUNT_WITH_THIS_EMAIL, 'code' => 0));
                    http_response_code(200);
                }
            }else{
                echo json_encode(array('status' => MESSAGE_SQL_ERROR, 'code' => 0));
                http_response_code(200);
            }
        }
    }
}