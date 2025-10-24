<?php
require_once 'config/db.php';

class MovieModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllMovies() {
        return $this->conn->query("SELECT * FROM movies")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMovieById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM movies WHERE movie_ID = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMoviesByGenre($genre) {
        $stmt = $this->conn->prepare("SELECT * FROM movies WHERE genre = ?");
        $stmt->execute([$genre]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
