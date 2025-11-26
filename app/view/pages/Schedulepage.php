<?php
// SchedulePage.php
// Connect to the database
include '../../../app/core/db.php'; 

// --- 1. Fetch All Schedules ---
// Query to get all movies, cinemas, and showtimes, sorted by movie name, cinema name, and then time.
$schedule_query = $con->query("
    SELECT 
        m.movie_id,
        m.movie_name,
        m.movie_poster,
        c.cinema_id,
        c.cinema_name,
        c.cinema_address,
        cm.showtime
    FROM cinema_movies cm
    JOIN movies m ON cm.movie_id = m.movie_id
    JOIN cinemas c ON cm.cinema_id = c.cinema_id
    ORDER BY m.movie_name, c.cinema_name, cm.showtime
");

// --- 2. Group Schedules into a Nested Array ---
$schedules_grouped = [];
if ($schedule_query && $schedule_query->num_rows > 0) {
    while ($row = $schedule_query->fetch_assoc()) {
        $movie_id = $row['movie_id'];
        $cinema_id = $row['cinema_id'];
        $show_date = date('Y-m-d', strtotime($row['showtime']));
        $show_time_ampm = date('h:i A', strtotime($row['showtime']));
        
        // Group by Movie
        if (!isset($schedules_grouped[$movie_id])) {
            $schedules_grouped[$movie_id] = [
                'name' => $row['movie_name'],
                'poster' => $row['movie_poster'],
                'cinemas' => []
            ];
        }

        // Group by Cinema within the Movie
        if (!isset($schedules_grouped[$movie_id]['cinemas'][$cinema_id])) {
            $schedules_grouped[$movie_id]['cinemas'][$cinema_id] = [
                'name' => $row['cinema_name'],
                'address' => $row['cinema_address'],
                'schedules_by_date' => []
            ];
        }

        // Group by Date within the Cinema
        if (!isset($schedules_grouped[$movie_id]['cinemas'][$cinema_id]['schedules_by_date'][$show_date])) {
             $schedules_grouped[$movie_id]['cinemas'][$cinema_id]['schedules_by_date'][$show_date] = [];
        }
        
        // Add the showtime
        $schedules_grouped[$movie_id]['cinemas'][$cinema_id]['schedules_by_date'][$show_date][] = [
            'time_display' => $show_time_ampm,
            'full_showtime' => $row['showtime'] // full DATETIME for the booking link
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Movie Schedules - MoviEase</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .schedule-time-button {
            transition: background-color 0.2s, transform 0.1s;
            @apply bg-red-100 text-red-700 font-medium py-1 px-3 rounded-lg text-sm border border-red-200;
        }
        .schedule-time-button:hover {
            @apply bg-red-700 text-white shadow-md;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <div class="max-w-6xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-extrabold text-gray-800 mb-10 border-b-4 border-red-700 pb-3">
            <span class="text-red-700">Daily</span> Showtimes
        </h1>

        <?php if (empty($schedules_grouped)): ?>
            <div class="text-center py-12 bg-white rounded-xl shadow-lg">
                <p class="text-xl text-gray-500">No movie schedules are currently available.</p>
            </div>
        <?php else: ?>

            <?php foreach ($schedules_grouped as $movie_id => $movie): ?>
            
                <div class="bg-white rounded-xl shadow-xl overflow-hidden mb-12 border border-gray-100">
                    <div class="flex items-center p-6 bg-red-800 text-white">
                        <img 
                            src="../../../public/assets/images/<?= htmlspecialchars($movie['poster']) ?>" 
                            alt="<?= htmlspecialchars($movie['name']) ?> Poster"
                            class="w-16 h-20 object-cover rounded-md shadow-lg mr-6"
                            onerror="this.onerror=null;this.src='https://placehold.co/64x80/2f3640/white?text=Poster';"
                        >
                        <h2 class="text-3xl font-bold"><?= htmlspecialchars($movie['name']) ?></h2>
                    </div>

                    <div class="p-6">
                        <?php foreach ($movie['cinemas'] as $cinema_id => $cinema): ?>
                            <div class="mb-8 border-b pb-4 last:border-b-0 last:pb-0">
                                <h3 class="text-xl font-semibold text-gray-700 mb-4 flex justify-between items-center">
                                    <span class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                        </svg>
                                        <?= htmlspecialchars($cinema['name']) ?>
                                    </span>
                                    <span class="text-sm font-normal text-gray-500"><?= htmlspecialchars($cinema['address']) ?></span>
                                </h3>

                                <div class="space-y-4">
                                    <?php foreach ($cinema['schedules_by_date'] as $date_string => $showtimes): ?>
                                        <?php $date_display = date('l, M d', strtotime($date_string)); ?>

                                        <div class="flex flex-col md:flex-row md:items-center bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-100">
                                            <div class="md:w-1/4 mb-2 md:mb-0">
                                                <span class="font-bold text-red-600"><?= $date_display ?></span>
                                            </div>
                                            <div class="flex flex-wrap gap-2 md:w-3/4">
                                                <?php foreach ($showtimes as $time): ?>
                                                    <a 
                                                        href="BuyPage.php?movie_id=<?= $movie_id ?>&cinema_id=<?= $cinema_id ?>&schedule=<?= urlencode($time['full_showtime']) ?>" 
                                                        class="schedule-time-button"
                                                    >
                                                        <?= $time['time_display'] ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php endforeach; ?>

        <?php endif; ?>
    </div>
</body>
</html>