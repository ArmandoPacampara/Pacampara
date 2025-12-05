<?php
session_start();
include '../../../../app/core/db.php';

$is_resumed = false; // Flag to track if we are resuming
$ticket_id_resume = 0;

if (isset($_GET['resume']) && $_GET['resume'] == 1 && isset($_GET['ticket_id'])) {
    $ticket_id_resume = intval($_GET['ticket_id']);
    $user_id = $_SESSION['user_id'];

    // Fetch existing booking details
    $resume_stmt = $con->prepare("SELECT * FROM booking WHERE ticket_id = ? AND user_id = ? AND status = 'Pending'");
    $resume_stmt->bind_param("ii", $ticket_id_resume, $user_id);
    $resume_stmt->execute();
    $booking_data = $resume_stmt->get_result()->fetch_assoc();
    $resume_stmt->close();

    if ($booking_data) {
        $is_resumed = true;
        
        // Fetch Seats for this booking to get cinema_id and seat names
        $seat_query = $con->prepare("
            SELECT s.seat_number, s.cinema_id 
            FROM booked_seats bs 
            JOIN seats s ON bs.seat_id = s.seat_id 
            WHERE bs.ticket_id = ?
        ");
        $seat_query->bind_param("i", $ticket_id_resume);
        $seat_query->execute();
        $seat_result = $seat_query->get_result();
        
        $seat_arr = [];
        $cinema_id_fetched = 0;
        while($row = $seat_result->fetch_assoc()) {
            $seat_arr[] = $row['seat_number'];
            $cinema_id_fetched = $row['cinema_id'];
        }
        $seat_query->close();

        // MANUALLY POPULATE $_REQUEST so the rest of the script works normally
        $_REQUEST['movie_id'] = $booking_data['movie_id'];
        $_REQUEST['cinema_id'] = $cinema_id_fetched;
        $_REQUEST['schedule'] = $booking_data['schedule'];
        $_REQUEST['qty'] = count($seat_arr);
        $_REQUEST['selected_seats'] = implode(', ', $seat_arr);
        $_REQUEST['voucher_id'] = $booking_data['voucher_id'];
        $_REQUEST['discount_amount'] = $booking_data['discount_amount'];
        $_REQUEST['final_price'] = $booking_data['final_price'];
        $_REQUEST['step'] = 4; // Jump straight to payment
    } else {
        echo "<script>alert('Transaction not found or already completed.'); window.location.href='AccountPage.php';</script>";
        exit;
    }
}

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
if ($current_step == 1 && !empty($selected_seats) && !$is_resumed) {
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
                $voucher_message = "<span class='text-green-600 font-bold'><i class='fas fa-check-circle mr-2'></i>Voucher Applied!</span>";
            } else {
                $voucher_message = "<span class='text-red-600'><i class='fas fa-exclamation-circle mr-2'></i>Min spend ₱" . number_format($voucher['min_spend']) . " required.</span>";
            }
        } else {
            $voucher_message = "<span class='text-red-600'><i class='fas fa-times-circle mr-2'></i>Invalid code.</span>";
        }
    }
    $final_price = $subtotal - $discount_amount;
} 

