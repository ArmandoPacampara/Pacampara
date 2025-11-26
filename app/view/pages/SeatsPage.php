<?php
// SeatsPage.php
session_start();
include '../../../app/core/db.php';

// ------------------------------
// 1. GET PARAMETERS FROM URL
// ------------------------------
// These come from the Step 1 "Next" button (GET request)
$movie_id  = isset($_GET['movie_id']) ? intval($_GET['movie_id']) : 0;
$cinema_id = isset($_GET['cinema_id']) ? intval($_GET['cinema_id']) : 0;
$schedule  = isset($_GET['schedule']) ? $_GET['schedule'] : ''; // This might contain spaces/special chars
$qty       = isset($_GET['qty']) ? intval($_GET['qty']) : 1; 

// Debugging check (Optional, remove in production)
if ($movie_id === 0 || $cinema_id === 0 || empty($schedule)) {
    die("Error in SeatsPage: Missing input parameters. Movie: $movie_id, Cinema: $cinema_id, Schedule: $schedule");
}

// ------------------------------
// 2. FETCH CINEMA DETAILS
// ------------------------------
$cinema_stmt = $con->prepare("SELECT cinema_name FROM cinemas WHERE cinema_id = ?");
$cinema_stmt->bind_param("i", $cinema_id);
$cinema_stmt->execute();
$cinema_res = $cinema_stmt->get_result()->fetch_assoc();
$cinema_name = $cinema_res['cinema_name'] ?? '';
$cinema_stmt->close();

$is_sm_cinema = (strpos(strtoupper($cinema_name), 'SM') !== false);

// ------------------------------
// 3. FETCH BOOKED SEATS
// ------------------------------
$booked_seats_array = [];
$query = "
    SELECT s.seat_number 
    FROM booked_seats bs
    JOIN booking b ON bs.ticket_id = b.ticket_id
    JOIN seats s ON bs.seat_id = s.seat_id
    WHERE b.movie_id = ? 
      AND b.schedule = ? 
      AND s.cinema_id = ? 
      AND b.status IN ('Booked', 'Completed')
";

