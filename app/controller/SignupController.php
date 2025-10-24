<?php
require_once __DIR__ . '/../model/UserModel.php';

class SignupController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new UserModel($db);
    }

    public function register($name, $email, $contact, $password, $recoveryEmail, $roleID) {

        if ($this->userModel->getUserByEmail($email)) {
            return "Email is already registered.";
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $userData = [
            'name' => $name,
            'email' => $email,
            'contact' => $contact,
            'password' => $hashedPassword,
            'recoveryEmail' => $recoveryEmail,
            'roleID' => $roleID
        ];
        $result = $this->userModel->createUser($userData);

        if ($result) {
            header('Location: /app/view/pages/login.php');
            exit;
        } else {
            return "Failed to register user. Please try again.";
        }
    }
}
?>