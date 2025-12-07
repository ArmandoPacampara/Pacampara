    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Select Seats — Cinema 4</title>
    <link rel="stylesheet" href="../../../public/styles/Seats_Robinson.css" />
</head>
<body>
<style> 
            body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f9f9f9;
        }


        #openPopup {
            padding: 10px 20px;
            background: #d60000;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }


        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
        }


        .modal-content {
            background: white;
            width: 95%;
            max-width: 1400px;
            margin: 40px auto;
            padding: 20px;
            border-radius: 12px;
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }


        .close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 22px;
            cursor: pointer;
            border: none;
            background: none;
        }


        h2 {
            text-align: center;
            margin-bottom: 20px;
        }


        /* LEGEND */
        .legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }


        .legend-item {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 5px;
            border-radius: 4px;
        }


        .legend-item.available {
            background: #555;
        }


        .legend-item.selected {
            background: #d60000;
        }


        .legend-item.taken {
            background: #ccc;
        }


        .seat-limit-counter {
            font-weight: bold;
            color: #333;
        }


        .seat-limit-warning {
            color: #d60000;
        }


        /* SCREEN */
        .screen {
            background: #d60000;
            color: white;
            padding: 8px;
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
            border-radius: 6px;
        }


        /* SEAT GRID WRAPPER */
        .seat-grid-wrapper {
            margin: 20px 0;
            padding: 10px;
        }


        /* ROW CONTAINER - using grid for perfect alignment */
        .seat-row {
            display: grid;
            grid-template-columns: 30px repeat(9, 28px) 20px repeat(20, 28px) 20px repeat(9, 28px) 30px;
            gap: 5px;
            justify-content: center;
            align-items: center;
            margin-bottom: 8px;
        }


        /* Row labels */
        .row-label {
            width: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }


        /* Seat */
        .seat {
            width: 28px;
            height: 28px;
            background: #555;
            color: white;
            font-size: 11px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 6px;
            cursor: pointer;
            flex-shrink: 0;
        }


        .seat:hover:not(.booked) {
            opacity: 0.8;
        }


        /* Selected */
        .seat.selected {
            background: #d60000;
        }


        /* Booked */
        .seat.booked {
            background: #ccc;
            color: #666;
            cursor: not-allowed;
        }


        /* Aisle blank space */
        .aisle {
            width: 20px;
            flex-shrink: 0;
        }


        /* Empty seat slot (invisible placeholder) */
        .seat-empty {
            width: 28px;
            height: 28px;
            flex-shrink: 0;
        }


/* BOTTOM SECTION */
        .bottom-items {
            margin-top: 20px;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }


        .result {
            text-align: left;
        }


        .result h3 {
            font-size: 16px;
            color: #333;
            margin: 0;
        }


        .result h3 span {
            color: #d60000;
            font-weight: bold;
        }


        .action-buttons {
            display: flex;
            justify-content: flex-end;
        }


        .action-buttons button {
            padding: 12px 30px;
            background: #d60000;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }


        .action-buttons button:hover {
            background: #b00000;
        }


        /* CONFIRMATION MODAL */
        .confirm-content {
            background: white;
            width: 90%;
            max-width: 500px;
            margin: 150px auto;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            justify-content: center;
            margin-top: 300px;
        }


        .confirm-text {
            margin: 20px 0;
            font-size: 16px;
        }


        .confirm-text span {
            color: #d60000;
            font-weight: bold;
        }


        .confirm-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }


        .confirm-buttons button {
            padding: 10px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
           
        }


        .close-confirm {
            background: #d60000;
            color: white;
        }


        .close-confirm:hover {
            background: #b30000;
        }


        .confirm-final {
            background: #008000;
            color: white;
        }


        .confirm-final:hover {
            background: #007e30;
        }






</style>

<button id="openPopup">Select Seats</button>


<!-- MAIN SEAT SELECTION MODAL -->
<div id="seatModal" class="modal" aria-hidden="true">
    <div class="modal-content">


        <button id="closePopup" class="close" aria-label="Close">&times;</button>


        <h2>Select Seats</h2>


        <!-- LEGEND -->
        <div class="legend">
            <span><span class="legend-item available"></span> Available</span>
            <span><span class="legend-item selected"></span> Your Seat</span>
            <span><span class="legend-item taken"></span> Taken</span>


            <span class="seat-limit-counter" id="seatLimitCounter">
                <span id="seatCountNumber">0/6</span>
                <span id="seatCountText"> seats selected</span>
            </span>
        </div>


        <!-- SCREEN -->
        <div class="screen">MOVIE SCREEN - 6:10 PM @ Cinema 4</div>


        <!-- ROWS + SEATS (scrollable container) -->
        <div id="seatGrid" class="seat-grid-wrapper" role="list" aria-label="Seats"></div>


        <div class="bottom-items">
            <div class="result">
                <h3>Selected Seats: <span id="selectedSeats">None</span></h3>
            </div>


            <div class="action-buttons">
                <button id="saveSeatsBtn">Save</button>
            </div>
        </div>
    </div>
