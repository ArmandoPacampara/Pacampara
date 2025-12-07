<?php
// app/core/Csrf.php

class Csrf {
    
    /**
     * Generate a token if one doesn't exist, and return it.
     */
    public static function getToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Return the HTML input field with the token.
     * Usage: <?= Csrf::getTokenField() ?> inside your <form>
     */
    public static function getTokenField() {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Verify the token from a POST request.
     * Usage: Csrf::verifyToken(); at the top of your POST processing logic.
     */
    public static function verifyToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            
            // Log the attempt (optional but recommended)
            if (class_exists('Logger') && isset($GLOBALS['con'])) {
                $userId = $_SESSION['user_id'] ?? 0;
                Logger::log($GLOBALS['con'], $userId, "CSRF_FAIL", "Invalid CSRF token detected.");
            }
            
            die("Security Error: Invalid Request (CSRF Token Mismatch). Please refresh the page.");
        }
        return true;
    }
}
?>