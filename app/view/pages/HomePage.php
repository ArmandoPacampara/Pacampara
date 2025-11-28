<?php
require_once __DIR__ . '/../../core/session_check.php';
// ... rest of your page content
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MoviEase</title>
  <link rel="stylesheet" href="../../../public/styles/css/HomepageStyle.css">
  <style>
      /* Optional: Add a hover effect to show it's clickable */
      .account-icon {
          cursor: pointer;
          transition: transform 0.2s;
      }
      .account-icon:hover {
          transform: scale(1.1);
      }
  </style>
</head>
<body>

  <div class="nav-holder">
    <nav class="navbar">
      <div class="nav-left">
        <img src="../../../public/assets/images/logo_icon.jpg" alt="Logo" class="logo">
        <span class="site-name">MoviEase</span>
      </div>

      <div class="nav-links">
        <a href="MoviesPage.php" class="nav-link" target="main-frame">MOVIES</a>
        <a href="SchedulePage.php" class="nav-link" target="main-frame">SCHEDULE</a>
        <a href="CinemasPage.php" class="nav-link" target="main-frame">CINEMAS</a>
      </div>

      <div class="nav-right">
        <a href="AccountPage.php" target="main-frame">
            <img src="../../../public/assets/images/account_icon.png" alt="Account" class="account-icon">
        </a>
      </div>
    </nav>
  </div>

  <iframe name="main-frame" id="mainFrame" src="CinemasPage.php"></iframe>

  <script>
    // Match the server-side timeout (120 seconds)
    const INACTIVITY_TIMEOUT_SECONDS = 120; 
    let timeoutTimer;

    function resetTimer() {
        clearTimeout(timeoutTimer);
        timeoutTimer = setTimeout(autoLogout, INACTIVITY_TIMEOUT_SECONDS * 1000);
    }

    function autoLogout() {
        // Ensure this path is correct for your logout script
        window.location.href = '../../../public/index.php'; 
    }

    // --- Event Listeners to Detect User Activity ---
    const activityEvents = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'];

    activityEvents.forEach(event => {
        document.addEventListener(event, resetTimer, true);
    });

    // Start the timer when the page loads
    resetTimer();
  </script>

</body>
</html>