<?php
session_start();
require_once '../../../../app/core/db.php'; 

// --- 1. AUTHENTICATION & VALIDATION ---
if (!isset($_SESSION['user_id'])) {
    header("Location: LoginPage.php");
    exit;
}

if (!isset($_GET['ticket_id']) || empty($_GET['ticket_id'])) {
    echo "No ticket specified.";
    exit;
}

$user_id = $_SESSION['user_id'];
$ticket_id = intval($_GET['ticket_id']);

// --- 2. FETCH BOOKING DETAILS (FIXED SQL) ---
// We use MAX() on columns to satisfy 'only_full_group_by' error
$sql = "
    SELECT 
        b.ticket_id, 
        MAX(b.schedule) as schedule, 
        MAX(b.status) as status, 
        MAX(b.payment_method) as payment_method, 
        MAX(b.price) as original_price, 
        MAX(b.discount_amount) as discount_amount, 
        MAX(b.final_price) as final_price, 
        MAX(b.date_booked) as date_booked,
        MAX(m.movie_name) as movie_name, 
        MAX(m.movie_poster) as movie_poster, 
        MAX(m.movie_hours) as movie_hours, 
        MAX(m.movie_class) as movie_class,
        MAX(c.cinema_name) as cinema_name, 
        MAX(c.cinema_address) as cinema_address,
        MAX(u.user_name) as user_name, 
        MAX(u.user_email) as user_email,
        MAX(v.voucher_code) as voucher_code,
        GROUP_CONCAT(s.seat_number ORDER BY s.seat_number SEPARATOR ', ') as seat_numbers
    FROM booking b
    JOIN movies m ON b.movie_id = m.movie_id
    JOIN users u ON b.user_id = u.user_id
    JOIN booked_seats bs ON b.ticket_id = bs.ticket_id
    JOIN seats s ON bs.seat_id = s.seat_id
    JOIN cinemas c ON s.cinema_id = c.cinema_id
    LEFT JOIN vouchers v ON b.voucher_id = v.voucher_id
    WHERE b.ticket_id = ? AND b.user_id = ?
    GROUP BY b.ticket_id
";

$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $ticket_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Receipt not found or access denied.";
    exit;
}

$ticket = $result->fetch_assoc();
$stmt->close();

