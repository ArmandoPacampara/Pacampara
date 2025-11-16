<?php
require_once '../app/model/Login.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoviEase Login</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #a31212;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 40px;
        }

        .poster-container {
            background-color: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            width: 380px;
            height: 520px;
            transition: transform 0.3s ease;
            margin-right: -260px;
            position: relative;
        }

        .poster-container:hover {
            transform: translateY(-8px);
        }

        .poster-container img {
            width: 100%;
            height: 100%;
            border-radius: 20px;
            object-fit: cover;
        }

        .container {
            position: unset;
            background-color: #fff;
            border-radius: 25px;
            width: 920px;
            height: 610px;
            padding: 80px;
            padding-top: 70px;
            padding-left: 290px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .logo img {
            width: 42px;
            margin-top: -10px;
            margin-right: 10px;
        }

        .logo h1 {
            color: #a31212;
            font-size: 38px;
            font-weight: 700;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-top: 12px;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .divider {
            display: flex;
            align-items: center;
            color: #999;
            margin: 20px 0;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #ccc;
        }

        .divider span {
            margin: 0 10px;
            font-size: 14px;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .email {
            position: absolute;
            top: 10px;
            left: 12px;
            width: 28px;
            height: 28px;
        }

        .pass {
            position: absolute;
            top: 7px;
            left: 10px;
            width: 28px;
            height: 28px;
        }

        form .input-group input[type="email"],
        form .input-group input[type="password"] {
            width: 80%;
            margin-top: 3px;
            padding: 12px 16px;
            border: 1px solid #a31212;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            color: #333 !important;
            background-color: #fff6f6;
            transition: all 0.3s ease;
        }

        .input-group .eye {
            position: absolute;
            top: 12px;
            right: 12px;
            color: #a31212;
            cursor: pointer;
        }

        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 15px;
            margin-bottom: 20px;
            padding-left: 55px;
            padding-right: 55px;
            margin-top: 12px;
        }

        .options label {
            color: #a31212;
            cursor: pointer;
        }

        .options a {
            color: #a31212;
            text-decoration: none;
        }

        .options a:hover {
            text-decoration: underline;
        }

        .login-btn {
            width: 80%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: #a31212;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .login-btn:hover {
            background-color: #870e0e;
        }

        .signup {
            text-align: center;
            margin-top: 20px;
            font-size: 15px;
        }

        .signup a {
            color: #a31212;
            text-decoration: none;
            font-weight: bold;
        }

        .signup a:hover {
            text-decoration: underline;
        }

        @media (max-width: 900px) {
            .wrapper {
                flex-direction: column;
                height: 90%;
                gap: 20px;
            }

            .poster-container {
                width: 70%;
                height: 300px;
            }

            .container {
                position: unset;
                width: 80%;
            }
        }

        input[type="checkbox"] {
            accent-color: #a31212;
            cursor: pointer;
            margin-top: -10px;
            margin-bottom: 10px;
            width: 20px;
            height: 12px;
        }
    </style>

    <div class="wrapper">
        <div class="poster-container">
            <img src="https://cdn.myanimelist.net/images/anime/1806/126216.jpg" alt="Chainsaw Man Poster">
        </div>

        <div class="container">
            <div class="logo">
                <img src="assets/images/movies_icon.png" alt="logo">
                <h1>MoviEase</h1>
            </div>

            <h2>Log In to your Account</h2>

            <div class="divider"></div>

            <form method="POST" action="">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message" style="color: red; text-align: center; margin-bottom: 10px;">
                        <?php echo $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <center>
                    <div class="input-group">
                        <img src="assets/images/mail_icon.png" class="email" alt="email">
                        <input type="email" placeholder="Email" required name="email">
                    </div>

                    <div class="input-group">
                        <img src="assets/images/lock_icon.png" class="pass" alt="password">
                        <input type="password" placeholder="Password" required name="password">
                    </div>

                    <div class="options">
                        <label><input type="checkbox" name="remember-me"> Remember me</label>
                        <a href="#" name="forgot-password"><b>Forgot Password?</b></a>
                    </div>

                    <div class="g-recaptcha" data-sitekey="6LcWAgwsAAAAALl4FSBG6_2tVBB8msJpmc88e8KR"></div>
                    <br>

                    <button type="submit" class="login-btn" name="login">LOGIN</button>

                    <div class="signup">
                        Don’t have an account? &nbsp;&nbsp;<a href="/register"> Create an account</a>
                    </div>
                </center>
            </form>

            <!-- reCAPTCHA script -->
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        </div>
    </div>
</body>

</html>