<?php
// moviease/app/model/PasswordReset.php
// Adjust paths as necessary for your project structure
require_once __DIR__ . '/../core/db.php'; 
require_once __DIR__ . '/../core/Logger.php';
require_once __DIR__ . '/mail_function.php'; 

$database = new Database();
$con = $database->getConnection(); 


if (isset($_POST['forgot_password'])) {
    $email = trim($_POST['email'] ?? '');

    // 1. Check if user exists
    $user_query = "SELECT user_id FROM users WHERE user_email = ? LIMIT 1";
    $user_stmt = $con->prepare($user_query);
    $user_stmt->bind_param("s", $email);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    $user_stmt->close();
    
    $user_id = $user['user_id'] ?? 0;

    // Security: Provide a generic success message
    if (!$user) {
        Logger::log($con, $user_id, "PASSWORD_RESET_REQUEST_FAILED", "Request for non-existent email: $email");
        $_SESSION['success'] = "If a matching account was found, a password reset link has been sent to your email.";
        header("Location: ForgotPassword.php"); 
        exit;
    }
    
    // 2. Generate token (Do NOT generate expiry time here)
    $token = bin2hex(random_bytes(32)); // 64-character token
    
    // 3. Upsert (Insert or Update) the token in the password_resets table
    // CRITICAL FIX: Use MySQL's internal NOW() function + 1 hour for expiry
    $upsert_query = "INSERT INTO password_resets (email, token, expires_at) 
                     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR)) 
                     ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)";
    
    $upsert_stmt = $con->prepare($upsert_query);
    // CRITICAL: We only bind 2 parameters now (email, token)
    $upsert_stmt->bind_param("ss", $email, $token);
    
    if ($upsert_stmt->execute()) {
        Logger::log($con, $user_id, "PASSWORD_RESET_TOKEN_GENERATED", "Reset token generated for user ID: {$user_id}.");

        // 4. Send email
        if (sendPasswordResetEmail($email, $token)) {
            $_SESSION['success'] = "If a matching account was found, a password reset link has been sent to your email.";
        } else {
            $_SESSION['error'] = "A system error occurred while sending the reset email. Please try again later.";
            Logger::log($con, $user_id, "PASSWORD_RESET_MAIL_FAILED", "Failed to send reset email to $email.");
        }
    } else {
        $_SESSION['error'] = "A database error occurred. Please try again.";
        Logger::log($con, $user_id, "PASSWORD_RESET_DB_ERROR", "Failed to store reset token for $email.");
    }
    
    $upsert_stmt->close();
    
    header("Location: ForgotPassword.php"); 
    exit;
}
?>