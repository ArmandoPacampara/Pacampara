<?php
class Database {
    private $host = "localhost";
    private $db_name = "moviease_db";
    private $username = "root";
    private $password = "admin";

    private $root = "3307";
    public $conn;

    

    function getConnection() {
        $mysqli = new mysqli("localhost", "root", "admin", "moviease_db", 3307);

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
$pass = "admin";
$dbname = "moviease_db";
$port = 3307;
$con = new mysqli($host, $user, $pass, $dbname, $port);
?>