<?php
// MoviesPage.php
session_start();
// Prevent Browser Caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");




include '../../../../app/core/db.php';




$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;


$you_might_like_result = null;


if ($user_id > 0) {
    // A. FETCH FRESH PREFERENCES FROM DB
    $pref_stmt = $con->prepare("SELECT user_genre FROM users WHERE user_id = ?");
    $pref_stmt->bind_param("i", $user_id);
    $pref_stmt->execute();
    $pref_res = $pref_stmt->get_result()->fetch_assoc();
    $pref_stmt->close();




    $db_genre_string = $pref_res['user_genre'] ?? '';
   
    // Convert "Action, Horror" -> Array ['Action', 'Horror']
    $preferred_genres = [];
    if (!empty($db_genre_string)) {
        $preferred_genres = array_map('trim', explode(',', $db_genre_string));
    }




    // B. EXCLUDE MOVIES ALREADY BOOKED
    $booked_ids = [0];
    $book_stmt = $con->prepare("SELECT DISTINCT movie_id FROM booking WHERE user_id = ?");
    $book_stmt->bind_param("i", $user_id);
    $book_stmt->execute();
    $book_res = $book_stmt->get_result();
    while($row = $book_res->fetch_assoc()) {
        $booked_ids[] = $row['movie_id'];
    }
    $book_stmt->close();
   
    $booked_ids_str = implode(',', $booked_ids);




    // C. BUILD THE QUERY
    if (!empty($preferred_genres)) {
        $genre_clauses = [];
        foreach ($preferred_genres as $genre) {
            $safe_genre = $con->real_escape_string($genre);
            $genre_clauses[] = "genre = '$safe_genre'";
        }
        $sql_genre_part = implode(' OR ', $genre_clauses);




        // Include both Now Showing and Coming Soon in recommendations
        $rec_sql = "SELECT * FROM movies
                    WHERE ($sql_genre_part)
                    AND movie_id NOT IN ($booked_ids_str)
                    AND movie_status IN ('Now Showing', 'Coming Soon')
                    ORDER BY RAND() LIMIT 5";
                   
        $you_might_like_result = $con->query($rec_sql);
    }
}




// 2. FALLBACK
if (!$you_might_like_result || $you_might_like_result->num_rows === 0) {
    $you_might_like_result = $con->query("SELECT * FROM movies WHERE movie_status IN ('Now Showing', 'Coming Soon') ORDER BY RAND() LIMIT 5");
}




// 3. STANDARD LISTS
$now_showing_result = $con->query("SELECT * FROM movies WHERE movie_status='Now Showing'");
$coming_soon_result = $con->query("SELECT * FROM movies WHERE movie_status='Coming Soon'");
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Movies</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .shadow-strong {
      box-shadow: 0 0 12px rgba(0,0,0,0.4);
    }
  </style>
</head>


<body class="flex flex-col justify-start items-center w-screen h-screen m-0 bg-gray-100 overflow-x-hidden">




<div class="w-[95%] flex items-center justify-between px-10 py-4 bg-white shadow-md mt-[10px]">


  <div class="flex items-center gap-2">
    <label for="genres" class="text-xl text-red-700 font-bold">Genre:</label>
    <select id="genres" class="border bg-red-700 border-red-600 rounded-lg text-white px-5 py-2 h-[40px]">
      <option class="hover:bg-red-600">Show All Genres</option>
      <option class="hover:bg-red-600">Action</option>
      <option class="hover:bg-red-600">Comedy</option>
      <option class="hover:bg-red-600">Horror</option>
      <option class="hover:bg-red-600">Drama</option>
      <option class="hover:bg-red-600">Romance</option>
    </select>
  </div>


  <div class="flex-1 mx-6">
    <div class="flex w-full">
      <input type="text" placeholder="Search movies..."
             class="flex-1 border border-red-300 rounded-l-lg px-4 py-2 focus:outline-none focus:ring-1 focus:ring-red-700 h-[40px]">
      <button class="bg-red-700 text-white font-bold px-4 py-2 rounded-r-lg hover:bg-red-600 transition-colors h-[40px]">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
        </svg>      
      </button>
    </div>
  </div>


  <div class="flex items-center gap-2">
    <label for="cinemas" class="text-red-700 font-bold">Cinema:</label>
    <select id="cinemas" class="border bg-red-700 rounded-lg text-white px-10 py-2 h-[40px] hover:bg-red-600">
      <option>Show All cinema</option>
      <option>SM Mall</option>
      <option>Robinsons</option>
      <option>Ayala</option>
    </select>
  </div>


