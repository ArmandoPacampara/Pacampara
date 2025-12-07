<?php
// Compatibility shim: create $db PDO connection used by older require_once 'config/db.php' calls
require_once __DIR__ . '/../app/core/db.php';

$database = new Database();
$db = $database->getConnection();
?>