<?php
session_start();
require_once '../../../core/db.php';

// Auth Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Access Denied");
}

// --- FETCH COUNTS ---
$user_count = $con->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$movie_count = $con->query("SELECT COUNT(*) as total FROM movies")->fetch_assoc()['total'];
$cinema_count = $con->query("SELECT COUNT(*) as total FROM cinemas")->fetch_assoc()['total'];
$booking_count = $con->query("SELECT COUNT(*) as total FROM booking")->fetch_assoc()['total'];

// Revenue (Completed bookings only)
$revenue = $con->query("SELECT SUM(final_price) as total FROM booking WHERE status='Completed'")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-100 p-8">
    
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Dashboard Overview</h1>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Users Card -->
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500 flex items-center">
            <div class="p-3 bg-blue-100 rounded-full text-blue-600 mr-4">
                <i class="fa-solid fa-users text-2xl"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Users</p>
                <p class="text-2xl font-bold text-gray-800"><?= number_format($user_count) ?></p>
            </div>
        </div>

        <!-- Movies Card -->
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500 flex items-center">
            <div class="p-3 bg-red-100 rounded-full text-red-600 mr-4">
                <i class="fa-solid fa-film text-2xl"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Active Movies</p>
                <p class="text-2xl font-bold text-gray-800"><?= number_format($movie_count) ?></p>
            </div>
        </div>

        <!-- Cinemas Card -->
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500 flex items-center">
            <div class="p-3 bg-yellow-100 rounded-full text-yellow-600 mr-4">
                <i class="fa-solid fa-building text-2xl"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Cinemas</p>
                <p class="text-2xl font-bold text-gray-800"><?= number_format($cinema_count) ?></p>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500 flex items-center">
            <div class="p-3 bg-green-100 rounded-full text-green-600 mr-4">
                <i class="fa-solid fa-money-bill-wave text-2xl"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Revenue</p>
                <p class="text-2xl font-bold text-gray-800">₱<?= number_format($revenue, 2) ?></p>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Bookings</h2>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="text-gray-500 border-b">
                    <th class="pb-3">ID</th>
                    <th class="pb-3">Date</th>
                    <th class="pb-3">Status</th>
                    <th class="pb-3 text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $recent = $con->query("SELECT ticket_id, date_booked, status, final_price FROM booking ORDER BY date_booked DESC LIMIT 5");
                while($row = $recent->fetch_assoc()):
                ?>
                <tr class="border-b last:border-0 hover:bg-gray-50">
                    <td class="py-3 text-gray-800">#<?= $row['ticket_id'] ?></td>
                    <td class="py-3 text-gray-600"><?= date('M d, Y', strtotime($row['date_booked'])) ?></td>
                    <td class="py-3">
                        <span class="px-2 py-1 rounded text-xs font-bold 
                            <?= $row['status']=='Completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                            <?= $row['status'] ?>
                        </span>
                    </td>
                    <td class="py-3 text-right font-bold text-gray-800">₱<?= number_format($row['final_price'], 2) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>