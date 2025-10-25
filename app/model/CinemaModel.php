<?php
require_once 'config/db.php';

class CinemaModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllCinemas() {
        $query = "SELECT * FROM cinemas";
        return $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCinemaById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM cinemas WHERE cinema_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
