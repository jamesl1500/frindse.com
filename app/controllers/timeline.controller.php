<?php
class TimelineController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        // Find out model
        $this->initiateModel('index');

        // Initiate the device
        if($this->view->mobile->isTablet())
        {
            $this->view->device = "tablet";
        }else if($this->view->mobile->isMoble())
        {
            $this->view->device = "mobile";
        }else{
            $this->view->device = "desktop";
        }

        // Login status
        $login = new LoginSystem();
        $this->logged = $login->isLoggedIn();
    }

    public function index()
    {
        // Initiate view vars for this page
        $this->view->title = "Timeline | Frindse";
        $this->view->version = SITE_TEMPLATES_VER;
        $this->view->stylesheet = "timeline";
        $this->view->javascript = "timeline";
        $this->view->header = "header";
        $this->view->page = "timeline";

        // Now create the view
        if($this->logged)
        {
            $this->view->render('timeline', 'index', SITE_TEMPLATES_VER);
        }else{
            Redirect::to('location', 'index');
        }
    }
}