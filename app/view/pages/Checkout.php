<?php
session_start();
include '../../../app/core/db.php';

// ------------------------------
// 1. CAPTURE DATA & DETERMINE STEP
// ------------------------------
$movie_id       = isset($_REQUEST['movie_id']) ? intval($_REQUEST['movie_id']) : 0;
$cinema_id      = isset($_REQUEST['cinema_id']) ? intval($_REQUEST['cinema_id']) : 0;
$schedule       = isset($_REQUEST['schedule']) ? $_REQUEST['schedule'] : '';
$qty            = isset($_REQUEST['qty']) ? intval($_REQUEST['qty']) : 1;
$selected_seats = isset($_REQUEST['selected_seats']) ? $_REQUEST['selected_seats'] : '';

// Capture Financial Data (From Step 3 -> 4)
$voucher_id      = isset($_REQUEST['voucher_id']) ? intval($_REQUEST['voucher_id']) : 0;
$discount_amount = isset($_REQUEST['discount_amount']) ? floatval($_REQUEST['discount_amount']) : 0;
$final_price     = isset($_REQUEST['final_price']) ? floatval($_REQUEST['final_price']) : 0;

// Determine Current Step
$current_step = isset($_REQUEST['step']) ? intval($_REQUEST['step']) : 1;

// Auto-detect step based on data if 'step' param is missing
if ($current_step == 1 && !empty($selected_seats)) {
    $current_step = 3;
}

if ($movie_id === 0 || $cinema_id === 0 || empty($schedule)) {
    die("Missing required booking parameters.");
}

// ------------------------------
// 2. FETCH COMMON DETAILS
// ------------------------------
$movie_stmt = $con->prepare("SELECT movie_name, movie_poster, movie_description, genre, movie_class FROM movies WHERE movie_id = ?");
$movie_stmt->bind_param("i", $movie_id);
$movie_stmt->execute();
$movie_data = $movie_stmt->get_result()->fetch_assoc();
$movie_stmt->close();

$cinema_stmt = $con->prepare("SELECT cinema_name, cinema_address FROM cinemas WHERE cinema_id = ?");
$cinema_stmt->bind_param("i", $cinema_id);
$cinema_stmt->execute();
$cinema_data = $cinema_stmt->get_result()->fetch_assoc();
$cinema_stmt->close();

$price_stmt = $con->prepare("SELECT ticket_price FROM cinema_movies WHERE movie_id = ? AND cinema_id = ? AND showtime = ?");
$price_stmt->bind_param("iis", $movie_id, $cinema_id, $schedule);
$price_stmt->execute();
$price_data = $price_stmt->get_result()->fetch_assoc();
$ticket_price = $price_data['ticket_price'] ?? 0;
$price_stmt->close();

// ------------------------------
// 3. STEP-SPECIFIC LOGIC
// ------------------------------
$seats_available = 0;
$voucher_message = '';
$voucher_code = '';

// Calculate Base Subtotal
$subtotal = $ticket_price * $qty;

// If we haven't calculated final price yet (Step 1 or initial Step 3), set default
if ($final_price == 0 && $current_step < 4) {
    $final_price = $subtotal;
}

