<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinemas</title>
    <link rel="stylesheet" href="../../../public/styles/css/CinemasPage.css">


    <script>
        function handleVisitClick(cinema) {
            // Tell the parent page to change the iframe
            window.parent.loadCinema(cinema);
        }
    </script>
</head>


<body>
    <div class="container">
        <h2>MoviEase is an online movie ticket reservation system that makes booking faster, easier, and more convenient.</h2>


        <p>It allows users to check movie schedules, view available seats, and buy tickets securely from anywhere.</p>


        <p>
            With MoviEase, you can easily reserve your seats for cinemas in SM, Robinsons, and Ayala Malls, all in one platform.
            No more long lines — just smooth, real-time booking and instant digital tickets for a hassle-free movie experience.
        </p>


        <div class="cinema-container">


            <!-- SM -->
            <div class="cinema-card">
                <img src="../../../public/assets/images/smlogo.png" alt="SM Logo">
                <h3>SM</h3>
                <a href="javascript:void(0)" class="visit-btn" onclick="handleVisitClick('SM')">VISIT</a>
            </div>


            <!-- ROBINSON -->
            <div class="cinema-card">
                <img src="../../../public/assets/images/robinl.jpg" alt="Robinsons Logo">
                <h3>ROBINSON</h3>
                <a href="javascript:void(0)" class="visit-btn" onclick="handleVisitClick('Robinson')">VISIT</a>
            </div>


            <!-- AYALA -->
            <div class="cinema-card">
                <img src="../../../public/assets/images/ayalamlogo.png" alt="Ayala Logo">
                <h3>AYALA</h3>
                <a href="javascript:void(0)" class="visit-btn" onclick="handleVisitClick('Ayala')">VISIT</a>
            </div>
        </div>


    </div>
</body>


</html>



