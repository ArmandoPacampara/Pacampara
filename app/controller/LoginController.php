<?php
// app/controller/LoginController.php
require_once __DIR__ . '/../model/UserModel.php';

class LoginController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    // Method to show the Login Page (View)
    public function index() {
        // Check for error messages in session to pass to view
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']); // Clear after showing
        
        // Load the View
        require_once __DIR__ . '/../view/pages/User/Login.php';
    }

    // Method to process the POST request
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // 1. Sanitize Input
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];
            // (Add your ReCaptcha check here)

            // 2. Ask Model for User Data
            $user = $this->userModel->checkUserByEmail($email);

            // 3. Logic Check
            if ($user && password_verify($password, $user['user_password'])) {
                // Success
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['role_id'] = $user['role_id'];
                
                // Redirect based on role
                if($user['role_id'] == 1) { // Admin
                     header("Location: /moviease/app/view/pages/Admin/AdminPage.php");
                } else {
                     header("Location: /moviease/app/view/pages/User/HomePage.php");
                }
                exit;
            } else {
                // Failure
                $_SESSION['error'] = "Invalid email or password.";
                header("Location: index.php"); // Redirect back to login
                exit;
            }
        }
    }
}
?>