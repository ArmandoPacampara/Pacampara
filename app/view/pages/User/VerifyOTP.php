<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - MoviEase</title>
    <!-- Assuming you have a CSS file, link it here -->
    <link rel="stylesheet" href="../../../../public/styles/css/Login.css"> 
</head>
<style> 
    /* VerifyOTP.css */

/* General Reset & Body */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background-color: #f4f4f4; /* Light gray background */
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* Container Card */
.container {
    background-color: #ffffff;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    text-align: center;
}

/* Headings */
h2 {
    color: #333;
    margin-bottom: 10px;
    font-size: 24px;
    font-weight: 600;
}

p {
    color: #666;
    font-size: 14px;
    margin-bottom: 25px;
    line-height: 1.5;
}

strong {
    color: #d60000; /* Brand Red for Email highlight */
}

/* Input Fields */
.input-group {
    margin-bottom: 20px;
    position: relative;
}

.input-group input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 18px; /* Larger font for OTP */
    letter-spacing: 8px; /* Spacing between digits */
    text-align: center;
    transition: border-color 0.3s;
    outline: none;
}

.input-group input:focus {
    border-color: #d60000; /* Brand Red Focus */
    box-shadow: 0 0 5px rgba(214, 0, 0, 0.2);
}

.input-group input::placeholder {
    letter-spacing: 1px; /* Reset spacing for placeholder text */
    font-size: 14px;
    color: #aaa;
}

/* Buttons */
.login-btn {
    width: 100%;
    padding: 12px;
    background-color: #d60000; /* Brand Red */
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s;
}

.login-btn:hover {
    background-color: #b30000; /* Darker Red */
}

/* Error Messages */
.error-msg {
    color: #e74c3c;
    background-color: #fceceb;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #f5c6cb;
    font-size: 13px;
    margin-bottom: 20px;
}
</style>
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