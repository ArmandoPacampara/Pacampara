<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MoviEase</title>
  <link rel="stylesheet" href="../../../public/styles/css/HomepageStyle.css">
  <style>
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
    }

    body {
      display: flex;
      flex-direction: column;
    }

    .nav-holder {
      flex-shrink: 0;
    }

    iframe {
      flex-grow: 1;
      border: none;
      width: 100%;
      height: 100%;
    }

    .navbar a {
      text-decoration: none;
      color: white;
      padding: 10px 15px;
      font-weight: 600;
    }

    .navbar a:hover {
      color: #f87171; /* hover effect */
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
  </script>

</body>
</html>
