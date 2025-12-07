<?php 
// moviease/app/view/pages/User/ForgotPassword.php
session_start();
// The model logic handles the POST request and redirection
require_once '../../../../app/model/PasswordReset.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - MoviEase</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .forgot-password-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; max-width: 400px; }
        h1 { color: #a31212; margin-bottom: 15px; }
        input[type="email"] { width: 100%; padding: 10px; margin: 10px 0 20px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #a31212; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background-color: #870e0e; }
        a { color: #a31212; text-decoration: none; }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <h1>Forgot Your Password?</h1>
        <p>Enter your email address below and we'll send you a link to reset your password.</p>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message" style="color: red; margin-bottom: 15px;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message" style="color: green; margin-bottom: 15px;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit" name="forgot_password">Send Reset Link</button>
        </form>
        
        <p style="margin-top: 20px;"><a href="../../../../public/index.php">Back to Login</a></p>
    </div>
</body>
</html>