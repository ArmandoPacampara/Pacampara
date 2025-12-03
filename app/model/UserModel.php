<?php
// app/model/UserModel.php
require_once __DIR__ . '/../core/db.php'; // Assuming you have your DB connection here

class UserModel {
    private $conn;

    public function __construct() {
        $db = new Database(); // Assuming your db.php class is named Database
        $this->conn = $db->getConnection();
    }

    public function checkUserByEmail($email) {
        // 1. Prepare Statement
        $query = "SELECT user_id, user_name, user_email, user_password, role_id, failed_login_attempts, lockout_until 
                  FROM users WHERE user_email = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Return the user array
        }
        
        return false; // User not found
    }

    // You can add methods here to update failed attempts, etc.
    public function updateLoginAttempts($user_id, $attempts, $lockout = null) {
        $query = "UPDATE users SET failed_login_attempts = ?, lockout_until = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isi", $attempts, $lockout, $user_id);
        $stmt->execute();
    }
}
?>