</div>


<!-- CONFIRMATION POPUP -->
<div id="confirmModal" class="modal" aria-hidden="true">
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
/* ---------------------------
   Seat layout based on image reference
   Analyzing the image:
   - Rows A-H have 3 sections with 2 aisles
   - Row I (green seats) has same pattern
   - Row J has 3 sections with 2 aisles
   
   From the image, the pattern appears to be:
   Left section - Aisle - Middle section - Aisle - Right section
   
   Based on visual count from image:
   A: ~5 seats | aisle | ~16 seats | aisle | ~5 seats = 26 total
   B: ~6 seats | aisle | ~16 seats | aisle | ~6 seats = 28 total
   C: ~7 seats | aisle | ~18 seats | aisle | ~7 seats = 32 total
   D: ~7 seats | aisle | ~20 seats | aisle | ~7 seats = 34 total
   E: ~8 seats | aisle | ~20 seats | aisle | ~8 seats = 36 total
   F: ~8 seats | aisle | ~20 seats | aisle | ~8 seats = 36 total
   G: ~9 seats | aisle | ~20 seats | aisle | ~9 seats = 38 total
   H: ~9 seats | aisle | ~20 seats | aisle | ~9 seats = 38 total
   I: ~8 seats | aisle | ~20 seats | aisle | ~8 seats = 36 total
   J: ~7 seats | aisle | ~18 seats | aisle | ~7 seats = 32 total
--------------------------------*/


const rows = [
  { letter: "A", count: 26, sections: [5, 16, 5] },
  { letter: "B", count: 28, sections: [6, 16, 6] },
  { letter: "C", count: 32, sections: [7, 18, 7] },
  { letter: "D", count: 34, sections: [7, 20, 7] },
  { letter: "E", count: 36, sections: [8, 20, 8] },
  { letter: "F", count: 36, sections: [8, 20, 8] },
  { letter: "G", count: 38, sections: [9, 20, 9] },
  { letter: "H", count: 38, sections: [9, 20, 9] },
  { letter: "I", count: 36, sections: [8, 20, 8] }, // green seats
  { letter: "J", count: 32, sections: [7, 18, 7] }
];


let bookedSeats = [];   // prebooked seats array, can fill like ["C10","D11"]
let selectedSeats = [];


const seatGrid = document.getElementById("seatGrid");
const selectedSeatsDisplay = document.getElementById("selectedSeats");
const seatCountNumber = document.getElementById("seatCountNumber");
const seatCountText = document.getElementById("seatCountText");


/* OPEN / CLOSE MODALS */
const seatModal = document.getElementById("seatModal");
const confirmModal = document.getElementById("confirmModal");


document.getElementById("openPopup").addEventListener("click", () => {
  seatModal.style.display = "block";
  seatModal.setAttribute("aria-hidden", "false");
});


document.getElementById("closePopup").addEventListener("click", () => {
  seatModal.style.display = "none";
  seatModal.setAttribute("aria-hidden", "true");
});


window.addEventListener("click", (e) => {
  if (e.target === seatModal) {
    seatModal.style.display = "none";
    seatModal.setAttribute("aria-hidden", "true");
  }
  if (e.target === confirmModal) {
    confirmModal.style.display = "none";
    confirmModal.setAttribute("aria-hidden", "true");
  }
});


/* SAVE -> SHOW CONFIRM */
document.getElementById("saveSeatsBtn").addEventListener("click", () => {
  if (selectedSeats.length === 0) {
    alert("Please select at least one seat.");
    return;
  }
  document.getElementById("confirmSeatList").textContent = selectedSeats.join(", ");
  confirmModal.style.display = "block";
  confirmModal.setAttribute("aria-hidden", "false");
});


document.getElementById("confirmCloseBtn").addEventListener("click", () => {
  confirmModal.style.display = "none";
  confirmModal.setAttribute("aria-hidden", "true");
});


/* CONFIRM final booking */
document.getElementById("confirmFinalBtn").addEventListener("click", () => {
  // move selections to booked
  bookedSeats.push(...selectedSeats);
  bookedSeats = Array.from(new Set(bookedSeats));
  // clear selection
  selectedSeats = [];
  selectedSeatsDisplay.innerText = "None";
  seatCountNumber.textContent = `0/6`;
  seatCountText.classList.remove("seat-limit-warning");
  renderSeats();
  confirmModal.style.display = "none";
  confirmModal.setAttribute("aria-hidden", "true");
  alert("Your seats have been successfully booked!");
});


