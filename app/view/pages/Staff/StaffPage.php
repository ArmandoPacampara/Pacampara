<?php
session_start();

// 1. SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header("Location: ../../../../public/index.php");
    exit;
}

require_once '../../../../app/core/db.php';

$staff_id = $_SESSION['user_id'];
$cinema_id = $_SESSION['cinema_id'] ?? 0;

// 2. FETCH CINEMA DETAILS
$cinema_name = "MoviEase"; // Default
if ($cinema_id > 0) {
    $c_stmt = $con->prepare("SELECT cinema_name FROM cinemas WHERE cinema_id = ?");
    $c_stmt->bind_param("i", $cinema_id);
    $c_stmt->execute();
    $res = $c_stmt->get_result()->fetch_assoc();
    if ($res) {
        $cinema_name = $res['cinema_name'];
    }
    $c_stmt->close();
}

// 3. FETCH USER AVATAR
$u_stmt = $con->prepare("SELECT user_avatar FROM users WHERE user_id = ?");
$u_stmt->bind_param("i", $staff_id);
$u_stmt->execute();
$u_res = $u_stmt->get_result()->fetch_assoc();
$avatar = !empty($u_res['user_avatar']) ? "../../../../public/assets/images/" . $u_res['user_avatar'] : "../../../../public/assets/images/account_icon.png";
$u_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Staff Dashboard - <?= htmlspecialchars($cinema_name) ?></title>
    <link rel="stylesheet" href="../../../../public/styles/css/StaffPage.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />
  </head>
  <body>
    <header class="navbar">
      <div class="navbar-left">
        <button id="toggleSidebar"><i class="fa-solid fa-bars"></i></button>
        <span class="navbar-title">MOVIEASE</span>
      </div>
      
      <div class="navbar-center" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
          <h2 style="margin: 0;">STAFF DASHBOARD</h2>
          <span style="font-size: 12px; font-weight: normal; letter-spacing: 1px; opacity: 0.9;">
              <?= htmlspecialchars($cinema_name) ?>
          </span>
      </div>

      <div class="navbar-right">
        <i class="fa-solid fa-gear" style="font-size: 25px;"></i>
        <a href="StaffAccount.php" target="content-frame">
            <img
              src="<?= htmlspecialchars($avatar) ?>"
              alt="Staff"
              class="profile-pic"
              style="cursor: pointer;"
            />
        </a>
      </div>
    </header>

    <div class="container">
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