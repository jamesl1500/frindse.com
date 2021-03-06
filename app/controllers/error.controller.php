<?php
class errorController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        // Find out model
        $this->initiateModel('index');

        // Initiate the view vars
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

    public function index($type)
    {
        $this->view->title = $type['type'] . " | Frindse";
        $this->view->version = SITE_TEMPLATES_VER;
        $this->view->stylesheet = "error";
        $this->view->header = "header-logged-out";

        // Now create the view
        $this->view->render('error', $type['type'], SITE_TEMPLATES_VER);
    }
}