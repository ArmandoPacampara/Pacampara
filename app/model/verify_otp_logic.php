<?php
session_start();
// Adjust path to reach core from app/model/
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Logger.php'; 

$database = new Database();
$con = $database->getConnection();

if (isset($_POST['verify_otp'])) {
    
    $input_otp = $_POST['otp_code'];
    // 1. Check against the OTP stored in SESSION by Signup.php
    $session_otp = $_SESSION['otp'] ?? null;

    if ($input_otp == $session_otp) {
        // --- OTP MATCHED: CREATE USER ACCOUNT ---
        
        // Retrieve the temporary data saved in Signup.php
        $data = $_SESSION['signup_data'];
        
        $name = $data['name'];
        $email = $data['email'];
        $password = $data['password']; // This is already hashed
        $phone = $data['phone'];
        $full_address = $data['province']; // Contains "Province / City / Barangay"
        
        // Default Role: Customer (ID 2)
        $role_id = 2; 

        // Prepare Insert Statement
        // FIX: Removed 'created_at' and 'NOW()' because the column does not exist in your users table.
        $stmt = $con->prepare("
            INSERT INTO users 
            (role_id, user_name, user_email, user_contact, user_password, user_address, email_recovery, user_avatar) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'account_icon.png')
        ");

        if ($stmt) {
            $stmt->bind_param("issssss", $role_id, $name, $email, $phone, $password, $full_address, $email);
            
            if ($stmt->execute()) {
                $new_user_id = $stmt->insert_id;

                // [LOG SUCCESS]
                Logger::log($con, $new_user_id, "SIGNUP_SUCCESS", "User verified OTP and account was created.");

                // --- LOGIN THE USER DIRECTLY ---
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['role'] = 'Customer'; 

                // Clear temporary session data
                unset($_SESSION['otp']);
                unset($_SESSION['signup_data']);

                // Redirect to Home Page
                // Path: app/model/ -> app/view/pages/User/HomePage.php
                header("Location: ../../public/index.php");
                exit;
            } else {
                die("Database Error: " . $stmt->error);
            }
            $stmt->close();
        } else {
            die("Prepare Failed: " . $con->error);
        }

    } else {
        // --- OTP FAILED ---
        $_SESSION['error'] = "Invalid or expired verification code.";
        // Redirect back to VerifyOTP (User view)
        header("Location: ../view/pages/User/VerifyOTP.php"); 
        exit;
    }
} else {
    // If accessed directly without form submission
    header("Location: ../../public/index.php");
    exit;
}
?>