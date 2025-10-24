<?php
require_once __DIR__ . '/../model/UserModel.php';

class LoginController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new UserModel($db);
    }

    public function login($email, $password) {
        $user = $this->userModel->getUserByEmail($email);

        if ($user && password_verify($password, $user['user_Password'])) {
            // Create session
            session_start();
            $_SESSION['user_ID'] = $user['user_ID'];
            $_SESSION['user_Name'] = $user['user_Name'];
            $_SESSION['role_ID'] = $user['role_ID'];
            
            header('Location: /app/view/pages/home.php');
            exit;
        } else {
            return "Invalid email or password.";
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /app/view/pages/login.php');
        exit;
    }
}
?>
