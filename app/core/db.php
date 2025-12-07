<?php
class Database {
    private $host = "localhost";
    private $db_name = "moviease_db";
    private $username = "root";
    private $password = "admin";
    private $port = 3307; // Defined port property
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // Use the properties ($this->host, etc.) instead of hardcoded strings
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

// Procedural connection (Global variable)
$host = "localhost";
$user = "root";
$pass = "admin";
$dbname = "moviease_db";
$port = 3307; // Port added here
// Establish procedural connection
$con = new mysqli($host, $user, $pass, $dbname, $port);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>