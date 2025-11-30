<?php
session_start();
require_once '../../../../app/core/db.php';

// Stats
$total_sales = $con->query("SELECT SUM(final_price) as total FROM booking WHERE status='Completed'")->fetch_assoc()['total'] ?? 0;
$total_bookings = $con->query("SELECT COUNT(*) as total FROM booking")->fetch_assoc()['total'];
$pending_bookings = $con->query("SELECT COUNT(*) as total FROM booking WHERE status='Pending'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
    <h1 class="text-2xl font-bold mb-6">Business Reports</h1>

    <div class="grid grid-cols-3 gap-6 mb-8">
        <div class="bg-green-100 p-6 rounded shadow border-l-4 border-green-500">
            <h3 class="text-green-800 font-bold">Total Revenue</h3>
            <p class="text-3xl font-bold text-green-900">₱<?= number_format($total_sales, 2) ?></p>
        </div>
        <div class="bg-blue-100 p-6 rounded shadow border-l-4 border-blue-500">
            <h3 class="text-blue-800 font-bold">Total Bookings</h3>
            <p class="text-3xl font-bold text-blue-900"><?= $total_bookings ?></p>
        </div>
        <div class="bg-yellow-100 p-6 rounded shadow border-l-4 border-yellow-500">
            <h3 class="text-yellow-800 font-bold">Pending Payments</h3>
            <p class="text-3xl font-bold text-yellow-900"><?= $pending_bookings ?></p>
        </div>
    </div>
    
    </body>
</html>