<?php
include '../../../../app/core/db.php'; 

$movie_id = isset($_GET['movie_id']) ? intval($_GET['movie_id']) : 0; 
if ($movie_id === 0) die("Error: No movie ID provided.");

function getBestViewingTimes($movie_id) {
    $api_url = "http://127.0.0.1:5000/predict_best_time?movie_id=" . $movie_id;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3); 
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code === 200 && $response) {
        $data = json_decode($response, true);
        return $data['error'] ?? $data;
    } else {
        return []; 
    }
}
$ai_suggestions = getBestViewingTimes($movie_id);

// --- 3. Fetch Movie Details ---
$movie_query = $con->prepare("
    SELECT movie_name, movie_poster, movie_trailer, genre, movie_description, movie_class
    FROM movies 
    WHERE movie_id = ?
");
$movie_query->bind_param("i", $movie_id);
$movie_query->execute();
$movie_result = $movie_query->get_result();
$movie_data = $movie_result->fetch_assoc();
$movie_query->close();

// Assign variables
$movie_name = htmlspecialchars($movie_data['movie_name']);
$movie_poster = htmlspecialchars($movie_data['movie_poster']);
$movie_trailer = htmlspecialchars($movie_data['movie_trailer']);
$genre = htmlspecialchars($movie_data['genre']);
$movie_description = htmlspecialchars($movie_data['movie_description']);

// --- 3a. Convert YouTube URL to embed ---
function convertYoutubeToEmbed($url) {
    if (preg_match("/v=([a-zA-Z0-9_-]+)/", $url, $matches)) {
        return "https://www.youtube.com/embed/" . $matches[1];
    }
    return $url;
}
$movie_trailer_embed = $movie_trailer ? convertYoutubeToEmbed($movie_trailer) : null;

// --- 4. Fetch Schedules Grouped by Cinema and Date ---
$schedule_query = $con->prepare("
    SELECT c.cinema_id, c.cinema_name, c.cinema_address,
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="ml-20 mr-20 mt-10 mb-10">

    <!-- AI Smart Schedule Pop-up -->
    <div id="ai-pop-up" class="hidden flex-row justify-center items-center fixed inset-0 bg-black/60 w-screen h-screen z-50 backdrop-blur-sm">
        <div class="relative bg-white w-[50%] max-w-[800px] rounded-3xl shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-red-800 to-red-600 p-6 flex justify-between items-center">
                <div class="text-white">
                    <h2 class="text-2xl font-extrabold flex items-center gap-2">
                        <i class="fas fa-sparkles text-yellow-300"></i> Best Time to Watch
                    </h2>
                    <p class="text-red-100 text-sm opacity-90">
                        Top recommended schedules for <strong><?= $movie_name ?></strong>
                    </p>
                </div>
                <button id="close-ai-popup" class="text-white/80 hover:text-white text-3xl font-bold">&times;</button>
            </div>
            <div class="p-6 bg-gray-50 min-h-[300px] max-h-[500px] overflow-y-auto">
                <?php if (empty($ai_suggestions)): ?>
                    <div class="flex flex-col items-center justify-center h-full text-gray-500 mt-10">
                        <i class="fas fa-calendar-times text-4xl mb-3 text-gray-300"></i>
                        <p class="font-semibold">No optimal schedules found for this movie.</p>
                        <p class="text-sm">Try checking other dates manually.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 gap-4">
                        <?php foreach ($ai_suggestions as $index => $slot): ?>
                            <?php
                                $rankColor = match($index) {
                                    0 => 'border-yellow-400 bg-yellow-50',
                                    1 => 'border-gray-300 bg-white',
                                    default => 'border-gray-200 bg-white'
                                };
                                $badge = $index === 0 ? '<span class="bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-1 rounded">BEST CHOICE</span>' : '';
                                $posterUrl = "../../../../public/assets/images/" . htmlspecialchars($slot['movie_poster']);
                                $bookLink = "Checkout.php?movie_id={$slot['movie_id']}&cinema_id={$slot['cinema_id']}&schedule=" . urlencode($slot['full_date']);
                            ?>
                            <div class="flex items-center p-3 rounded-xl border-l-4 shadow-sm hover:shadow-md transition <?= $rankColor ?>">
                                <img src="<?= $posterUrl ?>" class="w-16 h-24 object-cover rounded-lg shadow-sm mr-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="font-extrabold text-lg text-gray-800 leading-tight"><?= htmlspecialchars($slot['movie_name']) ?></h3>
                                        <?= $badge ?>
                                    </div>
                                    <p class="text-sm text-gray-600 font-semibold mb-1">
                                        <i class="fas fa-map-marker-alt text-red-500 mr-1"></i> <?= htmlspecialchars($slot['cinema_name']) ?>
                                    </p>
                                    <div class="flex items-center gap-4 text-xs text-gray-500 mt-2">
                                        <span class="bg-gray-200 px-2 py-1 rounded text-gray-700 font-bold">
                                            <?= $slot['display_date'] ?> • <?= $slot['display_time'] ?>
                                        </span>
                                        <span class="flex items-center gap-1"><i class="fas fa-users text-blue-500"></i> <?= $slot['predicted_crowd'] ?></span>
                                        <span class="flex items-center gap-1"><i class="fas fa-cloud-sun text-orange-500"></i> <?= $slot['weather'] ?></span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-center justify-center gap-2 pl-4 border-l border-gray-200">
                                    <div class="flex flex-col items-center">
                                        <span class="text-2xl font-extrabold text-green-600"><?= intval($slot['score']) ?></span>
                                        <span class="text-[9px] text-gray-400 uppercase font-bold">AI Score</span>
                                    </div>
                                    <a href="<?= $bookLink ?>" class="bg-red-700 hover:bg-red-800 text-white text-xs font-bold px-3 py-2 rounded-lg transition shadow">BOOK</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="bg-gray-100 p-4 text-center border-t border-gray-200">
                <button onclick="document.getElementById('ai-pop-up').classList.add('hidden'); document.getElementById('ai-pop-up').classList.remove('flex');" 
                        class="text-red-700 font-bold hover:underline text-sm">Close</button>
            </div>
        </div>
    </div>

    <!-- Movie Poster / Trailer -->
    <div class="w-full h-[500px] mb-10">
        <span class="flex flex-row justify-between w-full h-fit items-center">
            <h1 class="text-4xl font-bold text-red-700">Book Tickets for <?= $movie_name ?></h1> 
            <button id="btn-smart-schedule" class="flex items-center gap-2 bg-yellow-500 text-white font-bold px-5 py-2 rounded-lg hover:bg-yellow-600 transition h-[45px] shadow-md transform hover:scale-105">
                <i class="fas fa-magic"></i> Smart Schedule
            </button>
        </span>

        <div class="flex gap-8 mb-10 h-[450px] mt-6">

        <!-- Left: Movie Poster -->
        <div class="relative flex-shrink-0 w-[280px] h-[450px] rounded-2xl overflow-hidden shadow-lg">

            <!-- Movie Poster Image -->
            <img
                src="../../../../public/assets/images/<?= $movie_poster ?>"
                alt="<?= htmlspecialchars($movie_name) ?> Poster"
                class="w-full h-full object-cover"
            />

            <?php
                $badgeColor = match ($movie_data['movie_class']) {
                    'G'   => 'bg-green-600',
                    'PG'  => 'bg-blue-600',
                    'R13' => 'bg-yellow-500',
                    'R16' => 'bg-orange-600',
                    'R18' => 'bg-red-600',
                    default => 'bg-gray-600',
                };
            ?>

            <!-- MOVIE CLASS (upper right corner) -->
            <div class="absolute top-3 right-3 <?= $badgeColor ?> text-white px-3 py-1 rounded-lg font-bold shadow">
                <?= htmlspecialchars($movie_data['movie_class']); ?>
            </div>

            <!-- MOVIE GENRE (center bottom) -->
            <div class="absolute bottom-0 w-full bg-gradient-to-t from-black via-black/60 to-transparent p-4 text-center">
                <p class="text-white font-semibold text-sm"><?= htmlspecialchars($genre); ?></p>
            </div>

        </div>


            <!-- Right: Trailer + Description -->
            <div class="flex-grow flex flex-col rounded-2xl shadow-lg overflow-hidden">

                <!-- Movie Title & Genre -->
                <div class="bg-red-700 p-4">
                    <h1 class="text-3xl font-bold text-white pl-3"><?= htmlspecialchars($movie_name) ?></h1>
                </div>

                <!-- Trailer -->
                <div class="flex-grow bg-black">
                    <?php if (!empty($movie_trailer_embed)): ?>
                        <iframe
                            class="w-full h-full"
                            src="<?= $movie_trailer_embed ?>?autoplay=1"
                            title="<?= htmlspecialchars($movie_name) ?> Trailer"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            Trailer not available
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="p-4 bg-white border-t border-gray-200">
                    <p class="text-gray-700"><?= nl2br(htmlspecialchars_decode($movie_description)) ?></p>
                </div>
                
            </div>
        </div>
    </div>
    <!-- Cinemas & Schedules -->
    <div class="flex flex-row justify-between items-center w-full h-fit mt-12 mb-5">
        <div class="bg-gray-500 w-[37%] h-[1px]"></div>
        <h1 class="text-xl text-gray-700">AVAILABLE CINEMAS & SCHEDULES</h1>
        <div class="bg-gray-500 w-[37%] h-[1px]"></div>
    </div>

    <?php foreach ($schedules_grouped as $cinema_name => $cinema_info): ?>
        <h2 class="text-2xl font-bold text-gray-800 mt-8 mb-4 border-b-2 border-red-700 pb-2">
            <?= $cinema_name ?> <span class="text-base font-normal text-gray-600">(<?= $cinema_info['address'] ?>)</span>
        </h2>
        <?php foreach ($cinema_info['dates'] as $date_string => $showtimes): ?>
            <?php $header = format_date_header($date_string); ?>
            <div class="flex justify-between items-center w-full h-[70px] bg-red-700 rounded-xl p-4 mb-3">
                <span class="flex justify-between items-center flex-nowrap h-fit w-[280px]">
                    <h1 class="font-bold text-white text-xl"><?= $header['day'] ?></h1>
                    <h1 class="font-light text-white text-xl"><?= $header['date'] ?></h1>
                </span>
                <div class="flex flex-row flex-nowrap justify-start items-center w-[60%] gap-2 overflow-x-auto p-2">
                    <?php foreach ($showtimes as $time): ?>
                    <a href="SeatsPage.php?movie_id=<?= $movie_id ?>&cinema_id=<?= $cinema_info['id'] ?>&schedule=<?= urlencode($time['full_showtime']) ?>"
                       class="flex justify-center items-center bg-gray-200 rounded-xl text-red-700 font-bold p-2 min-w-[100px] hover:bg-white duration-200 text-center whitespace-nowrap">
                        <?= $time['time'] ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <a class="flex justify-center items-center bg-red-500 rounded-xl text-white font-bold p-2 w-[100px] hover:bg-red-400 duration-200"
                   href="Checkout.php?movie_id=<?= $movie_id ?>&cinema_id=<?= $cinema_info['id'] ?>&schedule=<?= urlencode($showtimes[0]['full_showtime']) ?>">
                    BUY
                </a>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <?php if (empty($schedules_grouped)): ?>
        <p class="text-center text-xl text-gray-500 mt-10">No schedules found for this movie.</p>
    <?php endif; ?>

    <!-- Scripts -->
    <script>
        const aiBtn = document.getElementById("btn-smart-schedule");
        const aiPopup = document.getElementById("ai-pop-up");
        const closeAiPopup = document.getElementById("close-ai-popup");

        if (aiBtn && aiPopup) {
            aiBtn.addEventListener("click", () => {
                aiPopup.classList.remove("hidden");
                aiPopup.classList.add("flex");
            });
            closeAiPopup.addEventListener("click", () => {
                aiPopup.classList.add("hidden");
                aiPopup.classList.remove("flex");
            });
            aiPopup.addEventListener("click", (e) => {
                if (e.target.id === "ai-pop-up") {
                    aiPopup.classList.add("hidden");
                    aiPopup.classList.remove("flex");
                }
            });
        }
    </script>
</body>
</html>
