<?php
session_start();
require_once '../../../core/db.php';

// --- AUTH CHECK ---
// Ensure only Staff can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') {
    die("Access Denied");
}

$staff_id = $_SESSION['user_id'];
$msg = "";

// --- LOGOUT LOGIC ---
if (isset($_POST['logout'])) {
    session_destroy();
    // Use JavaScript to break out of the iframe and redirect the top window
    echo "<script>window.top.location.href = '../../../../public/index.php';</script>";
    exit;
}

// --- HANDLE UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Basic Validation
    if (empty($username) || empty($email)) {
        $msg = "Username and Email are required.";
    } else {
        if (!empty($password)) {
            // Update with password change
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $con->prepare("UPDATE users SET user_name=?, user_email=?, user_password=? WHERE user_id=?");
            $stmt->bind_param("sssi", $username, $email, $hashed, $staff_id);
        } else {
            // Update info only
            $stmt = $con->prepare("UPDATE users SET user_name=?, user_email=? WHERE user_id=?");
            $stmt->bind_param("ssi", $username, $email, $staff_id);
        }
        
        if ($stmt->execute()) {
            $msg = "Profile updated successfully.";
            $_SESSION['user_name'] = $username; // Update session name
        } else {
            $msg = "Error updating profile. Email might be taken.";
        }
        $stmt->close();
    }
}

// --- FETCH DETAILS ---
$stmt = $con->prepare("SELECT user_name, user_email, user_avatar FROM users WHERE user_id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$staff = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 p-8 flex justify-center items-start min-h-screen">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md relative mt-10">
        
        <div class="text-center mb-6">
             <!-- You can display the avatar here if you like -->
            <div class="w-20 h-20 bg-gray-200 rounded-full mx-auto mb-3 flex items-center justify-center overflow-hidden">
                 <?php if($staff['user_avatar']): ?>
                    <img src="../../../../public/assets/images/<?= htmlspecialchars($staff['user_avatar']) ?>" class="w-full h-full object-cover">
                 <?php else: ?>
                    <i class="fa-solid fa-user text-3xl text-gray-500"></i>
                 <?php endif; ?>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">My Profile</h2>
            <p class="text-sm text-gray-500">Staff Member</p>
        </div>
        
        <?php if($msg): ?>
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4 text-sm border border-green-200 text-center">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="update_profile" value="1">

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($staff['user_name']) ?>" required
                       class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-blue-500 transition">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($staff['user_email']) ?>" required
                       class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-blue-500 transition">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">New Password (Optional)</label>
                <input type="password" name="password" placeholder="Leave blank to keep current"
                       class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-blue-500 transition">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded hover:bg-blue-700 transition mb-4 shadow-md">
                Save Changes
            </button>
        </form>

        <!-- LOGOUT BUTTON -->
        <form method="POST">
            <button type="submit" name="logout" class="w-full bg-white border-2 border-red-500 text-red-500 font-bold py-3 rounded hover:bg-red-50 transition flex justify-center items-center gap-2">
                <i class="fa-solid fa-power-off"></i> Log Out
            </button>
        </form>
    </div>

</body>
</html>