if ($current_step == 1) {
    // --- STEP 1: CALCULATE AVAILABILITY ---
    $total_stmt = $con->prepare("SELECT COUNT(*) FROM seats WHERE cinema_id = ?");
    $total_stmt->bind_param("i", $cinema_id);
    $total_stmt->execute();
    $total_seats = $total_stmt->get_result()->fetch_row()[0] ?? 0;
    
    $booked_stmt = $con->prepare("
        SELECT COUNT(*) FROM booked_seats bs
        JOIN booking b ON bs.ticket_id = b.ticket_id
        WHERE b.movie_id = ? AND b.schedule = ? AND b.status IN ('Booked', 'Completed')
    ");
    $booked_stmt->bind_param("is", $movie_id, $schedule);
    $booked_stmt->execute();
    $booked_count = $booked_stmt->get_result()->fetch_row()[0] ?? 0;
    
    $seats_available = max(0, $total_seats - $booked_count);

} elseif ($current_step == 3) {
    // --- STEP 3: VOUCHERS ---
    if (isset($_POST['apply_voucher']) && !empty($_POST['voucher_code'])) {
        $voucher_code = trim($_POST['voucher_code']);
        
        $v_stmt = $con->prepare("SELECT * FROM vouchers WHERE voucher_code = ? AND valid_until > NOW() AND used_count < usage_limit");
        $v_stmt->bind_param("s", $voucher_code);
        $v_stmt->execute();
        $voucher = $v_stmt->get_result()->fetch_assoc();
        
        if ($voucher) {
            if ($subtotal >= $voucher['min_spend']) {
                if ($voucher['discount_type'] === 'Fixed') {
                    $discount_amount = $voucher['discount_value'];
                } else {
                    $discount_amount = ($subtotal * $voucher['discount_value']) / 100;
                }
                $discount_amount = min($discount_amount, $subtotal);
                $voucher_id = $voucher['voucher_id']; 
                $voucher_message = "<span class='text-green-600 font-bold'>Voucher Applied!</span>";
            } else {
                $voucher_message = "<span class='text-red-600'>Min spend ₱" . number_format($voucher['min_spend']) . " required.</span>";
            }
        } else {
            $voucher_message = "<span class='text-red-600'>Invalid code.</span>";
        }
    }
    $final_price = $subtotal - $discount_amount;
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MoviEase Booking</title>
  <link rel="stylesheet" href="../../../public/styles/css/checkout.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
      .payment-option { border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-bottom: 10px; display: flex; align-items: center; cursor: pointer; transition: 0.2s; }
      .payment-option:hover { background: #f9f9f9; border-color: #aaa; }
      .payment-option input { margin-right: 15px; transform: scale(1.2); accent-color: #d60000; }
      .summary-box { background: #fdfdfd; padding: 15px; border: 1px dashed #ccc; border-radius: 8px; margin-bottom: 20px; }
  </style>
</head>

<body>
  <div class="booking-container">
    
    <div class="movie-header">
      <img src="../../../public/assets/images/<?= $movie_data['movie_poster'] ?>" alt="Poster" class="movie-poster">
      <div class="movie-info">
        <h2 class="movie-title"><?= $movie_data['movie_name'] ?></h2>
        <div class="address"><p><?= $cinema_data['cinema_address'] ?></p></div>
        <div class="cinema_number"><p><?= $cinema_data['cinema_name'] ?></p></div>
        <div class="schedule"><p><?= date("M d, Y • g:i A", strtotime($schedule)) ?></p></div>
        <?php if($current_step >= 3): ?>
            <div class="seats"><p>Seats: <strong><?= htmlspecialchars($selected_seats) ?></strong></p></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="progress-container">
      <div class="progress-bar">
        <div class="progress-line" style="width: <?= ($current_step == 1) ? '10%' : (($current_step == 3) ? '60%' : '85%') ?>;"></div>
      </div>
      <div class="steps">
        <div class="step <?= ($current_step >= 1) ? 'completed' : '' ?>">
            <div class="circle">✔</div><p>Step 1<br><span>Tickets</span></p>
        </div>
        <div class="step <?= ($current_step >= 2) ? 'completed' : 'disabled' ?>">
            <div class="circle">✔</div><p>Step 2<br><span>Seats</span></p>
        </div>
        <div class="step <?= ($current_step >= 3) ? 'completed' : 'disabled' ?>">
            <div class="circle">✔</div><p>Step 3<br><span>Vouchers</span></p>
        </div>
        <div class="step <?= ($current_step == 4) ? 'active' : 'disabled' ?>">
            <div class="circle">✔</div><p>Step 4<br><span>Payment</span></p>
        </div>
        <div class="step disabled">
            <div class="circle">✔</div><p>Step 5<br><span>Success</span></p>
        </div>
      </div>
    </div>

    <div class="step-content">
      
      <?php if ($current_step == 1): ?>
          <h3>SELECT TICKETS <span>(Available: <?= $seats_available ?>)</span></h3>
          <div class="content-box">
            <label style="font-size: 20px;">Number of Tickets:</label>
            <input type="number" id="ticketQty" min="1" max="<?= $seats_available ?>" value="<?= $qty ?>" class="border p-2 rounded w-20 text-center">
            <p style="margin-top: 10px; font-size: 18px;">
              Ticket Price: <strong>₱<?= number_format($ticket_price, 2) ?></strong>
            </p>
          </div>

      <?php elseif ($current_step == 3): ?>
          <h3>APPLY VOUCHERS</h3>
          <div class="content-box" style="height:auto; min-height:220px;">
            <form method="POST" action="Checkout.php" class="flex flex-col gap-2">
                <input type="hidden" name="step" value="3">
                <input type="hidden" name="movie_id" value="<?= $movie_id ?>">
                <input type="hidden" name="cinema_id" value="<?= $cinema_id ?>">
                <input type="hidden" name="schedule" value="<?= htmlspecialchars($schedule) ?>">
                <input type="hidden" name="qty" value="<?= $qty ?>">
                <input type="hidden" name="selected_seats" value="<?= htmlspecialchars($selected_seats) ?>">

                <div class="flex gap-2 items-center mt-2">
                    <input type="text" name="voucher_code" value="<?= htmlspecialchars($voucher_code) ?>" placeholder="Enter Code" class="border p-2 rounded w-1/2">
                    <button type="submit" name="apply_voucher" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">Apply</button>
                </div>
                <p class="text-sm mt-1"><?= $voucher_message ?></p>
            </form>

            <div class="mt-6 pt-4 border-t border-dashed border-gray-300">
                <div class="flex justify-between text-lg">
                    <span>Subtotal (<?= $qty ?> items)</span>
                    <span>₱<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="flex justify-between text-lg text-green-600">
                    <span>Discount</span>
                    <span>-₱<?= number_format($discount_amount, 2) ?></span>
                </div>
                <div class="flex justify-between text-xl font-bold text-red-700 mt-2">
                    <span>Total Amount</span>
                    <span>₱<?= number_format($final_price, 2) ?></span>
                </div>
            </div>
          </div>

      <?php elseif ($current_step == 4): ?>
          <h3>CONFIRM PAYMENT</h3>
          <div class="content-box" style="height:auto; min-height:300px;">
            
            <div class="summary-box">
                <p class="font-bold text-gray-700 mb-2 border-b pb-1">Order Summary</p>
                <div class="flex justify-between text-sm">
                    <span>Tickets (<?= $qty ?>x)</span>
                    <span>₱<?= number_format($subtotal, 2) ?></span>
                </div>
                <?php if($discount_amount > 0): ?>
                <div class="flex justify-between text-sm text-green-600">
                    <span>Voucher Applied</span>
                    <span>-₱<?= number_format($discount_amount, 2) ?></span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between font-bold text-xl text-red-700 mt-2 pt-2 border-t">
                    <span>Amount to Pay</span>
                    <span>₱<?= number_format($final_price, 2) ?></span>
                </div>
            </div>

            <form action="ProcessPayment.php" method="POST" id="paymentForm">
                <input type="hidden" name="movie_id" value="<?= $movie_id ?>">
                <input type="hidden" name="cinema_id" value="<?= $cinema_id ?>">
                <input type="hidden" name="schedule" value="<?= htmlspecialchars($schedule) ?>">
                <input type="hidden" name="qty" value="<?= $qty ?>">
                <input type="hidden" name="selected_seats" value="<?= htmlspecialchars($selected_seats) ?>">
                <input type="hidden" name="voucher_id" value="<?= $voucher_id ?>">
                <input type="hidden" name="discount_amount" value="<?= $discount_amount ?>">
                <input type="hidden" name="final_price" value="<?= $final_price ?>">

                <p class="font-bold mb-3">Select Payment Method:</p>
                
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="GCash" checked>
                    <span class="font-bold">GCash</span>
                </label>

                <label class="payment-option">
                    <input type="radio" name="payment_method" value="PayPal">
                    <span class="font-bold">PayPal</span>
                </label>

                <label class="payment-option">
                    <input type="radio" name="payment_method" value="Credit Card">
                    <span class="font-bold">Credit / Debit Card</span>
                </label>
            </form>
          </div>

      <?php endif; ?>

    </div>

    <div class="buttons">
      
      <?php if ($current_step == 1): ?>
          <button id="prevBtn" disabled>Previous</button>
          <button id="nextBtn" onclick="window.location.href='SeatsPage.php?movie_id=<?= $movie_id ?>&cinema_id=<?= $cinema_id ?>&schedule=<?= urlencode($schedule) ?>&qty='+document.getElementById('ticketQty').value;">Next</button>
      
      <?php elseif ($current_step == 3): ?>
          <button id="prevBtn" onclick="history.back()">Change Seats</button>
          
          <form method="POST" action="Checkout.php" style="display:inline;">
              <input type="hidden" name="step" value="4">
              <input type="hidden" name="movie_id" value="<?= $movie_id ?>">
              <input type="hidden" name="cinema_id" value="<?= $cinema_id ?>">
              <input type="hidden" name="schedule" value="<?= htmlspecialchars($schedule) ?>">
              <input type="hidden" name="qty" value="<?= $qty ?>">
              <input type="hidden" name="selected_seats" value="<?= htmlspecialchars($selected_seats) ?>">
              <input type="hidden" name="voucher_id" value="<?= $voucher_id ?>">
              <input type="hidden" name="discount_amount" value="<?= $discount_amount ?>">
              <input type="hidden" name="final_price" value="<?= $final_price ?>">
              
              <button id="nextBtn" type="submit">Proceed to Payment</button>
          </form>

      <?php elseif ($current_step == 4): ?>
          <button id="prevBtn" onclick="history.back()">Back</button>
          <button id="nextBtn" onclick="document.getElementById('paymentForm').submit()">Pay & Book</button>
      <?php endif; ?>

    </div>

  </div>
</body>
</html>