<?php
require_once __DIR__ . '/../../config/db.php';

class UserModel {
    private $conn;

    public function __construct($db = null) {
        $this->conn = $db ?? require __DIR__ . '/../config/db.php';
    }

    public function getAllUsers() {
        $query = "SELECT * FROM users";
        return $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE user_Email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function getUserById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE user_ID = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($data) {
        $query = "INSERT INTO users (user_Name, user_Email, user_contact, user_Password, email_Recovery, role_ID)
                  VALUES (:name, :email, :contact, :password, :recovery, :role)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    public function deleteUser($id) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE user_ID = ?");
        return $stmt->execute([$id]);
    }
}
