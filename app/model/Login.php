<?php
session_start();
require_once __DIR__ . '/../core/db.php'; 
require_once __DIR__ . '/../core/Logger.php'; // 1. Import Logger

$database = new Database();
$con = $database->getConnection(); 

// --- CONSTANTS ---
const MAX_ATTEMPTS = 3;
const LOCKOUT_DURATION_MINUTES = 30; 

// --- 0. REMEMBER ME CHECK (Auto-Login) ---
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    
    $cookie_parts = explode(':', $_COOKIE['remember_me']);

    // Check for correct format (selector:authenticator)
    if (count($cookie_parts) === 2) {
        list($selector, $authenticator_b64) = $cookie_parts;
        $authenticator_raw = base64_decode($authenticator_b64);
        
        // 0a. Fetch token from DB using selector
        $token_query = "SELECT t.user_id, t.hashed_authenticator, u.user_name, u.user_email, u.role_id, u.cinema_id, r.user_role 
                        FROM remember_tokens t
                        JOIN users u ON t.user_id = u.user_id
                        JOIN roles r ON u.role_id = r.role_id
                        WHERE t.selector = ? AND t.expires > NOW() LIMIT 1";
        $token_stmt = $con->prepare($token_query);
        $token_stmt->bind_param("s", $selector);
        $token_stmt->execute();
        $token_result = $token_stmt->get_result();
        $token_data = $token_result->fetch_assoc();
        $token_stmt->close();
        
        if ($token_data) {
            // 0b. Verify authenticator
            if (hash_equals($token_data['hashed_authenticator'], hash('sha256', $authenticator_raw))) {
                
                // VALID TOKEN: Auto-login successful
                session_regenerate_id(true);

                // Create session (same logic as successful login)
                $_SESSION['user_id'] = $token_data['user_id'];
                $_SESSION['user_name'] = $token_data['user_name'];
                $_SESSION['user_email'] = $token_data['user_email'];
                $_SESSION['role'] = $token_data['user_role'];
                $_SESSION['cinema_id'] = $token_data['cinema_id'];
                $_SESSION['last_activity'] = time();

                // Redirect based on role (same logic as successful login)
                if ($token_data['user_role'] === 'Admin') {
                    header("Location: /moviease/app/view/pages/Admin/AdminPage.php"); 
                } elseif ($token_data['user_role'] === 'Staff') {
                    header("Location: /moviease/app/view/pages/Staff/StaffPage.php"); 
                } else {
                    header("Location: /moviease/app/view/pages/User/HomePage.php"); 
                }
                exit;

            } else {
                // TOKEN MISMATCH (potential theft): Delete all tokens for this user
                $delete_query = "DELETE FROM remember_tokens WHERE user_id = ?";
                $delete_stmt = $con->prepare($delete_query);
                $delete_stmt->bind_param("i", $token_data['user_id']);
                $delete_stmt->execute();
                $delete_stmt->close();
            }
        }
    }
    // Clear the cookie if it was invalid, expired, or failed verification
    setcookie('remember_me', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// Check for the login form submission
if (isset($_POST['login'])) {

    // --- 1. CAPTCHA CHECK ---
    $captcha = $_POST['g-recaptcha-response'] ?? '';

    if (empty($captcha)) {
        $_SESSION['error'] = "Please verify that you're not a robot.";
        header("Location: index.php"); 
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
        header("Location: index.php"); 
        exit;
    }

    // --- 2. SECURE LOGIN LOGIC ---
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $current_time = date('Y-m-d H:i:s');
    
    // 2a. Fetch user data including lockout fields
    $query = "SELECT user_id, user_name, user_email, user_password, role_id, cinema_id, failed_login_attempts, lockout_until FROM users WHERE user_email = ? LIMIT 1";
    $stmt = $con->prepare($query);

    if (!$stmt) {
        $_SESSION['error'] = "Database error during login. Please try again.";
        header("Location: index.php");
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
        $lockout_time = $user['lockout_until'] ? new DateTime($user['lockout_until']) : null;
        $now = new DateTime($current_time);

        // Check if the user is currently locked out
        if ($user['lockout_until'] !== NULL && $lockout_time > $now) {
            $remaining_minutes = $now->diff($lockout_time)->i + 1; 
            
            // [LOG LOCKOUT ATTEMPT]
            Logger::log($con, $user['user_id'], "LOGIN_BLOCKED", "Locked user tried to login: $email");

            $_SESSION['error'] = "Account locked for security. Try again in approximately {$remaining_minutes} minutes.";
            header("Location: index.php"); 
            exit;
        }

        // --- PASSWORD VERIFICATION ---
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
            
            // --- LOGGING SUCCESS ---
            Logger::log($con, $user['user_id'], "LOGIN_SUCCESS", "User logged in successfully.");

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
            $_SESSION['cinema_id'] = $user['cinema_id'];
            $_SESSION['last_activity'] = time();

            if (isset($_POST['remember-me']) && $_POST['remember-me'] === 'on') {
                
                // 1. GENERATE SECURE TOKENS: Selector/Authenticator pair
                $selector = bin2hex(random_bytes(6)); // 12 chars
                $authenticator = random_bytes(32); // 64 chars raw
                $hashed_authenticator = hash('sha256', $authenticator); // Hash for DB storage
                $expires = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 days expiry

                // 2. STORE TOKEN IN DATABASE 
                $token_query = "INSERT INTO remember_tokens (user_id, selector, hashed_authenticator, expires) VALUES (?, ?, ?, ?)";
                $token_stmt = $con->prepare($token_query);
                $user_id = $user['user_id'];
                $token_stmt->bind_param("isss", $user_id, $selector, $hashed_authenticator, $expires);
                $token_stmt->execute();
                $token_stmt->close();

                // 3. SET SECURE COOKIE (selector:raw_authenticator)
                $cookie_value = $selector . ':' . base64_encode($authenticator);
                setcookie('remember_me', $cookie_value, [
                    'expires' => time() + (86400 * 30), // 30 days
                    'path' => '/',
                    'secure' => true, 
                    'httponly' => true, 
                    'samesite' => 'Lax'
                ]);
            }

            // Redirection
            if ($role === 'Admin') {
                header("Location: /moviease/app/view/pages/Admin/AdminPage.php"); 
            } elseif ($role === 'Staff') {
                header("Location: /moviease/app/view/pages/Staff/StaffPage.php"); 
            } else {
                header("Location: /moviease/app/view/pages/User/HomePage.php"); 
            }
            exit;

        } else {
            // ------------------------------------------
            // B. FAILED LOGIN (Password Mismatch)
            // ------------------------------------------
            
            // --- LOGGING PASSWORD FAILURE ---
            Logger::log($con, $user['user_id'], "LOGIN_FAILED", "Incorrect password for email: $email");

            $new_attempts = $user['failed_login_attempts'] + 1;
            $lockout_time_sql = NULL;
            $error_message = "Incorrect email or password. Attempt {$new_attempts} of " . MAX_ATTEMPTS . ".";
            
            if ($new_attempts >= MAX_ATTEMPTS) {
                // Calculate lockout time
                $lockout_dt = new DateTime();
                $lockout_dt->modify('+'.LOCKOUT_DURATION_MINUTES.' minutes');
                $lockout_time_sql = $lockout_dt->format('Y-m-d H:i:s');
                
                $error_message = "Account locked! Maximum attempts reached. Try again in " . LOCKOUT_DURATION_MINUTES . " minutes.";
                
                // [LOG ACCOUNT LOCKOUT]
                Logger::log($con, $user['user_id'], "ACCOUNT_LOCKED", "Account locked due to too many failed attempts.");
            }

            // Update attempts and lockout status in database
            $update_query = "UPDATE users SET failed_login_attempts = ?, lockout_until = ? WHERE user_id = ?";
            $update_stmt = $con->prepare($update_query);
            $update_stmt->bind_param("isi", $new_attempts, $lockout_time_sql, $user['user_id']);
            $update_stmt->execute();
            $update_stmt->close();

            $_SESSION['error'] = $error_message;
            header("Location: index.php"); 
            exit;
        }
    } else {
        // ------------------------------------------
        // C. FAILED LOGIN (User Not Found)
        // ------------------------------------------
        
        // --- LOGGING INVALID EMAIL ---
        // Pass 0 or NULL for user_id, but log the email attempted in details
        Logger::log($con, 0, "LOGIN_FAILED", "Attempted login with non-existent email: $email");

        $_SESSION['error'] = "Incorrect email or password.";
        header("Location: index.php"); 
        exit;
    }
}
?>