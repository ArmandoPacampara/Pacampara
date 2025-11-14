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

        return $mysqli; // <-- return the connection
    }
}
?>