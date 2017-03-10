<?php
/*
 * Main model file
 * ----
 * This will initiate all the things thats needed for a model to work!
 */
class Model
{
    public $database;
    
    public function __construct()
    {
        $this->database = new Database();
    }
}