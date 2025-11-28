<?php
// SeatsPage.php
session_start();
include '../../../../app/core/db.php';

// ------------------------------
// 1. GET PARAMETERS FROM URL
// ------------------------------
$movie_id  = isset($_GET['movie_id']) ? intval($_GET['movie_id']) : 0;
$cinema_id = isset($_GET['cinema_id']) ? intval($_GET['cinema_id']) : 0;
$schedule  = isset($_GET['schedule']) ? $_GET['schedule'] : '';
$qty       = isset($_GET['qty']) ? intval($_GET['qty']) : 1; 

if ($movie_id === 0 || $cinema_id === 0 || empty($schedule)) {
    die("Missing required parameters.");
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

// DETERMINE CINEMA TYPE
$is_sm_cinema = (strpos(strtoupper($cinema_name), 'SM') !== false);
$is_robinsons_cinema = (strpos(strtoupper($cinema_name), 'ROBINSON') !== false);

// ------------------------------
// 3. FETCH BOOKED SEATS
// ------------------------------
$booked_seats_array = [];
$query = "
    SELECT s.seat_number 
    FROM booked_seats bs
    JOIN booking b ON bs.ticket_id = b.ticket_id
    JOIN seats s ON bs.seat_id = s.seat_id
    WHERE b.movie_id = ? AND b.schedule = ? AND s.cinema_id = ? AND b.status IN ('Booked', 'Completed')
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
    <title>Select Seats - <?= htmlspecialchars($cinema_name) ?></title>
    
    <?php if ($is_robinsons_cinema): ?>
        <link rel="stylesheet" href="../../../../public/styles/Seats_Robinson.css" />
        <style>
            /* ROBINSONS SPECIFIC CSS */
            body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
            #openPopup { padding: 10px 20px; background: #d60000; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
            .modal { display: block; position: fixed; z-index: 100; inset: 0; background: rgba(0, 0, 0, 0.6); overflow-y: auto;}
            .modal-content { background: white; width: 95%; max-width: 1400px; margin: 40px auto; padding: 20px; border-radius: 12px; position: relative; }
            h2 { text-align: center; margin-bottom: 20px; }
            .legend { display: flex; justify-content: center; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
            .legend-item { display: inline-block; width: 20px; height: 20px; margin-right: 5px; border-radius: 4px; }
            .legend-item.available { background: #555; }
            .legend-item.selected { background: #d60000; }
            .legend-item.taken { background: #ccc; }
            .seat-limit-warning { color: #d60000; }
            .screen { background: #d60000; color: white; padding: 8px; text-align: center; font-size: 18px; margin-bottom: 20px; border-radius: 6px; }
            .seat-grid-wrapper { margin: 20px 0; padding: 10px; overflow-x: auto; }
            .seat-row { display: flex; gap: 5px; justify-content: center; align-items: center; margin-bottom: 8px; min-width: 1000px; }
            .row-label { width: 30px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; }
            .seat { width: 28px; height: 28px; background: #555; color: white; font-size: 11px; display: flex; justify-content: center; align-items: center; border-radius: 6px; cursor: pointer; flex-shrink: 0; }
            .seat:hover:not(.booked) { opacity: 0.8; }
            .seat.selected { background: #d60000; }
            .seat.booked { background: #ccc; color: #666; cursor: not-allowed; }
            .aisle { width: 20px; flex-shrink: 0; }
            .seat-empty { width: 28px; height: 28px; flex-shrink: 0; }
            .bottom-items { margin-top: 20px; display: flex; flex-direction: row; justify-content: space-between; align-items: center; gap: 15px; }
            .result h3 { font-size: 16px; color: #333; margin: 0; }
            .result h3 span { color: #d60000; font-weight: bold; }
            .action-buttons button { padding: 12px 30px; background: #d60000; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; }
            .action-buttons button:hover { background: #b00000; }
            
            /* Confirm Modal */
            #confirmModal { display: none; }
            .confirm-content { background: white; width: 90%; max-width: 500px; margin: 150px auto; padding: 30px; border-radius: 12px; text-align: center; }
            .confirm-buttons { display: flex; gap: 10px; justify-content: center; margin-top: 20px; }
            .confirm-buttons button { padding: 10px 25px; border: none; border-radius: 6px; cursor: pointer; font-size: 15px; }
            .close-confirm { background: #d60000; color: white; }
            .confirm-final { background: #008000; color: white; }
            .confirm-text span { color: #d60000; font-weight: bold; }
        </style>

    <?php elseif ($is_sm_cinema): ?>
        <style> 
            body { font-family: 'Poppins', sans-serif; margin: 0; padding: 20px; background: #f9f9f9; }
            .modal { display: block; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); padding-top: 40px; overflow-y: auto; }
            .modal-content { background: white; margin: auto; padding: 15px; width: 90%; max-width: 650px; border-radius: 10px; position: relative; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
            
            .legend { margin-bottom: 10px; display: flex; justify-content: center; gap: 15px; align-items: center; width: 100%; font-size: 13px; }
            .legend-item { display: inline-block; width: 12px; height: 12px; margin-right: 5px; border-radius: 2px; }
            .available { background: #555; } .selected { background: #d60000; } .taken { background: #bbb; }
            
            .screen { background: #d60000; color: white; padding: 4px; text-align: center; font-weight: bold; font-size: 12px; margin-bottom: 15px; border-radius: 4px; }
            .seat-row { display: flex; justify-content: center; align-items: center; gap: 3px; margin-bottom: 3px; }
            .row-label { font-weight: bold; font-size: 11px; color: #333; width: 15px; text-align: center; }
            
            .seat { width: 22px; height: 22px; background: #555; color: white; font-size: 9px; display: flex; justify-content: center; align-items: center; border-radius: 3px; cursor: pointer; user-select: none; }
            .seat.booked { background: #e0e0e0; cursor: not-allowed; color: #999; pointer-events: none; }
            .seat.selected { background: #d60000; box-shadow: 0 0 5px #d60000; transform: scale(1.1); }
            
            .bottom-items { padding-top: 15px; border-top: 1px solid #eee; margin-top: 15px; display: flex; justify-content: space-between; align-items: center; }
            #saveSeatsBtn { padding: 10px 25px; background: #d60000; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; transition: 0.2s; font-weight: bold; }
            #saveSeatsBtn:hover { background: #b30000; }

            /* --- CONFIRMATION MODAL STYLES (ADDED) --- */
            #confirmModal { display: none; z-index: 200; /* Higher than seat modal */ }
            .confirm-content { background: white; width: 90%; max-width: 400px; margin: 150px auto; padding: 25px; border-radius: 12px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.3); }
            .confirm-text { margin: 20px 0; font-size: 16px; }
            .confirm-text span { color: #d60000; font-weight: bold; }
            .confirm-buttons { display: flex; gap: 10px; justify-content: center; margin-top: 20px; }
            .confirm-buttons button { padding: 10px 25px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: bold; }
            .close-confirm { background: #d60000; color: white; }
            .confirm-final { background: #008000; color: white; }
        </style>
    <?php endif; ?>
</head>

<body>

<?php if ($is_robinsons_cinema): ?>

    <div id="seatModal" class="modal">
        <div class="modal-content">
            <h2>Select Seats (Robinsons)</h2>
            <div class="legend">
                <span><span class="legend-item available"></span> Available</span>
                <span><span class="legend-item selected"></span> Your Seat</span>
                <span><span class="legend-item taken"></span> Taken</span>
                <span class="seat-limit-counter" id="seatLimitCounter">
                    <span id="seatCountNumber">0/<?= $qty ?></span> selected
                </span>
            </div>
            <div class="screen">SCREEN</div>
            <div id="seatGrid" class="seat-grid-wrapper"></div>
            <div class="bottom-items">
                <div class="result"><h3>Selected: <span id="selectedSeats">None</span></h3></div>
                <div class="action-buttons"><button id="saveSeatsBtn">Save & Confirm</button></div>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="modal" style="display: none;">
        <div class="confirm-content">
            <h2>Confirm Your Seats</h2>
            <p class="confirm-text">You selected: <span id="confirmSeatList"></span></p>
            <form id="robinsonForm" action="Checkout.php" method="POST">
                <input type="hidden" name="movie_id" value="<?= $movie_id ?>">
                <input type="hidden" name="cinema_id" value="<?= $cinema_id ?>">
                <input type="hidden" name="schedule" value="<?= htmlspecialchars($schedule) ?>">
                <input type="hidden" name="qty" value="<?= $qty ?>">
                <input type="hidden" name="selected_seats" id="hiddenSelectedSeats" value="">
                <input type="hidden" name="current_step" value="2">
                <div class="confirm-buttons">
                    <button type="button" id="confirmCloseBtn" class="close-confirm">Close</button>
                    <button type="button" id="confirmFinalBtn" class="confirm-final">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    {   // <--- BLOCK SCOPE START (Fixes Redeclaration Error)
        const maxTickets = <?= $qty ?>; 
        const bookedSeats = <?= json_encode($booked_seats_array) ?>;
        
        const seatGrid = document.getElementById("seatGrid");
        const selectedSeatsDisplay = document.getElementById("selectedSeats");
        const seatCountNumber = document.getElementById("seatCountNumber");
        const hiddenInput = document.getElementById("hiddenSelectedSeats");
        const confirmModal = document.getElementById("confirmModal");
        const confirmSeatList = document.getElementById("confirmSeatList");
        let selectedSeats = [];
        
        const rows = [
          { letter: "A", count: 26, sections: [5, 16, 5] },
          { letter: "B", count: 28, sections: [6, 16, 6] },
          { letter: "C", count: 32, sections: [7, 18, 7] },
          { letter: "D", count: 34, sections: [7, 20, 7] },
          { letter: "E", count: 36, sections: [8, 20, 8] },
          { letter: "F", count: 36, sections: [8, 20, 8] },
          { letter: "G", count: 38, sections: [9, 20, 9] },
          { letter: "H", count: 38, sections: [9, 20, 9] },
          { letter: "I", count: 36, sections: [8, 20, 8] },
          { letter: "J", count: 32, sections: [7, 18, 7] }
        ];

        function renderSeats() {
            seatGrid.innerHTML = "";
            rows.forEach((rowObj) => {
                const rowLetter = rowObj.letter;
                const sections = rowObj.sections; 
                const rowDiv = document.createElement("div");
                rowDiv.className = "seat-row";
                const leftLabel = document.createElement("div");
                leftLabel.className = "row-label";
                leftLabel.textContent = rowLetter;
                rowDiv.appendChild(leftLabel);
                let seatNumber = 1;
                const leftEmpty = 9 - sections[0]; 
                for (let i = 0; i < leftEmpty; i++) rowDiv.appendChild(createEmpty());
                for (let i = 0; i < sections[0]; i++) { rowDiv.appendChild(createSeat(rowLetter + seatNumber, seatNumber)); seatNumber++; }
                const aisle1 = document.createElement("div"); aisle1.className = "aisle"; rowDiv.appendChild(aisle1);
                const middleEmpty = (20 - sections[1]) / 2;
                for (let i = 0; i < Math.floor(middleEmpty); i++) rowDiv.appendChild(createEmpty());
                for (let i = 0; i < sections[1]; i++) { rowDiv.appendChild(createSeat(rowLetter + seatNumber, seatNumber)); seatNumber++; }
                for (let i = 0; i < Math.ceil(middleEmpty); i++) rowDiv.appendChild(createEmpty());
                const aisle2 = document.createElement("div"); aisle2.className = "aisle"; rowDiv.appendChild(aisle2);
                for (let i = 0; i < sections[2]; i++) { rowDiv.appendChild(createSeat(rowLetter + seatNumber, seatNumber)); seatNumber++; }
                const rightEmpty = 9 - sections[2]; 
                for (let i = 0; i < rightEmpty; i++) rowDiv.appendChild(createEmpty());
                const rightLabel = document.createElement("div"); rightLabel.className = "row-label"; rightLabel.textContent = rowLetter; rowDiv.appendChild(rightLabel);
                seatGrid.appendChild(rowDiv);
            });
        }
        function createEmpty() { const el = document.createElement("div"); el.className = "seat-empty"; return el; }
        function createSeat(seatID, number) {
            const seat = document.createElement("div"); seat.className = "seat"; seat.innerText = number;
            if (bookedSeats.includes(seatID)) { seat.classList.add("booked"); } else { seat.onclick = () => selectSeat(seatID, seat); }
            return seat;
        }
        function selectSeat(seatID, seatEl) {
            if (seatEl.classList.contains("selected")) { seatEl.classList.remove("selected"); selectedSeats = selectedSeats.filter((x) => x !== seatID); } 
            else { if (selectedSeats.length >= maxTickets) { alert("Maximum " + maxTickets + " seats allowed."); return; } seatEl.classList.add("selected"); selectedSeats.push(seatID); }
            updateUI();
        }
        function updateUI() {
            selectedSeatsDisplay.innerText = selectedSeats.length > 0 ? selectedSeats.join(", ") : "None";
            seatCountNumber.textContent = `${selectedSeats.length}/${maxTickets}`;
            hiddenInput.value = selectedSeats.join(",");
        }
        document.getElementById("saveSeatsBtn").onclick = () => {
            if (selectedSeats.length !== maxTickets) { alert(`Please select exactly ${maxTickets} seats.`); return; }
            confirmSeatList.textContent = selectedSeats.join(", ");
            confirmModal.style.display = "block";
        };
        document.getElementById("confirmCloseBtn").onclick = () => { confirmModal.style.display = "none"; };
        document.getElementById("confirmFinalBtn").onclick = () => { document.getElementById("robinsonForm").submit(); };
        renderSeats();
    }   // <--- BLOCK SCOPE END
    </script>

<?php elseif ($is_sm_cinema): ?>

    <div id="seatModal" class="modal">
        <div class="modal-content">
            <div style="text-align: center; margin-bottom: 10px;">
                <h3 style="margin:0; color:#333; font-size: 18px;">Select Seats (SM)</h3>
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

            <div class="bottom-items">
                <div class="result">
                    <h4 style="margin:0; font-size: 14px;">Selected: <span id="selectedSeats" style="color:#d60000;">None</span></h4>
                </div>
                <div class="action-buttons">
                    <button type="button" id="saveSeatsBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="modal" style="display: none;">
        <div class="confirm-content">
            <h2>Confirm Your Seats</h2>
            <p class="confirm-text">You selected: <span id="confirmSeatList"></span></p>
            <form id="seatForm" action="Checkout.php" method="POST">
                <input type="hidden" name="movie_id" value="<?= $movie_id ?>">
                <input type="hidden" name="cinema_id" value="<?= $cinema_id ?>">
                <input type="hidden" name="schedule" value="<?= htmlspecialchars($schedule) ?>">
                <input type="hidden" name="qty" value="<?= $qty ?>">
                <input type="hidden" name="selected_seats" id="hiddenSelectedSeats" value="">
                <input type="hidden" name="current_step" value="2">
                <div class="confirm-buttons">
                    <button type="button" id="confirmCloseBtn" class="close-confirm">Close</button>
                    <button type="button" id="confirmFinalBtn" class="confirm-final">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    {   // <--- BLOCK SCOPE START (Fixes Redeclaration Error)
        const maxTickets = <?= $qty ?>; 
        const bookedSeats = <?= json_encode($booked_seats_array) ?>;
        
        const seatGrid = document.getElementById("seatGrid");
        const selectedSeatsDisplay = document.getElementById("selectedSeats");
        const seatCountNumber = document.getElementById("seatCountNumber");
        const hiddenInput = document.getElementById("hiddenSelectedSeats");
        const saveBtn = document.getElementById("saveSeatsBtn");
        const seatForm = document.getElementById("seatForm");
        const confirmModal = document.getElementById("confirmModal");
        const confirmSeatList = document.getElementById("confirmSeatList");
        const confirmCloseBtn = document.getElementById("confirmCloseBtn");
        const confirmFinalBtn = document.getElementById("confirmFinalBtn");

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
            } else {
                if (selectedSeats.length >= maxTickets) {
                    alert(`You have already selected ${maxTickets} seats.`);
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
            seatCountNumber.style.color = (selectedSeats.length === maxTickets) ? "#d60000" : "#333";
            hiddenInput.value = selectedSeats.join(",");
        }

        // --- SM SAVE BUTTON LOGIC ---
        saveBtn.onclick = () => {
            if (selectedSeats.length !== maxTickets) {
                alert(`Please select exactly ${maxTickets} seats.`);
                return;
            }
            // Populate Confirmation Modal
            confirmSeatList.textContent = selectedSeats.join(", ");
            confirmModal.style.display = "block";
        };

        // --- SM MODAL CONTROLS ---
        confirmCloseBtn.onclick = () => {
            confirmModal.style.display = "none";
        };

        confirmFinalBtn.onclick = () => {
            seatForm.submit();
        };

        renderSeats();
    }   // <--- BLOCK SCOPE END
    </script>

<?php else: ?>
    <div class="generic-layout">
        <h3>Seat Selection</h3>
        <p>This layout is optimized for SM or Robinsons Cinemas. <br>Contact support for layout: <?= htmlspecialchars($cinema_name) ?></p>
        <a href="javascript:history.back()">Go Back</a>
    </div>
<?php endif; ?>

</body>
</html>