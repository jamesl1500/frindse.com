<?php
class SignupController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        // Find out model
        $this->initiateModel('signup');

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
    }

    public function index()
    {
        // Initiate view vars for this page
        $this->view->title = "Signup | Frindse";
        $this->view->version = SITE_TEMPLATES_VER;
        $this->view->stylesheet = "signup";
        $this->view->javascript = "Signup";
        $this->view->header = "header-logged-out";

        // Now create the view
        $this->view->render('signup', 'index', SITE_TEMPLATES_VER);
    }
}