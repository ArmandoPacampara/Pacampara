<?php
// moviease/app/view/pages/User/MyBookings.php
session_start();

// Adjust paths based on your file structure
require_once __DIR__ . '/../../../model/Login.php'; 
require_once __DIR__ . '/../../../core/db.php'; 

// Ensure only customers can view this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header("Location: /moviease/index.php"); 
    exit;
}

$database = new Database();
$con = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Query to fetch user bookings with details
$bookings_query = "
    SELECT 
        b.ticket_id, 
        m.movie_name, 
        c.cinema_name, 
        b.schedule, 
        b.final_price, 
        b.status
    FROM 
        booking b
    JOIN 
        movies m ON b.movie_id = m.movie_id
    LEFT JOIN 
        cinema_movies cm ON b.schedule = cm.showtime AND b.movie_id = cm.movie_id
    LEFT JOIN 
        cinemas c ON cm.cinema_id = c.cinema_id
    WHERE 
        b.user_id = ?
    ORDER BY 
        b.schedule DESC
";

$stmt = $con->prepare($bookings_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings_result = $stmt->get_result();
$bookings = $bookings_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - MoviEase</title>
    <link rel="stylesheet" href="../../../../public/styles/css/UserBookings.css"> 
    <style>
        /* Minimal inline styles for demonstration */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #a31212; border-bottom: 2px solid #a31212; padding-bottom: 10px; margin-bottom: 20px; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #a31212; color: white; }
        .status-Completed, .status-Booked { color: green; font-weight: bold; }
        .status-Cancelled { color: red; font-weight: bold; }
        .cancel-btn { background-color: #ffc107; color: black; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .cancel-btn:hover { background-color: #e0a800; }
        .disabled-btn { background-color: #ccc; color: #666; cursor: not-allowed; border: none; padding: 8px 12px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container">
    <h1>My Bookings</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <?php if (empty($bookings)): ?>
        <p>You have no current or past bookings.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Movie</th>
                    <th>Cinema</th>
                    <th>Schedule</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): 
                    $schedule_dt = new DateTime($booking['schedule']);
                    // Calculate the cancellation deadline (3 days before show)
                    $deadline_dt = (clone $schedule_dt)->sub(new DateInterval('P3D'));
                    $current_dt = new DateTime();
                    
                    // Check eligibility: Current time is before the deadline AND status is 'Completed' or 'Booked'
                    $is_active_booking = ($booking['status'] === 'Completed' || $booking['status'] === 'Booked');
                    $can_cancel = $current_dt < $deadline_dt && $is_active_booking;
                ?>
                    <tr>
                        <td><?php echo str_pad($booking['ticket_id'], 8, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo htmlspecialchars($booking['movie_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['cinema_name'] ?: 'N/A'); ?></td>
                        <td><?php echo $schedule_dt->format('F d, Y h:i A'); ?></td>
                        <td>₱<?php echo number_format($booking['final_price'], 2); ?></td>
                        <td class="status-<?php echo htmlspecialchars($booking['status']); ?>">
                            <?php echo htmlspecialchars($booking['status']); ?>
                        </td>
                        <td>
                            <?php if ($can_cancel): ?>
                                <a href="../../../model/CancelBooking.php?ticket_id=<?php echo $booking['ticket_id']; ?>" 
                                   class="cancel-btn" 
                                   onclick="return confirm('WARNING: Are you sure you want to cancel this booking? This action is usually irreversible and may be subject to a refund processing period.')">
                                    Cancel
                                </a>
                            <?php else: ?>
                                <button class="disabled-btn" disabled>
                                    <?php if (!$is_active_booking) echo 'Not Applicable'; 
                                          elseif ($current_dt >= $schedule_dt) echo 'Show Ended';
                                          else echo 'Deadline Passed'; ?>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>