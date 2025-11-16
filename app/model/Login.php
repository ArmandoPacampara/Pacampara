<?php
session_start();
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../controller/LoginController.php';

$database = new Database();
$con = $database->getConnection();

$loginController = new LoginController($con);

if (isset($_POST['login'])) {

    /** CAPTCHA Check **/
    $captcha = $_POST['g-recaptcha-response'] ?? null;

    if (!$captcha) {
        $_SESSION['error'] = "Please verify that you're not a robot.";
        header("Location: ");
        exit;
    }

    $secretKey = "YOUR_SECRET_KEY";
    $verifyURL = "https://www.google.com/recaptcha/api/siteverify";

    $response = file_get_contents($verifyURL . "?secret=$secretKey&response=$captcha");
    $responseKeys = json_decode($response, true);

    if (!$responseKeys["success"]) {
        $_SESSION['error'] = "Captcha verification failed. Please try again.";
        header("Location: ");
        exit;
    }

    /** SECURE LOGIN **/
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM users WHERE user_Email = '$email' LIMIT 1";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if ($password === $user['user_Password']) {

            $roleID = $user['role_ID'];
            $roleQuery = "SELECT user_Role FROM roles WHERE role_ID = '$roleID'";
            $roleResult = mysqli_query($con, $roleQuery);
            $roleData = mysqli_fetch_assoc($roleResult);
            $role = $roleData['user_Role'] ?? 'User';

            $_SESSION['user_ID'] = $user['user_ID'];
            $_SESSION['user_Name'] = $user['user_Name'];
            $_SESSION['user_Email'] = $user['user_Email'];
            $_SESSION['role'] = $role;

            if ($role === 'Admin') {
                header("Location: app/view/pages/AdminDashboard.php");
            } elseif ($role === 'Staff') {
                header("Location: app/view/pages/StaffDashboard.php");
            } else {
                header("Location: app/view/pages/HomePage.php");
            }
            exit;

        } else {
            $_SESSION['error'] = "Incorrect email or password.";
            header("Location: ");
            exit;
        }
    } else {
        $_SESSION['error'] = "Incorrect email or password.";
        header("Location: ");
        exit;
    }
}
?>