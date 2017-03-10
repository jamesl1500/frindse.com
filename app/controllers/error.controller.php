<?php
class errorController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        // Find out model
        $this->initiateModel('index');

        // Initiate the view vars

    }

    public function index($type)
    {
        echo 'error';
    }
}