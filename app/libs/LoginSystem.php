<?php
/*
 * Login system
 * ----
 * This system will be used to process everything for the login system
 */
class LoginSystem extends Database
{
	private $db;

	private static $email;
	private static $password;

	/*
     * Register
     * ----
     * This will process the entire login system and create our user
     */
	public function __construct()
	{
		$this->db = new Database();
	}
	
	public function login($data)
	{
		if(!empty($data['email']) && !empty($data['password']))
		{
			self::$email = Validation::encrypt(Validation::santitize($data['email']));
			self::$password = Validation::santitize($data['password']);
			
			// First see if the person used a valid email
			if(Validation::isEmail(Validation::decrypt(self::$email)))
			{
				// Now see if the person with this email exist
				$check = $this->db->prepare("SELECT * FROM ". USERS ." WHERE email='".self::$email."'");
				$check->execute();
				
				if($check->rowCount() == 1)
				{
					$fetch = $check->fetch(PDO::FETCH_ASSOC);

					// Now do the real password check
					if(password_verify(self::$password, $fetch['password']))
					{
						// Now make sure the persons account is activated
						$activated = $fetch['activated'];
						$user_id = $fetch['user_id'];
						
						if($activated == 1)
						{
							// Means they're activated so log them in!
							switch ($fetch['status']) {
								case 'unlocked':
									// Start session
									$_SESSION['uid'] = $user_id;
									$_SESSION['salt'] = $fetch['user_salt'];
									$_SESSION['session_id'] = Sessions::getSessionToken();

									// Means to go ahead and log the person in
									$insert = $this->db->prepare("UPDATE ". USERS ." SET last_login=now(), sess_token='".Sessions::getSessionToken()."' WHERE user_id='" . $user_id . "'");
									$insert->execute();

									// Make a new token
									@$this->createLoginToken($fetch['user_salt'], bin2hex(openssl_random_pseudo_bytes(64, $cstrong = true)));

									$response['code'] = 1;

									// Send back sesison data for websockets
									$response['sid'] = $_SESSION['uid'];
									$response['sid_s'] = $_SESSION['salt'];
									
									$response['status'] = "Login successful!";
									echo json_encode($response);
									return false;
									break;
								case 'locked':
									$response['code'] = 0;
									$response['string'] = "Your account is locked because you have choosen to change your password!";
									echo json_encode($response);
									return false;
									break;
								case 'blocked':
									$response['code'] = 0;
									echo Response::make("Sorry your account is blocked");
									return false;
									break;

							}
						}else{
							echo Response::make("Wait! You haven't activated your account yet!<button class='btn resendEmailCall' data-email='" . self::$email . "' style='color: white;font-weight: 400;font-size: 14px;padding: 5px 13px;border: none;cursor: pointer;line-height: 1.5;margin-top: 5px;background: #2ecc71;border-radius: 2px;'>Resend Email</button>");
							return false;
						}
					}else{
						echo Response::make('Oops! That password is incorrect!');
						return false;
					}
				}else{
					echo Response::make('Oops! Nobody with this email exist!');
					return false;
				}
			}else{
				echo Response::make('Please enter a valid email');
				return false;
			}
		}
	}

	public function createLoginToken($salt, $token)
	{
		if(!empty($salt) && !empty($token))
		{
			// Delete any tokens by the user
			$delete = $this->db->prepare("DELETE FROM ". LOGIN_TOKENS ." WHERE user_salt=:salt");
			if($delete->execute(array(':salt'=>$salt)))
			{
				$insert = $this->db->prepare("INSERT INTO ". LOGIN_TOKENS ." VALUES('', :token, :salt)");
				if($insert->execute(array(':token' => sha1($token), ':salt' => $salt)))
				{
					// Create cokies
					Cookie::set("FUID", $token, time() + 60 * 60 * 24 * 7, '/', NULL, NULL);
					Cookie::set("FUID3D", '1', time() + 60 * 60 * 24 * 3, '/', NULL, NULL);
				}
			}
		}
	}

	public function isLoggedIn($type = "cookie", $return = 'user_id')
	{
		// Lets check the cookies
		if($type == "cookie")
		{
			if(isset($_COOKIE['FUID']))
			{
				// Means we have a good cookie but lets make sure it exists
				$check = $this->db->prepare("SELECT * FROM ". LOGIN_TOKENS ." WHERE token=:token");
				if($check->execute(array(':token'=>sha1($_COOKIE['FUID']))))
				{
					$fetch = $check->fetch(PDO::FETCH_ASSOC);

					if(isset($_COOKIE['FUID3D']))
					{
						// Means were still logged in
						return true;
					}else{
						// Then create a new FUID
						$this->createLoginToken($fetch['user_salt'], bin2hex(openssl_random_pseudo_bytes(64, $cstrong = true)));
						return true;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
	}
}