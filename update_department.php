<?php
include 'includes/db.php';

$sql = "UPDATE job_applications ja
        LEFT JOIN job_postings jp ON ja.job_id = jp.id
        SET ja.department = jp.department
        WHERE ja.department IS NULL OR ja.department != jp.department";

if ($conn->query($sql) === TRUE) {
    echo "Department column updated in job_applications.";
} else {
    echo "Error updating department: " . $conn->error;
}

$conn->close();
?>