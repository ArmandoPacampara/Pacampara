<?php
session_start();
require_once '../../../core/db.php';

// Auth Check
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Staff' && $_SESSION['role'] !== 'Admin')) {
    die("Access Denied");
}

// --- 1. FETCH KEY METRICS ---
// Total Revenue (Completed bookings)
$revenue_query = "SELECT SUM(final_price) as total FROM booking WHERE status = 'Completed'";
$revenue = $con->query($revenue_query)->fetch_assoc()['total'] ?? 0;

// Tickets Sold (Completed bookings)
$tickets_query = "SELECT COUNT(*) as count FROM booking WHERE status = 'Completed'";
$tickets_sold = $con->query($tickets_query)->fetch_assoc()['count'] ?? 0;

// Today's Bookings (Any status)
$today = date('Y-m-d');
$today_query = "SELECT COUNT(*) as count FROM booking WHERE DATE(date_booked) = '$today'";
$today_bookings = $con->query($today_query)->fetch_assoc()['count'] ?? 0;

// --- 2. FETCH CHART DATA (Last 7 Days Revenue) ---
$chart_labels = [];
$chart_data = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $sql = "SELECT SUM(final_price) as total FROM booking WHERE DATE(date_booked) = '$date' AND status = 'Completed'";
    $res = $con->query($sql)->fetch_assoc()['total'] ?? 0;
    
    $chart_labels[] = date('M d', strtotime($date));
    $chart_data[] = $res;
}

// --- 3. FETCH RECENT TRANSACTIONS ---
$recent_query = "
    SELECT b.ticket_id, u.user_name, m.movie_name, b.final_price, b.status, b.date_booked 
    FROM booking b
    JOIN users u ON b.user_id = u.user_id
    JOIN movies m ON b.movie_id = m.movie_id
    ORDER BY b.date_booked DESC 
    LIMIT 5
";
$recent_transactions = $con->query($recent_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Reports - Analytics</title>
    <!-- Your existing CSS -->
    <link rel="stylesheet" href="../../../../public/styles/css/StaffPage.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Chart.js for Visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* Page Specific Styles (can be moved to CSS file later) */
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .reports-container { max-width: 1200px; margin: 0 auto; }
        
        .page-header { margin-bottom: 25px; }
        .page-title { font-size: 24px; font-weight: bold; color: #333; }
        
        /* Metric Cards */
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; border-left: 5px solid #ccc; }
        .card-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-right: 15px; }
        .card-info p { margin: 0; color: #666; font-size: 14px; }
        .card-info h3 { margin: 5px 0 0; font-size: 24px; color: #333; }

        .card.blue { border-color: #3498db; }
        .card.blue .card-icon { background: #ebf5fb; color: #3498db; }
        
        .card.green { border-color: #2ecc71; }
        .card.green .card-icon { background: #eafaf1; color: #2ecc71; }

        .card.orange { border-color: #f39c12; }
        .card.orange .card-icon { background: #fef5e7; color: #f39c12; }

        /* Chart & Table Layout */
        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        @media(max-width: 900px) { .dashboard-grid { grid-template-columns: 1fr; } }

        .chart-container, .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .section-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #444; }

        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; color: #888; font-size: 12px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        td { padding: 12px 0; font-size: 14px; color: #333; border-bottom: 1px solid #f5f5f5; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; }
        .status-Completed { background: #eafaf1; color: #2ecc71; }
        .status-Pending { background: #fef9e7; color: #f1c40f; }
        .status-Cancelled { background: #fdecec; color: #e74c3c; }
    </style>
</head>
<body>

    <div class="reports-container">
        <div class="page-header">
            <h1 class="page-title">Sales & Booking Reports</h1>
        </div>

        <!-- 1. KEY METRICS -->
        <div class="metrics-grid">
            <!-- Total Revenue -->
            <div class="card green">
                <div class="card-icon"><i class="fa-solid fa-peso-sign"></i></div>
                <div class="card-info">
                    <p>Total Revenue</p>
                    <h3>₱<?= number_format($revenue, 2) ?></h3>
                </div>
            </div>

            <!-- Tickets Sold -->
            <div class="card blue">
                <div class="card-icon"><i class="fa-solid fa-ticket"></i></div>
                <div class="card-info">
                    <p>Tickets Sold</p>
                    <h3><?= number_format($tickets_sold) ?></h3>
                </div>
            </div>

            <!-- Today's Activity -->
            <div class="card orange">
                <div class="card-icon"><i class="fa-regular fa-calendar-check"></i></div>
                <div class="card-info">
                    <p>Bookings Today</p>
                    <h3><?= number_format($today_bookings) ?></h3>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- 2. SALES CHART -->
            <div class="chart-container">
                <div class="section-title">Revenue Trends (Last 7 Days)</div>
                <canvas id="revenueChart"></canvas>
            </div>

            <!-- 3. RECENT TRANSACTIONS -->
            <div class="table-container">
                <div class="section-title">Recent Transactions</div>
                <table>
                    <thead>
                        <tr>
                            <th>MOVIE</th>
                            <th>AMOUNT</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_transactions->num_rows > 0): ?>
                            <?php while($row = $recent_transactions->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:bold;"><?= htmlspecialchars($row['movie_name']) ?></div>
                                    <div style="font-size:11px; color:#999;"><?= date('M d, h:i A', strtotime($row['date_booked'])) ?></div>
                                </td>
                                <td style="font-weight:bold;">₱<?= number_format($row['final_price']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $row['status'] ?>"><?= $row['status'] ?></span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" style="text-align:center; color:#999;">No transactions found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- CHART JS CONFIGURATION -->
    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'bar', // or 'line'
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                    label: 'Daily Revenue (₱)',
                    data: <?= json_encode($chart_data) ?>,
                    backgroundColor: 'rgba(52, 152, 219, 0.6)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 4], color: '#f0f0f0' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    </script>

</body>
</html>