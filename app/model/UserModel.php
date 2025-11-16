<?php

class UserModel {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    public function getUserByEmail($email) {
        $stmt = $this->con->prepare("SELECT * FROM users WHERE user_Email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function createUser($name, $email, $contact, $passwordHash, $avatar = null) {
        $roleID = 2; // Customer
        $recoveryEmail = $email; // Default recovery email

        if ($avatar === null) {
            $avatar = "account_icon.png";
        }

        $stmt = $this->con->prepare("
            INSERT INTO users (role_ID, user_Name, user_Email, user_contact, user_Password, email_Recovery, user_avatar)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "issssss",
            $roleID,
            $name,
            $email,
            $contact,
            $passwordHash,
            $recoveryEmail,
            $avatar
        );

        return $stmt->execute();
    }
}
?>
