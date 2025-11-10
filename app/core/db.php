<?php
class Database {
    private $host = "localhost";
    private $db_name = "moviease_db";
    private $username = "root";
    private $password = "MandoMando_11";
    public $conn;

    function getConnection() {
        $mysqli = new mysqli("localhost", "root", "MandoMando_11", "moviease_db");

        // Check connection
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }

        return $mysqli; // <-- return the connection
    }
}
?>