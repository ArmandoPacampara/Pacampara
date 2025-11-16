<?php
// MoviesPage.php
require_once '../app/core/db.php';

$db = new Database();
$con = $db->getConnection();

$now_showing_result = $con->query("SELECT * FROM movies WHERE movie_Status='Now Showing'");
$coming_soon_result = $con->query("SELECT * FROM movies WHERE movie_Status='Coming Soon'");
$you_might_like_result = $con->query("SELECT * FROM movies WHERE movie_Status='You might like'");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Movies</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex flex-col justify-start items-center w-screen h-screen m-0">

  <div id="movie-pop-up"
    class="hidden flex-row justify-center items-center fixed inset-0 bg-black/50 w-screen h-screen z-10">
    <div id="pop-up-content"
      class="flex flex-row justify-evenly bg-gray-100 w-[1200px] h-[500px] rounded-3xl pt-4 pb-4">

    </div>
  </div>


  <div class="w-[95%] h-fit mb-10 mt-10">
    <span class="flex flex-row w-[100%] justify-between items-center">
      <h1 class="text-2xl text-red-600 font-bold underline">YOU MIGHT LIKE</h1>
    </span>
    <div class="flex flex-wrap gap-3 justify-start w-full h-fit p-3">
      <?php while ($movie = $you_might_like_result->fetch_assoc()) { ?>
        <div
          class="movie-card bg-red-900 h-[400px] w-[260px] rounded-2xl p-2 hover:cursor-pointer hover:scale-105 hover:bg-red-800 duration-200"
          data-name="<?= htmlspecialchars($movie['movie_Name']); ?>" data-hours="<?= $movie['movie_Hours']; ?>"
          data-price="<?= $movie['price']; ?>" data-poster="<?= htmlspecialchars($movie['movie_poster']); ?>">
          <div class="h-[300px] mb-5">
            <img class="rounded-2xl object-cover w-full h-full"
              src="assets/images/<?= htmlspecialchars($movie['movie_poster']); ?>" />
          </div>
          <h1 class="flex justify-center items-center text-white font-bold text-xl w-[100%] text-center">
            <?= htmlspecialchars($movie['movie_Name']); ?>
          </h1>
        </div>
      <?php } ?>
    </div>
  </div>

  <!-- NOW SHOWING -->
  <div class="w-[95%] h-fit mb-10 mt-10">
    <span class="flex flex-row w-[100%] justify-between items-center">
      <h1 class="text-2xl text-red-600 font-bold underline">NOW SHOWING</h1>
    </span>
    <div class="flex flex-wrap gap-3 justify-start w-full h-fit p-3">
      <?php while ($movie = $now_showing_result->fetch_assoc()) { ?>
        <div
          class="movie-card bg-red-900 h-[400px] w-[260px] rounded-2xl p-2 hover:cursor-pointer hover:scale-105 hover:bg-red-800 duration-200"
          data-name="<?= htmlspecialchars($movie['movie_Name']); ?>" data-hours="<?= $movie['movie_Hours']; ?>"
          data-price="<?= $movie['price']; ?>" data-poster="<?= htmlspecialchars($movie['movie_poster']); ?>">
          <div class="h-[300px] mb-5">
            <img class="rounded-2xl object-cover w-full h-full"
              src="assets/images/<?= htmlspecialchars($movie['movie_poster']); ?>" />
          </div>
          <h1 class="flex justify-center items-center text-white font-bold text-xl w-[100%] text-center">
            <?= htmlspecialchars($movie['movie_Name']); ?>
          </h1>
        </div>
      <?php } ?>
    </div>
  </div>


  <div class="w-[95%] h-fit mb-10 mt-10">
    <span class="flex flex-row w-[100%] justify-between items-center">
      <h1 class="text-2xl text-red-600 font-bold underline">COMING SOON</h1>
    </span>
    <div class="flex flex-wrap gap-3 justify-start w-full h-fit p-3">
      <?php while ($movie = $coming_soon_result->fetch_assoc()) { ?>
        <div
          class="movie-card bg-red-900 h-[400px] w-[260px] rounded-2xl p-2 hover:cursor-pointer hover:scale-105 hover:bg-red-800 duration-200"
          data-name="<?= htmlspecialchars($movie['movie_Name']); ?>" data-hours="<?= $movie['movie_Hours']; ?>"
          data-price="<?= $movie['price']; ?>" data-poster="<?= htmlspecialchars($movie['movie_poster']); ?>">
          <div class="h-[300px] mb-5">
            <img class="rounded-2xl object-cover w-full h-full"
              src="assets/images/<?= htmlspecialchars($movie['movie_poster']); ?>" />
          </div>
          <h1 class="flex justify-center items-center text-white font-bold text-xl w-[100%] text-center">
            <?= htmlspecialchars($movie['movie_Name']); ?>
          </h1>
        </div>
      <?php } ?>
    </div>
  </div>


  <script>
    const popUp = document.getElementById("movie-pop-up");
    const popUpContent = document.getElementById("pop-up-content");

    // Close popup when clicking anywhere on the overlay
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
        const name = card.getAttribute("data-name");
        const hours = card.getAttribute("data-hours");
        const price = card.getAttribute("data-price");
        const poster = card.getAttribute("data-poster");

        popUpContent.innerHTML = `
          <div class="h-full rounded-3xl">
            <img class="rounded-3xl object-cover w-full h-full" src="assets/images/${poster}" />
          </div>
          <div class="flex flex-col w-[70%] h-full">
            <div class="h-[70%]">
              <img class="rounded-3xl object-cover w-full h-full" src="assets/images/${poster}" />
            </div>
            <div class="flex flex-row justify-between items-center w-full h-full p-5">
              <h1 class="text-red-700 font-bold text-3xl">${hours}</h1>
              <span class="flex flex-nowrap items-center gap-5 w-fit h-fit">
                <h1 class="text-3xl">P ${price}</h1>
                <a class="flex justify-center items-center bg-red-700 rounded-xl text-white font-bold p-3 w-[150px] hover:bg-red-600 duration-200" href="/buy">
                  BUY TICKETS
                </a>
              </span>
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