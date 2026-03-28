<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/db.php';

echo "<h2>Production Database Seeding...</h2>";

// 1. Add 1 Driver
$sql = "INSERT INTO employees (job_id, full_name, email, phone, home_address, date_of_birth, gender, civil_status, nationality, position, work_status) 
        VALUES (202, 'Niana Guerrero', 'niana@log2.ph', '09170000002', 'Manila, PH', '2000-01-27', 'female', 'single', 'Filipino', 'Driver', 'available')";
$conn->query($sql);
echo "Added 1 Driver: Niana Guerrero.<br>";

// 2. Add 1 Vehicle
$sql = "INSERT INTO vehicles (brand, model, plate_number, status, vehicle_type, year_model, color, fuel_type, transmission_type) 
        VALUES ('Nissan', 'Urvan', 'PRO-002', 'Available', 'Van', '2023', 'Silver', 'Diesel', 'Manual')";
$conn->query($sql);
echo "Added 1 Vehicle: Nissan Urvan.<br>";

// 3. Add 1 Reservation
$sql = "INSERT INTO vehicle_requests (request_id, department, needed_at, destination, pickup_location, items_transport, goods_type, vehicle_size, dispatched) 
        VALUES ('PRO-NEW-01', 'Logistics', '2026-04-01 09:00:00', 'Manila Port', 'Warehouse', 'Misc', 'General', 'Van', 0)";
$conn->query($sql);
echo "Added 1 Reservation Request.<br>";

echo "<h3>All Production Records added successfully!</h3>";
echo "<a href='log2/log2main.php'>Go to Dashboard</a>";
