<?php
class IndexController extends Controller
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
    }

    public function index()
    {
        // Initiate view vars for this page
        $this->view->title = "Welcome to Frindse";
        $this->view->version = SITE_TEMPLATES_VER;
        $this->view->stylesheet = "index";
        $this->view->header = "header-logged-out";

        // Now create the view
        $this->view->render('index', 'index', SITE_TEMPLATES_VER);
    }
}