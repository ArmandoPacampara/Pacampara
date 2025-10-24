<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MoviEase Sign Up</title>
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
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
      width: 380px;
      height: 520px;
      margin-right: -260px;
      position: relative;
      transition: transform 0.3s ease;
    }

    .poster-container:hover {
      transform: translateY(-8px);
    }

    .poster-container img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 20px;
    }

    .container {
      position: unset;
      background-color: #fff;
      border-radius: 25px;
      height: 609px;
      width: 920px;
      padding: 80px;
      padding-left: 280px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    }

    .header {
      display: flex;
      align-items: center;
      margin-bottom: 30px;
    }

    .header i {
      font-size: 22px;
      color: #a31212;
      cursor: pointer;
      margin-right: 10px;
    }

    .header h1 {
      color: #a31212;
      font-size: 26px;
      font-weight: 700;
      display: flex;
      align-items: center;
    }

    .header h1 i {
      font-size: 28px;
      margin-right: 8px;
    }

    .input-group {
      position: relative;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
    }

    .input-group input, 
    .input-group select {
      width: 80%;
      padding: 12px 12px 12px 44px; 
      border: 1px solid #a31212;
      border-radius: 8px;
      outline: none;
      font-size: 14px;
      transition: border 0.3s;
    }

    .input-group input:focus, 
    .input-group select:focus {
      border-color: #870e0e;
    }

    .input-icon {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      width: 20px;
      height: 20px;
      pointer-events: none;
    }

    .signup-btn {
      width: 120px;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background-color: #a31212;
      color: #fff;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
      display: block;
      margin: 20px auto 0;
    }

    .signup-btn:hover {
      background-color: #870e0e;

    }

    .dots {
      display: flex;
      justify-content: center;
      margin-top: 10px;
      color: #a31212;
    }

    .dots span {
      height: 8px;
      width: 8px;
      background-color: #a31212;
      border-radius: 50%;
      display: inline-block;
      margin: 0 4px;
    }

    .dots span:nth-child(1) {
      opacity: 0.6;
    }

    @media (max-width: 900px) {
      .wrapper {
        flex-direction: column;
        gap: 20px;
      }

      .poster-container {
        width: 70%;
        height: 300px;
      }

      .container {
        width: 80%;
        padding-left: 40px;
      }
    }

        .logo {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 10px;
    }
        .logo h1 {
      color: #a31212;
      font-size: 26px;
      font-weight: 700;
    }

    .icon {
      width: 40px;
    }

        .logo img {
      width: 40px;
      margin-right: 10px;
    }

        .input-icon {
      position: absolute;
      left: 12px;
      width: 20px;
      height: 20px;
      pointer-events: none;
    }
    .container2 {
      display: flex;
      flex-direction: column;
      padding-top: 30px;
      padding-left: 100px;
    }
    .button-container {
      display: flex;
      justify-content: right;
      margin-top: 10px;
      padding-left: 260px;
    }

  </style>

  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

  <div class="wrapper">
    <div class="poster-container">
      <img src="https://cdn.myanimelist.net/images/anime/1806/126216.jpg" alt="Chainsaw Man Poster">
    </div>

    <div class="container">
      <div class="logo">
        <img src="../assets/MoviEase_logo (2).png" alt="logo">
        <h1>MoviEase</h1>
      </div>

      <form>
        <center>
            <div class="container2">
          <div class="input-group">
            <img src="../assets/profile_icon.png" alt="profile_icon" class="input-icon">
            <select name="genre" required>
              <option value="" disabled selected>Choose a Genre</option>
              <option value="Action">Action</option>
              <option value="Comedy">Comedy</option>
              <option value="Horror">Horror</option>
              <option value="Romance">Romance</option>
              <option value="Sci-Fi">Sci-Fi</option>
              <option value="Thriller">Thriller</option>
              <option value="Drama">Drama</option>
            </select>
          </div>

          <div class="input-group">
            <img src="../assets/profile_icon.png" alt="profile_icon" class="input-icon">
            <input type="email" placeholder="Email" required name="email">
          </div>

          <div class="input-group">
            <img src="../assets/lock_icon.png" alt="lock_icon" class="input-icon">
            <input type="password" placeholder="Password" required name="password">
          </div>

          <div class="input-group">
            <img src="../assets/lock_icon.png" alt="lock_icon" class="input-icon">
            <input type="password" placeholder="Confirm Password" required name="confirm_password">
          </div>

        </div>

          <div class="dots">
            <span></span>
            <span></span>
          </div>
          <div class="button-container">
          <button type="submit" class="signup-btn">Sign Up</button>
          </div>

        </center>
      </form>
    </div>
  </div>

</body>
</html>
