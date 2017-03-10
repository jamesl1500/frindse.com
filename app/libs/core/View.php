<?php
/*
 * Main view file
 * ----
 * This is the main view file that will render pages
 */
class View
{
    public $mobile;

    private $path = '';
    public $header = '';
    private $footer = '';
    private $sidebar = '';

    public function __construct()
    {
        $this->mobile = new Mobile();
    }

    /*
     * Render
     * ----
     * This will render the view for each page
     */
    public function render($page, $name, $version = 1)
    {
        if(!empty($name) and !empty($page))
        {
            // Now figure out what device they're on
            if($this->mobile->isTablet())
            {
                // Its a tablet

            }else if($this->mobile->isMobile())
            {
                // Its on mobile

            }else{
                // Its on desktop
                $path = VIEWS . 'desktop/' . $page . '/v' . $version . '/' . $name . '.php';

                // Get the required templates
                $header = VIEWS . 'templates/desktop/v' . $version . '/'.$this->header.'.php';
                $footer = VIEWS . 'templates/desktop/v' . $version . '/footer.php';
                $this->sidebar = VIEWS . 'templates/desktop/v' . $version . '/sidebar.php';
                
                // Make sure the files exist
                if(file_exists($path))
                {
                    require $header;
                    require $path;
                    require $footer;
                }else{
                    Redirect::to('errors', '404');
                }
            }
        }else{
            Redirect::to('errors', '404');
        }
    }
}