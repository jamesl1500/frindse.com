<?php
class LoginController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        // Find out model
        $this->initiateModel('login');

        // Access database
        $this->db = $this->model->database;

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
        $this->view->title = "Login | Frindse";
        $this->view->version = SITE_TEMPLATES_VER;
        $this->view->stylesheet = "login";
        $this->view->javascript = "login";
        $this->view->header = "header-logged-out";

        // Now create the view
        $this->view->render('login', 'index', SITE_TEMPLATES_VER);
    }
}