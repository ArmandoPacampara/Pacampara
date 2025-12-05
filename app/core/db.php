<?php
class Database {
    private $host = "localhost";
    private $db_name = "moviease_db";
    private $username = "root";
    private $password = "MandoMando_11";
    private $port = 3306; 
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name, $this->port);

            if ($this->conn->connect_error) {
                die("Connection failed: " . $this->conn->connect_error);
            }
        } catch (Exception $e) {
            echo "Connection error: " . $e->getMessage();
        }

        return $this->conn;
    }
}

$host = "localhost";
$user = "root";
$pass = "MandoMando_11";
$dbname = "moviease_db";
$port = 3306; 

$con = new mysqli($host, $user, $pass, $dbname, $port);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>