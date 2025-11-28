<?php
session_start();
require_once '../../../app/core/db.php';

// --- 1. AUTHENTICATION CHECK ---
if (!isset($_SESSION['user_id'])) {
    header("Location: LoginPage.php"); // Redirect if not logged in
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// --- 2. HANDLE FORM SUBMISSION (UPDATE PROFILE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username']);
    $new_contact  = trim($_POST['phone']);
    // Note: Email, Age, Address, Genre are not in your users table schema provided earlier.
    // Assuming 'user_contact' maps to PHONE.
    // For Age, Address, Genre to be saved, you would need to add columns to your 'users' table.
    // I will update the available fields: user_name and user_contact.
    
    // Handle Profile Image Upload
    $avatar_sql = "";
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === 0) {
        $upload_dir = '../../../public/assets/uploads/'; // Ensure this directory exists
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_name = time() . '_' . $_FILES['profile_img']['name'];
        $upload_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $upload_file)) {
            $avatar_sql = ", user_avatar = '$file_name'";
        }
    }

    $update_stmt = $con->prepare("UPDATE users SET user_name = ?, user_contact = ? $avatar_sql WHERE user_id = ?");
    $update_stmt->bind_param("ssi", $new_username, $new_contact, $user_id);
    
    if ($update_stmt->execute()) {
        $message = "Profile updated successfully!";
        // Refresh session name if changed
        $_SESSION['user_name'] = $new_username;
    } else {
        $message = "Error updating profile.";
    }
    $update_stmt->close();
}

