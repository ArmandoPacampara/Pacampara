<?php
session_start();
require_once '../../../../app/core/db.php';

// Auth Check
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Staff' && $_SESSION['role'] !== 'Admin')) {
    die("Access Denied");
}

$staff_id = $_SESSION['user_id'];
$cinema_id = $_SESSION['cinema_id'] ?? 0;

// --- 1. FETCH KEY METRICS (PHP Logic - Fast & Direct) ---

// Total Revenue
$revenue_query = "
    SELECT SUM(b.final_price) as total 
    FROM booking b
    JOIN booked_seats bs ON b.ticket_id = bs.ticket_id
    JOIN seats s ON bs.seat_id = s.seat_id
    WHERE b.status = 'Completed' AND s.cinema_id = $cinema_id
";
$revenue = $con->query($revenue_query)->fetch_assoc()['total'] ?? 0;

// Tickets Sold
$tickets_query = "
    SELECT COUNT(DISTINCT b.ticket_id) as count 
    FROM booking b
    JOIN booked_seats bs ON b.ticket_id = bs.ticket_id
    JOIN seats s ON bs.seat_id = s.seat_id
    WHERE b.status = 'Completed' AND s.cinema_id = $cinema_id
";
$tickets_sold = $con->query($tickets_query)->fetch_assoc()['count'] ?? 0;

// Today's Bookings
$today = date('Y-m-d');
$today_query = "
    SELECT COUNT(DISTINCT b.ticket_id) as count 
    FROM booking b
    JOIN booked_seats bs ON b.ticket_id = bs.ticket_id
    JOIN seats s ON bs.seat_id = s.seat_id
    WHERE DATE(b.date_booked) = '$today' AND s.cinema_id = $cinema_id
";
$today_bookings = $con->query($today_query)->fetch_assoc()['count'] ?? 0;

// --- 2. FETCH PYTHON ANALYTICS (Charts) ---
function getAnalyticsCharts($cinema_id) {
    // Pass cinema_id to the Python API
    $api_url = "http://127.0.0.1:5001/get_reports?cinema_id=" . $cinema_id;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8); // Slightly longer timeout for image generation
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 && $response) {
        return json_decode($response, true);
    }
    return null;
}

$charts = getAnalyticsCharts($cinema_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Reports - Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 p-8 min-h-screen font-sans">

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-extrabold text-gray-800 border-l-8 border-red-700 pl-4">Sales & Booking Reports</h1>
            <button onclick="window.location.reload()" class="bg-red-700 text-white px-4 py-2 rounded hover:bg-red-800 transition shadow">
                <i class="fas fa-sync-alt mr-2"></i> Refresh Data
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-xl shadow-md border-b-4 border-green-500 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-bold uppercase tracking-wider">Total Revenue</p>
                    <p class="text-3xl font-extrabold text-gray-800 mt-1">₱<?= number_format($revenue, 2) ?></p>
                </div>
                <div class="p-3 bg-green-100 rounded-full text-green-600">
                    <i class="fas fa-peso-sign text-2xl"></i>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md border-b-4 border-blue-500 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-bold uppercase tracking-wider">Tickets Sold</p>
                    <p class="text-3xl font-extrabold text-gray-800 mt-1"><?= number_format($tickets_sold) ?></p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full text-blue-600">
                    <i class="fas fa-ticket-alt text-2xl"></i>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md border-b-4 border-yellow-500 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-bold uppercase tracking-wider">Bookings Today</p>
                    <p class="text-3xl font-extrabold text-gray-800 mt-1"><?= number_format($today_bookings) ?></p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full text-yellow-600">
                    <i class="far fa-calendar-check text-2xl"></i>
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
                <p class="text-sm">Please ensure the Python Analytics Server is running on port 5001.</p>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>