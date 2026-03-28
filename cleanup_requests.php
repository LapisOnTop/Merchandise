<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/db.php';

echo "<h2>Production Database Cleanup...</h2>";

// 1. Delete all requests
$delete_sql = "DELETE FROM vehicle_requests";
if ($conn->query($delete_sql)) {
    echo "Deleted all existing requests.<br>";
}

// 2. Insert exactly 1 fresh request
$insert_sql = "INSERT INTO vehicle_requests (request_id, department, needed_at, destination, pickup_location, items_transport, goods_type, vehicle_size, dispatched) 
               VALUES ('REQ-CLEAN-01', 'HQ', '2026-03-29 09:00:00', 'Warehouse A', 'Office', 'Misc Supplies', 'General', 'Van', 0)";

if ($conn->query($insert_sql)) {
    echo "Successfully created 1 fresh request.<br>";
}

echo "<h3>Cleanup Complete!</h3>";
echo "<a href='log2/log2main.php'>Go to Dashboard</a>";
