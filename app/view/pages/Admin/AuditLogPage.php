<?php
session_start();
require_once '../../../../app/core/db.php';

// --- 1. ACCESS CONTROL ---
// Ensure only Admins can view this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Access Denied: You do not have permission to view audit logs.");
}

// --- 2. PAGINATION SETUP ---
$limit = 20; // Logs per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

// --- 3. FETCH LOGS ---
// We use a LEFT JOIN so we still see logs even if the user was deleted later (if ON DELETE SET NULL is used)
$sql = "
    SELECT a.*, u.user_name, u.user_email 
    FROM audit_logs a 
    LEFT JOIN users u ON a.user_id = u.user_id 
    ORDER BY a.created_at DESC 
    LIMIT ?, ?
";

$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $start, $limit);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// --- 4. COUNT TOTAL LOGS (For Pagination Links) ---
$count_res = $con->query("SELECT COUNT(*) as total FROM audit_logs");
$total_logs = $count_res->fetch_assoc()['total'];
$total_pages = ceil($total_logs / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Audit Logs - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; }
        .action-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-blue { background-color: #dbeafe; color: #1e40af; }    /* General Actions */
        .badge-red { background-color: #fee2e2; color: #991b1b; }     /* Critical/Delete Actions */
        .badge-green { background-color: #dcfce7; color: #166534; }   /* Success/Login Actions */
        .badge-yellow { background-color: #fef9c3; color: #854d0e; }  /* Updates/Edits */
    </style>
</head>
<body class="p-6">

    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><i class="fa-solid fa-shield-halved mr-2 text-red-700"></i> Audit Trails</h1>
                <p class="text-gray-500 text-sm">Monitoring user activity and system integrity.</p>
            </div>
            <a href="AdminDashboard.php" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded shadow transition duration-200">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back to Dashboard
            </a>
        </div>

        <!-- Audit Table Card -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <th class="px-5 py-3">Date & Time</th>
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">Action Type</th>
                            <th class="px-5 py-3">Details</th>
                            <th class="px-5 py-3">IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <?php 
                                    // Determine Badge Color based on Action Type
                                    $action = strtoupper($row['action_type']);
                                    $badgeClass = 'badge-blue';
                                    if (strpos($action, 'DELETE') !== false || strpos($action, 'FAIL') !== false) {
                                        $badgeClass = 'badge-red';
                                    } elseif (strpos($action, 'LOGIN') !== false || strpos($action, 'SUCCESS') !== false || strpos($action, 'BOOKING') !== false) {
                                        $badgeClass = 'badge-green';
                                    } elseif (strpos($action, 'UPDATE') !== false || strpos($action, 'EDIT') !== false) {
                                        $badgeClass = 'badge-yellow';
                                    }
                                ?>
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 whitespace-no-wrap font-medium">
                                            <?= date("M d, Y", strtotime($row['created_at'])) ?>
                                        </p>
                                        <p class="text-gray-500 text-xs">
                                            <?= date("h:i:s A", strtotime($row['created_at'])) ?>
                                        </p>
                                    </td>
                                    
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <?php if($row['user_name']): ?>
                                            <div class="flex items-center">
                                                <div class="ml-0">
                                                    <p class="text-gray-900 whitespace-no-wrap font-semibold">
                                                        <?= htmlspecialchars($row['user_name']) ?>
                                                    </p>
                                                    <p class="text-gray-500 text-xs">
                                                        <?= htmlspecialchars($row['user_email']) ?>
                                                    </p>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400 italic text-xs">Guest / System</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <span class="action-badge <?= $badgeClass ?>">
                                            <?= htmlspecialchars($row['action_type']) ?>
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-700 whitespace-normal break-words max-w-xs">
                                            <?= htmlspecialchars($row['details']) ?>
                                        </p>
                                    </td>

                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <span class="text-gray-500 font-mono text-xs border border-gray-300 rounded px-2 py-1 bg-gray-50">
                                            <?= htmlspecialchars($row['ip_address']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-5 py-10 border-b border-gray-200 bg-white text-sm text-center text-gray-500 italic">
                                    No audit logs found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="px-5 py-4 bg-white border-t border-gray-200 flex flex-col xs:flex-row items-center xs:justify-between">
                <span class="text-xs xs:text-sm text-gray-600 mb-2 xs:mb-0">
                    Showing Page <?= $page ?> of <?= max(1, $total_pages) ?>
                </span>
                
                <div class="inline-flex mt-2 xs:mt-0 gap-2">
                    <?php if($page > 1): ?>
                        <a href="?page=<?= $page-1 ?>" class="text-sm bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-300 rounded shadow-sm transition">
                            Previous
                        </a>
                    <?php else: ?>
                        <span class="text-sm bg-gray-100 text-gray-400 font-semibold py-2 px-4 border border-gray-200 rounded cursor-not-allowed">
                            Previous
                        </span>
                    <?php endif; ?>

                    <?php if($page < $total_pages): ?>
                        <a href="?page=<?= $page+1 ?>" class="text-sm bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-300 rounded shadow-sm transition">
                            Next
                        </a>
                    <?php else: ?>
                        <span class="text-sm bg-gray-100 text-gray-400 font-semibold py-2 px-4 border border-gray-200 rounded cursor-not-allowed">
                            Next
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</body>
</html>