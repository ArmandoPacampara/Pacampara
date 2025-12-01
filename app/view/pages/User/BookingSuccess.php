<?php
session_start();
// Use __DIR__ with a starting slash to prevent path errors
require_once __DIR__ . '/../../../../app/core/db.php';
require_once __DIR__ . '/../../../../app/core/Logger.php'; 
require_once __DIR__ . '/../../../../app/model/mail_function.php'; 

if (!isset($_GET['ticket_id'])) {
    die("Invalid Access");
}

$ticket_id = intval($_GET['ticket_id']);
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// 1. UPDATE STATUS TO COMPLETED
$stmt = $con->prepare("UPDATE booking SET status = 'Completed' WHERE ticket_id = ?");
$stmt->bind_param("i", $ticket_id);
if ($stmt->execute()) {
    Logger::log($con, $user_id, "PAYMENT_VERIFIED", "Ticket #$ticket_id marked as Completed.");
}
$stmt->close();

// 2. FETCH DETAILS
$stmt = $con->prepare("
    SELECT 
        b.*, 
        MAX(m.movie_name) as movie_name, 
        MAX(u.user_email) as user_email,
        MAX(u.user_name) as user_name,
        MAX(c.cinema_name) as cinema_name
    FROM booking b
    JOIN movies m ON b.movie_id = m.movie_id
    JOIN users u ON b.user_id = u.user_id
    LEFT JOIN booked_seats bs ON b.ticket_id = bs.ticket_id
    LEFT JOIN seats s ON bs.seat_id = s.seat_id
    LEFT JOIN cinemas c ON s.cinema_id = c.cinema_id
    WHERE b.ticket_id = ?
    GROUP BY b.ticket_id
");

$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch Seats
$s_stmt = $con->prepare("
    SELECT s.seat_number FROM booked_seats bs 
    JOIN seats s ON bs.seat_id = s.seat_id 
    WHERE bs.ticket_id = ?
");
$s_stmt->bind_param("i", $ticket_id);
$s_stmt->execute();
$res = $s_stmt->get_result();
$seatsArr = [];
while($row = $res->fetch_assoc()) $seatsArr[] = $row['seat_number'];
$seatsString = implode(', ', $seatsArr);

// 3. SEND EMAIL RECEIPT
$emailSent = false;
if ($booking) {
    $emailData = [
        'ticket_id'   => $ticket_id,
        'movie_name'  => $booking['movie_name'],
        'cinema_name' => $booking['cinema_name'] ?? 'MoviEase Cinema',
        'schedule'    => $booking['schedule'],
        'seats'       => $seatsString,
        'final_price' => $booking['final_price']
    ];
    
    // Call the function from mail_function.php
    $emailSent = send_ticket_receipt($booking['user_email'], $booking['user_name'], $emailData);
}
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
        <p class="text-gray-600 mb-6">
            Your booking has been confirmed.<br>
            <?php if ($emailSent): ?>
                <span class="text-sm text-green-600 font-semibold">A receipt has been sent to your email.</span>
            <?php else: ?>
                <span class="text-sm text-red-500 font-semibold">Email receipt could not be sent.</span>
            <?php endif; ?>
        </p>

        <div class="bg-gray-50 p-4 rounded-lg text-left mb-6 border border-gray-200">
            <p><strong>Movie:</strong> <?= htmlspecialchars($booking['movie_name']) ?></p>
            <p><strong>Schedule:</strong> <?= date("M d, Y h:i A", strtotime($booking['schedule'])) ?></p>
            <p><strong>Seats:</strong> <?= $seatsString ?></p>
            <p><strong>Amount Paid:</strong> ₱<?= number_format($booking['final_price'], 2) ?></p>
            <p><strong>Ticket ID:</strong> #<?= $ticket_id ?></p>
        </div>

        <div class="flex flex-col gap-3">
            <a href="ReceiptPage.php?ticket_id=<?= $ticket_id ?>" class="block w-full border border-gray-800 text-gray-800 py-3 rounded-lg font-bold hover:bg-gray-100 transition">View Full Receipt</a>
            <a href="CinemasPage.php" class="block w-full bg-gray-900 text-white py-3 rounded-lg font-bold hover:bg-gray-800 transition">Back to Home</a>
        </div>
    </div>

</body>
</html>