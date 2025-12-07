<?php
session_start();
require_once '../../../../app/core/db.php';
require_once '../../../../app/core/Logger.php'; // Import Logger

// --- 1. AUTHENTICATION CHECK ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Access Denied: Administrator privileges required.");
}

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = "";
$msg_type = "";

// --- 2. HANDLE FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $u_name    = trim($_POST['user_name']);
    $u_contact = trim($_POST['user_contact']);
    $u_role_id = intval($_POST['role_id']);
    $u_status  = $_POST['account_status']; // 'Active' or 'Locked'

    // Prepare Lock/Unlock Logic
    $failed_attempts = 0;
    $lockout_until = NULL;

    if ($u_status === 'Locked') {
        // Manually lock the account
        $failed_attempts = 5; // Set above threshold (3)
        $lockout_until = date('Y-m-d H:i:s', strtotime('+10 years')); // Lock for a long time
    } else {
        // Unlock the account
        $failed_attempts = 0;
        $lockout_until = NULL;
    }

    $update_stmt = $con->prepare("
        UPDATE users 
        SET user_name = ?, user_contact = ?, role_id = ?, 
            failed_login_attempts = ?, lockout_until = ?
        WHERE user_id = ?
    ");
    $update_stmt->bind_param("ssiisi", $u_name, $u_contact, $u_role_id, $failed_attempts, $lockout_until, $user_id);

    if ($update_stmt->execute()) {
        // --- LOGGING SUCCESSFUL UPDATE ---
        $admin_id = $_SESSION['user_id'] ?? 0;
        $action = "UPDATE_USER";
        $details = "Admin updated User ID $user_id. Status set to: $u_status. Role ID: $u_role_id.";
        Logger::log($con, $admin_id, $action, $details);

        $msg = "User updated successfully!";
        $msg_type = "success";
        // Refresh to show new data
        header("Refresh:1; url=UsersPage.php"); 
    } else {
        $msg = "Error updating user: " . $con->error;
        $msg_type = "error";
    }
    $update_stmt->close();
}

// --- 3. FETCH USER DATA ---
$stmt = $con->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

// Determine Current Status based on DB values
$current_status = ($user['failed_login_attempts'] >= 3 || ($user['lockout_until'] && strtotime($user['lockout_until']) > time())) ? 'Locked' : 'Active';

// --- 4. FETCH ROLES (For Dropdown) ---
$roles_result = $con->query("SELECT * FROM roles");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6f9; padding: 20px; display: flex; justify-content: center; }
        .card { background: white; width: 100%; max-width: 600px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background-color: #a10000; color: white; padding: 15px 20px; font-size: 1.2rem; display: flex; justify-content: space-between; align-items: center; }
        .card-body { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-weight: 600; color: #333; margin-bottom: 5px; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; }
        .form-control:focus { outline: none; border-color: #a10000; }
        
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 1rem; }
        .btn-primary { background-color: #a10000; color: white; }
        .btn-primary:hover { background-color: #800000; }
        .btn-secondary { background-color: #6c757d; color: white; text-decoration: none; display: inline-block; }
        .btn-secondary:hover { background-color: #5a6268; }

        .alert { padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <div class="card">
        <div class="card-header">
            <span><i class="fa-solid fa-user-pen"></i> Edit User</span>
            <a href="UsersPage.php" class="text-white text-sm hover:underline">Back to List</a>
        </div>

        <div class="card-body">
            <?php if ($msg): ?>
                <div class="alert alert-<?= $msg_type ?>">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="user_name" class="form-control" value="<?= htmlspecialchars($user['user_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address (Read Only)</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['user_email']) ?>" disabled style="background-color: #eee;">
                </div>

                <div class="form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="user_contact" class="form-control" value="<?= htmlspecialchars($user['user_contact']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role_id" class="form-control">
                        <?php while($role = $roles_result->fetch_assoc()): ?>
                            <option value="<?= $role['role_id'] ?>" <?= ($user['role_id'] == $role['role_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['user_role']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Account Status</label>
                    <select name="account_status" class="form-control">
                        <option value="Active" <?= ($current_status === 'Active') ? 'selected' : '' ?>>Active (Unlocked)</option>
                        <option value="Locked" <?= ($current_status === 'Locked') ? 'selected' : '' ?>>Locked (Prevent Login)</option>
                    </select>
                    <small class="text-gray-500">
                        Select "Active" to unlock a user who has been locked out due to failed login attempts.
                    </small>
                </div>

                <div class="flex justify-between mt-6">
                    <a href="UsersPage.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="update_user" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>