<?php
session_start();
require_once '../../../../app/core/db.php';

// Auth Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { die("Access Denied"); }

// Handle Add/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_cinema'])) {
        $name = $_POST['name'];
        $address = $_POST['address'];
        $contact = $_POST['contact'];
        $stmt = $con->prepare("INSERT INTO cinemas (cinema_name, cinema_address, cinema_contact) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $address, $contact);
        $stmt->execute();
    } elseif (isset($_POST['delete_cinema'])) {
        $id = $_POST['cinema_id'];
        $con->query("DELETE FROM cinemas WHERE cinema_id = $id");
    }
}

$cinemas = $con->query("SELECT * FROM cinemas");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Cinemas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 p-6">
    <h1 class="text-2xl font-bold mb-4">Manage Cinemas</h1>
    
    <div class="bg-white p-4 rounded shadow mb-6">
        <h3 class="font-bold mb-2">Add New Cinema</h3>
        <form method="POST" class="flex gap-2">
            <input type="text" name="name" placeholder="Cinema Name" required class="border p-2 rounded w-1/3">
            <input type="text" name="address" placeholder="Address" required class="border p-2 rounded w-1/3">
            <input type="text" name="contact" placeholder="Contact" required class="border p-2 rounded w-1/4">
            <button type="submit" name="add_cinema" class="bg-green-600 text-white px-4 py-2 rounded">Add</button>
        </form>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-red-800 text-white">
                <tr>
                    <th class="p-3">ID</th>
                    <th class="p-3">Name</th>
                    <th class="p-3">Address</th>
                    <th class="p-3">Contact</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $cinemas->fetch_assoc()): ?>
                <tr class="border-b hover:bg-gray-100">
                    <td class="p-3"><?= $row['cinema_id'] ?></td>
                    <td class="p-3 font-bold"><?= htmlspecialchars($row['cinema_name']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($row['cinema_address']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($row['cinema_contact']) ?></td>
                    <td class="p-3">
                        <form method="POST" onsubmit="return confirm('Delete this cinema?');">
                            <input type="hidden" name="cinema_id" value="<?= $row['cinema_id'] ?>">
                            <button type="submit" name="delete_cinema" class="text-red-600 hover:text-red-800"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>