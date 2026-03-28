<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/db.php';

echo "<h2>Production Assets Cleanup...</h2>";

// 1. Delete all vehicles and leave 1
$conn->query("DELETE FROM vehicles");
$conn->query("INSERT INTO vehicles (brand, model, plate_number, status, vehicle_type, year_model, color, fuel_type, transmission_type) 
               VALUES ('Ford', 'Ranger', 'PRO-001', 'Available', 'Pickup', '2024', 'Orange', 'Diesel', 'Automatic')");
echo "Cleaned Vehicles: Left only Ford Ranger PRO-001.<br>";

// 2. Delete all drivers/employees and leave 1
$conn->query("DELETE FROM employees WHERE position = 'Driver'");
$conn->query("INSERT INTO employees (job_id, full_name, email, phone, home_address, date_of_birth, gender, civil_status, nationality, position, work_status) 
               VALUES (201, 'Ricardo Dalisay', 'cardos@log2.ph', '09170000001', 'Luzon, PH', '1985-05-15', 'male', 'married', 'Filipino', 'Driver', 'available')");
echo "Cleaned Drivers: Left only Ricardo Dalisay.<br>";

echo "<h3>Cleanup Complete!</h3>";
echo "<a href='log2/log2main.php'>Go to Dashboard</a>";
