<?php
session_start();
include 'db.php';
require '../../../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $province = $_POST['province'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $phone = $_POST['phone-number'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $confirm = $_POST['confirm_password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email address.");
    }

    if ($_POST['password'] !== $_POST['confirm_password']) {
        die("Passwords do not match.");
    }

    $check = $con->prepare("SELECT * FROM users WHERE user_Email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        die("Email already exists.");
    }

    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['signup_data'] = [
        'name' => $name,
        'province' => $province,
        'address' => $address,
        'city' => $city,
        'phone' => $phone,
        'email' => $email,
        'password' => $password
    ];


    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@gmail.com'; 
        $mail->Password   = 'your_app_password';  
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('your_email@gmail.com', 'MoviEase');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'MoviEase Email Verification OTP';
        $mail->Body    = "<h2>Hi $name!</h2><p>Your OTP is: <b>$otp</b></p><p>Enter this code to complete your signup.</p>";

        $mail->send();
        header("Location: verify_otp.php");
        exit;
    } catch (Exception $e) {
        echo "Error sending OTP: {$mail->ErrorInfo}";
    }
}
?>
