<?php
session_start();
require_once '../../../../app/core/db.php';

// --- 1. AUTH CHECK ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') {
    // Break out of iframe if session expired
    echo "<script>window.top.location.href = '../../../../public/index.php';</script>";
    exit;
}

$staff_id = $_SESSION['user_id'];
$message = "";

// --- 2. LOGOUT LOGIC (Breaks Iframe) ---
if (isset($_GET['logout'])) {
    session_destroy();
    echo "<script>window.top.location.href = '../../../../public/index.php';</script>";
    exit;
}

// --- 3. HANDLE UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
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

    // Basic Validation
    if (empty($username) || empty($email)) {
        $message = "Username and Email are required.";
    } else {
        if (!empty($password)) {
            // Update with password change
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $con->prepare("UPDATE users SET user_name=?, user_email=?, user_password=? $avatar_sql WHERE user_id=?");
            $stmt->bind_param("sssi", $username, $email, $hashed, $staff_id);
        } else {
            // Update info only
            $stmt = $con->prepare("UPDATE users SET user_name=?, user_email=? $avatar_sql WHERE user_id=?");
            $stmt->bind_param("ssi", $username, $email, $staff_id);
        }
        
        if ($stmt->execute()) {
            $message = "Profile updated successfully.";
            $_SESSION['user_name'] = $username;
        } else {
            $message = "Error updating profile. Email might be taken.";
        }
        $stmt->close();
    }
}

// --- 4. FETCH DETAILS ---
$stmt = $con->prepare("SELECT user_name, user_email, user_avatar, role_id FROM users WHERE user_id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$staff = $stmt->get_result()->fetch_assoc();
$stmt->close();

$avatar = !empty($staff['user_avatar']) ? "../../../../public/assets/images/" . $staff['user_avatar'] : "../../../../public/assets/images/account_icon.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Account</title>
    <link rel="stylesheet" href="../../../../public/styles/css/AccountPage.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Staff Specific Overrides */
        body {
            background-color: #f9f9f9; /* Light background for iframe content */
            margin: 20px;
            font-family: Arial, sans-serif;
        }
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
        .account-container {
            max-width: 900px;
            margin: 0 auto;
        }
    </style>
</head>
<body>

    <div class="page-wrapper">
      
      <?php if ($message): ?>
          <div style="position: fixed; top: 20px; right: 20px; background: #4caf50; color: white; padding: 15px; border-radius: 5px; z-index: 1000;">
              <?= htmlspecialchars($message) ?>
          </div>
      <?php endif; ?>

      <form id="profileForm" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="update_profile" value="1">

          <div class="account-container">
            <div class="profile-section">
              <button type="button" class="edit-btn" id="editBtn">
                <img src="../../../../public/assets/edit_btn.png" id="editIcon" alt="edit-icon" />
              </button>

              <div class="profile-img-wrapper">
                <img src="<?= htmlspecialchars($avatar) ?>" class="profile-img" id="profileImg" alt="Profile Image" />
                <input type="file" name="profile_img" id="profileInput" accept="image/*" style="display: none" />
              </div>

              <h1 class="username">
                <span id="usernameDisplay"><?= htmlspecialchars($staff['user_name']) ?></span>
              </h1>
              <h2 class="user-role">Staff Member</h2>

              <a href="?logout=1" class="logout-btn">
                  <i class="fas fa-sign-out-alt"></i> Logout
              </a>
            </div>

            <div class="user-details">
              
              <div class="detail-row">
                <label class="label">USERNAME</label>
                <div>
                  <input type="text" name="username" class="field-input" id="usernameInput" value="<?= htmlspecialchars($staff['user_name']) ?>" disabled />
                </div>
              </div>

              <div class="detail-row">
                <label class="label">EMAIL</label>
                <div>
                  <input type="email" name="email" class="field-input" id="emailInput" value="<?= htmlspecialchars($staff['user_email']) ?>" disabled />
                </div>
              </div>

              <div class="detail-row">
                <label class="label">NEW PASSWORD</label>
                <div>
                  <input type="password" name="password" class="field-input" id="passwordInput" placeholder="Leave blank to keep current" disabled />
                </div>
              </div>

            </div>
          </div>
      </form>
    </div>

    <div id="savePopup" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
      <div style="background: white; padding: 30px; border-radius: 12px; text-align: center; width: 90%; max-width: 400px;">
        <h2 style="margin-bottom: 15px; color: #333;">Save Changes?</h2>
        <p style="margin-bottom: 25px; color: #666;">Are you sure you want to update your profile?</p>
        <div style="display: flex; gap: 10px; justify-content: center;">
          <button id="cancelSave" style="padding: 10px 20px; border: 1px solid #ccc; background: white; border-radius: 6px; cursor: pointer;">Cancel</button>
          <button id="confirmSave" style="padding: 10px 20px; background: #d60000; color: white; border: none; border-radius: 6px; cursor: pointer;">Save</button>
        </div>
      </div>
    </div>

    <script>
      document.addEventListener("DOMContentLoaded", () => {
        const editBtn = document.getElementById("editBtn");
        const editIcon = document.getElementById("editIcon");
        
        // Fields
        const usernameInput = document.getElementById("usernameInput");
        const emailInput = document.getElementById("emailInput");
        const passwordInput = document.getElementById("passwordInput");
        
        const profileImg = document.getElementById("profileImg");
        const profileInput = document.getElementById("profileInput");
        const form = document.getElementById("profileForm");

        let editing = false;

        editBtn.addEventListener("click", () => {
          editing = !editing;

          if (editing) {
            // --- ENTER EDIT MODE ---
            usernameInput.disabled = false;
            emailInput.disabled = false;
            passwordInput.disabled = false;
            
            usernameInput.style.border = "1px solid #d60000";
            emailInput.style.border = "1px solid #d60000";
            passwordInput.style.border = "1px solid #d60000";
            
            editIcon.src = "../../../../public/assets/save_btn.png";
          } else {
            // --- CLICKED SAVE ---
            document.getElementById("savePopup").style.display = "flex";
          }
        });

        document.getElementById("confirmSave").onclick = () => form.submit();
        document.getElementById("cancelSave").onclick = () => {
            editing = true; // Keep in edit mode
            document.getElementById("savePopup").style.display = "none";
        };

        // Image Upload Preview
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