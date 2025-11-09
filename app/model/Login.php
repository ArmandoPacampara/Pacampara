<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "admin";
$dbname = "moviease_db";
$port = "3307";

$con = mysqli_connect($host, $user, $pass, $dbname, $port);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    $query = "SELECT * FROM users WHERE user_Email = '$email' LIMIT 1";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if ($password === $user['user_Password']) {

            $roleID = $user['role_ID'];
            $roleQuery = "SELECT user_Role FROM roles WHERE role_ID = '$roleID'";
            $roleResult = mysqli_query($con, $roleQuery);
            $roleData = mysqli_fetch_assoc($roleResult);
            $role = $roleData['user_Role'];

            $_SESSION['user_ID'] = $user['user_ID'];
            $_SESSION['user_Name'] = $user['user_Name'];
            $_SESSION['user_Email'] = $user['user_Email'];
            $_SESSION['role'] = $role;

            if ($role === 'Admin') {
                echo "connected successfully";
            } elseif ($role === 'Staff') {
                echo "connected successfully";
            } else {
                echo "connected successfully";
                header("Location: ../app/view/pages/HomePage.php");
            }
            exit;
        } else {
            $_SESSION['error'] = "Incorrect password.";
            header("Location: index.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "No account found with that email.";
        header("Location: index.php");
        exit;
    }
}
?>