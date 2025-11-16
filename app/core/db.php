<?php
class Database
{
    private $host = "localhost";
    private $db_name = "moviease_db";
    private $username = "root";
    private $password = "MandoMando_11";
    private $port = 3306;

    public function getConnection()
    {
        $conn = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->db_name,
            $this->port
        );

        if ($conn->connect_error) {
            die("Database connection failed: " . $conn->connect_error);
        }

        return $conn;
    }
}
?>