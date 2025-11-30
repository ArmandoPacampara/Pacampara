<?php
session_start();
require_once '../../../core/db.php';

// Auth Check
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Staff' && $_SESSION['role'] !== 'Admin')) {
    die("Access Denied");
}

// --- 1. HANDLE FORM SUBMISSIONS ---

// Add Movie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_movie') {
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $duration = $_POST['duration']; // "02:30:00"
    $price = $_POST['price'];
    $class = $_POST['class'];
    $status = 'Coming Soon'; // Default
    
    // Basic Poster Logic (You can enhance this)
    $poster = 'default_poster.jpg'; 

    $stmt = $con->prepare("INSERT INTO movies (movie_name, genre, movie_hours, price, movie_class, movie_status, movie_poster) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $title, $genre, $duration, $price, $class, $status, $poster);
    $stmt->execute();
    $stmt->close();
    
    header("Location: StaffMoviesPage.php"); // Refresh
    exit;
}

// Add Schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_schedule') {
    $movie_id = $_POST['movie_id'];
    $cinema_id = $_POST['cinema_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $price = $_POST['price'];
    
    $datetime = $date . ' ' . $time;

    $stmt = $con->prepare("INSERT INTO cinema_movies (cinema_id, movie_id, showtime, ticket_price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisd", $cinema_id, $movie_id, $datetime, $price);
    $stmt->execute();
    $stmt->close();
    
    header("Location: StaffMoviesPage.php");
    exit;
}

// --- 2. FETCH DATA ---
$movies = $con->query("SELECT * FROM movies ORDER BY movie_id DESC");
$cinemas = $con->query("SELECT * FROM cinemas");

