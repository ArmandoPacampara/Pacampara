<?php
// BuyPage.php
// Connect to the database
include '../../../../app/core/db.php'; 

// --- 1. Get Movie ID from URL ---
// The movie_id is passed from the MoviesPage.php pop-up link: BuyPage.php?movie_id=X
$movie_id = isset($_GET['movie_id']) ? intval($_GET['movie_id']) : 0; 

if ($movie_id === 0) {
    die("Error: No movie ID provided.");
}

// --- 2. Fetch Movie Details ---
$movie_query = $con->prepare("SELECT movie_name, movie_poster FROM movies WHERE movie_id = ?");
$movie_query->bind_param("i", $movie_id);
$movie_query->execute();
$movie_result = $movie_query->get_result();
$movie_data = $movie_result->fetch_assoc();
$movie_query->close();

if (!$movie_data) {
    die("Error: Movie not found.");
}

$movie_name = htmlspecialchars($movie_data['movie_name']);
$movie_poster = htmlspecialchars($movie_data['movie_poster']);

// --- 3. Fetch Schedules Grouped by Cinema and Date (The core logic) ---
// This complex query joins the movie, cinema, and schedule data.
$schedule_query = $con->prepare("
    SELECT 
        c.cinema_id,
        c.cinema_name,
        c.cinema_address,
        DATE(cm.showtime) AS show_date, 
        TIME_FORMAT(cm.showtime, '%h:%i%p') AS show_time_ampm,
        cm.showtime AS full_showtime
    FROM cinema_movies cm
    JOIN cinemas c ON cm.cinema_id = c.cinema_id
    WHERE cm.movie_id = ? 
    ORDER BY c.cinema_name, show_date, TIME(cm.showtime)
");
$schedule_query->bind_param("i", $movie_id);
$schedule_query->execute();
$schedule_result = $schedule_query->get_result();

// Group results by Cinema, then by Date
$schedules_grouped = [];
while ($schedule = $schedule_result->fetch_assoc()) {
    $cinema_name = $schedule['cinema_name'];
    $show_date = $schedule['show_date'];
    
    if (!isset($schedules_grouped[$cinema_name])) {
        $schedules_grouped[$cinema_name] = [
            'id' => $schedule['cinema_id'],
            'address' => $schedule['cinema_address'],
            'dates' => []
        ];
    }
    
    $schedules_grouped[$cinema_name]['dates'][$show_date][] = [
        'time' => $schedule['show_time_ampm'],
        'full_showtime' => $schedule['full_showtime']
    ];
}
$schedule_query->close();

// Function to format the date header 
function format_date_header($date_string) {
    $timestamp = strtotime($date_string);
    $day_name = strtoupper(date('l', $timestamp)); 
    $date_format = strtoupper(date('M d, Y', $timestamp)); 
    return ['day' => $day_name, 'date' => $date_format];
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Buy Tickets for <?= $movie_name ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="ml-20 mr-20 mt-10 mb-10">
    
    <div class="w-full h-[500px] mb-10">
      <span class="flex flex-row justify-between w-full h-fit items-center">
        <h1 class="text-4xl font-bold text-red-700">Book Tickets for <?= $movie_name ?></h1> 
        <h1
          class="text-xl font-medium text-gray-700 bg-gray-200 border border-gray-600 rounded-2xl pl-7 pr-7 pt-2 pb-2"
        >
          Select Cinema & Time Below
        </h1>
      </span>
      <div class="h-[400px] p-5">
        <img
          class="rounded-2xl object-cover w-full h-full"
          src="../../../../public/assets/images/<?= $movie_poster ?>"
          alt="<?= $movie_name ?> Poster"
        />
      </div>
    </div>
    
    <div class="flex flex-row justify-between items-center w-full h-fit mb-5">
      <div class="bg-gray-500 w-[42%] h-[1px]"></div>
      <h1 class="text-xl text-gray-700">AVAILABLE CINEMAS & SCHEDULES</h1>
      <div class="bg-gray-500 w-[42%] h-[1px]"></div>
    </div>

    <?php foreach ($schedules_grouped as $cinema_name => $cinema_info): ?>
        
        <h2 class="text-2xl font-bold text-gray-800 mt-8 mb-4 border-b-2 border-red-700 pb-2">
            <?= $cinema_name ?> <span class="text-base font-normal text-gray-600">(<?= $cinema_info['address'] ?>)</span>
        </h2>
        
        <?php foreach ($cinema_info['dates'] as $date_string => $showtimes): ?>
            <?php $header = format_date_header($date_string); ?>
            
            <div
              class="flex justify-between items-center w-full h-[70px] bg-red-700 rounded-xl p-4 mb-3"
            >
              <span
                class="flex justify-between items-center flex-nowrap h-fit w-[280px]"
              >
                <h1 class="font-bold text-white text-xl"><?= $header['day'] ?></h1>
                <h1 class="font-light text-white text-xl"><?= $header['date'] ?></h1>
              </span>
              
              <div
                class="flex flex-row flex-nowrap justify-start items-center w-[60%] gap-2 overflow-x-auto p-2"
              >
                <?php foreach ($showtimes as $time): ?>
                <a 
                  href="SeatsPage.php?movie_id=<?= $movie_id ?>&cinema_id=<?= $cinema_info['id'] ?>&schedule=<?= urlencode($time['full_showtime']) ?>"
                  class="flex justify-center items-center bg-gray-200 rounded-xl text-red-700 font-bold p-2 min-w-[100px] hover:bg-white duration-200 text-center whitespace-nowrap"
                >
                  <?= $time['time'] ?>
                </a>
                <?php endforeach; ?>
              </div>
              
              <a
                class="flex justify-center items-center bg-red-500 rounded-xl text-white font-bold p-2 w-[100px] hover:bg-red-400 duration-200"
                href="Checkout.php?movie_id=<?= $movie_id ?>&cinema_id=<?= $cinema_info['id'] ?>&schedule=<?= urlencode($showtimes[0]['full_showtime']) ?>"
              >
                BUY
              </a>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <?php if (empty($schedules_grouped)): ?>
        <p class="text-center text-xl text-gray-500 mt-10">No schedules found for this movie.</p>
    <?php endif; ?>

  </body>
</html>