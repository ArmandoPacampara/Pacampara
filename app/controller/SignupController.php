<?php
session_start();
// Path from app/Controller/ up two levels to the project root, then down into app/core/
require_once __DIR__ . '/../../app/core/db.php'; 

$database = new Database();
$con = $database->getConnection(); // mysqli object connection

// --- Utility Function: Strong Password Policy (Matches your JS rules) ---
function is_strong_password($password) {
    $min_length = 8;
    // Regex: at least one lowercase, one uppercase, one digit
    $complexity_regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/';

    if (strlen($password) < $min_length) {
        return "Password must be at least {$min_length} characters long.";
    }
    if (!preg_match($complexity_regex, $password)) {
        return "Password must include at least one uppercase letter, one lowercase letter, and one number.";
    }
    return true;
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- 1. Collect and Sanitize Input ---
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone-number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Data from Steps 1 & 2
    $location = trim($_POST['location'] ?? ''); 
    $birthdate = trim($_POST['birthdate'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $genres = trim($_POST['genres'] ?? ''); // Comma-separated string
    
    // Default values
    $default_role_id = 2; // Assuming 2 is 'Customer'
    $email_recovery = $email; 
    
    // Profile Image Handling (Basic)
    $avatar_file = $_FILES['profileImageInput'] ?? null;
    $avatar_filename = 'account_icon.png';
    // NOTE: Full image upload logic (moving file, checking size/type) is complex and omitted here.

    $error = null;

    // --- 2. PHP Server-Side Validation (CRITICAL) ---
    
    // a. Basic checks (must mirror client-side)
    if (empty($name) || empty($phone) || empty($email) || empty($password) || empty($location)) {
        $error = "Please fill in all required fields.";
    }
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    }
    if ($age < 13) {
        $error = "You must be at least 13 years old to register.";
    }
    
    // b. Strong Password Policy Check
    $policy_result = is_strong_password($password);
    if ($policy_result !== true) {
        $error = $policy_result;
    }

    // c. Check if Email Already Exists (Use Prepared Statements)
    if (!$error) {
        $check_query = "SELECT user_id FROM users WHERE user_email = ? LIMIT 1";
        $stmt_check = $con->prepare($check_query);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "This email address is already registered.";
        }
        $stmt_check->close();
    }
    
    // --- 3. Process and Save User ---
    if (!$error) {
        
        // **CRITICAL SECURITY STEP: HASH THE PASSWORD**
        // Store the hash securely.
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $current_datetime = date('Y-m-d H:i:s'); 

        $insert_query = "INSERT INTO users (
            role_id, user_name, user_email, user_contact, user_password, 
            email_recovery, user_avatar, last_password_change
            -- Note: 'user_location' and 'user_genres' columns are assumed to be handled elsewhere, 
            -- or you need to add them to the 'users' table or separate tables.
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?
        )";

        $stmt = $con->prepare($insert_query);
        
        // Bind parameters: isssssss (i=int, s=string)
        $stmt->bind_param("isssssss", 
            $default_role_id, 
            $name, 
            $email, 
            $phone, 
            $hashed_password, 
            $email_recovery, 
            $avatar_filename, // Using default filename for now
            $current_datetime 
        );

        if ($stmt->execute()) {
            $stmt->close();
            
            // Registration SUCCESS: Redirect to login page (index.php)
            $_SESSION['success_message'] = "Registration successful! Please log in.";
            // Path from app/Controller/ up two levels to the project root index.php
            header("Location: ../../public/index.php"); 
            exit;
        } else {
            $error = "Database error: Could not register user. " . $con->error;
        }
        $stmt->close();
    }

    // --- 4. Handle Error and Redirect Back ---
    if ($error) {
        $_SESSION['signup_error'] = $error;
        $_SESSION['form_data'] = $_POST; // Persist form data to re-fill fields
        // Path from app/Controller/ to app/view/pages/Signup.php
        header("Location: ../view/pages/Signup.php"); 
        exit;
    }
} else {
    // If accessed directly, redirect
    header("Location: ../view/pages/Signup.php"); 
    exit;
}
?>