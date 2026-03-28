<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/db.php';

echo "<h2>Adding 2 New Drivers...</h2>";

$new_drivers = [
    [206, 'Coco Martin', 'coco@log2.ph', '09170000006', 'Quezon City, PH', '1981-11-01', 'male', 'single', 'Filipino'],
    [207, 'Marian Rivera', 'marian@log2.ph', '09170000007', 'Cavite, PH', '1984-08-12', 'female', 'married', 'Spanish-Filipino']
];

foreach ($new_drivers as $d) {
    $sql = "INSERT INTO employees (job_id, full_name, email, phone, home_address, date_of_birth, gender, civil_status, nationality, position, work_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Driver', 'available')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssss", $d[0], $d[1], $d[2], $d[3], $d[4], $d[5], $d[6], $d[7], $d[8]);
    if ($stmt->execute()) {
        echo "Successfully added Driver: " . $d[1] . "<br>";
    } else {
        echo "Failed to add " . $d[1] . ": " . $conn->error . "<br>";
    }
}

echo "<h3>Done!</h3>";
echo "<a href='log2/driver_management.php'>Go to Driver Management</a>";
