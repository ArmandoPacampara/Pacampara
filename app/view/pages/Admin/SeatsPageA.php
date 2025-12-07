<?php
// app/view/pages/Admin/SeatsPageA.php
session_start();
require_once '../../../core/db.php';

// Auth Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { die("Access Denied"); }

// --- 1. FETCH AVAILABLE SCHEDULES ---
// We need a dropdown so the admin can choose WHICH screening to view.
$schedules = $con->query("
    SELECT cm.id, c.cinema_id, c.cinema_name, m.movie_id, m.movie_name, cm.showtime 
    FROM cinema_movies cm
    JOIN cinemas c ON cm.cinema_id = c.cinema_id
    JOIN movies m ON cm.movie_id = m.movie_id
    ORDER BY cm.showtime DESC
");

// --- 2. HANDLE SELECTION ---
$selected_schedule = null;
$booked_seats_array = [];
$cinema_name = "";
$is_sm_cinema = false;
$is_robinsons_cinema = false;

if (isset($_GET['schedule_id'])) {
    $schedule_id = intval($_GET['schedule_id']);
    
    // Fetch details for this specific schedule
    $stmt = $con->prepare("
        SELECT cm.id, c.cinema_id, c.cinema_name, m.movie_id, m.movie_name, cm.showtime 
        FROM cinema_movies cm
        JOIN cinemas c ON cm.cinema_id = c.cinema_id
        JOIN movies m ON cm.movie_id = m.movie_id
        WHERE cm.id = ?
    ");
    $stmt->bind_param("i", $schedule_id);
    $stmt->execute();
    $selected_schedule = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($selected_schedule) {
        $cinema_name = $selected_schedule['cinema_name'];
        $is_sm_cinema = (strpos(strtoupper($cinema_name), 'SM') !== false);
        $is_robinsons_cinema = (strpos(strtoupper($cinema_name), 'ROBINSON') !== false);

        // Fetch Booked Seats for this Movie + Cinema + Time
        // Note: We match using the exact movie_id, cinema_id, and showtime from the schedule record
        $b_stmt = $con->prepare("
            SELECT s.seat_number, b.ticket_id, u.user_name 
            FROM booked_seats bs
            JOIN booking b ON bs.ticket_id = b.ticket_id
            JOIN seats s ON bs.seat_id = s.seat_id
            JOIN users u ON b.user_id = u.user_id
            WHERE b.movie_id = ? 
              AND b.schedule = ? 
              AND s.cinema_id = ? 
              AND b.status IN ('Booked', 'Completed')
        ");
        
        // Use parameters from the selected schedule
        $b_stmt->bind_param("isi", 
            $selected_schedule['movie_id'], 
            $selected_schedule['showtime'], 
            $selected_schedule['cinema_id']
        );
        $b_stmt->execute();
        $res = $b_stmt->get_result();
        
        while($row = $res->fetch_assoc()) {
            // Store seat info, maybe add customer name for tooltip
            $booked_seats_array[$row['seat_number']] = [
                'status' => 'booked',
                'user' => $row['user_name'],
                'ticket' => $row['ticket_id']
            ];
        }
        $b_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seat Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Reuse your existing seat styles but slightly modified for Admin (no interaction needed, just view) */
        .seat-row { display: flex; gap: 3px; justify-content: center; margin-bottom: 3px; }
        .row-label { width: 20px; font-weight: bold; text-align: center; font-size: 12px; }
        .seat { 
            width: 20px; height: 20px; background: #ccc; border-radius: 3px; 
            font-size: 8px; display: flex; align-items: center; justify-content: center; color: #555; cursor: default; 
        }
        .seat.booked { background: #d60000; color: white; cursor: help; } /* Red for booked */
        .seat.available { background: #4caf50; color: white; } /* Green for available */
        
        .aisle { width: 15px; }
        .seat-empty { width: 20px; height: 20px; }
        
        /* Tooltip */
        .seat:hover::after {
            content: attr(data-info);
            position: absolute;
            background: #333;
            color: #fff;
            padding: 5px;
            border-radius: 4px;
            font-size: 10px;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            z-index: 10;
            display: none;
        }
        .seat.booked:hover::after { display: block; }
        .seat { position: relative; }
    </style>
</head>
<body class="bg-gray-50 p-6">

    <h1 class="text-2xl font-bold mb-6 text-gray-800">Seat Occupancy View</h1>

    <!-- 1. SELECT SCHEDULE -->
    <div class="bg-white p-4 rounded shadow mb-6">
        <form method="GET" class="flex gap-4 items-end">
            <div class="w-full max-w-md">
                <label class="block text-sm font-bold mb-1 text-gray-600">Select Screening:</label>
                <select name="schedule_id" class="w-full border p-2 rounded">
                    <option value="">-- Choose Movie / Cinema / Time --</option>
                    <?php while($row = $schedules->fetch_assoc()): ?>
                        <?php 
                            $label = $row['movie_name'] . " @ " . $row['cinema_name'] . " (" . date('M d, h:i A', strtotime($row['showtime'])) . ")";
                            $sel = (isset($schedule_id) && $schedule_id == $row['id']) ? 'selected' : '';
                        ?>
                        <option value="<?= $row['id'] ?>" <?= $sel ?>><?= htmlspecialchars($label) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">View Map</button>
        </form>
    </div>

    <!-- 2. SEAT MAP DISPLAY -->
    <?php if ($selected_schedule): ?>
        <div class="bg-white p-6 rounded shadow overflow-x-auto text-center">
            <h2 class="text-xl font-bold mb-2"><?= htmlspecialchars($selected_schedule['movie_name']) ?></h2>
            <p class="text-gray-500 mb-6"><?= htmlspecialchars($selected_schedule['cinema_name']) ?> | <?= date('F j, Y - g:i A', strtotime($selected_schedule['showtime'])) ?></p>

            <div class="inline-block border-4 border-gray-800 p-4 rounded bg-gray-900">
                <div class="bg-white text-gray-800 font-bold text-center py-1 px-10 mb-6 rounded uppercase tracking-widest">SCREEN</div>
                
                <div id="adminSeatGrid"></div>
            </div>

            <div class="mt-6 flex justify-center gap-6 text-sm">
                <div class="flex items-center gap-2"><span class="w-4 h-4 bg-green-500 rounded"></span> Available</div>
                <div class="flex items-center gap-2"><span class="w-4 h-4 bg-red-700 rounded"></span> Booked</div>
            </div>
        </div>

        <script>
            const bookedData = <?= json_encode($booked_seats_array) ?>; // Object: { "A1": {status, user...}, "A2": ... }
            const seatGrid = document.getElementById("adminSeatGrid");

            // -------------------------------------------
            // RENDER LOGIC (Simplified from your SeatsPage)
            // -------------------------------------------
            
            <?php if ($is_robinsons_cinema): ?>
                // ROBINSONS LAYOUT CONFIG
                const rows = [
                    { letter: "A", sections: [5, 16, 5] }, { letter: "B", sections: [6, 16, 6] },
                    { letter: "C", sections: [7, 18, 7] }, { letter: "D", sections: [7, 20, 7] },
                    { letter: "E", sections: [8, 20, 8] }, { letter: "F", sections: [8, 20, 8] },
                    { letter: "G", sections: [9, 20, 9] }, { letter: "H", sections: [9, 20, 9] },
                    { letter: "I", sections: [8, 20, 8] }, { letter: "J", sections: [7, 18, 7] }
                ];

                rows.forEach(row => {
                    const rowDiv = document.createElement("div");
                    rowDiv.className = "seat-row";
                    
                    // Left Label
                    rowDiv.appendChild(createLabel(row.letter));

                    let num = 1;
                    // Left Section
                    addEmpty(rowDiv, 9 - row.sections[0]);
                    for(let i=0; i<row.sections[0]; i++) { addSeat(rowDiv, row.letter + num); num++; }
                    
                    addAisle(rowDiv);

                    // Middle Section
                    const midEmpty = (20 - row.sections[1]) / 2;
                    addEmpty(rowDiv, Math.floor(midEmpty));
                    for(let i=0; i<row.sections[1]; i++) { addSeat(rowDiv, row.letter + num); num++; }
                    addEmpty(rowDiv, Math.ceil(midEmpty));

                    addAisle(rowDiv);

                    // Right Section
                    for(let i=0; i<row.sections[2]; i++) { addSeat(rowDiv, row.letter + num); num++; }
                    addEmpty(rowDiv, 9 - row.sections[2]);

                    // Right Label
                    rowDiv.appendChild(createLabel(row.letter));
                    seatGrid.appendChild(rowDiv);
                });

            <?php else: ?>
                // SM LAYOUT CONFIG (Default)
                const smRows = ["A","B","C","D","E","F","G","H","J","K","L","M","N","P"];
                const seatsPerRow = 19;

                smRows.forEach(letter => {
                    const rowDiv = document.createElement("div");
                    rowDiv.className = "seat-row";
                    rowDiv.appendChild(createLabel(letter));
                    
                    for(let i=1; i<=seatsPerRow; i++) {
                        addSeat(rowDiv, letter + i);
                    }
                    
                    rowDiv.appendChild(createLabel(letter));
                    seatGrid.appendChild(rowDiv);
                });
            <?php endif; ?>

            // --- HELPER FUNCTIONS ---
            function createLabel(txt) {
                const d = document.createElement("div"); d.className = "row-label text-white"; d.innerText = txt; return d;
            }
            function addAisle(parent) {
                const d = document.createElement("div"); d.className = "aisle"; parent.appendChild(d);
            }
            function addEmpty(parent, count) {
                for(let i=0; i<count; i++) {
                    const d = document.createElement("div"); d.className = "seat-empty"; parent.appendChild(d);
                }
            }
            function addSeat(parent, id) {
                const seat = document.createElement("div");
                seat.innerText = id.replace(/[A-Z]/, ''); // Show number only
                
                if (bookedData[id]) {
                    seat.className = "seat booked";
                    seat.setAttribute("data-info", "Booked by: " + bookedData[id].user);
                } else {
                    seat.className = "seat available";
                }
                parent.appendChild(seat);
            }
        </script>

    <?php elseif (isset($_GET['schedule_id'])): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded mt-6">Schedule not found.</div>
    <?php endif; ?>

</body>
</html>