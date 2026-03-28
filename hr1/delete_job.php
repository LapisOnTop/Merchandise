<?php
include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_id = $_POST['job_id'];

    if (empty($job_id)) {
        die("Error: Job ID is required");
    }

    // Prepare and bind
    $stmt = $conn->prepare("DELETE FROM job_postings WHERE id=?");
    $stmt->bind_param("i", $job_id);

    if ($stmt->execute()) {
        // Redirect back to the main page with recruitment section active
        header("Location: hr1main.php?section=recruitment");
        exit();
    } else {
        echo "Error deleting job: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // If not a POST request, redirect back
    header("Location: hr1main.php");
    exit();
}
?>