<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MoviEase Staff Dashboard</title>
    <link rel="stylesheet" href="../../../../public/styles/css/StaffPage.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />
  </head>
  <body>
    <!-- ===== TOP NAVBAR ===== -->
    <header class="navbar">
      <div class="navbar-left">
        <button id="toggleSidebar"><i class="fa-solid fa-bars"></i></button>
        <span class="navbar-title">MOVIEASE</span>
      </div>
      <h2 class="navbar-center">STAFF DASHBOARD</h2>
      <div class="navbar-right">
        <i class="fa-solid fa-gear" style="font-size: 25px;"></i>
<a href="StaffAccount.php" target="content-frame">
    <img
      src="../../../../public/assets/images/account_icon.png"
      alt="Staff"
      class="profile-pic"
      style="cursor: pointer;"
    />
  </a>
      </div>
    </header>


    <div class="container">
      <!-- ===== SIDEBAR ===== -->
      <aside class="sidebar" id="sidebar">
        <nav class="menu">
          <a href="StaffReports.php" target="content-frame" class="active">
            <i class="fa-solid fa-chart-line"></i> <span>REPORTS</span>
          </a>
          <a href="StaffMoviesPage.php" target="content-frame">
            <i class="fa-solid fa-film"></i> <span>MOVIES</span>
          </a>
          <a href="StaffNotificationPage.php" target="content-frame">
            <i class="fa-solid fa-bell"></i> <span>NOTIFICATIONS</span>
          </a>
        </nav>
      </aside>


      <!-- ===== MAIN CONTENT (IFRAME) ===== -->
      <main class="main-content">
        <iframe
          id="staffIframe"
          name="content-frame"
          src="StaffReports.php"
          frameborder="0"
          class="content-frame"
        ></iframe>
      </main>
    </div>


    <script>
      const toggleButton = document.getElementById("toggleSidebar");
      const sidebar = document.getElementById("sidebar");
      const menuItems = sidebar.querySelectorAll(".menu a");


      // Toggle sidebar collapse
      toggleButton.addEventListener("click", () => {
        sidebar.classList.toggle("collapsed");
      });


      // Active menu item tracking
      menuItems.forEach((menuItem) => {
        menuItem.addEventListener("click", () => {
          // Remove active class from all items
          menuItems.forEach((item) => item.classList.remove("active"));
         
          // Add active class to clicked item
          menuItem.classList.add("active");
        });
      });
    </script>
  </body>
</html>

