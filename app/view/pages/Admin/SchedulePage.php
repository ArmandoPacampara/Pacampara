<?php
session_start();
require_once '../../../../app/core/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
    $cinema_id = $_POST['cinema_id'];
    $movie_id = $_POST['movie_id'];
    $datetime = $_POST['date'] . ' ' . $_POST['time'];
    $price = $_POST['price'];

    $stmt = $con->prepare("INSERT INTO cinema_movies (cinema_id, movie_id, showtime, ticket_price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisd", $cinema_id, $movie_id, $datetime, $price);
    $stmt->execute();
}

// Fetch Data for Dropdowns
$cinemas = $con->query("SELECT * FROM cinemas");
$movies = $con->query("SELECT * FROM movies");
// Fetch Schedules
$schedules = $con->query("
    SELECT cm.id, c.cinema_name, m.movie_name, cm.showtime, cm.ticket_price 
    FROM cinema_movies cm 
    JOIN cinemas c ON cm.cinema_id = c.cinema_id 
    JOIN movies m ON cm.movie_id = m.movie_id 
    ORDER BY cm.showtime DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Schedule</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
    <h1 class="text-2xl font-bold mb-4">Manage Schedules</h1>

    <div class="bg-white p-4 rounded shadow mb-6">
        <h3 class="font-bold mb-2">Create Showtime</h3>
        <form method="POST" class="flex gap-2 flex-wrap">
            <select name="cinema_id" required class="border p-2 rounded">
                <option value="">Select Cinema</option>
                <?php while($c = $cinemas->fetch_assoc()): ?>
                    <option value="<?= $c['cinema_id'] ?>"><?= $c['cinema_name'] ?></option>
                <?php endwhile; ?>
            </select>
            <select name="movie_id" required class="border p-2 rounded">
                <option value="">Select Movie</option>
                <?php while($m = $movies->fetch_assoc()): ?>
                    <option value="<?= $m['movie_id'] ?>"><?= $m['movie_name'] ?></option>
                <?php endwhile; ?>
            </select>
            <input type="date" name="date" required class="border p-2 rounded">
            <input type="time" name="time" required class="border p-2 rounded">
            <input type="number" name="price" placeholder="Price" step="0.01" required class="border p-2 rounded w-24">
            <button type="submit" name="add_schedule" class="bg-purple-600 text-white px-4 py-2 rounded">Schedule</button>
        </form>
    </div>

    <table class="w-full bg-white rounded shadow text-left border-collapse">
        <thead class="bg-gray-800 text-white">
            <tr><th>Cinema</th><th>Movie</th><th>Showtime</th><th>Price</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php while($row = $schedules->fetch_assoc()): ?>
            <tr class="border-b">
                <td class="p-3"><?= $row['cinema_name'] ?></td>
                <td class="p-3"><?= $row['movie_name'] ?></td>
                <td class="p-3"><?= date('M d, Y h:i A', strtotime($row['showtime'])) ?></td>
                <td class="p-3">₱<?= $row['ticket_price'] ?></td>
                <td class="p-3 text-red-600 cursor-pointer">Delete</td> </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>