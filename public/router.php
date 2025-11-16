<?php

// Return static files normally
if (php_sapi_name() === 'cli-server') {
    $path = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($path)) {
        return false;
    }
}

// Convert /register into ?url=register
$_GET['url'] = trim($_SERVER['REQUEST_URI'], "/");

// Load your index.php
require __DIR__ . "/index.php";
