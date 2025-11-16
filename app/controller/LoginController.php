<?php
require_once __DIR__ . '/../model/UserModel.php';

class LoginController
{
    private $userModel;

    public function __construct($db = null)
    {
        if ($db)
            $this->userModel = new UserModel($db);
    }

    // public function index()
    // {
    //     require __DIR__ . '/../view/pages/Login.php';
    // }

    public function login($email, $password): ?string
    {
        $user = $this->userModel->getUserByEmail($email);

        if ($user && password_verify($password, $user['user_Password'])) {
            // Create session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_ID'] = $user['user_ID'];
            $_SESSION['user_Name'] = $user['user_Name'];
            $_SESSION['role_ID'] = $user['role_ID'];

            header('Location: /app/view/pages/home.php');
            exit; // after exit, function ends
        }

        // Return error if login failed
        return "Invalid email or password.";
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header('Location: /app/view/pages/login.php');
        exit;
    }
}
?>