<?php
// Note: Session is started in the main index.php or Signup.php before this is included

require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../core/Logger.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class SignupController {
    private $userModel;
    private $con;

    public function __construct($con) {
        $this->con = $con;
        $this->userModel = new UserModel($con);
    }

    /**
     * Handles the full signup request lifecycle (validation, email check, OTP generation).
     */
    public function handleSignupRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        
        // Data should be coming from JavaScript-injected hidden fields when the form is submitted
        $name = $_POST['name'] ?? '';
        $province = $_POST['province'] ?? ''; // This holds the concatenated location (Province / City / Barangay)
        // Note: address and city are not passed in the current JS logic, so they are set to empty string
        $address = $_POST['address'] ?? ''; // Currently unused in JS, adjust if needed
        $city = $_POST['city'] ?? ''; // Currently unused in JS, adjust if needed
        $phone = $_POST['phone-number'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        // --- 1. Server-Side Validation ---

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email address format.";
            $this->redirectBack();
        }

        if ($password !== $confirm) {
            $_SESSION['error'] = "Passwords do not match.";
            $this->redirectBack();
        }
        
        // Re-check password strength requirements if necessary (as done in JS validateAndSubmit)
        // Example check:
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
            $_SESSION['error'] = "Password must be at least 8 characters long and contain uppercase, lowercase, and a number.";
            $this->redirectBack();
        }


        // --- 2. Database Check (Model Call) ---
        if ($this->userModel->isEmailTaken($email)) {
            Logger::log($this->con, 0, "SIGNUP_FAILED", "Attempted signup with existing email: $email");
            $_SESSION['error'] = "Email address already exists.";
            $this->redirectBack();
        }

        // --- 3. Data Staging & OTP Generation ---
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['signup_data'] = [
            'name' => $name,
            'province' => $province,
            'address' => $address, 
            'city' => $city,
            'phone' => $phone,
            'email' => $email,
            // Hashing the password immediately before storing in session
            'password' => password_hash($password, PASSWORD_DEFAULT) 
        ];


        // --- 4. Email Sending (PHPMailer) ---
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'austrianeon@gmail.com';
            $mail->Password   = 'nghr kpmt blck nkwg'; // WARNING: Use environment variable or vault for real password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('austrianeon@gmail.com', 'MoviEase');
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = 'MoviEase Email Verification OTP';
            $mail->Body    = "<h2>Hi $name!</h2><p>Your OTP is: <b>$otp</b></p><p>Enter this code to complete your signup.</p>";

            $mail->send();
           
            Logger::log($this->con, 0, "SIGNUP_INITIATED", "OTP sent to potential new user: $email");

            // --- 5. Final Redirection ---
            header("Location: VerifyOTP.php");
            exit;
            
        } catch (Exception $e) {
            // Log the error and set user-friendly message
            Logger::log($this->con, 0, "EMAIL_FAILED", "Error sending OTP to $email: {$mail->ErrorInfo}");
            $_SESSION['error'] = "Error sending verification code. Please check your email address and try again. Technical error: {$mail->ErrorInfo}";
            $this->redirectBack();
        }
    }

    /**
     * Helper function to redirect back to the signup form on failure.
     */
    private function redirectBack() {
        header("Location: Signup.php"); 
        exit;
    }
}
?>