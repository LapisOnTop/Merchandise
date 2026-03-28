<?php
// Database configuration
// Check both standard and Railway-specific variable names
$servername = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: "localhost";
$username   = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: "root";
$password   = getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : "");
$dbname     = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: "system";
$port       = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: "3306";

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
