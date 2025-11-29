<?php
// UsersPage.php
session_start();
require_once '../../../../app/core/db.php';

// --- 1. AUTHENTICATION CHECK (Ensure only Admin can access) ---
// Assuming you set $_SESSION['role'] = 'Admin' during login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Access Denied: You must be an Administrator to view this page.");
}

// --- 2. HANDLE DELETE ACTION ---
if (isset($_POST['delete_user'])) {
    $delete_id = intval($_POST['user_id']);
    // Prevent deleting yourself
    if ($delete_id != $_SESSION['user_id']) {
        $stmt = $con->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $msg = "User deleted successfully.";
            $msg_type = "success";
        } else {
            $msg = "Error deleting user.";
            $msg_type = "error";
        }
        $stmt->close();
    } else {
        $msg = "You cannot delete your own account.";
        $msg_type = "error";
    }
}

// --- 3. FETCH ALL USERS ---
// We join with 'roles' table to get the role name instead of just ID
$query = "
    SELECT u.user_id, u.user_name, u.user_email, u.user_contact, r.user_role, u.failed_login_attempts
    FROM users u
    JOIN roles r ON u.role_id = r.role_id
    ORDER BY u.user_id ASC
";
$result = $con->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6f9; padding: 20px; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background-color: #a10000; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { margin: 0; font-size: 1.2rem; }
        .add-btn { background-color: #28a745; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-size: 0.9rem; font-weight: bold; }
        .add-btn:hover { background-color: #218838; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; color: #333; font-weight: 600; }
        tr:hover { background-color: #f1f1f1; }
        
        .action-btn { border: none; background: none; cursor: pointer; font-size: 1.1rem; margin: 0 5px; }
        .edit-btn { color: #007bff; }
        .delete-btn { color: #dc3545; }
        
        .alert { padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <?php if (isset($msg)): ?>
        <div class="alert alert-<?= $msg_type ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>User Management</h2>
            <a href="AddUser.php" class="add-btn"><i class="fa-solid fa-plus"></i> Add User</a>
        </div>
        
        <div class="card-body">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['user_id'] ?></td>
                                <td>
                                    <div class="font-bold"><?= htmlspecialchars($row['user_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($row['user_contact']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($row['user_email']) ?></td>
                                <td>
                                    <?php 
                                        $roleColor = ($row['user_role'] === 'Admin') ? 'text-red-600 font-bold' : 'text-gray-700';
                                        echo "<span class='$roleColor'>" . htmlspecialchars($row['user_role']) . "</span>";
                                    ?>
                                </td>
                                <td>
                                    <?php if($row['failed_login_attempts'] >= 3): ?>
                                        <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded">Locked</span>
                                    <?php else: ?>
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="EditUser.php?id=<?= $row['user_id'] ?>" class="action-btn edit-btn" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    
                                    <form method="POST" action="UsersPage.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                                        <button type="submit" name="delete_user" class="action-btn delete-btn" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:20px;">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>