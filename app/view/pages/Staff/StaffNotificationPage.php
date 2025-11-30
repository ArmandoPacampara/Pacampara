<?php
session_start();
require_once '../../../core/db.php';

// Auth Check
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Staff' && $_SESSION['role'] !== 'Admin')) {
    die("Access Denied");
}

// --- 1. HANDLE ACTIONS (AJAX-like via POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notif_id = intval($_POST['id']);
    
    if (isset($_POST['action']) && $_POST['action'] === 'mark_read') {
        // For now, let's assume "Mark as Read" means acknowledging a pending booking
        // You could also just have a separate 'is_read' column in a notifications table.
        // Here, we'll update status to 'Booked' as an example of "Processing" it.
        $stmt = $con->prepare("UPDATE booking SET status = 'Booked' WHERE ticket_id = ?");
        $stmt->bind_param("i", $notif_id);
        $stmt->execute();
        exit("success");
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        // "Delete" notification = Cancel booking
        $stmt = $con->prepare("UPDATE booking SET status = 'Cancelled' WHERE ticket_id = ?");
        $stmt->bind_param("i", $notif_id);
        $stmt->execute();
        exit("success");
    }
}

// --- 2. FETCH NOTIFICATIONS ---
// We fetch bookings that are 'Pending' or 'Booked' to show as notifications.
// Ordered by date_booked DESC so newest are at top.
$query = "
    SELECT b.ticket_id, b.date_booked, b.schedule, b.status, 
           u.user_name, m.movie_name,
           (SELECT COUNT(*) FROM booked_seats bs WHERE bs.ticket_id = b.ticket_id) as seat_count
    FROM booking b
    JOIN users u ON b.user_id = u.user_id
    JOIN movies m ON b.movie_id = m.movie_id
    WHERE b.status IN ('Pending', 'Booked', 'Completed')
    ORDER BY b.date_booked DESC
    LIMIT 20
";
$result = $con->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Staff Notifications</title>
    <link rel="stylesheet" href="../../../../public/styles/css/StaffNotificationPage.css">
    <style>
        /* Add some dynamic status styling */
        .status-pending { border-left: 4px solid #f1c40f; } /* Yellow */
        .status-booked { border-left: 4px solid #3498db; } /* Blue */
        .status-completed { border-left: 4px solid #2ecc71; } /* Green */
        .status-cancelled { border-left: 4px solid #e74c3c; opacity: 0.6; } /* Red */
    </style>
</head>
<body>

<div class="notif-page">
    <h2 class="notif-title">Booking Notifications</h2>

    <div class="notif-list" id="notifList">

        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <?php 
                    // Determine style based on status
                    $statusClass = 'status-' . strtolower($row['status']);
                    $isUnread = ($row['status'] === 'Pending') ? 'unread' : '';
                    
                    // Format Date (e.g., "6:14 AM" or "Nov 25")
                    $dateBooked = strtotime($row['date_booked']);
                    $timeDisplay = (date('Y-m-d') == date('Y-m-d', $dateBooked)) ? date('g:i A', $dateBooked) : date('M d', $dateBooked);
                    
                    // Format Schedule
                    $schedDate = date('M d, g:i A', strtotime($row['schedule']));
                ?>
                
                <div class="notif-item <?= $isUnread ?> <?= $statusClass ?>" data-id="<?= $row['ticket_id'] ?>">
                    <div class="notif-left">
                        <input type="checkbox" class="check" />
                        <span class="star">&#9734;</span>
                        <span class="notif-label">Booking #<?= $row['ticket_id'] ?> • <?= htmlspecialchars($row['user_name']) ?></span>
                        <span class="notif-text">
                            <strong><?= htmlspecialchars($row['movie_name']) ?></strong> — 
                            <?= $row['status'] ?> request for <?= $schedDate ?> • <?= $row['seat_count'] ?> seat(s)
                        </span>
                    </div>
                    <div class="notif-date"><?= $timeDisplay ?></div>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align:center; padding:20px; color:#777;">No new notifications.</div>
        <?php endif; ?>

    </div>
</div>

<!-- 📌 Popup Modal -->
<div class="notif-modal" id="notifModal">
    <div class="modal-box">
        <div class="popup-header">
            <span id="modalTitle" class="popup-title">Notification</span>
            <button class="popup-close-btn" aria-label="Close">&times;</button>
        </div>
        <div id="modalMessage" class="popup-content"></div>

        <div class="modal-actions">
            <!-- We pass the ID to these buttons via JS -->
            <button id="markReadBtn">Approve / Mark Read</button>
            <button id="deleteBtn" class="delete">Cancel Booking</button>
        </div>
    </div>
</div>

<script>
    const notifItems = document.querySelectorAll(".notif-item");
    const modal = document.getElementById("notifModal");
    const modalTitle = document.getElementById("modalTitle");
    const modalMessage = document.getElementById("modalMessage");
    const markReadBtn = document.getElementById("markReadBtn");
    const deleteBtn = document.getElementById("deleteBtn");

    let currentNotif = null; 
    let currentId = null;

    // Open Notification
    notifItems.forEach(item => {
        item.addEventListener("click", (e) => {
            if (e.target.classList.contains("check") || e.target.classList.contains("star")) return;

            currentNotif = item;
            currentId = item.getAttribute('data-id');

            modalTitle.textContent = item.querySelector(".notif-label").textContent;
            modalMessage.innerHTML = item.querySelector(".notif-text").innerHTML; // Use innerHTML to keep bold tags

            modal.classList.add("active");
        });
    });

    // Star Toggle
    document.querySelectorAll(".star").forEach(star => {
        star.addEventListener("click", (e) => {
            e.stopPropagation();
            star.classList.toggle("starred");
            star.innerHTML = star.classList.contains("starred") ? "★" : "☆";
        });
    });

    // Mark as Read (Approve)
    markReadBtn.addEventListener("click", () => {
        if (currentNotif && currentId) {
            // AJAX Request to Update DB
            const formData = new FormData();
            formData.append('action', 'mark_read');
            formData.append('id', currentId);

            fetch('StaffNotificationPage.php', { method: 'POST', body: formData })
            .then(res => res.text())
            .then(data => {
                if(data.includes('success')) {
                    currentNotif.classList.remove("unread");
                    currentNotif.style.borderLeft = "4px solid #3498db"; // Visual feedback
                    closeModal();
                } else {
                    alert("Error updating status.");
                }
            });
        }
    });

    // Delete (Cancel Booking)
    deleteBtn.addEventListener("click", () => {
        if (currentNotif && currentId) {
            if(!confirm("Are you sure you want to cancel this booking?")) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', currentId);

            fetch('StaffNotificationPage.php', { method: 'POST', body: formData })
            .then(res => res.text())
            .then(data => {
                if(data.includes('success')) {
                    currentNotif.remove();
                    closeModal();
                } else {
                    alert("Error deleting booking.");
                }
            });
        }
    });

    function closeModal() {
        modal.classList.remove("active");
        currentNotif = null;
        currentId = null;
    }

    document.querySelector(".popup-close-btn").addEventListener("click", closeModal);
    modal.addEventListener("click", (e) => { if (e.target === modal) closeModal(); });
</script>

</body>
</html>