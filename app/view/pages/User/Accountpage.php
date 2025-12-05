<?php
  session_start();
  require_once '../../../../app/core/db.php';

  // --- 1. AUTHENTICATION CHECK ---
  if (!isset($_SESSION['user_id'])) {
      header("Location: LoginPage.php"); 
      exit;
  }

if (isset($_GET['logout'])) {
      session_destroy();
      // JavaScript to break out of iframe and redirect the top window
      echo "<script>window.top.location.href = '../../../../public/index.php';</script>";
      exit;
  }

  $user_id = $_SESSION['user_id'];
  $message = "";

  // --- 2. HANDLE FORM SUBMISSION ---
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
      $new_username = trim($_POST['username']);
      $new_contact  = trim($_POST['phone']);
      
      // CAPTURE GENRES (Array -> String)
      $new_genre_string = "";
      if (isset($_POST['genre']) && is_array($_POST['genre'])) {
          $new_genre_string = implode(', ', $_POST['genre']); 
      }
      
      // Handle Profile Image
      $avatar_sql = "";
      if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === 0) {
          $upload_dir = '../../../../public/assets/images/'; 
          if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
          
          $file_name = time() . '_' . $_FILES['profile_img']['name'];
          $upload_file = $upload_dir . $file_name;
          
          if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $upload_file)) {
              $avatar_sql = ", user_avatar = '$file_name'";
          }
      }

      $update_stmt = $con->prepare("
          UPDATE users 
          SET user_name = ?, user_contact = ?, user_genre = ? $avatar_sql 
          WHERE user_id = ?
      ");
      $update_stmt->bind_param("sssi", $new_username, $new_contact, $new_genre_string, $user_id);
      
      if ($update_stmt->execute()) {
          $message = "Profile updated successfully!";
          $_SESSION['user_name'] = $new_username;
      } else {
          $message = "Error updating profile.";
      }
      $update_stmt->close();
  }

  // --- 3. FETCH USER DETAILS ---
  $user_stmt = $con->prepare("
      SELECT u.user_name, u.user_email, u.user_contact, u.user_avatar, u.user_genre, r.user_role 
      FROM users u 
      JOIN roles r ON u.role_id = r.role_id 
      WHERE u.user_id = ?
  ");
  $user_stmt->bind_param("i", $user_id);
  $user_stmt->execute();
  $user = $user_stmt->get_result()->fetch_assoc();
  $user_stmt->close();

  $username = htmlspecialchars($user['user_name']);
  $email    = htmlspecialchars($user['user_email']);
  $phone    = htmlspecialchars($user['user_contact'] ?? '');
  $role     = htmlspecialchars($user['user_role']);
  $avatar   = !empty($user['user_avatar']) ? "/public/assets/uploads/" . htmlspecialchars($user['user_avatar']) : "/public/assets/account_icon.png";

  // Genre Logic
  $db_genre_string = $user['user_genre'] ?? '';
  $user_genres = array_map('trim', explode(',', $db_genre_string)); 

  $all_genres = ['Action', 'Comedy', 'Drama', 'Horror', 'Romance', 'Sci-Fi', 'Thriller', 'Animation'];

  $age     = "N/A"; 
  $address = "N/A"; 

  // --- 4. PAGINATION LOGIC (Bookings) ---
  $records_per_page = 5; 
  $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
  if ($page < 1) $page = 1; 
  $offset = ($page - 1) * $records_per_page;

  $count_stmt = $con->prepare("SELECT COUNT(*) as total FROM booking WHERE user_id = ?");
  $count_stmt->bind_param("i", $user_id);
  $count_stmt->execute();
  $total_records = $count_stmt->get_result()->fetch_assoc()['total'];
  $total_pages = ceil($total_records / $records_per_page);
  $count_stmt->close();

  $booking_stmt = $con->prepare("
      SELECT b.ticket_id, b.schedule, b.status, m.movie_name 
      FROM booking b
      JOIN movies m ON b.movie_id = m.movie_id
      WHERE b.user_id = ?
      ORDER BY b.date_booked DESC
      LIMIT ? OFFSET ?
  ");
  $booking_stmt->bind_param("iii", $user_id, $records_per_page, $offset);
  $booking_stmt->execute();
  $bookings_result = $booking_stmt->get_result();
  ?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Account - MoviEase</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../../../../public/styles/css/AccountPage.css" />
    <style>
        .pagination { display: flex; justify-content: center; margin-top: 20px; gap: 10px; }
        .pagination a { text-decoration: none; padding: 8px 12px; border: 1px solid #ddd; color: #333; border-radius: 4px; transition: background-color 0.3s; }
        .pagination a:hover { background-color: #f0f0f0; }
        .pagination a.active { background-color: #d60000; color: white; border-color: #d60000; }
        .pagination a.disabled { pointer-events: none; color: #ccc; border-color: #eee; }

        /* CHECKBOX STYLES */
        .genre-checkboxes {
            display: none;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 5px;
            background: #fff;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .genre-checkboxes label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            cursor: pointer;
            width: 45%; 
        }
        .genre-checkboxes input[type="checkbox"] {
            accent-color: #d60000;
            transform: scale(1.1);
        }

        /* ACTION BUTTON STYLES */
        .view-ticket-btn, .continue-btn, .detail-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-size: 13px;
            text-decoration: none;
        }
        .view-ticket-btn { background-color: #28a745; } 
        .view-ticket-btn:hover { background-color: #218838; }

        .continue-btn { background-color: #ffc107; color: #333; } 
        .continue-btn:hover { background-color: #e0a800; }

        .detail-btn { background-color: #6c757d; } 
        .detail-btn:hover { background-color: #5a6268; }

        /* NEW: LOGOUT BUTTON STYLES */
        .logout-btn {
            margin-top: 20px;
            padding: 10px 25px;
            background-color: white;
            color: #d60000;
            border: 2px solid #d60000;
            border-radius: 25px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        .logout-btn:hover {
            background-color: #d60000;
            color: white;
            box-shadow: 0 4px 10px rgba(214, 0, 0, 0.2);
        }

        body {
              margin: 50px;
              padding: 0;
              font-family: Arial, sans-serif;
              background-color: #fdecec;
              height: 59.92vh;
          }
    </style>
  </head>

  <body>
    <div class="page-wrapper">
      
      <?php if ($message): ?>
          <div style="position: fixed; top: 20px; right: 20px; background: #4caf50; color: white; padding: 15px; border-radius: 5px; z-index: 1000;">
              <?= $message ?>
          </div>
      <?php endif; ?>

      <form id="profileForm" method="POST" enctype="multipart/form-data" action="AccountPage.php">
          <input type="hidden" name="update_profile" value="1">

          <div class="account-container">
            <div class="profile-section">
              <button type="button" class="edit-btn" id="editBtn">
                <img src="../../../../public/assets/edit_btn.png" id="editIcon" alt="edit-icon" />
              </button>

              <div class="profile-img-wrapper">
                <img src="../../../../public/assets/profile_img.png" class="profile-img" id="profileImg" alt="Profile Image" />
                <input type="file" name="profile_img" id="profileInput" accept="image/*" style="display: none" />
              </div>

              <h1 class="username">
                <span id="usernameDisplay"><?= $username ?></span>
                <input type="text" name="username" class="username-input" id="usernameInput" value="<?= $username ?>" style="display: none;" />
              </h1>
              <h2 class="user-role"><?= $role ?></h2>

              <a href="?logout=1" class="logout-btn">
                  <i class="fas fa-sign-out-alt"></i> Logout
              </a>

            </div>

            <div class="user-details">
              <div class="detail-row">
                <label class="label">EMAIL</label>
                <div>
                  <input type="text" class="field-input" value="<?= $email ?>" disabled style="background:#eee; cursor:not-allowed;" />
                </div>
              </div>

              <div class="detail-row">
                <label class="label">PHONE</label>
                <div>
                  <input type="text" name="phone" class="field-input" id="phone" value="<?= $phone ?>" disabled />
                  <p class="error-msg"></p>
                </div>
              </div>

              <div class="detail-row">
                <label class="label">AGE</label>
                <div>
                  <input type="text" class="field-input" id="age" value="<?= $age ?>" disabled style="background:#eee;" />
                </div>
              </div>

              <div class="detail-row">
                <label class="label">ADDRESS</label>
                <div>
                  <input type="text" class="field-input" id="address" value="<?= $address ?>" disabled style="background:#eee;" />
                </div>
              </div>

              <div class="detail-row">
                <label class="label">GENRE</label>
                <div>
                  <input type="text" class="field-input" id="genreDisplayInput" value="<?= htmlspecialchars($db_genre_string) ?>" disabled />
                  
                  <div class="genre-checkboxes" id="genreCheckboxes">
                      <?php foreach ($all_genres as $g): ?>
                          <label>
                              <input type="checkbox" name="genre[]" value="<?= $g ?>" 
                                  <?= in_array($g, $user_genres) ? 'checked' : '' ?>>
                              <?= $g ?>
                          </label>
                      <?php endforeach; ?>
                  </div>
                  <p class="error-msg"></p>
                </div>
              </div>
            </div>
          </div>
      </form>

      <div class="records-container">
        <h1>BOOKING RECORDS</h1>
        <div class="bookings">
          <div class="table-container">
            <div class="record-header">All Bookings (Page <?= $page ?> of <?= max(1, $total_pages) ?>)</div>
            <table>
              <thead>
                <tr>
                  <th>Schedule</th>
                  <th>Movie Name</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="bookingsBody">
                <?php if ($bookings_result->num_rows > 0): ?>
                    <?php while($row = $bookings_result->fetch_assoc()): 
                          $status = htmlspecialchars($row['status']);
                          $ticket_id = htmlspecialchars($row['ticket_id']);
                          
                          $action_link = '#';
                          $button_text = 'Details';
                          $button_class = 'detail-btn';

                          if ($status == 'Completed' || $status == 'Booked') {
                              $action_link = "ReceiptPage.php?ticket_id=" . $ticket_id;
                              $button_text = "View Ticket";
                              $button_class = "view-ticket-btn";
                          } elseif ($status == 'Pending') {
                              $action_link = "Checkout.php?ticket_id=" . $ticket_id . "&resume=1";
                              $button_text = "Continue Payment";
                              $button_class = "continue-btn";
                          }
                    ?>
                    <tr>
                      <td><?= date("M d, Y - g:i A", strtotime($row['schedule'])) ?></td>
                      <td><?= htmlspecialchars($row['movie_name']) ?></td>
                      <td><?= $status ?></td>
                      <td>
                          <a href="<?= $action_link ?>">
                              <button class="<?= $button_class ?>"><?= $button_text ?></button>
                          </a>
                      </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center;">No booking history found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <?php if ($total_pages > 1): ?>
          <div class="pagination">
              <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>">Previous</a><?php else: ?><a href="#" class="disabled">Previous</a><?php endif; ?>
              <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                  <a href="?page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
              <?php endfor; ?>
              <?php if ($page < $total_pages): ?><a href="?page=<?= $page + 1 ?>">Next</a><?php else: ?><a href="#" class="disabled">Next</a><?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div id="savePopup" class="popup-overlay" style="display:none;">
      <div class="popup-box">
        <h2>Save Changes?</h2>
        <p>Are you sure you want to save the updated information?</p>
        <div class="popup-buttons">
          <button id="cancelSave" class="cancel-btn">Cancel</button>
          <button id="confirmSave" class="confirm-btn">Save</button>
        </div>
      </div>
    </div>

    <script>
      document.addEventListener("DOMContentLoaded", () => {
        const editBtn = document.getElementById("editBtn");
        const editIcon = document.getElementById("editIcon");
        const phoneInput = document.getElementById("phone");
        
        // Genre Elements
        const genreDisplay = document.getElementById("genreDisplayInput");
        const genreCheckboxes = document.getElementById("genreCheckboxes");

        const profileImg = document.getElementById("profileImg");
        const profileInput = document.getElementById("profileInput");
        const usernameDisplay = document.getElementById("usernameDisplay");
        const usernameInput = document.getElementById("usernameInput");
        const form = document.getElementById("profileForm");

        let editing = false;

        editBtn.addEventListener("click", () => {
          editing = !editing;

          if (editing) {
            // --- ENTER EDIT MODE ---
            
            // Enable Inputs
            phoneInput.disabled = false;
            phoneInput.classList.add("editing");
            
            // Toggle Genre: Hide text input, Show checkboxes
            genreDisplay.style.display = "none";
            genreCheckboxes.style.display = "flex";

            // Username Toggle
            usernameDisplay.style.display = "none";
            usernameInput.style.display = "inline-block";
            usernameInput.classList.add("editing");
            
            editIcon.src = "../../../../public/assets/save_btn.png";
          } else {
            // --- CLICKED SAVE (Validation) ---
            if (!validateFields()) {
              editing = true;
              return;
            }
            document.getElementById("savePopup").style.display = "flex";
          }
        });

        document.getElementById("confirmSave").onclick = () => form.submit();
        document.getElementById("cancelSave").onclick = () => {
            editing = true;
            document.getElementById("savePopup").style.display = "none";
        };

        function validateFields() {
          let isValid = true;
          document.querySelectorAll(".error-msg").forEach((e) => (e.textContent = ""));
          phoneInput.classList.remove("input-error");

          if (usernameInput.value.trim().length < 2) {
            alert("Username must be at least 2 characters long.");
            isValid = false;
          }

          const phoneVal = phoneInput.value.trim();
          if (phoneVal !== "" && !/^09\d{9}$/.test(phoneVal)) {
              phoneInput.classList.add("input-error");
              phoneInput.nextElementSibling.textContent = "Invalid PH mobile number (e.g. 09123456789).";
              isValid = false;
          }

          return isValid;
        }

        profileImg.addEventListener("click", () => { if (editing) profileInput.click(); });
        profileInput.addEventListener("change", () => {
          const file = profileInput.files[0];
          if (file) {
            const reader = new FileReader();
            reader.onload = (e) => { profileImg.src = e.target.result; };
            reader.readAsDataURL(file);
          }
        });
      });
    </script>
  </body>
  </html>