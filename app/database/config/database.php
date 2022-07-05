<?php


class database {
    private $hostname = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'nti';
    private $con;
     function __construct() {
        // connection
        $this->con = new mysqli($this->hostname,$this->username,$this->password,$this->database);
        // if ($con->connect_error) {
        //     die("Connection failed: " . $con->connect_error);
        // } else {
        //     echo "ok";
        // }
    }
    // insert - update - delete
    public function runDML($query)
    {
        $result = $this->con->query($query);
        if($result){
            return true;
        }else{
            return false;
        }
    }
    // selects
    public function runDQL($query)
    {
        $result = $this->con->query($query);
        if($result->num_rows > 0){
            return $result;
        }else{
            return [];
        }
    }
}
// $con
// DQL => SELECT => [] , has value
// DML => insert - update - delete => true , false