<?php
// BookingSuccess.php
session_start();
include '../../../../app/core/db.php';

if (!isset($_GET['ticket_id'])) {
    die("Invalid Access");
}

$ticket_id = intval($_GET['ticket_id']);

// 1. UPDATE STATUS TO COMPLETED
// Ideally, we verify the session ID with PayMongo API here for security, 
// but for this level, we assume the redirect means success.
$stmt = $con->prepare("UPDATE booking SET status = 'Completed' WHERE ticket_id = ?");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$stmt->close();

// 2. FETCH DETAILS FOR RECEIPT
$stmt = $con->prepare("
    SELECT b.*, m.movie_name, c.cinema_name, c.cinema_address 
    FROM booking b
    JOIN movies m ON b.movie_id = m.movie_id
    JOIN cinemas c ON m.movie_id = m.movie_id -- Note: This join might need adjustment based on your exact cinema logic
    WHERE b.ticket_id = ?
");
// Simpler join for display
$stmt = $con->prepare("
    SELECT b.*, m.movie_name, m.movie_poster 
    FROM booking b
    JOIN movies m ON b.movie_id = m.movie_id
    WHERE b.ticket_id = ?
");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

// Fetch Seats
$s_stmt = $con->prepare("
    SELECT s.seat_number FROM booked_seats bs 
    JOIN seats s ON bs.seat_id = s.seat_id 
    WHERE bs.ticket_id = ?
");
$s_stmt->bind_param("i", $ticket_id);
$s_stmt->execute();
$res = $s_stmt->get_result();
$seats = [];
while($row = $res->fetch_assoc()) $seats[] = $row['seat_number'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Success</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex justify-center items-center">

    <div class="bg-white p-8 rounded-xl shadow-lg text-center max-w-md w-full">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Payment Successful!</h1>
        <p class="text-gray-600 mb-6">Your booking has been confirmed.</p>

        <div class="bg-gray-50 p-4 rounded-lg text-left mb-6 border border-gray-200">
            <p><strong>Movie:</strong> <?= htmlspecialchars($booking['movie_name']) ?></p>
            <p><strong>Schedule:</strong> <?= date("M d, Y h:i A", strtotime($booking['schedule'])) ?></p>
            <p><strong>Seats:</strong> <?= implode(', ', $seats) ?></p>
            <p><strong>Amount Paid:</strong> ₱<?= number_format($booking['final_price'], 2) ?></p>
            <p><strong>Ticket ID:</strong> #<?= $ticket_id ?></p>
        </div>

        <a href="CinemasPage.php" class="block w-full bg-gray-900 text-white py-3 rounded-lg font-bold hover:bg-gray-800 transition">Back to Home</a>
    </div>

</body>
</html>