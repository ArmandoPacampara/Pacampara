<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - MoviEase</title>
    <!-- Assuming you have a CSS file, link it here -->
    <link rel="stylesheet" href="../../../../public/styles/css/Login.css"> 
</head>
<body>
    <div class="container">
        <h2>Enter Verification Code</h2>
        
        <!-- FIXED: Use the correct session variable from Signup.php -->
        <p>A 6-digit code has been sent to <strong><?php echo htmlspecialchars($_SESSION['signup_data']['email'] ?? 'your email'); ?></strong>.</p>
        
        <?php if(isset($_SESSION['error'])): ?>
            <p style="color: red; text-align: center;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <!-- ACTION: Points to the model logic file -->
        <form method="POST" action="../../../../app/model/verify_otp_logic.php">
            <center>
                <div class="input-group">
                    <input type="text" placeholder="6-digit Code" required name="otp_code" maxlength="6" pattern="\d{6}" style="text-align: center; letter-spacing: 5px; font-size: 1.2rem;">
                </div>
                
                <button type="submit" class="login-btn" name="verify_otp">Verify Code</button>
            </center>
        </form>
    </div>
</body>
</html>