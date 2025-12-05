<?php
class Database {
    private $host = "sql301.infinityfree.com";
    private $db_name = "if0_40607448_XXX";
    private $username = "if0_40607448";
    private $password = "z0gB9iFfmMA";
    private $port = 3306; // Defined port property
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
$host = "sql301.infinityfree.com";
$user = "if0_40607448";
$pass = "z0gB9iFfmMA";
$dbname = "if0_40607448_XXX";
$port = 3306; // Port added here

// Establish procedural connection
$con = new mysqli($host, $user, $pass, $dbname, $port);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>