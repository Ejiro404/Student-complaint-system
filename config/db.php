<?php
// Database configuration
$host = "localhost";
$user = "root";
$password = "";
$database = "student_complaint_system";

// Create connection
$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
