<?php
session_start();
require_once '../../../../app/core/db.php';

// 1. Basic Stats (PHP Logic)
$total_sales = $con->query("SELECT SUM(final_price) as total FROM booking WHERE status='Completed'")->fetch_assoc()['total'] ?? 0;
$total_bookings = $con->query("SELECT COUNT(*) as total FROM booking")->fetch_assoc()['total'];
$pending_bookings = $con->query("SELECT COUNT(*) as total FROM booking WHERE status='Pending'")->fetch_assoc()['total'];

// 2. Fetch Python Analytics (Visualizations)
function getAnalyticsCharts() {
    $api_url = "http://127.0.0.1:5001/get_reports"; // Python API on Port 5001
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 sec timeout
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 && $response) {
        return json_decode($response, true);
    }
    return null;
}

$charts = getAnalyticsCharts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Reports & Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-100 p-8 min-h-screen">

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-extrabold text-gray-800 border-l-8 border-red-700 pl-4">Business Intelligence Reports</h1>
        <button onclick="window.location.reload()" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 transition">
            <i class="fas fa-sync-alt"></i> Refresh Data
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-xl shadow-md border-b-4 border-green-500 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-bold uppercase tracking-wider">Total Revenue</p>
                <p class="text-3xl font-extrabold text-gray-800 mt-1">₱<?= number_format($total_sales, 2) ?></p>
            </div>
            <div class="p-3 bg-green-100 rounded-full text-green-600">
                <i class="fas fa-coins text-2xl"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md border-b-4 border-blue-500 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-bold uppercase tracking-wider">Total Bookings</p>
                <p class="text-3xl font-extrabold text-gray-800 mt-1"><?= number_format($total_bookings) ?></p>
            </div>
            <div class="p-3 bg-blue-100 rounded-full text-blue-600">
                <i class="fas fa-ticket-alt text-2xl"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md border-b-4 border-yellow-500 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-bold uppercase tracking-wider">Pending Payments</p>
                <p class="text-3xl font-extrabold text-gray-800 mt-1"><?= number_format($pending_bookings) ?></p>
            </div>
            <div class="p-3 bg-yellow-100 rounded-full text-yellow-600">
                <i class="fas fa-clock text-2xl"></i>
            </div>
        </div>
    </div>

    <h2 class="text-xl font-bold text-gray-700 mb-4 flex items-center gap-2">
        <i class="fab fa-python text-blue-600"></i> AI Data Analytics
    </h2>

    <?php if ($charts && !isset($charts['error'])): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Revenue Growth</h3>
                <?php if ($charts['sales_trend']): ?>
                    <img src="data:image/png;base64,<?= $charts['sales_trend'] ?>" alt="Sales Trend" class="w-full h-auto rounded-lg">
                <?php else: ?>
                    <p class="text-center text-gray-400 py-10">Not enough data to display sales trend.</p>
                <?php endif; ?>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Top Performing Movies</h3>
                <?php if ($charts['top_movies']): ?>
                    <img src="data:image/png;base64,<?= $charts['top_movies'] ?>" alt="Top Movies" class="w-full h-auto rounded-lg">
                <?php else: ?>
                    <p class="text-center text-gray-400 py-10">Not enough data to display top movies.</p>
                <?php endif; ?>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg lg:col-span-2 flex flex-col items-center">
                <h3 class="font-bold text-gray-700 mb-4 border-b pb-2 w-full">Audience Preference (Genres)</h3>
                <?php if ($charts['genre_dist']): ?>
                    <div class="w-1/2">
                        <img src="data:image/png;base64,<?= $charts['genre_dist'] ?>" alt="Genre Distribution" class="w-full h-auto rounded-lg">
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-400 py-10">Not enough data to display genre analysis.</p>
                <?php endif; ?>
            </div>

        </div>
    <?php else: ?>
        <div class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-xl text-center">
            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
            <p class="font-bold">Analytics Service Unavailable</p>
            <p class="text-sm">Please ensure the Python Analytics Server is running (<code>python analytics_api.py</code>).</p>
        </div>
    <?php endif; ?>

</body>
</html>