/* render rows with grid-based perfect aisle alignment */
function renderSeats() {
  seatGrid.innerHTML = "";


  rows.forEach((rowObj) => {
    const rowLetter = rowObj.letter;
    const sections = rowObj.sections; // [left, middle, right]


    // row container
    const rowDiv = document.createElement("div");
    rowDiv.className = "seat-row";


    // left label
    const leftLabel = document.createElement("div");
    leftLabel.className = "row-label";
    leftLabel.textContent = rowLetter;
    rowDiv.appendChild(leftLabel);


    let seatNumber = 1;


    // Section 1 (left) - always 9 slots, fill from right
    const leftSlots = 9;
    const leftEmpty = leftSlots - sections[0];
    for (let i = 0; i < leftEmpty; i++) {
      const empty = document.createElement("div");
      empty.className = "seat-empty";
      rowDiv.appendChild(empty);
    }
    for (let i = 0; i < sections[0]; i++) {
      const seatID = rowLetter + seatNumber;
      const seat = createSeat(seatID, seatNumber);
      rowDiv.appendChild(seat);
      seatNumber++;
    }


    // First aisle
    const aisle1 = document.createElement("div");
    aisle1.className = "aisle";
    rowDiv.appendChild(aisle1);


    // Section 2 (middle) - always 20 slots, center it
    const middleSlots = 20;
    const middleEmpty = (middleSlots - sections[1]) / 2;
    for (let i = 0; i < Math.floor(middleEmpty); i++) {
      const empty = document.createElement("div");
      empty.className = "seat-empty";
      rowDiv.appendChild(empty);
    }
    for (let i = 0; i < sections[1]; i++) {
      const seatID = rowLetter + seatNumber;
      const seat = createSeat(seatID, seatNumber);
      rowDiv.appendChild(seat);
      seatNumber++;
    }
    for (let i = 0; i < Math.ceil(middleEmpty); i++) {
      const empty = document.createElement("div");
      empty.className = "seat-empty";
      rowDiv.appendChild(empty);
    }


    // Second aisle
    const aisle2 = document.createElement("div");
    aisle2.className = "aisle";
    rowDiv.appendChild(aisle2);


    // Section 3 (right) - always 9 slots, fill from left
    const rightSlots = 9;
    for (let i = 0; i < sections[2]; i++) {
      const seatID = rowLetter + seatNumber;
      const seat = createSeat(seatID, seatNumber);
      rowDiv.appendChild(seat);
      seatNumber++;
    }
    const rightEmpty = rightSlots - sections[2];
    for (let i = 0; i < rightEmpty; i++) {
      const empty = document.createElement("div");
      empty.className = "seat-empty";
      rowDiv.appendChild(empty);
    }


    // right label
    const rightLabel = document.createElement("div");
    rightLabel.className = "row-label";
    rightLabel.textContent = rowLetter;
    rowDiv.appendChild(rightLabel);


    seatGrid.appendChild(rowDiv);
  });


  // re-apply selection visual for any previously selected seats
  markSelectedSeats();
}


/* Create individual seat element */
function createSeat(seatID, number) {
  const seat = document.createElement("div");
  seat.className = "seat";
  seat.innerText = number;
  seat.dataset.seatId = seatID;


  if (bookedSeats.includes(seatID)) {
    seat.classList.add("booked");
  } else {
    seat.addEventListener("click", () => selectSeat(seatID, seat));
  }


  return seat;
}


/* Add .selected class to seats that are in selectedSeats array */
function markSelectedSeats() {
  const allSeats = seatGrid.querySelectorAll(".seat");
  allSeats.forEach((seatEl) => {
    const id = seatEl.dataset.seatId;
    if (selectedSeats.includes(id)) {
      seatEl.classList.add("selected");
    } else {
      seatEl.classList.remove("selected");
    }
  });
}


/* selection toggle logic */
function selectSeat(seatID, seatEl) {
  if (seatEl.classList.contains("booked")) return;


  if (seatEl.classList.contains("selected")) {
    seatEl.classList.remove("selected");
    selectedSeats = selectedSeats.filter((x) => x !== seatID);
  } else {
    if (selectedSeats.length >= 6) {
      alert("Maximum 6 seats can be selected");
      return;
    }
    seatEl.classList.add("selected");
    selectedSeats.push(seatID);
  }


  selectedSeatsDisplay.innerText = selectedSeats.length > 0 ? selectedSeats.join(", ") : "None";
  seatCountNumber.textContent = `${selectedSeats.length}/6`;


  if (selectedSeats.length === 6) {
    seatCountText.classList.add("seat-limit-warning");
  } else {
    seatCountText.classList.remove("seat-limit-warning");
  }
}


/* initial draw */
renderSeats();
</script>


</body>
</html>