$stmt = $con->prepare($query);
$stmt->bind_param("isi", $movie_id, $schedule, $cinema_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $booked_seats_array[] = $row['seat_number'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Seats</title>
    
    <style> 
        body { font-family: 'Poppins', sans-serif; margin: 0; padding: 20px; background: #f9f9f9; }

        /* MODAL (Always Open) */
        .modal { display: block; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); padding-top: 40px; overflow-y: auto; }
        
        /* CONTENT BOX - MADE SMALLER */
        .modal-content { 
            background: white; 
            margin: auto; 
            padding: 15px; /* Reduced padding */
            width: 90%; 
            max-width: 650px; /* Reduced width */
            border-radius: 10px; 
            position: relative; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        /* LEGEND - COMPACT */
        .legend { margin-bottom: 10px; display: flex; justify-content: center; gap: 15px; align-items: center; width: 100%; font-size: 13px; }
        .legend-item { display: inline-block; width: 12px; height: 12px; margin-right: 5px; border-radius: 2px; }
        .available { background: #555; }
        .selected { background: #d60000; }
        .taken    { background: #bbb; }
        
        /* SCREEN BAR - THINNER */
        .screen { 
            background: #d60000; 
            color: white; 
            padding: 4px; 
            text-align: center; 
            font-weight: bold; 
            font-size: 12px;
            margin-bottom: 15px; 
            letter-spacing: 3px; 
            box-shadow: 0 3px 10px -3px rgba(214, 0, 0, 0.5); 
            border-radius: 4px;
        }
        
        /* ROW LAYOUT - TIGHTER */
        .seat-row { display: flex; justify-content: center; align-items: center; gap: 3px; margin-bottom: 3px; }
        .row-label { font-weight: bold; font-size: 11px; color: #333; width: 15px; text-align: center; }
        
        /* SEATS - SMALLER */
        .seat { 
            width: 22px; 
            height: 22px; 
            background: #555; 
            color: white; 
            font-size: 9px; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            border-radius: 3px; 
            cursor: pointer; 
            user-select: none; 
        }
        .seat.booked { background: #e0e0e0; cursor: not-allowed; color: #999; pointer-events: none; }
        .seat.selected { background: #d60000; box-shadow: 0 0 5px #d60000; transform: scale(1.1); }
        
        .seat-limit-warning { color: #d60000; font-weight: bold; }
        
        /* BOTTOM AREA */
        .bottom-items { padding-top: 15px; border-top: 1px solid #eee; margin-top: 15px; display: flex; justify-content: space-between; align-items: center; }
        #saveSeatsBtn { padding: 10px 25px; background: #d60000; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; transition: 0.2s; font-weight: bold; }
        #saveSeatsBtn:hover { background: #b30000; }
        
        .generic-layout { text-align: center; padding: 50px; }
    </style>
</head>

<body>

<?php if ($is_sm_cinema): ?>

    <div id="seatModal" class="modal">
        <div class="modal-content">
            
            <div style="text-align: center; margin-bottom: 10px;">
                <h3 style="margin:0; color:#333; font-size: 18px;">Select Seats</h3>
                <p style="margin:2px 0; color:#666; font-size: 13px;">Booking <strong><?= $qty ?></strong> seat(s)</p>
            </div>

            <div class="legend">
                <div><span class="legend-item available"></span> Available</div>
                <div><span class="legend-item selected"></span> Yours</div>
                <div><span class="legend-item taken"></span> Taken</div>
                <div style="margin-left: 10px; font-weight: bold;">
                    <span id="seatCountNumber">0/<?= $qty ?></span>
                </div>
            </div>

            <div class="screen">SCREEN</div>

            <div id="seatGrid"></div>

            <form id="seatForm" action="Checkout.php" method="POST">
                <input type="hidden" name="movie_id" value="<?= $movie_id ?>">
                <input type="hidden" name="cinema_id" value="<?= $cinema_id ?>">
                <input type="hidden" name="schedule" value="<?= htmlspecialchars($schedule) ?>">
                <input type="hidden" name="qty" value="<?= $qty ?>">
                
                <input type="hidden" name="selected_seats" id="hiddenSelectedSeats" value="">
                
                <input type="hidden" name="step" value="3">
                
                <div class="bottom-items">
                    <div class="result">
                        <h4 style="margin:0; font-size: 14px;">Selected: <span id="selectedSeats" style="color:#d60000;">None</span></h4>
                    </div>

                    <div class="action-buttons">
                        <button type="button" id="saveSeatsBtn">Confirm</button>
                    </div>
                </div>
            </form>

        </div>
    </div>

<?php else: ?>

    <div class="generic-layout">
        <h3>Seat Selection</h3>
        <p>This layout is optimized for SM Cinemas. Contact support for layout: <?= htmlspecialchars($cinema_name) ?></p>
        <a href="javascript:history.back()">Go Back</a>
    </div>

<?php endif; ?>

<script>
    const maxTickets = <?= $qty ?>; 
    const bookedSeats = <?= json_encode($booked_seats_array) ?>;
    
    const seatGrid = document.getElementById("seatGrid");
    const selectedSeatsDisplay = document.getElementById("selectedSeats");
    const seatCountNumber = document.getElementById("seatCountNumber");
    const hiddenInput = document.getElementById("hiddenSelectedSeats");
    const saveBtn = document.getElementById("saveSeatsBtn");
    const seatForm = document.getElementById("seatForm");

    let rows = ["A","B","C","D","E","F","G","H","J","K","L","M","N","P"];
    let seatsPerRow = 19;
    let selectedSeats = [];

    function renderSeats() {
        seatGrid.innerHTML = "";

        rows.forEach(rowLetter => {
            const rowDiv = document.createElement("div");
            rowDiv.classList.add("seat-row");

            const leftLabel = document.createElement("div");
            leftLabel.classList.add("row-label");
            leftLabel.textContent = rowLetter;
            rowDiv.appendChild(leftLabel);

            for (let i = 1; i <= seatsPerRow; i++) {
                const seatID = rowLetter + i;
                const seat = document.createElement("div");

                seat.classList.add("seat");
                seat.innerText = i;
                seat.setAttribute('data-id', seatID);

                if (bookedSeats.includes(seatID)) {
                    seat.classList.add("booked");
                    seat.title = "Taken";
                } else {
                    seat.onclick = () => toggleSeat(seatID, seat);
                }

                rowDiv.appendChild(seat);
            }

            const rightLabel = document.createElement("div");
            rightLabel.classList.add("row-label");
            rightLabel.textContent = rowLetter;
            rowDiv.appendChild(rightLabel);

            seatGrid.appendChild(rowDiv);
        });
    }

    function toggleSeat(seatID, seatDiv) {
        if (seatDiv.classList.contains("booked")) return;

        if (selectedSeats.includes(seatID)) {
            selectedSeats = selectedSeats.filter(s => s !== seatID);
            seatDiv.classList.remove("selected");
        } 
        else {
            if (selectedSeats.length >= maxTickets) {
                alert(`You have already selected ${maxTickets} seat(s).`);
                return;
            }
            selectedSeats.push(seatID);
            seatDiv.classList.add("selected");
        }
        updateUI();
    }

    function updateUI() {
        selectedSeatsDisplay.innerText = selectedSeats.length > 0 ? selectedSeats.join(", ") : "None";
        seatCountNumber.innerText = `${selectedSeats.length}/${maxTickets}`;

        if (selectedSeats.length === maxTickets) {
            seatCountNumber.style.color = "#d60000";
        } else {
            seatCountNumber.style.color = "#333";
        }
        // Update the hidden input that gets sent via POST
        hiddenInput.value = selectedSeats.join(",");
    }

    saveBtn.onclick = () => {
        if (selectedSeats.length !== maxTickets) {
            alert(`Please select exactly ${maxTickets} seat(s).`);
            return;
        }
        
        // Debug check (can remove later)
        if (hiddenInput.value === "") {
             alert("Error: No seats captured in hidden input.");
             return;
        }

        seatForm.submit();
    };

    renderSeats();
</script>

</body>
</html>