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

        // Now initiate the system
        $this->initiateSystem();

        // Now Start the sessions
        Sessions::initialize();
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
                if($url != "api")
                {
                    // Lets route this single page then
                    Router::Route($url[0]);
                }else{
                    // This is an API call so route this to the api route function that handles every api(ajax) call

                }
            }else if(count($url) == 1 and $url[0] == "wss" or count($url) == 1 and $url[0] == "server")
            {
                // This is for websockets and servers

            } else {
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
                    }
                }
            }
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

        require LIBS_CORE . 'Cookie.php';
        require LIBS_CORE . 'Session.php';

        require LIBS_CORE . 'Mobile.php';
        require LIBS_CORE . 'Redirect.php';

        require LIBS_CORE . 'View.php';
        require LIBS_CORE . 'Model.php';
        require LIBS_CORE . 'Controller.php';
        
        require LIBS_CORE . 'Router.php';
    }

}