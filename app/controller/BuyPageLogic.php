<?php
// ... (Includes and DB connection remain the same) ...
// Start session for potential user data/errors
session_start();

// Path from view/pages/ to app/core/db.php (Adjust as needed based on your file structure)
require_once __DIR__ . '../core/db.php'; 

$database = new Database();
$con = $database->getConnection(); // mysqli connection

// --- 1. Get IDs from URL ---
$movie_id = $_GET['movie_id'] ?? 0;
// We now allow cinema_id to be optional for the initial view
$cinema_id = $_GET['cinema_id'] ?? 0; 

if ($movie_id == 0) {
    die("Error: Missing Movie ID.");
}

// --- Fetch Movie Details (Required) ---
// ... (movie details query remains the same) ...
// ... (Assuming $movie_details is populated) ...

$cinemas_list = [];
$cinema_details = [];
$schedules = [];

// --- 2. If NO cinema is selected, fetch the list of cinemas showing the movie ---
if ($cinema_id == 0) {
    $cinemas_query = "
        SELECT DISTINCT c.cinema_id, c.cinema_name, c.cinema_address
        FROM cinemas c
        JOIN cinema_movies cm ON c.cinema_id = cm.cinema_id
        WHERE cm.movie_id = ?
        ORDER BY c.cinema_name ASC
    ";
    $stmt_cinemas = $con->prepare($cinemas_query);
    $stmt_cinemas->bind_param("i", $movie_id);
    $stmt_cinemas->execute();
    $cinemas_list = $stmt_cinemas->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_cinemas->close();

    // If only one cinema is found, automatically select it
    if (count($cinemas_list) == 1) {
        $cinema_id = $cinemas_list[0]['cinema_id'];
    }
}

// --- 3. If a cinema IS selected (or automatically selected) fetch schedules ---
if ($cinema_id != 0) {
    // a. Fetch Cinema Details
    // ... (cinema details query remains the same, populate $cinema_details) ...
    $cinema_query = "SELECT cinema_name, cinema_address FROM cinemas WHERE cinema_id = ?";
    $stmt_cinema = $con->prepare($cinema_query);
    $stmt_cinema->bind_param("i", $cinema_id);
    $stmt_cinema->execute();
    $cinema_details = $stmt_cinema->get_result()->fetch_assoc();
    $stmt_cinema->close();
    
    // b. Fetch Schedules
    $schedule_query = "
        SELECT id, showtime, ticket_price 
        FROM cinema_movies 
        WHERE movie_id = ? AND cinema_id = ? 
        AND showtime >= NOW()
        ORDER BY showtime ASC
    ";
    // ... (The rest of the schedule fetching and grouping logic remains the same) ...
    
    $stmt_schedule = $con->prepare($schedule_query);
    $stmt_schedule->bind_param("ii", $movie_id, $cinema_id);
    $stmt_schedule->execute();
    $schedule_result = $stmt_schedule->get_result();
    
    // ... (Populate $schedules array here) ...
}

// Data is now ready in $movie_details, $cinema_details, $cinemas_list, and $schedules
?>