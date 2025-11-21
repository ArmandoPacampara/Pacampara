<?php
session_start();
require_once __DIR__ . '/../core/db.php'; // Correct path to db.php

$database = new Database();
$con = $database->getConnection();

if (isset($_POST['verify_otp']) && isset($_SESSION['pending_user_id'])) {
    // ... (omitted OTP validation logic)
    
    $user_id = $_SESSION['pending_user_id'];
    $otp_code = $_POST['otp_code'] ?? '';
    $current_time = date('Y-m-d H:i:s');

    $query = "SELECT u.*, r.user_role FROM users u 
              JOIN otp_codes otp ON u.user_id = otp.user_id
              JOIN roles r ON u.role_id = r.role_id
              WHERE u.user_id = ? AND otp.otp_code = ? AND otp.expires_at > ? LIMIT 1";

    $stmt = $con->prepare($query);
    $stmt->bind_param("iss", $user_id, $otp_code, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        // --- SUCCESS: Log the user in and redirect ---
        // ... (omitted session setup and OTP deletion)
        
        // Final Redirection based on Role
        $role = $user['user_role'];
        if ($role === 'Admin') {
             // Redirect from app/model/ to app/view/pages/AdminPage.html
             header("Location: ../view/pages/AdminPage.html"); 
        } elseif ($role === 'Staff') {
             // Redirect from app/model/ to app/view/pages/StaffDashboard.php (assuming this exists)
             // Using HomePage.php as a placeholder for non-Admin/Staff roles.
             header("Location: ../view/pages/HomePage.php"); 
        } else {
             // Redirect from app/model/ to app/view/pages/HomePage.php
             header("Location: ../view/pages/HomePage.php");
        }
        exit;

    } else {
        $_SESSION['error'] = "Invalid or expired verification code.";
        // ✅ Correct path from app/model/ to app/view/pages/
        header("Location: ../view/pages/VerifyOTP.php"); 
        exit;
    }
} else {
    // If the user lands here without a pending ID, redirect them to the root index
    header("Location: /moviease/index.php");
    exit;
}
?>