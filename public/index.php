<?php
require_once '../app/model/Login.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MoviEase Login</title>
  <link rel="stylesheet" href="../public/styles/css/Login.css">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

  <div class="wrapper">
    <div class="poster-container">
      <img src="https://cdn.myanimelist.net/images/anime/1806/126216.jpg" alt="Chainsaw Man Poster">
    </div>

    <div class="container">
      <div class="logo">
      <img src="../public/assets/images/movies_icon.png" alt="logo">
        <h1>MoviEase</h1>
      </div>

      <h2>Log In to your Account</h2>

      <div class="divider"></div>

<form method="POST" action="">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message" style="color: red; text-align: center; margin-bottom: 10px;">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <center>
      <div class="input-group">
        <img src="../public/assets/images/mail_icon.png" class="email" alt="email">
        <input type="email" placeholder="Email" required name="email">
      </div>

      <div class="input-group">
        <img src="../public/assets/images/lock_icon.png" class="pass" alt="password">
        <input type="password" placeholder="Password" required name="password">
      </div>

      <div class="options">
        <label><input type="checkbox" name="remember-me"> Remember me</label>
        <a href="#" name="forgot-password"><b>Forgot Password?</b></a>
      </div>

      <br>

      <button type="submit" class="login-btn" name="login">LOGIN</button>

      <div class="signup">
        Don’t have an account? &nbsp;&nbsp;<a href="../app/view/pages/Customer/Signup.php"> Create an account</a>
      </div>
    </center>
</form>


    </div>
  </div>




</body>
</html>