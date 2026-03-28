<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/db.php';

echo "<h2>Production Database Seeding...</h2>";

// 1. Add Drivers
$drivers = [
    [201, 'Ricardo Dalisay', 'cardos@log2.ph', '09170000001', 'Luzon, PH', '1985-05-15', 'male', 'married', 'Filipino'],
    [202, 'Niana Guerrero', 'niana@log2.ph', '09170000002', 'Manila, PH', '2000-01-27', 'female', 'single', 'Filipino'],
    [203, 'Manny Pacquiao', 'manny@log2.ph', '09170000003', 'Gensan, PH', '1978-12-17', 'male', 'married', 'Filipino'],
    [204, 'Catriona Gray', 'catriona@log2.ph', '09170000004', 'Albay, PH', '1994-01-06', 'female', 'single', 'Filipino'],
    [205, 'Bong Go', 'bong@log2.ph', '09170000005', 'Davao, PH', '1974-06-14', 'male', 'married', 'Filipino']
];

foreach ($drivers as $d) {
    $sql = "INSERT INTO employees (job_id, full_name, email, phone, home_address, date_of_birth, gender, civil_status, nationality, position, work_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Driver', 'available')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssss", $d[0], $d[1], $d[2], $d[3], $d[4], $d[5], $d[6], $d[7], $d[8]);
    $stmt->execute();
}

// 2. Add Vehicles
$vehicles = [
    ['Ford', 'Ranger', 'PRO-001', 'Available', 'Pickup', '2024', 'Orange', 'Diesel', 'Automatic'],
    ['Nissan', 'Urvan', 'PRO-002', 'Available', 'Van', '2023', 'Silver', 'Diesel', 'Manual'],
    ['Fuso', 'Canter', 'PRO-003', 'Available', 'Dump Truck', '2022', 'White', 'Diesel', 'Manual'],
    ['Hyundai', 'Stargazer', 'PRO-004', 'Available', 'MPV', '2024', 'Grey', 'Gasoline', 'Automatic'],
    ['Kia', 'K2500', 'PRO-005', 'Available', 'Light Truck', '2023', 'White', 'Diesel', 'Manual']
];

foreach ($vehicles as $v) {
    $sql = "INSERT INTO vehicles (brand, model, plate_number, status, vehicle_type, year_model, color, fuel_type, transmission_type) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $v[0], $v[1], $v[2], $v[3], $v[4], $v[5], $v[6], $v[7], $v[8]);
    $stmt->execute();
}

// 3. Add Reservations
$reqs = [
    ['PRO-REQ-01', 'Admin', '2026-03-30 08:00:00', 'Davao Office', 'Main Port', 'IT Equipment', 'Fragile', 'Van'],
    ['PRO-REQ-02', 'HQ', '2026-03-31 10:00:00', 'Manila Airport', 'Warehouse A', 'Medical Supplies', 'Priority', 'Light Truck'],
    ['PRO-REQ-03', 'Sales', '2026-04-01 09:00:00', 'Iloilo Branch', 'Manila Port', 'Promotional Items', 'General', 'Van'],
    ['PRO-REQ-04', 'Security', '2026-04-02 11:00:00', 'Clark Base', 'Headquarters', 'CCTV Units', 'High Value', 'Pickup'],
    ['PRO-REQ-05', 'Logistics', '2026-04-03 14:00:00', 'Subic Port', 'Main Warehouse', 'Export Goods', 'Bulk', 'Dump Truck']
];

foreach ($reqs as $r) {
    $sql = "INSERT INTO vehicle_requests (request_id, department, needed_at, destination, pickup_location, items_transport, goods_type, vehicle_size, dispatched) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6], $r[7]);
    $stmt->execute();
}

echo "<h3>All Production Records added successfully!</h3>";
echo "<a href='log2/log2main.php'>Go to Dashboard</a>";
