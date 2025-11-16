<?php
require_once __DIR__ . '/../model/UserModel.php';

class SignupController
{
    private $userModel;

    public function __construct($db = null)
    {
        if ($db)
            $this->userModel = new UserModel($db);
    }

<<<<<<< HEAD
    public function register($name, $email, $contact, $password, $confirmPassword): ?string {
=======
    // public function index()
    // {
    //     require __DIR__ . '/../view/pages/Signup.php';
    // }

    public function register($name, $email, $contact, $password, $recoveryEmail, $roleID)
    {
>>>>>>> staging

        // Password match check
        if ($password !== $confirmPassword) {
            return "Passwords do not match.";
        }

        // Duplicate email check
        if ($this->userModel->getUserByEmail($email)) {
            return "Email already exists.";
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Create user (no recoveryEmail, no role param)
        $created = $this->userModel->createUser(
            $name,
            $email,
            $contact,
            $passwordHash
        );

<<<<<<< HEAD
        if ($created) {
            return null; // success
=======
        if ($result) {
            header('Location: ');
            exit;
        } else {
            return "Failed to register user. Please try again.";
>>>>>>> staging
        }

        return "Registration failed. Please try again.";
    }
}
?>