</div>


  <div id="movie-pop-up" class="hidden flex-row justify-center items-center fixed inset-0 bg-black/50 w-screen h-screen z-50">
    <div id="pop-up-content" class="flex flex-row justify-start bg-gray-100 w-[70%] max-w-[1200px] h-[600px] rounded-3xl shadow-2xl relative">


       </div>
  </div>




  <div class="w-[95%] h-fit mb-10 mt-10">
    <span class="flex flex-row w-[100%] justify-between items-center mb-4">
      <h1 class="text-3xl text-red-700 font-extrabold tracking-wide uppercase border-b-4 border-red-700 pb-1">YOU MIGHT LIKE</h1>
    </span>
    <div class="flex flex-wrap gap-6 justify-start w-full h-fit p-3">
      <?php while($movie = $you_might_like_result->fetch_assoc()) { ?>
        <div
          class="movie-card bg-gray-900 h-[420px] w-[260px] rounded-2xl shadow-xl hover:cursor-pointer hover:scale-105 hover:shadow-2xl transition-all duration-300 relative overflow-hidden group"
          data-id="<?= $movie['movie_id']; ?>"
          data-name="<?= htmlspecialchars($movie['movie_name']); ?>"
          data-genre="<?= htmlspecialchars($movie['genre']); ?>"
          data-hours="<?= $movie['movie_hours']; ?>"
          data-price="<?= $movie['price']; ?>"
          data-poster="<?= htmlspecialchars($movie['movie_poster']); ?>"
          data-trailer="<?= htmlspecialchars($movie['movie_trailer']); ?>"
          data-description="<?= htmlspecialchars($movie['movie_description']); ?>"
          data-class="<?= htmlspecialchars($movie['movie_class']); ?>"
        >
          <div class="h-[100%] w-full">
            <img
              class="object-cover w-full h-full opacity-80 group-hover:opacity-100 transition-opacity duration-300"
              src="../../../../public/assets/images/<?= htmlspecialchars($movie['movie_poster']); ?>"
              alt="<?= htmlspecialchars($movie['movie_name']); ?>"
            />


              <?php
                $badgeColor = match ($movie['movie_class']) {
                    'G'   => 'bg-green-600',
                    'PG'  => 'bg-blue-600',
                    'R13' => 'bg-yellow-500',
                    'R16' => 'bg-orange-600',
                    'R18' => 'bg-red-600',
                    default => 'bg-gray-600',
                };
                ?>


            <div class="absolute top-3 right-2 <?= $badgeColor ?> text-white px-3 py-1 rounded-lg font-bold shadow">
              <?= htmlspecialchars($movie['movie_class']); ?>
            </div>
          </div>
          <div class="absolute bottom-0 w-full bg-gradient-to-t from-black via-black/80 to-transparent p-4 pt-10">
             <h1 class="text-white font-bold text-lg text-center leading-tight">
               <?= htmlspecialchars($movie['movie_name']); ?>
             </h1>
             <p class="text-gray-300 text-xs text-center mt-1"><?= htmlspecialchars($movie['genre']); ?></p>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>




  <div class="w-[95%] h-fit mb-10">
    <span class="flex flex-row w-[100%] justify-between items-center mb-4">
      <h1 class="text-3xl text-red-700 font-extrabold tracking-wide uppercase border-b-4 border-red-700 pb-1">NOW SHOWING</h1>
    </span>
    <div class="flex flex-wrap gap-6 justify-start w-full h-fit p-3">
      <?php while($movie = $now_showing_result->fetch_assoc()) { ?>
        <div
          class="movie-card bg-gray-900 h-[420px] w-[260px] rounded-2xl shadow-xl hover:cursor-pointer hover:scale-105 hover:shadow-2xl transition-all duration-300 relative overflow-hidden group"
          data-id="<?= $movie['movie_id']; ?>"
          data-name="<?= htmlspecialchars($movie['movie_name']); ?>"
          data-genre="<?= htmlspecialchars($movie['genre']); ?>"
          data-hours="<?= $movie['movie_hours']; ?>"
          data-price="<?= $movie['price']; ?>"
          data-poster="<?= htmlspecialchars($movie['movie_poster']); ?>"
          data-trailer="<?= htmlspecialchars($movie['movie_trailer']); ?>"
          data-description="<?= htmlspecialchars($movie['movie_description']); ?>"
          data-class="<?= htmlspecialchars($movie['movie_class']); ?>"
        >
          <div class="h-[100%] w-full">
            <img
              class="object-cover w-full h-full opacity-80 group-hover:opacity-100 transition-opacity duration-300"
              src="../../../../public/assets/images/<?= htmlspecialchars($movie['movie_poster']); ?>"
              alt="<?= htmlspecialchars($movie['movie_name']); ?>"
            />


              <?php
                $badgeColor = match ($movie['movie_class']) {
                    'G'   => 'bg-green-600',
                    'PG'  => 'bg-blue-600',
                    'R13' => 'bg-yellow-500',
                    'R16' => 'bg-orange-600',
                    'R18' => 'bg-red-600',
                    default => 'bg-gray-600',
                };
                ?>


            <div class="absolute top-3 right-2 <?= $badgeColor ?> text-white px-3 py-1 rounded-lg font-bold shadow">
              <?= htmlspecialchars($movie['movie_class']); ?>
            </div>
          </div>
          <div class="absolute bottom-0 w-full bg-gradient-to-t from-black via-black/80 to-transparent p-4 pt-10">
             <h1 class="text-white font-bold text-lg text-center leading-tight">
               <?= htmlspecialchars($movie['movie_name']); ?>
             </h1>
             <p class="text-gray-300 text-xs text-center mt-1"><?= htmlspecialchars($movie['genre']); ?></p>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>




  <div class="w-[95%] h-fit mb-10">
    <span class="flex flex-row w-[100%] justify-between items-center mb-4">
      <h1 class="text-3xl text-red-700 font-extrabold tracking-wide uppercase border-b-4 border-red-700 pb-1">COMING SOON</h1>
    </span>
    <div class="flex flex-wrap gap-6 justify-start w-full h-fit p-3">
      <?php while($movie = $coming_soon_result->fetch_assoc()) { ?>
        <div
          class="movie-card bg-gray-900 h-[420px] w-[260px] rounded-2xl shadow-xl hover:cursor-pointer hover:scale-105 hover:shadow-2xl transition-all duration-300 relative overflow-hidden group"
          data-id="<?= $movie['movie_id']; ?>"
          data-name="<?= htmlspecialchars($movie['movie_name']); ?>"
          data-genre="<?= htmlspecialchars($movie['genre']); ?>"
          data-hours="<?= $movie['movie_hours']; ?>"
          data-price="<?= $movie['price']; ?>"
          data-poster="<?= htmlspecialchars($movie['movie_poster']); ?>"
          data-trailer="<?= htmlspecialchars($movie['movie_trailer']); ?>"
          data-description="<?= htmlspecialchars($movie['movie_description']); ?>"          
          data-class="<?= htmlspecialchars($movie['movie_class']); ?>"
        >
          <div class="h-[100%] w-full">
            <img
              class="object-cover w-full h-full opacity-80 group-hover:opacity-100 transition-opacity duration-300"
              src="../../../../public/assets/images/<?= htmlspecialchars($movie['movie_poster']); ?>"
              alt="<?= htmlspecialchars($movie['movie_name']); ?>"
            />


              <?php
                $badgeColor = match ($movie['movie_class']) {
                    'G'   => 'bg-green-600',
                    'PG'  => 'bg-blue-600',
                    'R13' => 'bg-yellow-500',
                    'R16' => 'bg-orange-600',
                    'R18' => 'bg-red-600',
                    default => 'bg-gray-600',
                };
                ?>


            <div class="absolute top-3 right-2 <?= $badgeColor ?> text-white px-3 py-1 rounded-lg font-bold shadow">
              <?= htmlspecialchars($movie['movie_class']); ?>
            </div>
          </div>
          <div class="absolute bottom-0 w-full bg-gradient-to-t from-black via-black/80 to-transparent p-4 pt-10">
             <h1 class="text-white font-bold text-lg text-center leading-tight">
               <?= htmlspecialchars($movie['movie_name']); ?>
             </h1>
             <p class="text-gray-300 text-xs text-center mt-1"><?= htmlspecialchars($movie['genre']); ?></p>
             <span class="block text-center text-yellow-400 text-xs font-bold mt-1">COMING SOON</span>
          </div>
        </div>
      <?php } ?>
    </div>
   
  </div>




<script>
const popUp = document.getElementById("movie-pop-up");
const popUpContent = document.getElementById("pop-up-content");


popUp.addEventListener("click", (e) => {
  if (e.target.id === "movie-pop-up") {
    popUp.classList.add("hidden");
    popUp.classList.remove("flex");
  }
});


const movieCards = document.querySelectorAll(".movie-card");


/* ===========================================================
   UNIVERSAL YOUTUBE LINK PARSER
   Supports:
   - youtube.com/watch?v=ID
   - youtu.be/ID
   - youtube.com/shorts/ID
   - any messy link with ?si=, &t=, &ab_channel= etc.
=========================================================== */
function getYoutubeEmbed(url) {
  if (!url) return "";


  let videoId = "";


  try {
    let u = new URL(url);


    // Case 1: ?v=XXXXXXXX
    if (u.searchParams.get("v")) {
      videoId = u.searchParams.get("v");
    }


    // Case 2: youtu.be/XXXXXXXX
    else if (u.hostname === "youtu.be") {
      videoId = u.pathname.slice(1);
    }


    // Case 3: youtube.com/shorts/XXXXXXXX
    else if (u.pathname.includes("/shorts/")) {
      videoId = u.pathname.split("/shorts/")[1];
    }


    // Cleanup (remove timestamps / random params)
    if (videoId.includes("?")) videoId = videoId.split("?")[0];
    if (videoId.includes("&")) videoId = videoId.split("&")[0];


  } catch (err) {
    return ""; // invalid video URL
  }


  return videoId ? `https://www.youtube.com/embed/${videoId}` : "";
}


movieCards.forEach((card) => {
  card.addEventListener("click", () => {
    const id = card.getAttribute("data-id");
    const name = card.getAttribute("data-name");
    const genre = card.getAttribute("data-genre");
    const hours = card.getAttribute("data-hours");
    const price = card.getAttribute("data-price");
    const poster = card.getAttribute("data-poster");
    const trailerUrl = card.getAttribute("data-trailer");
    const movieClass = card.getAttribute("data-class");


    // ======================================
    // GET EMBED YOUTUBE LINK
    // ======================================
    const embedTrailer = getYoutubeEmbed(trailerUrl);


    // ======================================
    // BADGE COLOR
    // ======================================
    let badgeColor = "bg-gray-600";


    switch (movieClass) {
      case "G":   badgeColor = "bg-green-600"; break;
      case "PG":  badgeColor = "bg-blue-600"; break;
      case "R13": badgeColor = "bg-yellow-500"; break;
      case "R16": badgeColor = "bg-orange-600"; break;
      case "R18": badgeColor = "bg-red-600"; break;
    }


    // ======================================
    // POPUP CONTENT
    // ======================================
    popUpContent.innerHTML = `
      <div class="h-full w-[30%] rounded-l-3xl overflow-hidden relative">
        <img class="object-cover w-full h-full" src="../../../../public/assets/images/${poster}" />
        <div class="absolute top-4 right-4 ${badgeColor}
            text-white px-3 py-1 rounded-lg
            font-bold shadow-strong z-10">
            ${movieClass}
        </div>
      </div>


      <div class="flex flex-col w-[70%] h-full bg-white rounded-r-3xl pb-8 pt-8 relative">
        <button onclick="document.getElementById('movie-pop-up').click()"
          class="absolute top-4 right-6 text-gray-500 hover:text-red-700 font-bold text-2xl">
          &times;
        </button>


        <div class="relative w-full h-[70%] mb-4 rounded-3xl overflow-hidden shadow-lg pl-10 pr-10">
          ${embedTrailer
            ? `<iframe class="w-full h-full rounded-3xl"
                src="${embedTrailer}?autoplay=1&rel=0"
                title="Movie Trailer"
                frameborder="0"
                allow="autoplay; fullscreen; encrypted-media"
                allowfullscreen></iframe>`
            : `<img class="rounded-3xl object-cover w-full h-full shadow-lg"
                src="../../../../public/assets/images/${poster}" />`
          }
        </div>


        <h1 class="text-4xl font-extrabold text-gray-800 mb-2 pl-12 pr-10">${name}</h1>


        <div class="flex gap-3 mb-6 flex-wrap pl-12 pr-12">
          <span class="bg-gray-800 text-white px-3 py-1 rounded-full text-sm font-semibold">${genre}</span>
          <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm font-semibold">${hours}</span>
          <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-semibold">₱ ${price}</span>
        </div>


        <p class="text-gray-600 mb-8 flex-grow pl-12 pr-12">
          Experience the action on the big screen. Book your tickets now to secure the best seats in the house.
        </p>


        <div class="flex flex-row justify-end items-center mt-auto pl-12 pr-12">
          <a class="flex justify-center items-center bg-red-700 rounded-xl text-white font-bold px-8 py-4 w-full shadow-lg hover:bg-red-800 hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1"
            href="BuyPage.php?movie_id=${id}">
            VIEW SCHEDULES
          </a>
        </div>
      </div>
    `;


    popUp.classList.remove("hidden");
    popUp.classList.add("flex");
  });
});
</script>


</body>
</html>