// Calculate progress width based on step position
$progress_width = match($current_step) {
    1 => '0%',      // At Step 1, line starts
    2 => '25%',     // Line reaches Step 2
    3 => '50%',     // Line reaches Step 3
    4 => '75%',     // Line reaches Step 4
    5 => '100%',    // Line reaches Step 5
    default => '0%'
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MoviEase Booking - <?= htmlspecialchars($movie_data['movie_name']) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="../../../../public/styles/css/Checkout.css">
</head>

<body class="p-4 md:p-8">
  <div class="max-w-6xl mx-auto">
    
<!-- Movie Header -->
<div class="movie-header-card">
  <div class="movie-poster-wrapper">
    <img src="../../../../public/assets/images/<?= htmlspecialchars($movie_data['movie_poster']) ?>" 
         alt="<?= htmlspecialchars($movie_data['movie_name']) ?> Poster">
  </div>
  <div class="movie-details">
    <h1 class="movie-title"><?= htmlspecialchars($movie_data['movie_name']) ?></h1>
    <div class="movie-info-item">
      <i class="fas fa-film"></i>
      <span><strong><?= htmlspecialchars($movie_data['genre']) ?></strong></span>
    </div>
    <div class="movie-info-item">
      <i class="fas fa-map-marker-alt"></i>
      <span><?= htmlspecialchars($cinema_data['cinema_address']) ?></span>
    </div>
    <div class="movie-info-item">
      <i class="fas fa-building"></i>
      <span><?= htmlspecialchars($cinema_data['cinema_name']) ?></span>
    </div>
    <div class="movie-info-item">
      <i class="fas fa-calendar-alt"></i>
      <span><?= date("M d, Y • g:i A", strtotime($schedule)) ?></span>
    </div>
    <?php if($current_step >= 3 && !empty($selected_seats)): ?>
    <div class="movie-info-item">
      <i class="fas fa-chair"></i>
      <span>Seats: <strong><?= htmlspecialchars($selected_seats) ?></strong></span>
    </div>
    <?php endif; ?>
  </div>

  <?php
  // Calculate cancellation expiration (3 days before showing)
  $showing_date = new DateTime($schedule);
  $cancellation_deadline = clone $showing_date;
  $cancellation_deadline->modify('-3 days');
  ?>
  
  <div style="position: absolute; top: 20px; right: 20px; background: linear-gradient(135deg, #fee2e2, #fecaca); border: 2px solid #fca5a5; border-radius: 12px; padding: 16px; max-width: 250px; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
      <i class="fas fa-exclamation-triangle" style="color: #991b1b; font-size: 20px;"></i>
      <span style="font-weight: 700; color: #991b1b; font-size: 14px;">CANCELLATION POLICY</span>
    </div>
    <div style="font-size: 12px; color: #7f1d1d; line-height: 1.4;">
      <strong>Last day to cancel:</strong><br>
      <?= $cancellation_deadline->format('M d, Y') ?> at <?= $cancellation_deadline->format('g:i A') ?>
    </div>
    <div style="font-size: 11px; color: #991b1b; margin-top: 8px; font-style: italic;">
      Free cancellation up to 3 days before show time
    </div>
  </div>
</div>

    <!-- Progress Steps -->
    <div class="progress-container">
      <div class="steps-wrapper">
        <!-- Step 1 -->
        <div class="step-item <?= ($current_step >= 1) ? 'completed' : 'disabled' ?>">
          <div class="step-icon <?= ($current_step == 1) ? 'active' : (($current_step > 1) ? 'completed' : 'disabled') ?>">
            <?= ($current_step > 1) ? '<i class="fas fa-check"></i>' : '<i class="fas fa-ticket-alt"></i>' ?>
          </div>
          <div class="step-label">
            <div class="title">Step 1</div>
            <div class="subtitle">Select Tickets</div>
          </div>
        </div>

        <!-- Connector 1-2 -->
        <?php if($current_step > 1): ?>
        <div class="step-connector active" style="left: calc(10% + 25px); width: calc(20% - 50px);"></div>
        <?php else: ?>
        <div class="step-connector" style="left: calc(10% + 25px); width: calc(20% - 50px);"></div>
        <?php endif; ?>

        <!-- Step 2 -->
        <div class="step-item <?= ($current_step >= 2) ? 'completed' : 'disabled' ?>">
          <div class="step-icon <?= ($current_step == 2) ? 'active' : (($current_step > 2) ? 'completed' : 'disabled') ?>">
            <?= ($current_step > 2) ? '<i class="fas fa-check"></i>' : '<i class="fas fa-chair"></i>' ?>
          </div>
          <div class="step-label">
            <div class="title">Step 2</div>
            <div class="subtitle">Select Seats</div>
          </div>
        </div>

        <!-- Connector 2-3 -->
        <?php if($current_step > 2): ?>
        <div class="step-connector active" style="left: calc(30% + 25px); width: calc(20% - 50px);"></div>
        <?php else: ?>
        <div class="step-connector" style="left: calc(30% + 25px); width: calc(20% - 50px);"></div>
        <?php endif; ?>

        <!-- Step 3 -->
        <div class="step-item <?= ($current_step >= 3) ? 'completed' : 'disabled' ?>">
          <div class="step-icon <?= ($current_step == 3) ? 'active' : (($current_step > 3) ? 'completed' : 'disabled') ?>">
            <?= ($current_step > 3) ? '<i class="fas fa-check"></i>' : '<i class="fas fa-tags"></i>' ?>
          </div>
          <div class="step-label">
            <div class="title">Step 3</div>
            <div class="subtitle">Apply Vouchers</div>
          </div>
        </div>

        <!-- Connector 3-4 -->
        <?php if($current_step > 3): ?>
        <div class="step-connector active" style="left: calc(50% + 25px); width: calc(20% - 50px);"></div>
        <?php else: ?>
        <div class="step-connector" style="left: calc(50% + 25px); width: calc(20% - 50px);"></div>
        <?php endif; ?>

        <!-- Step 4 -->
        <div class="step-item <?= ($current_step >= 4) ? 'active' : 'disabled' ?>">
          <div class="step-icon <?= ($current_step == 4) ? 'active' : (($current_step > 4) ? 'completed' : 'disabled') ?>">
            <?= ($current_step > 4) ? '<i class="fas fa-check"></i>' : '<i class="fas fa-credit-card"></i>' ?>
          </div>
          <div class="step-label">
            <div class="title">Step 4</div>
            <div class="subtitle">Confirm Payment</div>
          </div>
        </div>

        <!-- Connector 4-5 -->
        <?php if($current_step > 4): ?>
        <div class="step-connector active" style="left: calc(70% + 25px); width: calc(20% - 50px);"></div>
        <?php else: ?>
        <div class="step-connector" style="left: calc(70% + 25px); width: calc(20% - 50px);"></div>
        <?php endif; ?>

        <!-- Step 5 -->
        <div class="step-item disabled">
          <div class="step-icon disabled">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="step-label">
            <div class="title">Step 5</div>
            <div class="subtitle">Booking Success</div>
          </div>
        </div>
      </div>
    </div>

    <div class="step-content">
      
<?php if ($current_step == 1): ?>
    <!-- STEP 1: SELECT TICKETS -->
    <div class="content-card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;">
        <h2 class="content-title" style="margin-bottom: 0;">
          <i class="fas fa-ticket-alt"></i>
          SELECT TICKETS
          <span class="text-sm font-normal text-red-600">(Available: <?= $seats_available ?>)</span>
        </h2>

        <div style="display: flex; align-items: center; gap: 20px;">
          <label class="quantity-label">Number of Tickets:</label>
          <div class="quantity-controls">
            <button type="button" class="quantity-btn" id="decreaseQty" onclick="decreaseQuantity()">
              <i class="fas fa-minus"></i>
            </button>
            <input type="number" id="ticketQty" class="custom-input quantity-input" 
                   value="<?= $qty ?>" min="1" max="<?= $seats_available ?>" readonly>
            <button type="button" class="quantity-btn" id="increaseQty" onclick="increaseQuantity()">
              <i class="fas fa-plus"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="price-display">
        <div class="price-label">Ticket Price per Person</div>
        <div class="price-amount">₱<?= number_format($ticket_price, 2) ?></div>
      </div>

      <div class="summary-box mt-6">
        <div class="summary-title"><i class="fas fa-calculator mr-2"></i> Price Summary</div>
        <div class="summary-row">
          <span>Ticket Price</span>
          <span>₱<?= number_format($ticket_price, 2) ?></span>
        </div>
        <div class="summary-row">
          <span>Number of Tickets</span>
          <span id="displayQty"><?= $qty ?></span>
        </div>
        <div class="summary-total">
          <span>Total Price</span>
          <span id="totalPrice">₱<?= number_format($ticket_price * $qty, 2) ?></span>
        </div>
      </div>

      <script>
        const ticketPrice = <?= $ticket_price ?>;
        const maxSeats = <?= $seats_available ?>;

        function updateTotal() {
          const qty = parseInt(document.getElementById('ticketQty').value);
          const total = ticketPrice * qty;
          document.getElementById('totalPrice').textContent = '₱' + total.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
          document.getElementById('displayQty').textContent = qty;

          // Disable/enable buttons
          document.getElementById('decreaseQty').disabled = qty <= 1;
          document.getElementById('increaseQty').disabled = qty >= maxSeats;
        }

        function decreaseQuantity() {
          const qtyInput = document.getElementById('ticketQty');
          let currentQty = parseInt(qtyInput.value);
          if (currentQty > 1) {
            qtyInput.value = currentQty - 1;
            updateTotal();
          }
        }

        function increaseQuantity() {
          const qtyInput = document.getElementById('ticketQty');
          let currentQty = parseInt(qtyInput.value);
          if (currentQty < maxSeats) {
            qtyInput.value = currentQty + 1;
            updateTotal();
          }
        }

        updateTotal();
      </script>
    </div>

      <?php elseif ($current_step == 3): ?>
          <!-- STEP 3: APPLY VOUCHERS -->
          <div class="content-card">
            <h2 class="content-title">
              <i class="fas fa-tags"></i>
              APPLY VOUCHERS
            </h2>
            
            <form method="POST" action="Checkout.php">
              <input type="hidden" name="step" value="3">
              <input type="hidden" name="movie_id" value="<?= $movie_id ?>">
              <input type="hidden" name="cinema_id" value="<?= $cinema_id ?>">
              <input type="hidden" name="schedule" value="<?= htmlspecialchars($schedule) ?>">
              <input type="hidden" name="qty" value="<?= $qty ?>">
              <input type="hidden" name="selected_seats" value="<?= htmlspecialchars($selected_seats) ?>">

              <div class="voucher-group">
                <input type="text" name="voucher_code" value="<?= htmlspecialchars($voucher_code) ?>" 
                       placeholder="Enter Voucher Code" class="custom-input voucher-input">
                <button type="submit" name="apply_voucher" class="btn-primary whitespace-nowrap">
                  <i class="fas fa-check mr-2"></i> Apply
                </button>
              </div>
              
              <?php if(!empty($voucher_message)): ?>
              <p class="text-sm mt-3"><?= $voucher_message ?></p>
              <?php endif; ?>
            </form>

            <div class="summary-box mt-8">
              <div class="summary-title"><i class="fas fa-receipt mr-2"></i> Order Summary</div>
              <div class="summary-row">
                <span>Subtotal (<?= $qty ?> items)</span>
                <span>₱<?= number_format($subtotal, 2) ?></span>
              </div>
              <div class="summary-row text-green-600">
                <span>Discount</span>
                <span>-₱<?= number_format($discount_amount, 2) ?></span>
              </div>
              <div class="summary-total">
                <span>Total Amount</span>
                <span>₱<?= number_format($final_price, 2) ?></span>
              </div>
            </div>
          </div>

      <?php elseif ($current_step == 4): ?>
          <!-- STEP 4: CONFIRM PAYMENT -->
          <div class="content-card">
            <h2 class="content-title">
              <i class="fas fa-credit-card"></i>
              CONFIRM PAYMENT
            </h2>
            
            <div class="summary-box">
              <div class="summary-title"><i class="fas fa-receipt mr-2"></i> Order Summary</div>
              <div class="summary-row">
                <span>Tickets (<?= $qty ?>x)</span>
                <span>₱<?= number_format($subtotal, 2) ?></span>
              </div>
              <?php if($discount_amount > 0): ?>
              <div class="summary-row text-green-600">
                <span>Voucher Applied</span>
                <span>-₱<?= number_format($discount_amount, 2) ?></span>
              </div>
              <?php endif; ?>
              <div class="summary-total">
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
              
              <?php if($is_resumed): ?>
                  <input type="hidden" name="resume_ticket_id" value="<?= $ticket_id_resume ?>">
              <?php endif; ?>

              <p class="font-bold mb-3 text-xl text-gray-800">
                <i class="fas fa-wallet mr-2 text-red-600"></i> Select Payment Method:
              </p>
              
              <label class="payment-option">
                <input type="radio" name="payment_method" value="GCash" checked>
                <span class="font-bold">Pay Online with Paymongo</span>
              </label>
            </form>
          </div>

      <?php endif; ?>

    </div>

    <!-- Buttons -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-8">
      
      <?php if ($current_step == 1): ?>
          <button id="prevBtn" class="btn-secondary w-full sm:w-auto" disabled>
            <i class="fas fa-arrow-left mr-2"></i> Previous
          </button>
          <button id="nextBtn" class="btn-primary w-full sm:w-auto" 
                  onclick="window.location.href='SeatsPage.php?movie_id=<?= $movie_id ?>&cinema_id=<?= $cinema_id ?>&schedule=<?= urlencode($schedule) ?>&qty='+document.getElementById('ticketQty').value;">
            Next <i class="fas fa-arrow-right ml-2"></i>
          </button>
      
      <?php elseif ($current_step == 3): ?>
          <button id="prevBtn" type="button" class="btn-secondary w-full sm:w-auto"
              onclick="window.location.href='SeatsPage.php?movie_id=<?= $movie_id ?>&cinema_id=<?= $cinema_id ?>&schedule=<?= urlencode($schedule) ?>&qty=<?= $qty ?>'">
            <i class="fas fa-arrow-left mr-2"></i> Change Seats
          </button>
          
          <form method="POST" action="Checkout.php" class="w-full sm:w-auto">
              <input type="hidden" name="step" value="4">
              <input type="hidden" name="movie_id" value="<?= $movie_id ?>">
              <input type="hidden" name="cinema_id" value="<?= $cinema_id ?>">
              <input type="hidden" name="schedule" value="<?= htmlspecialchars($schedule) ?>">
              <input type="hidden" name="qty" value="<?= $qty ?>">
              <input type="hidden" name="selected_seats" value="<?= htmlspecialchars($selected_seats) ?>">
              <input type="hidden" name="voucher_id" value="<?= $voucher_id ?>">
              <input type="hidden" name="discount_amount" value="<?= $discount_amount ?>">
              <input type="hidden" name="final_price" value="<?= $final_price ?>">
              
              <button id="nextBtn" type="submit" class="btn-primary w-full">
                Proceed to Payment <i class="fas fa-arrow-right ml-2"></i>
              </button>
          </form>

      <?php elseif ($current_step == 4): ?>
          <?php if($is_resumed): ?>
              <button id="prevBtn" class="btn-secondary w-full sm:w-auto" 
                      onclick="window.location.href='AccountPage.php'">
                <i class="fas fa-arrow-left mr-2"></i> Back
              </button>
          <?php else: ?>
              <form method="POST" action="Checkout.php" class="w-full sm:w-auto">
                  <input type="hidden" name="step" value="3">
                  <input type="hidden" name="movie_id" value="<?= $movie_id ?>">
                  <input type="hidden" name="cinema_id" value="<?= $cinema_id ?>">
                  <input type="hidden" name="schedule" value="<?= htmlspecialchars($schedule) ?>">
                  <input type="hidden" name="qty" value="<?= $qty ?>">
                  <input type="hidden" name="selected_seats" value="<?= htmlspecialchars($selected_seats) ?>">
                  <input type="hidden" name="voucher_id" value="<?= $voucher_id ?>">
                  <button id="prevBtn" type="submit" class="btn-secondary w-full">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                  </button>
              </form>
          <?php endif; ?>

          <button id="nextBtn" class="btn-primary w-full sm:w-auto" 
                  onclick="document.getElementById('paymentForm').submit()">
            <i class="fas fa-lock mr-2"></i> Pay & Book
          </button>
      <?php endif; ?>

    </div>

  </div>
</body>
</html>