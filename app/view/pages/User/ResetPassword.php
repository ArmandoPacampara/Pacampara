<?php
// moviease/app/view/pages/User/ResetPassword.php
session_start();
// Adjust paths to your core files
require_once  '../../../../app/core/db.php'; 
require_once  '../../../../app/core/Logger.php';

$database = new Database();
$con = $database->getConnection();

$token = $_GET['token'] ?? '';
$error = '';
$user_email = ''; // To store the email associated with the token

// --- 1. HANDLE TOKEN VALIDATION ---
if (!empty($token)) {
    // Check if the token is valid and not expired. This query is correct.
    $query = "SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if ($data) {
        $user_email = $data['email'];
    } else {
        // Debugging check: Log the token status to see if it exists but expired
        
        // Check if token exists at all (ignoring expiry)
        $debug_query = "SELECT expires_at FROM password_resets WHERE token = ?";
        $debug_stmt = $con->prepare($debug_query);
        $debug_stmt->bind_param("s", $token);
        $debug_stmt->execute();
        $debug_result = $debug_stmt->get_result();
        $debug_data = $debug_result->fetch_assoc();
        $debug_stmt->close();

        if ($debug_data) {
            $error = "Password reset token has expired. (Expiry: {$debug_data['expires_at']})";
        } else {
            $error = "Invalid password reset token.";
        }
    }
} else {
    $error = "Password reset token is missing.";
}

// --- 2. HANDLE NEW PASSWORD SUBMISSION ---
if (isset($_POST['reset_password']) && empty($error)) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $current_token = $_POST['token'] ?? ''; 
    $reset_email = $_POST['email'] ?? ''; 

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Re-validate token and email one last time for security
        $validation_query = "SELECT email FROM password_resets WHERE token = ? AND email = ? AND expires_at > NOW()";
        $validation_stmt = $con->prepare($validation_query);
        $validation_stmt->bind_param("ss", $current_token, $reset_email);
        $validation_stmt->execute();
        $validation_result = $validation_stmt->get_result();
        $validation_stmt->close();

        if ($validation_result->num_rows > 0) {
            
            // a. Hash and update password in the users table
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET user_password = ? WHERE user_email = ?";
            $update_stmt = $con->prepare($update_query);
            $update_stmt->bind_param("ss", $hashed_password, $reset_email);
            
            if ($update_stmt->execute()) {
                // b. Delete the used token from password_resets table
                $delete_query = "DELETE FROM password_resets WHERE email = ?";
                $delete_stmt = $con->prepare($delete_query);
                $delete_stmt->bind_param("s", $reset_email);
                $delete_stmt->execute();
                $delete_stmt->close();

                Logger::log($con, 0, "PASSWORD_RESET_SUCCESS", "Password successfully reset for $reset_email.");

                $_SESSION['success'] = "Your password has been reset successfully. You can now log in.";
                header("Location: ../../../../public/index.php"); // Redirect to your login page
                exit;

            } else {
                $error = "Failed to update password. Database error.";
            }
            $update_stmt->close();
        } else {
            $error = "Security validation failed. Please restart the password reset process.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - MoviEase</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .reset-password-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; max-width: 400px; }
        h1 { color: #a31212; margin-bottom: 15px; }
        input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #a31212; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; margin-top: 10px; }
        button:hover { background-color: #870e0e; }
        a { color: #a31212; text-decoration: none; }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <h1>Reset Your Password</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error-message" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
            <p><a href="index.php">Back to Login</a></p>
        <?php elseif (!empty($user_email)): ?>
            <p>Setting new password for: <b><?php echo htmlspecialchars($user_email); ?></b></p>
            <form method="POST" action="">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($user_email); ?>">
                
                <input type="password" name="new_password" placeholder="New Password (min 8 characters)" required minlength="8">
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required minlength="8">
                <button type="submit" name="reset_password">Change Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>