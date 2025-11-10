<?php
$host = "localhost";
$user = "root";
$pass = "admin";
$dbname = "moviease_db";
$port = 3307;

$con = new mysqli($host, $user, $pass, $dbname, $port);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$now_showing_result = $con->query("SELECT * FROM movies WHERE movie_Status='Now Showing'");
$coming_soon_result = $con->query("SELECT * FROM movies WHERE movie_Status='Coming Soon'");
?>