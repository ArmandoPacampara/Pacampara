<?php
// ProcessPayment.php
session_start();
// Enable Error Reporting (Turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../../app/core/db.php';

// --- CONFIGURATION ---
// REPLACE THIS WITH YOUR ACTUAL TEST KEY
$paymongo_secret_key = 'sk_test_7nEvkxPN6zoadN1mM8QJAd1S'; 

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

// --- 1. RECEIVE DATA ---
if (empty($_POST['movie_id'])) {
    die("Error: No form data received. Make sure you clicked 'Pay & Book' inside the form.");
}

$movie_id        = $_POST['movie_id'];
$cinema_id       = $_POST['cinema_id'];
$schedule        = $_POST['schedule'];
$qty             = $_POST['qty'];
$selected_seats  = $_POST['selected_seats']; // "A1,A2"
$voucher_id      = !empty($_POST['voucher_id']) ? $_POST['voucher_id'] : NULL;
$discount_amount = $_POST['discount_amount'];
$final_price     = $_POST['final_price'];
$user_id         = $_SESSION['user_id'];

// --- 2. SAVE BOOKING TO DATABASE (PENDING) ---
$con->begin_transaction();

try {
    // A. Insert into Booking Table
    $stmt = $con->prepare("
        INSERT INTO booking (user_id, movie_id, date_booked, schedule, price, voucher_id, discount_amount, final_price, status, payment_method) 
        VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, 'Pending', 'PayMongo')
    ");
    
    // Check if prepare failed
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $con->error);
    }

    $stmt->bind_param("iisiddd", $user_id, $movie_id, $schedule, $final_price, $voucher_id, $discount_amount, $final_price);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $ticket_id = $con->insert_id;
    $stmt->close();

    // B. Insert into Booked Seats Table
    $seat_arr = explode(',', $selected_seats);
    
    foreach ($seat_arr as $seat_num) {
        $seat_num = trim($seat_num);
        
        // Find seat ID
        $s_stmt = $con->prepare("SELECT seat_id FROM seats WHERE seat_number = ? AND cinema_id = ?");
        $s_stmt->bind_param("si", $seat_num, $cinema_id);
        $s_stmt->execute();
        $res = $s_stmt->get_result()->fetch_assoc();
        
        if (!$res) {
            throw new Exception("Seat $seat_num not found for this cinema.");
        }
        $current_seat_id = $res['seat_id'];
        $s_stmt->close();

        // Insert Link
        $bs_stmt = $con->prepare("INSERT INTO booked_seats (ticket_id, seat_id) VALUES (?, ?)");
        $bs_stmt->bind_param("ii", $ticket_id, $current_seat_id);
        $bs_stmt->execute();
        $bs_stmt->close();
    }

    $con->commit();

} catch (Exception $e) {
    $con->rollback();
    die("Database Error: " . $e->getMessage());
}

// --- 3. CALL PAYMONGO API ---

// PayMongo amount is in CENTAVOS (e.g. 100.00 -> 10000)
// Ensure at least 100 pesos (10000 centavos) for testing if having issues, 
// strictly calculating based on input here:
$amount_in_centavos = intval(floatval($final_price) * 100);

// Basic payload validation
if ($amount_in_centavos < 2000) { // PayMongo minimum is usually 20.00 PHP (2000 centavos)
    die("Error: Amount too low for PayMongo. Minimum is ₱20.00");
}

$payload = [
    'data' => [
        'attributes' => [
            'line_items' => [
                [
                    'currency' => 'PHP',
                    'amount' => $amount_in_centavos,
                    'description' => 'Booking #' . $ticket_id,
                    'name' => 'Movie Ticket',
                    'quantity' => 1
                ]
            ],
            'payment_method_types' => ['card', 'gcash', 'paymaya', 'grab_pay'],
            'success_url' => "http://localhost/moviease/app/view/pages/BookingSuccess.php?ticket_id=$ticket_id",
            'cancel_url' => "http://localhost/moviease/app/view/pages/Checkout.php",
            'description' => 'Payment for Ticket ID ' . $ticket_id
        ]
    ]
];

$ch = curl_init('https://api.paymongo.com/v1/checkout_sessions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode($paymongo_secret_key . ':')
]);

// DISABLE SSL VERIFICATION FOR LOCALHOST (Fixes "Nothing happens" issues)
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    die("cURL Connection Error: " . $curl_error);
}

$json = json_decode($response, true);

// Check if PayMongo returned an error
if (isset($json['errors'])) {
    echo "<h3>PayMongo API Error:</h3>";
    echo "<pre>";
    print_r($json['errors']);
    echo "</pre>";
    // Cleanup pending booking
    $con->query("DELETE FROM booking WHERE ticket_id = $ticket_id");
    exit;
}

// --- 4. REDIRECT TO PAYMONGO ---
if (isset($json['data']['attributes']['checkout_url'])) {
    $checkout_url = $json['data']['attributes']['checkout_url'];
    header("Location: " . $checkout_url);
    exit;
} else {
    // Fallback error
    echo "<h3>Unexpected Response:</h3>";
    var_dump($json);
    exit;
}
?>