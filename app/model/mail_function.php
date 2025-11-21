<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ Correct path: Go up two directories (from app/model to MOVIEASE root)
require __DIR__ . '/../../vendor/autoload.php';

function send_otp_email($recipient_email, $otp_code) {
    $mail = new PHPMailer(true);
    try {
        // Server settings using Mailtrap Live SMTP for sending
        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io'; // Use Mailtrap's live sending host
        $mail->SMTPAuth   = true;
        $mail->Username   = '8493e9bef14c39'; // Your Mailtrap Username (API Key)
        $mail->Password   = 'f0d7d744c0b2fb'; // Your Mailtrap Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS encryption
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('noreply@movi-ease.com', 'MoviEase Security');
        $mail->addAddress($recipient_email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your MoviEase Login Verification Code';
        $mail->Body    = "
            <h2>Two-Factor Authentication Code</h2>
            <p>Your One-Time Password (OTP) for logging in is:</p>
            <h1 style='color: #a31212;'>$otp_code</h1>
            <p>This code will expire in 5 minutes. Do not share it with anyone.</p>
        ";
        $mail->AltBody = "Your One-Time Password (OTP) for logging in is: $otp_code. This code will expire in 5 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error: "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
        return false;
    }
}
?>