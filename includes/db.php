<?php
// Database configuration
// If MYSQLHOST is set, we are on Railway. Otherwise, we are on Localhost (XAMPP).
$servername = getenv('MYSQLHOST') ?: "localhost";
$username   = getenv('MYSQLUSER') ?: "root";
$password   = getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : "";
$dbname     = getenv('MYSQLDATABASE') ?: "system";
$port       = getenv('MYSQLPORT') ?: "3306";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    // In production, don't show the error details to avoid white screen leaks, just a simple message
    if (getenv('MYSQLHOST')) {
        die("System is temporarily unavailable. Please try again later.");
    } else {
        die("Connection failed: " . $conn->connect_error);
    }
}
?>
