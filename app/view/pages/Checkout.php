<?php
session_start();
include '../../../app/core/db.php';

// ------------------------------
// 1. REQUIRE PARAMETERS
// ------------------------------
$movie_id  = isset($_GET['movie_id']) ? intval($_GET['movie_id']) : 0;
$cinema_id = isset($_GET['cinema_id']) ? intval($_GET['cinema_id']) : 0;
$schedule  = isset($_GET['schedule']) ? $_GET['schedule'] : '';

if ($movie_id === 0 || $cinema_id === 0 || empty($schedule)) {
    die("Missing movie_id, cinema_id, or schedule.");
}

// ------------------------------
// 2. FETCH MOVIE DETAILS
// ------------------------------
$movie_stmt = $con->prepare("
    SELECT movie_name, movie_poster, movie_description, genre, movie_class 
    FROM movies 
    WHERE movie_id = ?
");
$movie_stmt->bind_param("i", $movie_id);
$movie_stmt->execute();
$movie_data = $movie_stmt->get_result()->fetch_assoc();
$movie_stmt->close();

// ------------------------------
// 3. FETCH CINEMA DETAILS
// ------------------------------
$cinema_stmt = $con->prepare("
    SELECT cinema_name, cinema_address 
    FROM cinemas 
    WHERE cinema_id = ?
");
$cinema_stmt->bind_param("i", $cinema_id);
$cinema_stmt->execute();
$cinema_data = $cinema_stmt->get_result()->fetch_assoc();
$cinema_stmt->close();

// ------------------------------
// 4. FETCH TICKET PRICE FROM cinema_movies
// ------------------------------
$price_stmt = $con->prepare("
    SELECT ticket_price 
    FROM cinema_movies
    WHERE movie_id = ? AND cinema_id = ? AND showtime = ?
");
$price_stmt->bind_param("iis", $movie_id, $cinema_id, $schedule);
$price_stmt->execute();
$price_data = $price_stmt->get_result()->fetch_assoc();
$ticket_price = $price_data['ticket_price'] ?? 0;
$price_stmt->close();

// ------------------------------
// 5. COUNT AVAILABLE SEATS
// ------------------------------
$seats_stmt = $con->prepare("
    SELECT COUNT(*) AS available
    FROM seats
    WHERE cinema_id = ? AND schedule = ? AND status = 'Available'
");
$seats_stmt->bind_param("is", $cinema_id, $schedule);
$seats_stmt->execute();
$seats_available = $seats_stmt->get_result()->fetch_assoc()['available'];
$seats_stmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MoviEase Booking</title>
  <link rel="stylesheet" href="../../../public/styles/css/checkout.css">
</head>

<body>
  <div class="booking-container">
    
    <!-- Movie Header -->
    <div class="movie-header">
      <img src="../../../public/assets/images/<?= $movie_data['movie_poster'] ?>" 
           alt="Poster" class="movie-poster">

      <div class="movie-info">
        <h2 class="movie-title"><?= $movie_data['movie_name'] ?></h2>

        <div class="address"><p><?= $cinema_data['cinema_address'] ?></p></div>
        <div class="cinema_number"><p><?= $cinema_data['cinema_name'] ?></p></div>
        <div class="schedule"><p><?= date("g:i A", strtotime($schedule)) ?></p></div>
        <div class="genre"><p><?= $movie_data['genre'] ?></p></div>
        <div class="rate"><p><?= $movie_data['movie_class'] ?></p></div>

        <div class="movie-desc">
          <h4 style="font-size: 18px;">Movie Description</h4>
          <p><?= $movie_data['movie_description'] ?></p>
        </div>
      </div>

    </div>

    <!-- Step Bar -->
    <div class="progress-container">
      <div class="progress-bar">
        <div class="progress-line" id="progress-line"></div>
      </div>
      <div class="steps">
        <div class="step" id="step1"><div class="circle">✔</div><p>Step 1<br><span>Select Tickets</span></p></div>
        <div class="step disabled" id="step2"><div class="circle">✔</div><p>Step 2<br><span>Select Seats</span></p></div>
        <div class="step disabled" id="step3"><div class="circle">✔</div><p>Step 3<br><span>Apply Vouchers</span></p></div>
        <div class="step disabled" id="step4"><div class="circle">✔</div><p>Step 4<br><span>Confirm Payment</span></p></div>
        <div class="step disabled" id="step5"><div class="circle">✔</div><p>Step 5<br><span>Booking Success</span></p></div>
      </div>
    </div>

    <!-- STEP 1 CONTENT -->
    <div class="step-content" id="step-content">
      <h3>SELECT TICKETS <span>(Available Slots: <?= $seats_available ?>)</span></h3>

      <div class="content-box">
        <label style="font-size: 20px;">Number of Tickets:</label>
        <input type="number" id="ticketQty" min="1" max="<?= $seats_available ?>" value="1">
        
        <p style="margin-top: 10px; font-size: 18px;">
          Ticket Price: <strong>₱<?= number_format($ticket_price, 2) ?></strong>
        </p>
      </div>
    </div>

    <div class="buttons">
      <button id="prevBtn" disabled>Previous</button>

      <!-- NEXT → goes to seats selection -->
      <button id="nextBtn"
              onclick="Seats_SM.href='SeatsPage.php?movie_id=<?= $movie_id ?>&cinema_id=<?= $cinema_id ?>&schedule=<?= urlencode($schedule) ?>&qty='+document.getElementById('ticketQty').value;">
        Next
      </button>
    </div>

  </div>

</body>
</html>

