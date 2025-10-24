<?php
require_once 'config/db.php';

class RecommendationModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getRecommendationsByUser($user_ID) {
        $stmt = $this->conn->prepare("
            SELECT r.*, m.movie_Name, m.genre 
            FROM recommendations r
            JOIN movies m ON r.movie_ID = m.movie_ID
            WHERE r.user_ID = ?");
        $stmt->execute([$user_ID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addRecommendation($data) {
        $query = "INSERT INTO recommendations (user_ID, movie_ID, reason)
                  VALUES (:user_ID, :movie_ID, :reason)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }
}
