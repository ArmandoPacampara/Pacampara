<?php
// public/index.php
session_start();

// Load the Controller
require_once __DIR__ . '/../app/controller/LoginController.php';

// Instantiate Controller
$controller = new LoginController();

// Simple Routing Logic
$action = $_GET['action'] ?? 'index'; // Default to 'index' (show login form)

if ($action === 'login') {
    // Process the POST request
    $controller->login();
} else {
    // Show the Login Page
    $controller->index();
}
?>