<!DOCTYPE html>
<html>
<head>
    </head>
<body>
    <div class="container">
        <h2>Enter Verification Code</h2>
        <p>A 6-digit code has been sent to **<?php echo htmlspecialchars($_SESSION['pending_user_email'] ?? 'your email'); ?>**.</p>
        
        <form method="POST" action="../../model/verify_otp_logic.php">
            <center>
                <div class="input-group">
                    <input type="text" placeholder="6-digit Code" required name="otp_code" maxlength="6" pattern="\d{6}">
                </div>
                
                <button type="submit" class="login-btn" name="verify_otp">Verify Code</button>
            </center>
        </form>
    </div>
</body>
</html>