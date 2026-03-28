<?php
include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_id = $_POST['job_id'];
    $department = $_POST['department'];
    $branch = $_POST['branch_select'] === 'other' ? $_POST['branch'] : $_POST['branch_select'];
    $position = $_POST['position'];
    $num_applicants = $_POST['num_applicants'];
    $requirements = $_POST['requirements'];

    // Validate required fields
    if (empty($job_id) || empty($department) || empty($branch) || empty($position) || empty($num_applicants) || empty($requirements)) {
        die("Error: All fields are required");
    }

    // Prepare and bind
    $stmt = $conn->prepare("UPDATE job_postings SET department=?, branch=?, position=?, num_applicants=?, requirements=? WHERE id=?");
    $stmt->bind_param("sssisi", $department, $branch, $position, $num_applicants, $requirements, $job_id);

    if ($stmt->execute()) {
        // Redirect back to the main page with recruitment section active
        header("Location: hr1main.php?section=recruitment");
        exit();
    } else {
        echo "Error updating job: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // If not a POST request, redirect back
    header("Location: hr1main.php");
    exit();
}
?>