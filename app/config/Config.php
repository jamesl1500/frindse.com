<?php
/*
 * Central Frindse.com Config File
 * -----
 * This is the new configuration file that will define everything we need
 * -----
 * SECTIONS
 *
 * 1. Global Config Array
 *  1.A Stylesheets Array
 *  1.B Javascript Array
 * 2. Site Info
 * 3. Database Configuration
 * 4. Directory Configuration
 * 5. Messages
 * 6. Sessions
 * 7. Cookies
 * 8. CSRF
 */


//-- 1. Global Config Array
$config = array();

$config['stylesheets'] = array('mobile'=>array(),'desktop'=>array());
$config['javascript'] = array('jquery', 'socket.io', 'main');

//-- 2. Site Info
defined('SITE_NAME') or define('SITE_NAME', 'Frindse');
defined('SITE_DESC') or define('SITE_DESC', 'Frindse is a simple social network that lets you meet new people and have fun with all your friends');
defined('SITE_TAGLINE') or define('SITE_TAGLINE', 'Frindse is a simple social network that lets you meet new people and have fun with all your friends');
defined('SITE_AUTHOR') or define('SITE_AUTHOR', 'James Latten');
defined('SITE_UTF') or define('SITE_UTF', 'UTF-8');
defined('SITE_LANG') or define('SITE_LANG', 'en');
defined('SITE_TAG') or define('SITE_TAGS','');
defined('SITE_VER') or define('SITE_VER','0.0.1');
defined('SITE_TEMPLATES_VER') or define('SITE_TEMPLATES_VER','1');
defined('SITE_LOGO') or define('SITE_LOGO','frindse-circle-logo.jpg');
define('DS', DIRECTORY_SEPARATOR);

defined('PASSWORD_HASH_METHOD') or define('PASSWORD_HASH_METHOD', PASSWORD_BCRYPT);

defined('SITE_ROOT') or define('SITE_ROOT',realpath(dirname(dirname(dirname(__FILE__)))));

define('APP_URL', 'http://localhost/frindse.com/');

defined('ENCRYPTION_METHOD') or define('ENCRYPTION_METHOD','AES-256-CBC');
defined('ENCRYPTION_KEY') or define('ENCRYPTION_KEY','N70g2FA4u9A5r5qssX6p927DH1yIK8uG');

//-- 3. Database Configuration
defined('DB_TYPE') or define('DB_TYPE', 'mysql');
defined('DB_NAME') or define('DB_NAME', 'frindse.com');
defined('DB_HOST') or define('DB_HOST','127.0.0.1');
defined('DB_USER') or define('DB_USER','root');
defined('DB_PASS') or define('DB_PASS','Cooley12%');

//-- 4. Directory Configuration
defined('APPLICATION') or define('APPLICATION', SITE_ROOT . '/app/');
defined('CONTROLLERS') or define('CONTROLLERS', APPLICATION . 'controllers/');
defined('LIBS') or define('LIBS', APPLICATION . 'libs/');
defined('LIBS_CORE') or define('LIBS_CORE', APPLICATION . 'libs/core/');
defined('MODELS') or define('MODELS', APPLICATION . 'models/');
defined('VIEWS') or define('VIEWS', APPLICATION . 'views/');
defined('ASSETS') or define('ASSETS', APP_URL . 'assets/');
defined('CSS') or define('CSS', ASSETS . 'css/');
defined('IMAGES') or define('IMAGES', ASSETS . 'images/');
defined('JAVASCRIPT') or define('JAVASCRIPT', ASSETS . 'javascript/');
defined('CONFIG') or define('CONFIG', APPLICATION . '/config/');
defined('DATA_PRIVATE') or define('DATA_PRIVATE', SITE_ROOT . '/data/');
defined('DATA_PRIVATE_FILES') or define('DATA_PRIVATE_FILES', DATA_PRIVATE . 'files/');
defined('DATA_PRIVATE_USERS') or define('DATA_PRIVATE_USERS', DATA_PRIVATE . 'user_data/');
defined('DATA_PUBLIC') or define('DATA_PUBLIC', APP_URL . '/data/');
defined('DATA_PUBLIC_FILES') or define('DATA_PUBLIC_FILES', DATA_PUBLIC . 'files/');
defined('DATA_PUBLIC_USERS') or define('DATA_PUBLIC_USERS', DATA_PUBLIC . 'user_data/');
defined('LOGS') or define('LOGS', SITE_ROOT . '/logs/');
defined('ERRORS') or define('ERRORS', LOGS . 'errors/');
defined('SESSIONS') or define('SESSIONS', LOGS . 'sessions/');

//-- 5. Messages
define('MESSAGE_SQL_ERROR', 'Sorry, '.SITE_NAME.' made a little mistake! Please try again.');
define('MESSAGE_USER_NO_EXIST', "Sorry but this person dosen't exist!");
define('MESSAGE_NOT_VALID_EMAIL','Please enter a valid email');
define('MESSAGE_EMAIL_AND_PASSWORD_INCORRECT','That password or email is incorrect!');
define('MESSAGE_NO_ACCOUNT_WITH_THIS_EMAIL','There is no account with this email');

define('MESSAGE_ACCOUNT_LOCKED','Your account is locked because you have chosen to change your password!');
define('MESSAGE_ACCOUNT_BLOCKED','Sorry your account is blocked');

