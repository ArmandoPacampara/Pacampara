<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$timeout_duration = 120;
$login_page = '../../../public/index.php'; 

// Check if the user is logged in AND if the activity tracker exists
if (isset($_SESSION['user_id']) && isset($_SESSION['last_activity'])) {
    
    // Calculate the time difference since last activity
    $elapsed_time = time() - $_SESSION['last_activity'];
    
    if ($elapsed_time > $timeout_duration) {
        // User has been inactive for too long: DESTROY SESSION
        session_unset();
        session_destroy();
        
        // Redirect to login page with a timeout message
        $_SESSION['error'] = "Your session has expired due to inactivity.";
        header("Location: " . $login_page);
        exit;
    }

    // If still active, update the last activity time for the current request
    $_SESSION['last_activity'] = time();

} else {
    // If user_id is missing (not logged in), redirect them to the login page
    // (You might already have this check, but it's good practice here.)
    if (basename($_SERVER['PHP_SELF']) !== 'index.php') {
        header("Location: " . $login_page);
        exit;
    }
}
?>