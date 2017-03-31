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

    public function activate($data)
    {
        // Initiate view vars for this page
        $this->view->title = "Activate | Frindse";
        $this->view->version = SITE_TEMPLATES_VER;
        $this->view->stylesheet = "signup";
        $this->view->javascript = "Signup";
        $this->view->header = "header-logged-out";

        // Now create the view
        $check = $this->db->prepare("SELECT * FROM users WHERE email=:email AND user_salt=:code AND activated='0'");
        $check->execute(array(':email'=>$data['email'], ':code'=>$data['code']));

        if($check->rowCount() == 1)
        {
            $fetch = $check->fetch(PDO::FETCH_ASSOC);

            // Plus vars
            $this->view->firstname = $fetch['firstname'];
            $this->view->email = $fetch['email'];
            $this->view->code = $fetch['user_salt'];

            $this->view->render('signup', 'activate', SITE_TEMPLATES_VER);
        }else{
            Redirect::to('errors', '404');
        }
    }
}