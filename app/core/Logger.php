<?php
// app/core/Logger.php

class Logger {
    
    /**
     * Log an action to the database.
     * * @param mysqli $con       Database connection object
     * @param int|null $userId  The ID of the user performing the action (0 or NULL if guest)
     * @param string $action    Short code for the action (e.g., 'LOGIN_SUCCESS', 'DELETE_USER')
     * @param string $details   Readable details describing the event
     */
    public static function log($con, $userId, $action, $details) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        // Handle case where user might be 0 or null
        if (empty($userId)) {
            $userId = NULL;
        }

        $stmt = $con->prepare("INSERT INTO audit_logs (user_id, action_type, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("issss", $userId, $action, $details, $ip, $userAgent);
            $stmt->execute();
            $stmt->close();
        }
    }
}
?>