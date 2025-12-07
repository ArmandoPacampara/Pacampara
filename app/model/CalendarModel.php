<?php
require_once 'config/db.php';

class CalendarModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getMovieSchedules($movie_ID) {
        $stmt = $this->conn->prepare("SELECT * FROM calendar WHERE movie_ID = ?");
        $stmt->execute([$movie_ID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
