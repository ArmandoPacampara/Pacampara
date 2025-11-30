<?php
session_start();
require_once '../../../core/db.php';

// --- AUTH CHECK ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Access Denied");
}

$admin_id = $_SESSION['user_id'];
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
    
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $con->prepare("UPDATE users SET user_name=?, user_email=?, user_password=? WHERE user_id=?");
        $stmt->bind_param("sssi", $username, $email, $hashed, $admin_id);
    } else {
        $stmt = $con->prepare("UPDATE users SET user_name=?, user_email=? WHERE user_id=?");
        $stmt->bind_param("ssi", $username, $email, $admin_id);
    }
    
    if ($stmt->execute()) {
        $msg = "Profile updated successfully.";
        $_SESSION['user_name'] = $username; 
    } else {
        $msg = "Error updating profile.";
    }
    $stmt->close();
}

// --- FETCH DETAILS ---
$stmt = $con->prepare("SELECT user_name, user_email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 p-8 flex justify-center">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md relative">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2 flex justify-between items-center">
            Admin Profile
        </h2>
        
        <?php if($msg): ?>
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4 text-sm border border-green-200">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="update_profile" value="1">

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($admin['user_name']) ?>" required
                       class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-red-500 transition">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($admin['user_email']) ?>" required
                       class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-red-500 transition">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">New Password (Optional)</label>
                <input type="password" name="password" placeholder="Leave blank to keep current"
                       class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-red-500 transition">
            </div>

            <button type="submit" class="w-full bg-blue-700 text-white font-bold py-3 rounded hover:bg-blue-800 transition mb-4 shadow-md">
                Update Profile
            </button>
        </form>

        <!-- LOGOUT BUTTON -->
        <form method="POST">
            <button type="submit" name="logout" class="w-full bg-white border-2 border-red-600 text-red-600 font-bold py-3 rounded hover:bg-red-50 transition flex justify-center items-center gap-2">
                <i class="fa-solid fa-power-off"></i> Log Out
            </button>
        </form>
    </div>

</body>
</html>