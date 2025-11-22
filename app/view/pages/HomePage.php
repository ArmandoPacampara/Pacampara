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

</head>
<body>

  <div class="nav-holder">
    <nav class="navbar">
      <div class="nav-left">
        <img src="../../../public/assets/images/logo_icon.jpg" alt="Logo" class="logo">
        <span class="site-name">MoviEase</span>
      </div>

      <div class="nav-links">
        <!-- We use data-page attributes to identify which page to load -->
        <a href="MoviesPage.php" class="nav-link" target="main-frame">MOVIES</a>
        <a href="SchedulePage.php" class="nav-link" target="main-frame">SCHEDULE</a>
        <a href="CinemasPage.php" class="nav-link" target="main-frame">CINEMAS</a>
      </div>

      <div class="nav-right">
        <img src="../../../public/assets/images/account_icon.png" alt="Account" class="account-icon">
      </div>
    </nav>
  </div>

  <iframe name="main-frame" id="mainFrame" src="CinemasPage.php"></iframe>

  <script>
    // const navLinks = document.querySelectorAll(".nav-link");
    // const iframe = document.getElementById("mainFrame");

    // navLinks.forEach(link => {
    //   link.addEventListener("click", (e) => {
    //     e.preventDefault();
    //     const page = link.getAttribute("data-page");
    //     iframe.src = page;
    //   });
    // });

    // Match the server-side timeout (120 seconds)
    const INACTIVITY_TIMEOUT_SECONDS = 120; 
    let timeoutTimer;

    function resetTimer() {
        // Clear any existing timer
        clearTimeout(timeoutTimer);

        // Set a new timer
        timeoutTimer = setTimeout(autoLogout, INACTIVITY_TIMEOUT_SECONDS * 1000);
    }

    function autoLogout() {
        // Log the user out by redirecting to a dedicated logout endpoint
        // You should create a simple logout.php file that destroys the session.
        window.location.href = '/moviease/logout.php?reason=timeout'; 
    }

    // --- Event Listeners to Detect User Activity ---

    // List of events to monitor
    const activityEvents = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'];

    activityEvents.forEach(event => {
        document.addEventListener(event, resetTimer, true);
    });

    // Start the timer when the page loads
    resetTimer();
  </script>

</body>
</html>
