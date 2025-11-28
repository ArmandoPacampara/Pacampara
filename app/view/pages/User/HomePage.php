<?php
// Correct path: HomePage.php → customer → pages → view → app → core
require_once __DIR__ . '/../../../core/session_check.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MoviEase</title>


  <!-- Correct path to CSS (3 levels up + styles/css/) -->
  <link rel="stylesheet" href="../../../../public/styles/css/HomepageStyle.css">
</head>


<body>


  <div class="nav-holder">
    <nav class="navbar">
      <div class="nav-left">
          <img src="../../../../public/assets/images/logo_icon.jpg" alt="Logo" class="logo">
        </a>
        <span class="site-name">MoviEase</span>
      </div>


      <div class="nav-links">
        <a href="CinemasPage.php" class="nav-link" target="main-frame">CINEMAS</a>
        <a href="MoviesPage.php" class="nav-link" target="main-frame">MOVIES</a>
        <a href="Schedulepage.php" class="nav-link" target="main-frame">SCHEDULE</a>
      </div>


      <div class="nav-right">
        <a href="AccountPage.php" class="nav-link" target="main-frame">
          <img src="../../../../public/assets/images/account_icon.png" alt="Account" class="account-icon">
        </a>
      </div>
    </nav>
  </div>


  <iframe name="main-frame" id="mainFrame" src="CinemasPage.php"></iframe>


  <script>
    // Auto logout after inactivity
    const INACTIVITY_TIMEOUT_SECONDS = 120;
    let timeoutTimer;


    function resetTimer() {
      clearTimeout(timeoutTimer);
      timeoutTimer = setTimeout(autoLogout, INACTIVITY_TIMEOUT_SECONDS * 1000);
    }


    function autoLogout() {
      // Correct path: HomePage.php → customer → pages → view → app → Pacampara/public
      window.location.href = '../../../../public/index.php';
    }


    // Events to detect user activity
    const activityEvents = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'];
    activityEvents.forEach(event => {
      document.addEventListener(event, resetTimer, true);
    });


    // Start the inactivity timer
    resetTimer();


        function loadCinema(cinema) {
        const iframe = document.getElementById("mainFrame");


        switch (cinema) {
            case "SM":
                iframe.src = "Cinema_S.html";
                break;
            case "Robinson":
                iframe.src = "Cinema_R.html";
                break;
            case "Ayala":
                iframe.src = "Cinema_A.html";
                break;
        }
    }


    function goBackToDashboard() {
        document.getElementById("mainFrame").src = "CinemasPage.php";
    }
  </script>


</body>
</html>





