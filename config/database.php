<?php
/**
 *  file used for connecting to the database.
 */ 

class Database {



    public $conn;

    //Db connection
    /**
     * @return mixed
     */
     
  
    public function getConnection()
    {
        $this->conn = null;
 include '/var/www/html/api.ericade.net/config.php';
        try{
            $this->conn = new PDO("mysql:host=" . $dbhost  . ";dbname=" . $dbname, $dbuser, $dbpasswd);
            $this->conn->exec("set names utf8");
        }catch (PDOException $exception){
            echo "Connection error: ". $exception->getMessage();
        }
        return $this->conn;
    }
}
