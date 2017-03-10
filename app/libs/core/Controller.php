<?php
/*
 * Main Controlelr File
 * ----
 * Every page will consist of 3 things
 * - Controller
 * - Model
 * - View
 *
 *  This file will load the model and the view for the controller
 */
class Controller
{
    /*
     * Main construct
     * ----
     * This will initialize things for every controller
     */
    public function __construct()
    {
        $this->initiateView();
    }

    public function initiateView()
    {
        $this->view = new View();
    }

    public function initiateModel($name)
    {
        $path = MODELS . '' . $name . '.model.php';

        if(file_exists($path))
        {
            require $path;

            $modalName = $name . 'Model';
            $this->model = new $modalName();
        }
    }
}