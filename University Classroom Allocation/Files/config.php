<?php
// Database configuration
$host = 'localhost';
$db = 'timetable';
$user = 'root';
$pass = '';

$con = new mysqli($host, $user, $pass, $db);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>
