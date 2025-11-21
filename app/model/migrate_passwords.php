<?php
// Load necessary files
require_once __DIR__ . '/../core/db.php'; 

// IMPORTANT: Ensure this script is NOT publicly accessible
if (php_sapi_name() !== 'cli' && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
    die("Access denied.");
}

$database = new Database();
$con = $database->getConnection();

echo "Starting password migration...\n";

// 1. Fetch all users whose passwords don't look like hashes (or simply fetch all)
// Note: This assumes plaintext passwords are shorter than 60 chars.
$select_query = "SELECT user_id, user_password FROM users WHERE LENGTH(user_password) < 60";
$result = $con->query($select_query);

if ($result && $result->num_rows > 0) {
    $count = 0;
    
    // Use prepared statement for the update to prevent SQL injection during the loop
    $update_query = "UPDATE users SET user_password = ? WHERE user_id = ?";
    $stmt = $con->prepare($update_query);
    $stmt->bind_param("si", $hashed_password, $user_id); // 's' for hash string, 'i' for user ID

    while ($row = $result->fetch_assoc()) {
        $raw_password = $row['user_password'];
        $user_id = $row['user_id'];
        
        // 2. Generate the secure hash using the plaintext password
        $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

        // 3. Execute the update
        if ($stmt->execute()) {
            $count++;
            echo "User ID {$user_id} migrated successfully.\n";
        } else {
            echo "Error updating User ID {$user_id}: {$con->error}\n";
        }
    }
    $stmt->close();
    echo "Migration complete. {$count} users were updated.\n";

} else {
    echo "No users found needing migration.\n";
}

$con->close();
?>