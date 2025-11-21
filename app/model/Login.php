<?php
session_start();
require_once __DIR__ . '/../core/db.php';

$database = new Database();
$con = $database->getConnection();

if (isset($_POST['login'])) {

    /** CAPTCHA CHECK **/
    $captcha = $_POST['g-recaptcha-response'];

    if (!$captcha) {
        $_SESSION['error'] = "Please verify that you're not a robot.";
        header("Location: index.php");
        exit;
    }


    $secretKey = "6LcWAgwsAAAAACPtasWSo-ZcrS97WmngAu9_sJxQ";


    $verifyURL = "https://www.google.com/recaptcha/api/siteverify";
    $response = file_get_contents($verifyURL . "?secret=" . $secretKey . "&response=" . $captcha);
    $responseKeys = json_decode($response, true);

    if (!$responseKeys["success"]) {
        $_SESSION['error'] = "Captcha verification failed. Please try again.";
        header("Location: index.php");
        exit;
    }

    /** ORIGINAL LOGIN LOGIC **/
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    $query = "SELECT * FROM users WHERE user_email = '$email' LIMIT 1";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if ($password === $user['user_password']) {

            $roleID = $user['role_id'];
            $roleQuery = "SELECT user_role FROM roles WHERE role_id = '$roleID'";
            $roleResult = mysqli_query($con, $roleQuery);
            $roleData = mysqli_fetch_assoc($roleResult);
            $role = $roleData['user_role'] ?? 'User';

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['user_email'] = $user['user_email'];
            $_SESSION['role'] = $role;

            if ($role === 'Admin') {
                header("Location: app/view/pages/AdminDashboard.php");
            } elseif ($role === 'Staff') {
                header("Location: app/view/pages/StaffDashboard.php");
            } else {
                header("Location: ../app/view/pages/HomePage.php");
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
