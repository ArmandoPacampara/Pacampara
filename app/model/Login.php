<?php
session_start();
require_once __DIR__ . '/../core/db.php'; // correct relative path to db.php

$database = new Database();
$con = $database->getConnection(); // use the same connection function

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    $query = "SELECT * FROM users WHERE user_Email = '$email' LIMIT 1";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // if you’re not using password_hash() yet, leave as plain check for now
        if ($password === $user['user_Password']) {

            // Get user role
            $roleID = $user['role_ID'];
            $roleQuery = "SELECT user_Role FROM roles WHERE role_ID = '$roleID'";
            $roleResult = mysqli_query($con, $roleQuery);
            $roleData = mysqli_fetch_assoc($roleResult);
            $role = $roleData['user_Role'] ?? 'User';

            // Store session data
            $_SESSION['user_ID'] = $user['user_ID'];
            $_SESSION['user_Name'] = $user['user_Name'];
            $_SESSION['user_Email'] = $user['user_Email'];
            $_SESSION['role'] = $role;

            // Redirect depending on role
            if ($role === 'Admin') {
                header("Location: app/view/pages/AdminDashboard.php");
            } elseif ($role === 'Staff') {
                header("Location: app/view/pages/StaffDashboard.php");
            } else {
                header("Location: app/view/pages/HomePage.php");
            }
            exit;

        } else {
            $_SESSION['error'] = "Incorrect email or password.";
            header("Location: index.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "Incorrect email or password.";
        header("Location: index.php");
        exit;
    }
}
?>
