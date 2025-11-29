<?php
class Database {
    private $host = "localhost";
    private $db_name = "moviease_db";
    private $username = "root";
    private $password = "MandoMando_11";

    private $root = "3306";
    public $conn;

    

    function getConnection() {
        $mysqli = new mysqli("localhost", "root", "MandoMando_11", "moviease_db", 3306);

        // Check connection
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }

        return $mysqli; 
    }
}
// baguhin niyo nalang yung details ng connection dito
$host = "localhost";
$user = "root";
$pass = "MandoMando_11";
$dbname = "moviease_db";
$port = 3306;
$con = new mysqli($host, $user, $pass, $dbname, $port);
?>