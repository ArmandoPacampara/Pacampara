<?php
// moviease/app/model/CancelBooking.php
session_start();

// Adjust path based on your file structure
require_once __DIR__ . '/../core/db.php'; 
require_once __DIR__ . '/../core/Logger.php'; 

// Ensure user is logged in as a Customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header("Location: /moviease/index.php"); 
    exit;
}

$database = new Database();
$con = $database->getConnection();

if (isset($_GET['ticket_id'])) {
    $ticket_id = filter_var($_GET['ticket_id'], FILTER_SANITIZE_NUMBER_INT);
    $user_id = $_SESSION['user_id'];
    
    // 1. Fetch booking details (schedule and current status)
    $query = "SELECT schedule, status FROM booking WHERE ticket_id = ? AND user_id = ? LIMIT 1";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ii", $ticket_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();

    if (!$booking) {
        $_SESSION['error'] = "Booking not found or does not belong to your account.";
    } elseif ($booking['status'] === 'Cancelled') {
        $_SESSION['error'] = "This booking is already cancelled.";
    } elseif ($booking['status'] !== 'Completed' && $booking['status'] !== 'Booked') {
        // Only allow cancellation for confirmed bookings
        $_SESSION['error'] = "This booking cannot be cancelled in its current state ({$booking['status']}).";
    } else {
        // --- CANCELLATION ELIGIBILITY CHECK (3 days before) ---
        
        $schedule_datetime = new DateTime($booking['schedule']);
        // Create the deadline by subtracting 3 days from the schedule
        $cancellation_deadline = $schedule_datetime->sub(new DateInterval('P3D'));
        $current_datetime = new DateTime();

        if ($current_datetime < $cancellation_deadline) {
            // Cancellation is allowed (Current time is before the deadline)
            
            // 2. Update booking status to 'Cancelled'
            $update_query = "UPDATE booking SET status = 'Cancelled' WHERE ticket_id = ?";
            $update_stmt = $con->prepare($update_query);
            $update_stmt->bind_param("i", $ticket_id);
            
            if ($update_stmt->execute()) {
                // 3. Log the action and remove associated booked seats
                Logger::log($con, $user_id, "BOOKING_CANCELLED", "Cancelled Ticket #$ticket_id. Seats freed.");
                
                // Remove the booked seats to make them available again
                $delete_seats_query = "DELETE FROM booked_seats WHERE ticket_id = ?";
                $delete_seats_stmt = $con->prepare($delete_seats_query);
                $delete_seats_stmt->bind_param("i", $ticket_id);
                $delete_seats_stmt->execute();
                $delete_seats_stmt->close();
                
                $_SESSION['success'] = "Ticket #$ticket_id has been successfully cancelled.";
            } else {
                $_SESSION['error'] = "Failed to cancel booking due to a database error.";
            }
            $update_stmt->close();
            
        } else {
            // Cancellation deadline has passed
            $_SESSION['error'] = "Cancellation failed: Bookings must be cancelled at least 3 days before the show time (Deadline: " . $cancellation_deadline->format('F d, Y h:i A') . ").";
        }
    }
}

// Redirect back to the user's bookings page
header("Location: ../../app/view/pages/User/Accountpage.php");
exit;
?>