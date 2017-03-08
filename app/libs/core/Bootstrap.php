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

        // Test
        Router::Route($_GET['u']);
    }

    /*
     * Require Files
     * ----
     * This file will require all of the files that
     * will be needed to run the site
     */
    private function requireFiles()
    {
        require LIBS_CORE . 'Controller.php';
        require LIBS_CORE . 'Router.php';
    }

}