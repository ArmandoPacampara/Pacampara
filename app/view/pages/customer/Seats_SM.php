<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Seats</title>


    <link rel="stylesheet" href="/public/styles/SM_EO_C1.css">
</head>
<style> 
    body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
    background: #f9f9f9;
}


/* OPEN BUTTON */
#openPopup {
    padding: 10px 20px;
    background: #d60000;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 4px;
}


/* MODAL BACKGROUND */
.modal {
    display: none;
    position: fixed;
    z-index: 100;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    padding-top: 60px;
}


/* POPUP BOX */
.modal-content {
    background: white;
    margin: auto;
    padding: 20px;
    width: 80%;
    max-width: 700px;
    border-radius: 10px;
    position: relative;
}


/* CLOSE BUTTON */
.close {
    position: absolute;
    right: 15px;
    top: 10px;
    font-size: 22px;
    cursor: pointer;
}


/* LEGEND */
.legend {
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}
.legend-item {
    display: inline-block;
    width: 15px;
    height: 15px;
    margin: 0 10px;
    border-radius: 3px;


}
.available { background: #555; }
.selected { background: #d60000; }
.taken   { background: #bbb; }




/* SCREEN BAR */
.screen {
    background: #d60000;
    color: white;
    padding: 6px;
    text-align: center;
    font-weight: bold;
    margin-bottom: 20px;
}


/* SEAT GRID */
.seat-container {
    display: grid;
    grid-template-columns: repeat(18, 30px);
    gap: 5px;
    justify-content: center;
}


/* SEAT STYLES */
.seat {
    width: 30px;
    height: 30px;
    background: #555;
    color: white;
    font-size: 12px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 4px;
    cursor: pointer;
}


.seat.booked {
    background: #d0d0d0;
    cursor: not-allowed;
    color: #666;
}


.seat.selected {
    background: #d60000;
}


.seat-limit-warning {
    color: #d60000;
    font-weight: bold;
}


/* EACH ROW = left label + 18 seats + right label */
.seat-row {
    display: grid;
    grid-template-columns: 30px repeat(19, 30px) 30px;
    gap: 5px;
    justify-content: center;
    margin-bottom: 6px;
}




/* Row letters left and right */
.row-label {
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    color: #333;
}




/* Seat grid wrapper (row labels + seats) */
.seat-wrapper {
    display: grid;
    grid-template-columns: 30px auto 30px; /* left letters — seats — right letters */
    column-gap: 10px;
    margin: 0 auto;
}


/* Seat container is now centered inside wrapper */
.seat-container {
    display: grid;
    grid-template-columns: repeat(18, 30px);
    gap: 5px;
    justify-content: center;
}


/* Seat box (numbers only) */
.seat {
    width: 30px;
    height: 30px;
    background: #555;
    color: white;
    font-size: 12px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}


/* SAVE BUTTON AREA */
.bottom-items{
    padding-top: 10px;
}
/* Save Button */
.action-buttons {
    display: flex;
    justify-content: flex-end;
    margin-top: -40px;
}


#saveSeatsBtn {
    padding: 10px 20px;
    background: #d60000;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: 0.2s;
}


#saveSeatsBtn:hover {
    background: #b30000;
}




/* -------------------------------------------------- */
/*  EXTRA CSS (for popup seat details)                */
/* -------------------------------------------------- */


/* Seat text inside popup */
.popup-seat-info {
    font-size: 22px;
    font-weight: bold;
    margin-top: 15px;
    text-align: center;
    color: #333;
}


/* Optional: styled row letter + seat number */
.popup-row {
    font-size: 26px;
    color: #d60000;
    font-weight: bold;
}


.popup-number {
    font-size: 26px;
    color: #333;
    font-weight: bold;
}


/* CONFIRMATION MODAL */
.confirm-content {
    background: white;
    margin: auto;
    padding: 25px 35px;
    width: 60%;
    max-width: 450px;
    border-radius: 12px;
    position: relative;
    text-align: center;
    margin-top: 250px;
}


.confirm-text {
    font-size: 18px;
    margin: 15px 0 25px 0;
}


.close-confirm {
    padding: 10px 25px;
    background: #d60000;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}


.close-confirm:hover {
    background: #b30000;
}


.confirm-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
}


.confirm-final {
    padding: 10px 25px;
    background: #008000;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}


.confirm-final:hover {
    background: #007e30;
}

</style>

<body>


<button id="openPopup">Select Seats</button>
<!-- MAIN SEAT SELECTION MODAL -->
<div id="seatModal" class="modal">
    <div class="modal-content">


        <span id="closePopup" class="close">&times;</span>


        <h2>Select Seats</h2>


        <!-- LEGEND -->
        <div class="legend">
            <span class="legend-item available"></span> Available
            <span class="legend-item selected"></span> Your Seat
            <span class="legend-item taken"></span> Taken


            <span class="seat-limit-counter" id="seatLimitCounter">
                <span id="seatCountNumber">0/6</span>
                <span id="seatCountText"> seats selected</span>
            </span>
        </div>


        <!-- SCREEN -->
        <div class="screen">SCREEN</div>


        <!-- ROWS + SEATS -->
        <div id="seatGrid"></div>


        <div class="bottom-items">
            <div class="result">
                <h3>Selected Seats: <span id="selectedSeats"></span></h3>
            </div>


            <div class="action-buttons">
                <button id="saveSeatsBtn">Save</button>
            </div>
        </div>


    </div>
</div>




<!-- CONFIRMATION POPUP MODAL -->
<div id="confirmModal" class="modal">
    <div class="confirm-content">


        <h2>Confirm Your Seats</h2>


        <p class="confirm-text">
            You selected: <span id="confirmSeatList"></span>
        </p>


        <div class="confirm-buttons">
            <button id="confirmCloseBtn" class="close-confirm">Close</button>
            <button id="confirmFinalBtn" class="confirm-final">Confirm</button>
        </div>


    </div>
</div>






<script>
// OPEN & CLOSE MAIN POPUP
document.getElementById("openPopup").onclick = () =>
    document.getElementById("seatModal").style.display = "block";


document.getElementById("closePopup").onclick = () =>
    document.getElementById("seatModal").style.display = "none";


window.onclick = e => {
    if (e.target === document.getElementById("seatModal")) {
        document.getElementById("seatModal").style.display = "none";
    }
};


// SEAT GENERATION
const seatGrid = document.getElementById("seatGrid");
const selectedSeatsDisplay = document.getElementById("selectedSeats");


let rows = ["A","B","C","D","E","F","G","H","J","K","L","M","N","P"];
let seatsPerRow = 19;


let bookedSeats = [];   // ← These become taken seats
let selectedSeats = []; // ← Temp selection before confirming


function renderSeats() {
    seatGrid.innerHTML = ""; // Clear grid before re-render


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


            if (bookedSeats.includes(seatID)) {
                seat.classList.add("booked");
                seat.onclick = null;
            } else {
                seat.onclick = () => selectSeat(seatID, seat);
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




// CONFIRMATION POPUP
const confirmModal = document.getElementById("confirmModal");
const confirmSeatList = document.getElementById("confirmSeatList");
const confirmCloseBtn = document.getElementById("confirmCloseBtn");


document.getElementById("saveSeatsBtn").onclick = () => {
    if (selectedSeats.length === 0) {
        alert("Please select at least one seat.");
        return;
    }


    confirmSeatList.textContent = selectedSeats.join(", ");
    confirmModal.style.display = "block";
};


// CLOSE CONFIRM POPUP
confirmCloseBtn.onclick = () => confirmModal.style.display = "none";


window.onclick = e => {
    if (e.target === confirmModal) {
        confirmModal.style.display = "none";
    }
};


// FINAL CONFIRMATION — UPDATE SEATS
document.getElementById("confirmFinalBtn").onclick = () => {


    // Add selected seats to booked seats
    bookedSeats.push(...selectedSeats);


    // Remove duplicates just in case
    bookedSeats = [...new Set(bookedSeats)];


    // Clear selections
    selectedSeats = [];
    selectedSeatsDisplay.innerText = "";


    document.getElementById("seatCountNumber").textContent = `0/6`;
    document.getElementById("seatCountText").classList.remove("seat-limit-warning");


    // Re-render seats (booked seats turn gray)
    renderSeats();


    // Close confirmation popup
    confirmModal.style.display = "none";


    alert("Your seats have been successfully booked!");
};




// SELECT / UNSELECT SEAT
function selectSeat(seatID, seatDiv) {
    if (seatDiv.classList.contains("booked")) return;


    if (seatDiv.classList.contains("selected")) {
        seatDiv.classList.remove("selected");
        selectedSeats = selectedSeats.filter(s => s !== seatID);
    } else {
        if (selectedSeats.length >= 6) return;
        seatDiv.classList.add("selected");
        selectedSeats.push(seatID);
    }


    selectedSeatsDisplay.innerText = selectedSeats.join(", ");
    document.getElementById("seatCountNumber").textContent = `${selectedSeats.length}/6`;


    const seatCountText = document.getElementById("seatCountText");
    if (selectedSeats.length === 6) {
        seatCountText.classList.add("seat-limit-warning");
    } else {
        seatCountText.classList.remove("seat-limit-warning");
    }
}


renderSeats();
</script>




</body>
</html>



