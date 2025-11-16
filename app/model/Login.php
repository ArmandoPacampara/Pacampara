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
        header("Location: index.php");
        exit;
    }

    $secretKey = "YOUR_SECRET_KEY";
    $verifyURL = "https://www.google.com/recaptcha/api/siteverify";

    $response = file_get_contents($verifyURL . "?secret=$secretKey&response=$captcha");
    $responseKeys = json_decode($response, true);

    if (!$responseKeys["success"]) {
        $_SESSION['error'] = "Captcha verification failed. Please try again.";
        header("Location: index.php");
        exit;
    }

    /** SECURE LOGIN **/
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $error = $loginController->login($email, $password);

    if ($error) {
        $_SESSION['error'] = $error;
        header("Location: index.php");
        exit;
    }
}
?>
