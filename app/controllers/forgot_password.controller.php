<?php
class Forgot_passwordController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        // Find out model
        $this->initiateModel('signup');

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
        $this->view->title = "Forgot Password | Frindse";
        $this->view->version = SITE_TEMPLATES_VER;
        $this->view->stylesheet = "forgot_password";
        $this->view->javascript = "forgot_password";
        $this->view->header = "header-logged-out";

        // Now create the view
        $this->view->render('forgot_password', 'index', SITE_TEMPLATES_VER);
    }

    public function reset($data)
    {
        // Initiate view vars for this page
        $this->view->title = "Change Password | Frindse";
        $this->view->version = SITE_TEMPLATES_VER;
        $this->view->stylesheet = "forgot_password";
        $this->view->javascript = "forgot_password";
        $this->view->header = "header-logged-out";

        // Now create the view but make sure this is a valid request
        $check = $this->db->prepare("SELECT * FROM forgot_password WHERE code=:code AND email=:email");
        $check->execute(array(':code'=>$data['code'], ':email'=>$data['email']));

        if($check->rowCount() == 1)
        {
            $fetch = $check->fetch(PDO::FETCH_ASSOC);

            // Plug vars
            $this->view->code = $fetch['code'];
            $this->view->user_id = $fetch['user_id'];
            $this->view->email = $fetch['email'];

            $this->view->render('forgot_password', 'change_password', SITE_TEMPLATES_VER);
        }else{
            Redirect::to('errors', '404');
        }
    }
}