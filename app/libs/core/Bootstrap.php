<?php
class Bootstrap
{
   /*
    * Main Construct
    * ----
    * This will initialize everything and start up the website!
    */
    public function __construct()
    {
        // Include all of the files needed for the site to load
        $this->requireFiles();

        // Now Start the sessions
        Sessions::initialize();

        // Now initiate the system
        $this->initiateSystem();
    }

    /*
     * Main starter
     * ----
     * This will route every page and api call we have. This function holds all of the routes for the site
     */
    private function initiateSystem()
    {
        if (isset($_GET) && isset($_GET['u']))
        {
            $url = $_GET['u'];

            // Trim the var
            $url = rtrim($url, '/');
            $url = explode('/', $url);

            // Now we need to check to see if this is a API call or page call
            if(count($url) == 1 or $url[0] == "api")
            {
                // Lets see if this is a single page call
                if($url[0] != "api")
                {
                    // Lets route this single page then
                    Sessions::set(CSRF_TOKEN_NAME, Validation::encrypt(Validation::randomHash()));
                    Router::Route($url[0]);
                }else {
                    // This is an API call so route this to the api route function that handles every api(ajax) call
                    if (isset($_POST) or isset($_GET))
                    {
                        if($_SESSION[CSRF_TOKEN_NAME] == $_POST['xhr_csrf_token'])
                        {
                            $api = new Api($url[1], $url[2], $_POST, $options = array('csrf' => $_POST['xhr_csrf_token'], 'xhr_true' => $_POST['xhr_true'], 'xhr_is_mobile' => $_POST['xhr_is_mobile']));
                        }else{
                            echo json_encode(array('status' => 'Invalid Request', 'code' => 0));
                            return false;
                        }
                    } else {
                        Redirect::to('errors', '404');
                    }
                }
            }else {
                // This means there are multiple strings and its not a api call
                if(count($url) >= 2)
                {
                    // Switch between the different pages
                    switch($url[0])
                    {
                        case 'error':
                            if($url[1] != "")
                            {
                                Router::RoutePageWithSub('error','index', array('type' => $url[1]));
                            }
                            break;
                        case 'signup':
                            if($url[1] != "" && $url[2] != "")
                            {
                                Router::RoutePageWithSub('signup','activate', array('email' => $url[2], 'code' => $url[3]));
                            }
                            break;
                    }
                }
            }
        }else{
            Redirect::to('location', 'index');
        }
    }

    /*
     * Require Files
     * ----
     * This file will require all of the files that
     * will be needed to run the site
     */
    private function requireFiles()
    {
        require LIBS_CORE . 'Database.php';

        require LIBS_CORE . 'Validation.php';
        require LIBS_CORE . 'Response.php';

        require LIBS_CORE . 'Cookie.php';
        require LIBS_CORE . 'Session.php';

        require LIBS_CORE . 'Mobile.php';
        require LIBS_CORE . 'Redirect.php';

        require LIBS_CORE . 'View.php';
        require LIBS_CORE . 'Model.php';
        require LIBS_CORE . 'Controller.php';
        
        require LIBS_CORE . 'Router.php';
        require LIBS_CORE . 'Api.php';

        require LIBS_CORE . 'Emailer.php';

        require LIBS . 'LoginSystem.php';
        require LIBS . 'SignupSystem.php';
        require LIBS . 'ForgotPasswordSystem.php';



    }

}