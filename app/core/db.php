<?php
$host = "localhost";
$user = "root";
$pass = "admin";
$dbname = "moviease_db";

$con = new mysqli($host, $user, $pass, $dbname);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>