// Format Data for Display
$poster_path = !empty($ticket['movie_poster']) ? "../../../../public/assets/images/" . $ticket['movie_poster'] : "../../../../public/assets/images/default_poster.jpg";
$formatted_date = date("F d, Y", strtotime($ticket['schedule']));
$formatted_time = date("h:i A", strtotime($ticket['schedule']));
$is_paid = ($ticket['status'] === 'Completed' || $ticket['status'] === 'Booked');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?= $ticket_id ?> - MoviEase</title>
    <style>
        /* --- CSS STYLES --- */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .receipt-container {
            background-color: white;
            width: 100%;
            max-width: 800px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .receipt-header {
            background-color: #d60000;
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }
        .receipt-header h1 { margin: 0; font-size: 24px; letter-spacing: 1px; }
        .receipt-header p { margin: 5px 0 0; opacity: 0.9; font-size: 14px; }
        .status-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 5px 12px;
            background: white;
            color: #d60000;
            font-weight: bold;
            border-radius: 20px;
            text-transform: uppercase;
            font-size: 12px;
        }

        /* Body Layout */
        .receipt-body {
            display: flex;
            padding: 30px;
            gap: 30px;
            flex-wrap: wrap;
        }

        /* Left Column: Movie Poster & Info */
        .movie-section {
            flex: 1;
            min-width: 250px;
            text-align: center;
            border-right: 1px solid #eee;
            padding-right: 30px;
        }
        .movie-poster {
            width: 100%;
            max-width: 200px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .movie-title { margin-top: 15px; color: #333; font-size: 20px; }
        .movie-meta { color: #666; font-size: 14px; margin-top: 5px; }

        /* Right Column: Transaction Details */
        .details-section {
            flex: 1.5;
            min-width: 300px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        .info-group label { display: block; font-size: 12px; color: #888; text-transform: uppercase; margin-bottom: 4px; }
        .info-group span { font-size: 16px; color: #333; font-weight: 500; }

        /* Pricing Table */
        .pricing-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .pricing-table td { padding: 8px 0; border-bottom: 1px solid #eee; }
        .pricing-table .total-row td { border-bottom: none; border-top: 2px solid #333; font-weight: bold; font-size: 18px; padding-top: 15px; }
        .price-val { text-align: right; }
        .discount-text { color: #2ecc71; }

        /* Footer / Actions */
        .receipt-footer {
            background-color: #f9f9f9;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #eee;
        }
        .footer-note { font-size: 12px; color: #777; }
        
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: 0.2s;
        }
        .btn-print { background-color: #333; color: white; }
        .btn-print:hover { background-color: #555; }
        .btn-home { background-color: transparent; color: #666; border: 1px solid #ccc; margin-right: 10px; }
        .btn-home:hover { background-color: #eee; }

        @media print {
            body { background-color: white; padding: 0; }
            .receipt-container { box-shadow: none; max-width: 100%; border-radius: 0; }
            .receipt-footer button, .receipt-footer a { display: none; } /* Hide buttons on print */
        }
        
        @media (max-width: 600px) {
            .receipt-body { flex-direction: column; gap: 20px; }
            .movie-section { border-right: none; padding-right: 0; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        }
    </style>
</head>
<body>

    <div class="receipt-container">
        <div class="receipt-header">
            <h1>MoviEase Ticket</h1>
            <p>Transaction ID: #<?= str_pad($ticket['ticket_id'], 8, '0', STR_PAD_LEFT) ?></p>
            <div class="status-badge"><?= htmlspecialchars($ticket['status']) ?></div>
        </div>

        <div class="receipt-body">
            <div class="movie-section">
                <img src="<?= htmlspecialchars($poster_path) ?>" alt="Poster" class="movie-poster">
                <h2 class="movie-title"><?= htmlspecialchars($ticket['movie_name']) ?></h2>
                <div class="movie-meta">
                    <?= htmlspecialchars($ticket['movie_class']) ?> | <?= date('g\h i\m', strtotime($ticket['movie_hours'])) ?>
                </div>
            </div>

            <div class="details-section">
                
                <div class="info-grid">
                    <div class="info-group">
                        <label>Cinema</label>
                        <span><?= htmlspecialchars($ticket['cinema_name']) ?></span>
                    </div>
                    <div class="info-group">
                        <label>Date & Time</label>
                        <span><?= $formatted_date ?><br><small><?= $formatted_time ?></small></span>
                    </div>
                    <div class="info-group">
                        <label>Seats</label>
                        <span><?= htmlspecialchars($ticket['seat_numbers']) ?></span>
                    </div>
                    <div class="info-group">
                        <label>Customer</label>
                        <span><?= htmlspecialchars($ticket['user_name']) ?></span>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px dashed #ccc; margin: 20px 0;">

                <h3>Payment Summary</h3>
                <table class="pricing-table">
                    <tr>
                        <td>Ticket Price</td>
                        <td class="price-val">₱<?= number_format($ticket['original_price'], 2) ?></td>
                    </tr>
                    
                    <?php if ($ticket['discount_amount'] > 0): ?>
                    <tr>
                        <td>Voucher (<?= htmlspecialchars($ticket['voucher_code']) ?>)</td>
                        <td class="price-val discount-text">- ₱<?= number_format($ticket['discount_amount'], 2) ?></td>
                    </tr>
                    <?php endif; ?>

                    <tr class="total-row">
                        <td>TOTAL PAID</td>
                        <td class="price-val">₱<?= number_format($ticket['final_price'], 2) ?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; color: #888;">Payment Method</td>
                        <td class="price-val" style="font-size: 12px; color: #888;"><?= htmlspecialchars($ticket['payment_method']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="receipt-footer">
            <div class="footer-note">
                Please present this e-ticket at the cinema entrance.<br>
                Booked on: <?= date("M d, Y h:i A", strtotime($ticket['date_booked'])) ?>
            </div>
            <div class="actions">
                <a href="AccountPage.php" class="btn btn-home">Back to Account</a>
                <button onclick="window.print()" class="btn btn-print">Print / Save as PDF</button>
            </div>
        </div>
    </div>

</body>
</html>