define('MESSAGE_ENTER_A_EMAIL','Please enter a email');
define('MESSAGE_ENTER_BOTH','Please enter a email and password');

define('MESSAGE_ALREADY_ACTIVATED','Your account is already activated!');

define('MESSAGE_USERNAME_ALREADY_EXIST','This username is already taken!');
define('MESSAGE_EMAIL_ALREADY_EXIST','This email is already taken!');
define('MESSAGE_BOTH_TAKEN','Both the email and username are taken!');

define('MESSAGE_ACCOUNT_EXIST','An account with this username or email already exists');

define('MESSAGE_EMAILS_DONT_MATCH',"Your emails don't match!");
define('MESSAGE_PASSWORDS_DONT_MATCH',"Your passwords don't match!");

define('MESSAGE_USERNAME_TOO_LONG','Your username has to be less then 25 letters');

define('MESSAGE_ENTER_EVERYTHING','Please enter everything!');

define('MESSAGE_CONVERSATION_ALR_MADE', 'A conversation has already been made');
define('MESSAGE_CONVERSATION_CANT_BE_DUP', 'You cant send a message to yourself silly!');

define('MESSAGE_ACTIVATION_ERROR_ONE',"This email or code don't match up!");

define('MESSAGE_FRIEND_REQUEST_NOT_FOUND', 'This is a invalid friend request');
define('MESSAGE_ALREADY_FRIENDS', "Looks like you're already friends with them!");

define('MESSAGE_INVALID_EMAIL','Please enter a valid email');

//-- 6. Sessions
ini_set('session.save_path', SESSIONS);
ini_set('session.gc_probability', 1);

session_save_path(SESSIONS);

//-- 7. Cookie
define('COOKIE_EXPIRY', time() + 2 * 7 * 24 * 3600);
define('COOKIE_NAME', 'remember_cache');

//-- 8. CSRF
defined('CSRF_TOKEN_NAME') or define('CSRF_TOKEN_NAME', 'FRINDSE_TOKEN_CSRF');
defined('CSRF_COOKIE_NAME') or define('CSRF_COOKIE_NAME', 'FRINDSE_COOKIE_CSRF');
defined('CSRF_EXPIRE') or define('CSRF_EXPIRE', 7200);

//-- 9. Database Names
defined('USERS') or define('USERS', 'users');
defined('RELATIONSHIPS') or define('RELATIONSHIPS', 'relationships');
defined('LOGIN_TOKENS') or define('LOGIN_TOKENS', 'login_tokens');
defined('FORGOT_PASSWORD') or define('FORGOT_PASSWORD', 'forgot_password');
defined('HASHTAGS') or define('HASHTAGS', 'hashtags');
defined('NOTES') or define('NOTES', 'notes');
define('BLOCKS','blocks');
define('MESSAGES','mesages');
define('EMAIL_NOTIFICATIONS','email_notifications');
define('ACHIEVEMENTS_TYPES','achievement_types');
define('RANK_TYPES','rank_types');
define('POST_REPORTS','post_reports');
define('PERSON_RATES','person_rates');
define('OPEN_TIMELINE_CHATS','open_timeline_chats');
define("PROFILE_VIEWERS","profile_viewers");
define('FRIEND_REQUESTS','friend_requests');

// Timeline Tables
define("TIMELINE_ITEM", "timeline_item");
define("TIMELINE_POST_COMMENTS","timeline_post_comments");
define("TIMELINE_ITEM_TEXT", "timeline_item_text");
define("TIMELINE_ITEM_PERSONLIKE","	timeline_item_personlike");
define("TIMELINE_ITEM_VIDEO","timeline_item_videos");
define("TIMELINE_ITEM_PHOTO","timeline_item_photo");
define("TIMELINE_ITEM_FRIENDS","timeline_item_friends");
define("TIMELINE_POST_COMMENTS_LIKES","timeline_post_comments_likes");
define("TIMELINE_ITEM_MUSIC","timeline_item_music");
define("TIMELINE_POST_LIKES","timeline_post_likes");
define("TIMELINE_ITEM_ACTIVITY","timeline_item_activity");
define("TIMELINE_ITEM_CLIQUE_SHARE","timeline_item_clique_share");
define("TIMELINE_ITEM_SHARE","timeline_item_share");
define("TIMELINE_ITEM_ORIGINAL_VIDEOS","timeline_item_original_videos");
define("TIMELINE_ITEM_CHAT_INVITE","timeline_item_chat_invite");

// Chats Tables
define('CHATS', 'chats');
define('CHAT_MEMBERS','chat_members');
define('CHAT_MESSAGES_ITEM','chat_messages_item');
define('CHAT_MESSAGE_ITEM_NOTE','chat_message_item_note');
define('CHAT_MESSAGE_ITEM_TEXT','chat_message_item_text');
define('CHAT_MESSAGE_ITEM_VIDEO','chat_message_item_video');
define('CHAT_MESSAGE_ITEM_PICTURE','chat_message_item_picture');
define('CHAT_MESSAGES_ITEM_USERJOINED','chat_message_item_userJoined');
define('CHAT_MESSAGE_ITEM_USERLEFT','chat_message_item_userLeft');

// Cliques
define('CLIQUES','cliques');
define('CLIQUE_MEMBERS','clique_members');
define('CLIQUE_REQUESTS','clique_requests');
define('CLIQUE_INVITES','clique_invites');