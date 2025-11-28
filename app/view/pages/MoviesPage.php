<?php
// MoviesPage.php
session_start();
// Prevent Browser Caching
header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

include '../../../app/core/db.php'; 

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// =========================================================
// 1. DYNAMIC "YOU MIGHT LIKE" ENGINE
// =========================================================
$you_might_like_result = null;

if ($user_id > 0) {
    // A. FETCH FRESH PREFERENCES FROM DB
    $pref_stmt = $con->prepare("SELECT user_genre FROM users WHERE user_id = ?");
    $pref_stmt->bind_param("i", $user_id);
    $pref_stmt->execute();
    $pref_res = $pref_stmt->get_result()->fetch_assoc();
    $pref_stmt->close();

    $db_genre_string = $pref_res['user_genre'] ?? '';
    
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

        // Include both Now Showing and Coming Soon
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
</head>

<body class="flex flex-col justify-start items-center w-screen h-screen m-0 bg-gray-100 overflow-x-hidden">
 
  <div id="movie-pop-up" class="hidden flex-row justify-center items-center fixed inset-0 bg-black/50 w-screen h-screen z-50">
    <div id="pop-up-content" class="flex flex-row justify-evenly bg-gray-100 w-[90%] max-w-[1000px] h-[500px] rounded-3xl pt-4 pb-4 shadow-2xl relative">
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
        >
          <div class="h-[100%] w-full">
            <img
              class="object-cover w-full h-full opacity-80 group-hover:opacity-100 transition-opacity duration-300"
              src="../../../public/assets/images/<?= htmlspecialchars($movie['movie_poster']); ?>"
              alt="<?= htmlspecialchars($movie['movie_name']); ?>"
            />
          </div>
          <div class="absolute bottom-0 w-full bg-gradient-to-t from-black via-black/80 to-transparent p-4 pt-10">
             <h1 class="text-white font-bold text-lg text-center leading-tight">
               <?= htmlspecialchars($movie['movie_name']); ?>
             </h1>
             <p class="text-gray-300 text-xs text-center mt-1"><?= htmlspecialchars($movie['genre']); ?></p>
             
             <?php if ($movie['movie_status'] === 'Coming Soon'): ?>
                <span class="block text-center text-yellow-400 text-xs font-bold mt-1">COMING SOON</span>
             <?php endif; ?>
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
        >
          <div class="h-[100%] w-full">
            <img
              class="object-cover w-full h-full opacity-80 group-hover:opacity-100 transition-opacity duration-300"
              src="../../../public/assets/images/<?= htmlspecialchars($movie['movie_poster']); ?>"
            />
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
        >
          <div class="h-[100%] w-full">
            <img
              class="object-cover w-full h-full opacity-80 group-hover:opacity-100 transition-opacity duration-300"
              src="../../../public/assets/images/<?= htmlspecialchars($movie['movie_poster']); ?>"
            />
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

    // Close popup on background click
    popUp.addEventListener("click", (e) => {
      if (e.target.id === "movie-pop-up") {
        popUp.classList.add("hidden");
        popUp.classList.remove("flex");
      }
    });

    // Attach click event to ALL movie cards
    const movieCards = document.querySelectorAll(".movie-card");
    movieCards.forEach((card) => {
      card.addEventListener("click", () => {
        const id = card.getAttribute("data-id"); 
        const name = card.getAttribute("data-name");
        const genre = card.getAttribute("data-genre"); 
        const hours = card.getAttribute("data-hours");
        const price = card.getAttribute("data-price");
        const poster = card.getAttribute("data-poster");

        popUpContent.innerHTML = `
          <div class="h-full w-[40%] rounded-l-3xl overflow-hidden">
            <img class="object-cover w-full h-full" src="../../../public/assets/images/${poster}" />
          </div>
          <div class="flex flex-col w-[60%] h-full bg-white rounded-r-3xl p-8 relative">
            <button onclick="document.getElementById('movie-pop-up').click()" class="absolute top-4 right-6 text-gray-500 hover:text-red-700 font-bold text-2xl">&times;</button>
            
            <h1 class="text-4xl font-extrabold text-gray-800 mb-2">${name}</h1>
            <div class="flex gap-3 mb-6 flex-wrap">
                <span class="bg-gray-800 text-white px-3 py-1 rounded-full text-sm font-semibold">${genre}</span>
                <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm font-semibold">${hours}</span>
                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-semibold">P ${price}</span>
            </div>
            
            <p class="text-gray-600 mb-8 flex-grow">Experience the action on the big screen. Book your tickets now to secure the best seats in the house.</p>

            <div class="flex flex-row justify-end items-center mt-auto">
                <a class="flex justify-center items-center bg-red-700 rounded-xl text-white font-bold px-8 py-4 w-full shadow-lg hover:bg-red-800 hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1" 
                   href="BuyPage.php?movie_id=${id}"> 
                   BUY TICKETS
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