// --- 3. FETCH USER DETAILS ---
$user_stmt = $con->prepare("
    SELECT u.user_name, u.user_email, u.user_contact, u.user_avatar, r.user_role 
    FROM users u 
    JOIN roles r ON u.role_id = r.role_id 
    WHERE u.user_id = ?
");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// Set defaults if null
$username = htmlspecialchars($user['user_name']);
$email    = htmlspecialchars($user['user_email']);
$phone    = htmlspecialchars($user['user_contact'] ?? '');
$role     = htmlspecialchars($user['user_role']);
$avatar   = !empty($user['user_avatar']) ? "/public/assets/uploads/" . htmlspecialchars($user['user_avatar']) : "/public/assets/account_icon.png";

// Note: Your DB schema does not have Age, Address, or Genre columns in 'users'.
// I will use placeholders or you can add these columns to your DB.
$age     = "N/A"; // Placeholder
$address = "N/A"; // Placeholder
$genre   = "N/A"; // Placeholder

// --- 4. FETCH BOOKING RECORDS ---
$booking_stmt = $con->prepare("
    SELECT b.ticket_id, b.schedule, b.status, m.movie_name 
    FROM booking b
    JOIN movies m ON b.movie_id = m.movie_id
    WHERE b.user_id = ?
    ORDER BY b.date_booked DESC
");
$booking_stmt->bind_param("i", $user_id);
$booking_stmt->execute();
$bookings_result = $booking_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Account - MoviEase</title>
  <link rel="stylesheet" href="../../../public/styles/css/AccountPage.css" />
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
              <img src="../../../public/assets/edit_btn.png" id="editIcon" alt="edit-icon" />
            </button>

            <div class="profile-img-wrapper">
              <img src="<?= $avatar ?>" class="profile-img" id="profileImg" alt="Profile Image" />
              <input type="file" name="profile_img" id="profileInput" accept="image/*" style="display: none" />
            </div>

            <h1 class="username">
              <span id="usernameDisplay"><?= $username ?></span>
              <input type="text" name="username" class="username-input" id="usernameInput" value="<?= $username ?>" style="display: none;" />
            </h1>
            <h2 class="user-role"><?= $role ?></h2>
          </div>

          <div class="user-details">
            <div class="detail-row">
              <label class="label">EMAIL</label>
              <div>
                <input type="text" class="field-input" value="<?= $email ?>" disabled style="background:#eee; cursor:not-allowed;" title="Email cannot be changed" />
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
                <input type="text" class="field-input" id="age" value="<?= $age ?>" disabled />
                <p class="error-msg"></p>
              </div>
            </div>

            <div class="detail-row">
              <label class="label">ADDRESS</label>
              <div>
                <input type="text" class="field-input" id="address" value="<?= $address ?>" disabled />
                <p class="error-msg"></p>
              </div>
            </div>

            <div class="detail-row">
              <label class="label">GENRE</label>
              <div>
                <input type="text" class="field-input" id="genre" value="<?= $genre ?>" disabled />
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
          <div class="record-header">All Bookings</div>
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
                  <?php while($row = $bookings_result->fetch_assoc()): ?>
                  <tr>
                    <td><?= date("M d, Y - g:i A", strtotime($row['schedule'])) ?></td>
                    <td><?= htmlspecialchars($row['movie_name']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><button class="view-btn">View</button></td>
                  </tr>
                  <?php endwhile; ?>
              <?php else: ?>
                  <tr>
                      <td colspan="4" style="text-align:center;">No booking history found.</td>
                  </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
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
      // Select only inputs inside the user-details that correspond to DB fields we allow editing
      // Currently only 'phone' and 'username' are editable in this PHP logic
      const phoneInput = document.getElementById("phone"); 
      const profileImg = document.getElementById("profileImg");
      const profileInput = document.getElementById("profileInput");
      const usernameDisplay = document.getElementById("usernameDisplay");
      const usernameInput = document.getElementById("usernameInput");
      const form = document.getElementById("profileForm");

      let editing = false;
      let tempProfileSrc = profileImg.src;

      editBtn.addEventListener("click", () => {
        editing = !editing;

        if (editing) {
          // Enable Phone Input
          phoneInput.disabled = false;
          phoneInput.classList.add("editing");
          
          // Toggle Username
          usernameDisplay.style.display = "none";
          usernameInput.style.display = "inline-block";
          usernameInput.classList.add("editing");
          
          // Change Icon
          editIcon.src = "../../../public/assets/save_btn.png";
        } else {
          // VALIDATION BEFORE SAVING
          if (!validateFields()) {
            editing = true; // Stay in edit mode if invalid
            return;
          }

          // Show Confirmation Popup
          document.getElementById("savePopup").style.display = "flex";
        }
      });

      // Handle Popup Confirmation
      document.getElementById("confirmSave").onclick = () => {
          form.submit(); // Submit the form to PHP
      };

      document.getElementById("cancelSave").onclick = () => {
          editing = true; // Revert state variable
          document.getElementById("savePopup").style.display = "none";
          // We stay in edit mode so user can correct or continue
      };

      function validateFields() {
        let isValid = true;
        document.querySelectorAll(".error-msg").forEach((e) => (e.textContent = ""));
        phoneInput.classList.remove("input-error");
        usernameInput.classList.remove("input-error");

        // Validate Username
        if (usernameInput.value.trim().length < 2) {
          alert("Username must be at least 2 characters long.");
          isValid = false;
        }

        // Validate Phone (Basic Check)
        const phoneVal = phoneInput.value.trim();
        // Allow empty or strictly 11 digits starting with 09
        if (phoneVal !== "" && !/^09\d{9}$/.test(phoneVal)) {
             phoneInput.classList.add("input-error");
             phoneInput.nextElementSibling.textContent = "Invalid PH mobile number (e.g. 09123456789).";
             isValid = false;
        }

        return isValid;
      }

      // Profile Image Click
      profileImg.addEventListener("click", () => {
        if (editing) profileInput.click();
      });

      // Profile Image Preview
      profileInput.addEventListener("change", () => {
        const file = profileInput.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            profileImg.src = e.target.result;
          };
          reader.readAsDataURL(file);
        }
      });
    });
  </script>
</body>
</html>