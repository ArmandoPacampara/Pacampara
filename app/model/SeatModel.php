<?php
require_once 'config/db.php';

class SeatModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getSeatsByCinema($cinema_id) {
        $stmt = $this->conn->prepare("SELECT * FROM seats WHERE cinema_id = ?");
        $stmt->execute([$cinema_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateSeatStatus($seat_id, $status) {
        $stmt = $this->conn->prepare("UPDATE seats SET status = ? WHERE seat_ID = ?");
        return $stmt->execute([$status, $seat_id]);
    }
}
