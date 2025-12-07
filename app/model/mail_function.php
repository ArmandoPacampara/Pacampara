<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

// --- CONFIGURATION ---
// We use constants to keep it clean and consistent
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USER', 'austrianeon@gmail.com');
define('MAIL_PASS', 'nghr kpmt blck nkwg'); // Your App Password
define('MAIL_PORT', 587);

function send_otp_email($recipient_email, $otp_code) {
    $mail = new PHPMailer(true);
    try {
        configure_mailer($mail); // Use helper function

        $mail->addAddress($recipient_email);
        $mail->isHTML(true);
        $mail->Subject = 'Your MoviEase Login Verification Code';
        $mail->Body    = "
            <h2>Two-Factor Authentication Code</h2>
            <p>Your One-Time Password (OTP) for logging in is:</p>
            <h1 style='color: #a31212;'>$otp_code</h1>
            <p>This code will expire in 5 minutes.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Echoing error for debugging (remove 'echo' in production)
        echo "OTP Error: {$mail->ErrorInfo}"; 
        return false;
    }
}

function send_ticket_receipt($recipient_email, $recipient_name, $ticket_data) {
    $mail = new PHPMailer(true);
    try {
        configure_mailer($mail); // Use helper function

        $mail->setFrom(MAIL_USER, 'MoviEase Ticketing');
        $mail->addAddress($recipient_email, $recipient_name);

        // Prepare Variables
        $ticket_id = str_pad($ticket_data['ticket_id'], 8, '0', STR_PAD_LEFT);
        $movie_name = $ticket_data['movie_name'];
        $cinema = $ticket_data['cinema_name'];
        $sched = date("F d, Y h:i A", strtotime($ticket_data['schedule']));
        $seats = $ticket_data['seats'];
        $price = number_format($ticket_data['final_price'], 2);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Your MoviEase E-Ticket - #$ticket_id";
        $mail->Body    = "
            <div style='font-family: Arial; padding: 20px; background: #f4f4f4;'>
                <div style='max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 10px;'>
                    <h2 style='color: #d60000; text-align: center;'>Booking Confirmed!</h2>
                    <p>Hi $recipient_name,</p>
                    <p>Here is your ticket for <strong>$movie_name</strong>.</p>
                    <table style='width: 100%; border-collapse: collapse; margin-top: 15px;'>
                        <tr><td style='padding: 5px; color: #666;'>Ticket ID:</td><td><strong>#$ticket_id</strong></td></tr>
                        <tr><td style='padding: 5px; color: #666;'>Cinema:</td><td><strong>$cinema</strong></td></tr>
                        <tr><td style='padding: 5px; color: #666;'>Schedule:</td><td><strong>$sched</strong></td></tr>
                        <tr><td style='padding: 5px; color: #666;'>Seats:</td><td><strong>$seats</strong></td></tr>
                        <tr><td style='padding: 5px; color: #666;'>Total Paid:</td><td><strong style='color: #d60000;'>₱$price</strong></td></tr>
                    </table>
                    <p style='margin-top: 20px; font-size: 12px; color: #999; text-align: center;'>Please show this email at the entrance.</p>
                </div>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // CRITICAL: This will show you WHY it failed on your screen
        echo "<script>alert('Email Error: " . addslashes($mail->ErrorInfo) . "');</script>";
        return false;
    }
}

// === THE FUNCTION FOR PASSWORD RESET (MUST NOT BE WRAPPED IN function_exists) ===
function sendPasswordResetEmail($recipient_email, $token) {
    $mail = new PHPMailer(true);
    try {
        configure_mailer($mail); // Use helper function
        
        // Construct the reset URL (ADJUST BASE_URL)
        $base_url = "http://localhost/moviease/app/view/pages/User/"; 
        $reset_link = $base_url . "ResetPassword.php?token=" . urlencode($token);

        $mail->setFrom(MAIL_USER, 'MoviEase Password Reset');
        $mail->addAddress($recipient_email);
        $mail->isHTML(true);
        $mail->Subject = 'MoviEase Password Reset Request';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h2>MoviEase Password Reset</h2>
                <p>You requested a password reset for your MoviEase account. Please click the button below to set a new password:</p>
                <p style='margin: 20px 0;'>
                    <a href='{$reset_link}' style='background: #a31212; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; display: inline-block;'>
                        Reset My Password
                    </a>
                </p>
                <p>If you did not request this, please ignore this email. This link will expire in 1 hour.</p>
                <hr>
                <p><small>MoviEase Support</small></p>
            </div>
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the detailed PHPMailer error (for debugging on your server)
        error_log("PHPMailer Error (Reset): {$e->getMessage()}");
        return false;
    }
}



// Helper to avoid repeating settings
function configure_mailer($mail) {
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USER;
    $mail->Password   = MAIL_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
    $mail->Port       = MAIL_PORT;

    // FIX FOR LOCALHOST SSL ISSUES (Often the cause of silent failures)
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
}
?>