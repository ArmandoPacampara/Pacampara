<?php
session_start();
require_once '../../../../app/core/db.php';
require_once '../../../../app/core/Logger.php'; 
require_once '../../../../app/core/Csrf.php'; // 1. Import CSRF Helper

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { die("Access Denied"); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. VERIFY TOKEN (Protects both Add and Delete actions)
    Csrf::verifyToken();

    if (isset($_POST['add_movie'])) {
        $name = $_POST['name'];
        $hours = $_POST['hours']; // format 02:30:00
        $price = $_POST['price'];
        $genre = $_POST['genre'];
        $status = $_POST['status'];
        $class = $_POST['class'];
        
        // Image Upload (Simplified)
        $poster = "default_poster.jpg"; 
        if(isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
            $poster = time() . "_" . $_FILES['poster']['name'];
            $target_dir = "../../../../public/assets/images/";
            // Ensure directory exists
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            move_uploaded_file($_FILES['poster']['tmp_name'], $target_dir . $poster);
        }

        $stmt = $con->prepare("INSERT INTO movies (movie_name, movie_hours, price, genre, movie_status, movie_class, movie_poster) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssss", $name, $hours, $price, $genre, $status, $class, $poster);
        
        if ($stmt->execute()) {
            // [LOG MOVIE ADDITION]
            Logger::log($con, $_SESSION['user_id'], "MOVIE_ADDED", "Added new movie: $name ($genre)");
        }
        $stmt->close();

    } elseif (isset($_POST['delete_movie'])) {
        $id = intval($_POST['movie_id']); // Sanitize ID
        
        // Fetch name before deleting for the log
        $check = $con->query("SELECT movie_name FROM movies WHERE movie_id = $id");
        $movie_name = ($check->num_rows > 0) ? $check->fetch_assoc()['movie_name'] : "Unknown Movie";

        if ($con->query("DELETE FROM movies WHERE movie_id = $id")) {
            // [LOG MOVIE DELETION]
            Logger::log($con, $_SESSION['user_id'], "MOVIE_DELETED", "Deleted movie: $movie_name (ID: $id)");
        }
    }
}

$movies = $con->query("SELECT * FROM movies ORDER BY movie_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Movies</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 p-6">
    <h1 class="text-2xl font-bold mb-4">Manage Movies</h1>

    <div class="bg-white p-4 rounded shadow mb-6">
        <h3 class="font-bold mb-2">Add New Movie</h3>
        <form method="POST" enctype="multipart/form-data" class="grid grid-cols-2 gap-4">
            
            <!-- 3. ADD TOKEN TO ADD FORM -->
            <?= Csrf::getTokenField() ?>

            <input type="text" name="name" placeholder="Movie Title" required class="border p-2 rounded">
            <input type="time" name="hours" step="1" required class="border p-2 rounded">
            <input type="number" name="price" placeholder="Price" step="0.01" required class="border p-2 rounded">
            <select name="genre" class="border p-2 rounded">
                <option value="Action">Action</option><option value="Comedy">Comedy</option><option value="Drama">Drama</option><option value="Horror">Horror</option>
                <option value="Sci-Fi">Sci-Fi</option><option value="Romance">Romance</option><option value="Animation">Animation</option><option value="Thriller">Thriller</option>
            </select>
            <select name="status" class="border p-2 rounded">
                <option value="Now Showing">Now Showing</option><option value="Coming Soon">Coming Soon</option>
            </select>
            <select name="class" class="border p-2 rounded">
                <option value="G">G</option><option value="PG">PG</option><option value="R13">R13</option><option value="R16">R16</option>
            </select>
            <input type="file" name="poster" class="border p-2 rounded">
            <button type="submit" name="add_movie" class="bg-blue-600 text-white px-4 py-2 rounded col-span-2 hover:bg-blue-700 transition">Add Movie</button>
        </form>
    </div>

    <div class="grid grid-cols-4 gap-4">
        <?php while($row = $movies->fetch_assoc()): ?>
        <div class="bg-white rounded shadow overflow-hidden relative group">
            <img src="../../../../public/assets/images/<?= htmlspecialchars($row['movie_poster']) ?>" class="w-full h-48 object-cover">
            <div class="p-3">
                <h3 class="font-bold text-lg truncate"><?= htmlspecialchars($row['movie_name']) ?></h3>
                <p class="text-sm text-gray-600"><?= htmlspecialchars($row['genre']) ?> | <?= htmlspecialchars($row['movie_hours']) ?></p>
                <div class="flex justify-between items-center mt-2">
                    <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded"><?= htmlspecialchars($row['movie_status']) ?></span>
                    
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this movie?');">
                        <!-- 4. ADD TOKEN TO DELETE FORM (Inside Loop) -->
                        <?= Csrf::getTokenField() ?>
                        
                        <input type="hidden" name="movie_id" value="<?= $row['movie_id'] ?>">
                        <button type="submit" name="delete_movie" class="text-red-600 hover:text-red-800 transition"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</body>
</html>