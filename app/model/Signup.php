<?php
session_start();
require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../controller/SignupController.php';

$database = new Database();
$con = $database->getConnection();

$signup = new SignupController($con);

if (isset($_POST['signup'])) {

    $captcha = $_POST['g-recaptcha-response'] ?? null;

    if (!$captcha) {
        $_SESSION['error'] = "Please verify you're not a robot.";
        header("Location: Signup.php");
        exit;
    }

    $secretKey = "YOUR_SECRET_KEY";
    $response = file_get_contents(
        "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captcha"
    );
    $responseKeys = json_decode($response, true);

    if (!$responseKeys["success"]) {
        $_SESSION['error'] = "Captcha failed.";
        header("Location: Signup.php");
        exit;
    }

    $error = $signup->register(
        $_POST['name'],
        $_POST['email'],
        $_POST['contact'],
        $_POST['password'],
        $_POST['confirm_password']
    );
    
    if ($error) {
        $_SESSION['error'] = $error;
        header("Location: Signup.php");
        exit;
    }

    $_SESSION['success'] = "Account created successfully!";
    header("Location: login.php");
    exit;
}
?>
