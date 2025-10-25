<?php
require_once 'config/db.php';

class BookingModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createBooking($data) {
        $query = "INSERT INTO booking (user_ID, movie_ID, seat_ID, date_booked, schedule, ticket_Quantity, price, status)
                  VALUES (:user_ID, :movie_ID, :seat_ID, :date_booked, :schedule, :ticket_Quantity, :price, :status)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    public function getUserBookings($user_ID) {
        $stmt = $this->conn->prepare("
            SELECT b.*, m.movie_Name, s.schedule 
            FROM booking b
            JOIN movies m ON b.movie_ID = m.movie_ID
            JOIN seats s ON b.seat_ID = s.seat_ID
            WHERE b.user_ID = ?");
        $stmt->execute([$user_ID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
