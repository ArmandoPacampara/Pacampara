<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Movies</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <style>
    .shadow-strong { box-shadow: 0 0 12px rgba(0,0,0,0.4); }
  </style>
</head>

<body class="flex flex-col justify-start items-center w-screen h-screen m-0 bg-gray-100 overflow-x-hidden">

  <div class="w-[95%] flex items-center justify-between px-10 py-4 bg-white shadow-md mt-[10px]">
    <div class="flex items-center gap-2">
      <label class="text-xl text-red-700 font-bold">Genre:</label>
      <select class="border bg-red-700 border-red-600 rounded-lg text-white px-5 py-2 h-[40px]">
        <option>Show All Genres</option>
        <option>Action</option>
        <option>Comedy</option>
        </select>
    </div>

    <div class="flex-1 mx-6">
      <div class="flex w-full">
        <input type="text" placeholder="Search movies..." class="flex-1 border border-red-300 rounded-l-lg px-4 py-2 outline-none h-[40px]">
        <button class="bg-red-700 text-white px-4 py-2 rounded-r-lg h-[40px]"><i class="fas fa-search"></i></button>
      </div>
    </div>

    <button id="btn-smart-schedule" class="flex items-center gap-2 bg-yellow-500 text-white font-bold px-5 py-2 rounded-lg hover:bg-yellow-600 transition h-[40px] shadow-md">
        <i class="fas fa-magic"></i> Smart Schedule
    </button>
  </div>

  <div id="ai-pop-up" class="hidden flex-row justify-center items-center fixed inset-0 bg-black/60 w-screen h-screen z-50 backdrop-blur-sm">
    <div class="relative bg-white w-[50%] max-w-[800px] rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-red-800 to-red-600 p-6 flex justify-between items-center">
            <h2 class="text-white text-2xl font-extrabold"><i class="fas fa-sparkles text-yellow-300"></i> Best Time to Watch</h2>
            <button id="close-ai-popup" class="text-white text-3xl font-bold">&times;</button>
        </div>
        <div class="p-6 bg-gray-50 min-h-[300px] max-h-[500px] overflow-y-auto">
            <?php if (empty($ai_suggestions)): ?>
                <div class="flex flex-col items-center justify-center h-full text-gray-500 mt-10">
                    <i class="fas fa-server text-4xl mb-3"></i>
                    <p>AI Service Unavailable</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($ai_suggestions as $index => $slot): 
                        // Logic for badge/color kept simple for brevity
                        $posterUrl = "/moviease/public/assets/images/" . htmlspecialchars($slot['movie_poster']);
                    ?>
                    <div class="flex items-center p-3 rounded-xl border-l-4 border-gray-200 bg-white shadow-sm">
                        <img src="<?= $posterUrl ?>" class="w-16 h-24 object-cover rounded-lg mr-4">
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-800"><?= htmlspecialchars($slot['movie_name']) ?></h3>
                            <p class="text-xs text-gray-500"><?= htmlspecialchars($slot['cinema_name']) ?> • <?= $slot['display_date'] ?></p>
                        </div>
                        <div class="text-green-600 font-extrabold text-xl"><?= intval($slot['score']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
  </div>

  <div class="w-[95%] h-fit mb-10 mt-10">
    <h1 class="text-3xl text-red-700 font-extrabold uppercase border-b-4 border-red-700 pb-1 mb-4">YOU MIGHT LIKE</h1>
    <div class="flex flex-wrap gap-6 justify-start w-full p-3">
      <?php foreach($recommended as $movie): ?>
        <?php include 'partials/movie_card.php'; // Optional: Extract card to partial, or paste logic below ?>
        <div class="movie-card bg-gray-900 h-[420px] w-[260px] rounded-2xl shadow-xl relative overflow-hidden group cursor-pointer"
             onclick="location.href='index.php?action=buy&movie_id=<?= $movie['movie_id'] ?>'">
          <img class="object-cover w-full h-full opacity-80 group-hover:opacity-100 transition"
               src="/moviease/public/assets/images/<?= htmlspecialchars($movie['movie_poster']) ?>">
          <div class="absolute bottom-0 w-full bg-gradient-to-t from-black p-4 pt-10">
             <h1 class="text-white font-bold text-lg text-center"><?= htmlspecialchars($movie['movie_name']) ?></h1>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="w-[95%] h-fit mb-10">
    <h1 class="text-3xl text-red-700 font-extrabold uppercase border-b-4 border-red-700 pb-1 mb-4">NOW SHOWING</h1>
    <div class="flex flex-wrap gap-6 justify-start w-full p-3">
      <?php foreach($nowShowing as $movie): ?>
        <div class="movie-card bg-gray-900 h-[420px] w-[260px] rounded-2xl shadow-xl relative overflow-hidden group cursor-pointer"
             onclick="location.href='index.php?action=buy&movie_id=<?= $movie['movie_id'] ?>'">
          <img class="object-cover w-full h-full opacity-80 group-hover:opacity-100 transition"
               src="/moviease/public/assets/images/<?= htmlspecialchars($movie['movie_poster']) ?>">
          <div class="absolute bottom-0 w-full bg-gradient-to-t from-black p-4 pt-10">
             <h1 class="text-white font-bold text-lg text-center"><?= htmlspecialchars($movie['movie_name']) ?></h1>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="w-[95%] h-fit mb-10">
    <h1 class="text-3xl text-red-700 font-extrabold uppercase border-b-4 border-red-700 pb-1 mb-4">COMING SOON</h1>
    <div class="flex flex-wrap gap-6 justify-start w-full p-3">
      <?php foreach($comingSoon as $movie): ?>
        <div class="movie-card bg-gray-900 h-[420px] w-[260px] rounded-2xl shadow-xl relative overflow-hidden group cursor-pointer">
          <img class="object-cover w-full h-full opacity-80 group-hover:opacity-100 transition"
               src="/moviease/public/assets/images/<?= htmlspecialchars($movie['movie_poster']) ?>">
          <div class="absolute bottom-0 w-full bg-gradient-to-t from-black p-4 pt-10">
             <h1 class="text-white font-bold text-lg text-center"><?= htmlspecialchars($movie['movie_name']) ?></h1>
             <p class="text-yellow-400 text-xs text-center font-bold">COMING SOON</p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

<script>
    // Simple popup logic
    const aiBtn = document.getElementById("btn-smart-schedule");
    const aiPopup = document.getElementById("ai-pop-up");
    const closeAiPopup = document.getElementById("close-ai-popup");

    if(aiBtn && aiPopup) {
        aiBtn.onclick = () => { aiPopup.classList.remove("hidden"); aiPopup.classList.add("flex"); };
        closeAiPopup.onclick = () => { aiPopup.classList.add("hidden"); aiPopup.classList.remove("flex"); };
    }
</script>
</body>
</html>