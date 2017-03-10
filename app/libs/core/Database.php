<?php
/*
 * Main database file
 * ----
 * This will allow us to make use of the database everywhere
 */
class Database extends PDO
{
    private $host = DB_HOST;
    private $username = DB_USER;
    private $socket = DB_TYPE;
    private $password = DB_PASS;
    private $dbname = DB_NAME;

    private $options = array(PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

    public function __construct()
    {
        parent::__construct($this->socket . ':host=' . $this->host . ';dbname=' . $this->dbname, $this->username, $this->password, $this->options);
    }
}