// Fetch Schedules for JSON use in JS
$schedules_data = [];
$sched_res = $con->query("
    SELECT cm.id, cm.movie_id, c.cinema_name, cm.showtime, cm.ticket_price
    FROM cinema_movies cm
    JOIN cinemas c ON cm.cinema_id = c.cinema_id
");
while($row = $sched_res->fetch_assoc()) {
    $schedules_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Movie Management</title>
    <!-- Adjust path to CSS -->
    <link rel="stylesheet" href="../../../../public/styles/css/StaffMoviesPage.css">
    <style>
        /* Small fix for modal visibility */
        .modal { display: none; position: fixed; z-index: 100; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        
        /* Ensure forms look good */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="content-wrapper">
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('movies')">Movie List</button>
            </div>

            <!-- Movie List Tab -->
            <div id="movies" class="tab-content active">
                <div class="card">
                    <h2>Available Movies</h2>
                    
                    <div class="search-bar">
                        <input type="text" id="movieSearch" placeholder="Search movies..." onkeyup="searchMovies()">
                        <button class="btn">Search</button>
                    </div>

                    <div class="filter-options">
                        <button class="filter-btn active" onclick="filterByGenre('All')">All</button>
                        <button class="filter-btn" onclick="filterByGenre('Action')">Action</button>
                        <button class="filter-btn" onclick="filterByGenre('Comedy')">Comedy</button>
                        <button class="filter-btn" onclick="filterByGenre('Drama')">Drama</button>
                        <button class="filter-btn" onclick="filterByGenre('Sci-Fi')">Sci-Fi</button>
                        <button class="filter-btn" onclick="filterByGenre('Horror')">Horror</button>
                    </div>

                    <div class="movie-grid" id="movieGrid">
                        <!-- Add Movie Button -->
                        <div class="movie-item add-movie-item" onclick="openAddMovieModal()">
                            <div class="movie-poster add-movie-poster">
                                <div class="add-icon">+</div>
                                <div class="add-text">Add Movie</div>
                            </div>
                            <div class="movie-title">Add Movie</div>
                            <div class="movie-info">New Title</div>
                        </div>

                        <!-- PHP Loop for Movies -->
                        <?php while($movie = $movies->fetch_assoc()): ?>
                            <div class="movie-item" 
                                 data-id="<?= $movie['movie_id'] ?>"
                                 data-title="<?= htmlspecialchars($movie['movie_name']) ?>"
                                 data-genre="<?= htmlspecialchars($movie['genre']) ?>"
                                 data-duration="<?= htmlspecialchars($movie['movie_hours']) ?>"
                                 data-poster="<?= htmlspecialchars($movie['movie_poster']) ?>"
                                 onclick="openMovieDetails(this)">
                                <div class="movie-poster">
                                    <img src="../../../../public/assets/images/<?= htmlspecialchars($movie['movie_poster']) ?>" 
                                         alt="Poster" style="width:100%; height:100%; object-fit:cover;">
                                </div>
                                <div class="movie-title"><?= htmlspecialchars($movie['movie_name']) ?></div>
                                <div class="movie-info"><?= $movie['genre'] ?> • <?= $movie['movie_hours'] ?></div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Movie Details Modal -->
        <div id="movieDetailsModal" class="modal">
            <div class="modal-content modal-large">
                <div class="modal-header">
                    <h3 id="movieDetailsTitle">Movie Details</h3>
                    <button class="close-btn" onclick="closeMovieDetails()">&times;</button>
                </div>

                <div class="movie-details-container">
                    <div class="movie-info-section">
                        <div class="movie-poster-large" id="moviePosterLarge">
                            <!-- Poster injected via JS -->
                        </div>
                        <div class="movie-meta">
                            <h2 id="movieMetaTitle">Title</h2>
                            <p id="movieMetaInfo" class="meta-info"></p>
                        </div>
                    </div>

                    <div class="details-tabs">
                        <button class="details-tab-btn active" onclick="switchDetailsTab('schedules')">Schedules</button>
                    </div>

                    <!-- Schedules Tab -->
                    <div id="schedules" class="details-tab-content active">
                        <div class="section-header">
                            <h3>Movie Schedules</h3>
                            <button class="btn btn-add" onclick="openScheduleForm()">+ Add Schedule</button>
                        </div>
                        <div id="schedulesList" class="schedules-list">
                            <!-- JS will populate this -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Form Modal -->
        <div id="scheduleFormModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Add Schedule</h3>
                    <button class="close-btn" onclick="closeScheduleForm()">&times;</button>
                </div>

                <form id="scheduleForm" method="POST">
                    <input type="hidden" name="action" value="add_schedule">
                    <input type="hidden" name="movie_id" id="schedMovieId">

                    <div class="form-group">
                        <label>Cinema</label>
                        <select name="cinema_id" required>
                            <?php 
                            $cinemas->data_seek(0); // Reset pointer
                            while($c = $cinemas->fetch_assoc()): 
                            ?>
                                <option value="<?= $c['cinema_id'] ?>"><?= htmlspecialchars($c['cinema_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-row" style="display:flex; gap:10px;">
                        <div class="form-group" style="flex:1;">
                            <label>Date</label>
                            <input type="date" name="date" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Time</label>
                            <input type="time" name="time" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Price (₱)</label>
                        <input type="number" name="price" step="0.01" required>
                    </div>

                    <button type="submit" class="btn" style="width:100%;">Save Schedule</button>
                </form>
            </div>
        </div>

        <!-- Add Movie Modal (New Form) -->
        <div id="addMovieModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Add New Movie</h3>
                    <button class="close-btn" onclick="closeAddMovieModal()">&times;</button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add_movie">
                    
                    <div class="form-group">
                        <label>Movie Title</label>
                        <input type="text" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Genre</label>
                        <select name="genre">
                            <option>Action</option><option>Comedy</option><option>Drama</option>
                            <option>Sci-Fi</option><option>Horror</option><option>Romance</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Duration (HH:MM:SS)</label>
                        <input type="text" name="duration" placeholder="02:30:00" required>
                    </div>

                    <div class="form-group">
                        <label>Default Price</label>
                        <input type="number" name="price" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Class</label>
                        <select name="class">
                            <option>G</option><option>PG</option><option>R13</option><option>R16</option>
                        </select>
                    </div>

                    <button type="submit" class="btn" style="width:100%;">Add Movie</button>
                </form>
            </div>
        </div>

    </div>

    <script>
        // Data passed from PHP
        const allSchedules = <?= json_encode($schedules_data) ?>;
        let currentMovieId = null;

        // --- MODAL CONTROLS ---
        function openAddMovieModal() {
            document.getElementById('addMovieModal').classList.add('active');
        }
        function closeAddMovieModal() {
            document.getElementById('addMovieModal').classList.remove('active');
        }

        function openMovieDetails(element) {
            currentMovieId = element.getAttribute('data-id');
            const title = element.getAttribute('data-title');
            const genre = element.getAttribute('data-genre');
            const poster = element.getAttribute('data-poster');

            document.getElementById('movieDetailsTitle').textContent = title;
            document.getElementById('movieMetaTitle').textContent = title;
            document.getElementById('movieMetaInfo').textContent = genre;
            
            // Update Poster in Modal
            document.getElementById('moviePosterLarge').innerHTML = `<img src="../../../../public/assets/images/${poster}" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">`;

            // Render Schedules for this movie
            renderSchedules(currentMovieId);

            document.getElementById('movieDetailsModal').classList.add('active');
        }

        function closeMovieDetails() {
            document.getElementById('movieDetailsModal').classList.remove('active');
        }

        function openScheduleForm() {
            document.getElementById('schedMovieId').value = currentMovieId;
            document.getElementById('scheduleFormModal').classList.add('active');
        }

        function closeScheduleForm() {
            document.getElementById('scheduleFormModal').classList.remove('active');
        }

        // --- RENDER SCHEDULES ---
        function renderSchedules(movieId) {
            const list = document.getElementById('schedulesList');
            list.innerHTML = '';

            // Filter schedules for this movie
            const movieScheds = allSchedules.filter(s => s.movie_id == movieId);

            if (movieScheds.length === 0) {
                list.innerHTML = '<p class="empty-state">No schedules yet.</p>';
                return;
            }

            movieScheds.forEach(s => {
                const date = new Date(s.showtime).toLocaleString();
                const item = document.createElement('div');
                item.className = 'schedule-card';
                item.innerHTML = `
                    <div class="schedule-info">
                        <h4>${date}</h4>
                        <p>Cinema: ${s.cinema_name} | Price: ₱${s.ticket_price}</p>
                    </div>
                `;
                list.appendChild(item);
            });
        }

        // --- SEARCH & FILTER ---
        function searchMovies() {
            const val = document.getElementById('movieSearch').value.toLowerCase();
            document.querySelectorAll('.movie-item:not(.add-movie-item)').forEach(el => {
                const title = el.getAttribute('data-title').toLowerCase();
                el.style.display = title.includes(val) ? 'block' : 'none';
            });
        }

        function filterByGenre(genre) {
            // Update active button
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            event.target.classList.add('active');

            document.querySelectorAll('.movie-item:not(.add-movie-item)').forEach(el => {
                const g = el.getAttribute('data-genre');
                if (genre === 'All' || g.includes(genre)) {
                    el.style.display = 'block';
                } else {
                    el.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>