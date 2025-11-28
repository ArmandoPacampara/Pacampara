<?php
session_start();
require_once __DIR__ . '/../core/db.php'; // Path to your db connection

$database = new Database();
$con = $database->getConnection(); // mysqli connection object

// --- CONSTANTS ---
const MAX_ATTEMPTS = 3;
const LOCKOUT_DURATION_MINUTES = 30; // Lockout user for 30 minutes

// Check for the login form submission
if (isset($_POST['login'])) {

    // --- 1. CAPTCHA CHECK ---
    $captcha = $_POST['g-recaptcha-response'] ?? '';

    if (empty($captcha)) {
        $_SESSION['error'] = "Please verify that you're not a robot.";
        header("Location: index.php"); // Updated path
        exit;
    }

    $secretKey = "6LcWAgwsAAAAACPtasWSo-ZcrS97WmngAu9_sJxQ";
    $verifyURL = "https://www.google.com/recaptcha/api/siteverify";

    // Use cURL for safer external request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $verifyURL . "?secret=" . $secretKey . "&response=" . $captcha);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $responseKeys = json_decode($response, true);

    if (!($responseKeys["success"] ?? false)) {
        $_SESSION['error'] = "Captcha verification failed. Please try again.";
        header("Location: index.php"); // Updated path
        exit;
    }

    // --- 2. SECURE LOGIN LOGIC ---
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $current_time = date('Y-m-d H:i:s');
    
    // 2a. Fetch user data including lockout fields
    $query = "SELECT user_id, user_name, user_email, user_password, role_id, failed_login_attempts, lockout_until FROM users WHERE user_email = ? LIMIT 1";
    $stmt = $con->prepare($query);

    if (!$stmt) {
        $_SESSION['error'] = "Database error during login. Please try again.";
        header("Location: index.php"); // Updated path
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // 2b. Initial Check: Does the user exist?
    if ($user) {
        
        // --- LOCKOUT CHECK ---
        $lockout_time = new DateTime($user['lockout_until']);
        $now = new DateTime($current_time);

        // Check if the user is currently locked out
        if ($user['lockout_until'] !== NULL && $lockout_time > $now) {
            $remaining_minutes = $now->diff($lockout_time)->i + 1; // Remaining minutes
            $_SESSION['error'] = "Account locked for security. Try again in approximately {$remaining_minutes} minutes.";
            header("Location: index.php"); // Updated path
            exit;
        }

        // --- PASSWORD VERIFICATION ---
        // Using password_verify() (assuming you fixed the hash issue)
        if (password_verify($password, $user['user_password'])) {
            
            // ------------------------------------------
            // A. SUCCESSFUL LOGIN
            // ------------------------------------------
            
            // Reset attempts and lockout time on successful login
            $reset_query = "UPDATE users SET failed_login_attempts = 0, lockout_until = NULL WHERE user_id = ?";
            $reset_stmt = $con->prepare($reset_query);
            $reset_stmt->bind_param("i", $user['user_id']);
            $reset_stmt->execute();
            $reset_stmt->close();
            
            // --- ROLE LOOKUP AND SESSION CREATION ---
            $roleID = $user['role_id'];
            $roleQuery = "SELECT user_role FROM roles WHERE role_id = ?";
            $roleStmt = $con->prepare($roleQuery);
            $roleStmt->bind_param("i", $roleID);
            $roleStmt->execute();
            $roleResult = $roleStmt->get_result();
            $role = $roleResult->fetch_assoc()['user_role'] ?? 'User';
            $roleStmt->close();
            
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['user_email'] = $user['user_email'];
            $_SESSION['role'] = $role;
            $_SESSION['last_activity'] = time();
            $_SESSION['user_id'] = $user['user_id'];
// ...

            // Redirection
            if ($role === 'Admin') {
                header("Location: /moviease/app/view/pages/Admin/AdminPage.html"); // Updated path
            } elseif ($role === 'Staff') {
                header("Location: /moviease/app/view/pages/Staff/StaffPage.html"); // Assuming this path
            } else {
                header("Location: /moviease/app/view/pages/User/HomePage.php"); // Updated path
            }
            exit;

        } else {
            // ------------------------------------------
            // B. FAILED LOGIN (Password Mismatch)
            // ------------------------------------------
            
            $new_attempts = $user['failed_login_attempts'] + 1;
            $lockout_time_sql = NULL;
            $error_message = "Incorrect email or password. Attempt {$new_attempts} of " . MAX_ATTEMPTS . ".";
            
            if ($new_attempts >= MAX_ATTEMPTS) {
                // Calculate lockout time
                $lockout_dt = new DateTime();
                $lockout_dt->modify('+'.LOCKOUT_DURATION_MINUTES.' minutes');
                $lockout_time_sql = $lockout_dt->format('Y-m-d H:i:s');
                
                $error_message = "Account locked! Maximum attempts reached. Try again in " . LOCKOUT_DURATION_MINUTES . " minutes.";
            }

            // Update attempts and lockout status in database
            $update_query = "UPDATE users SET failed_login_attempts = ?, lockout_until = ? WHERE user_id = ?";
            $update_stmt = $con->prepare($update_query);
            
            // Note: If $lockout_time_sql is NULL, we bind 's' and it works.
            $update_stmt->bind_param("isi", $new_attempts, $lockout_time_sql, $user['user_id']);
            $update_stmt->execute();
            $update_stmt->close();

            $_SESSION['error'] = $error_message;
            header("Location: index.php"); // Updated path
            exit;
        }
    } else {
        // ------------------------------------------
        // C. FAILED LOGIN (User Not Found)
        // ------------------------------------------
        // For security, do not leak whether the user exists.
        $_SESSION['error'] = "Incorrect email or password.";
        header("Location: index.php"); // Updated path
        exit;
    }
}