<?php
include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $application_id = $_POST['application_id'];
    $status = $_POST['status'];

    // Validate inputs
    if (empty($application_id) || empty($status)) {
        die("Error: Missing required parameters");
    }

    // Validate status value
    $valid_statuses = ['pending', 'reviewed', 'contacted', 'accepted', 'rejected'];
    if (!in_array($status, $valid_statuses)) {
        die("Error: Invalid status value");
    }

    // Update application status
    $stmt = $conn->prepare("UPDATE job_applications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $application_id);

    if ($stmt->execute()) {
        // Success - redirect back to applicant management
        header("Location: hr1main.php?section=applicant");
        exit();
    }

    // If the update failed due to ENUM restriction (e.g., "contacted" not allowed), attempt to fix schema and retry.
    $errorMsg = $stmt->error;
    if (strpos($errorMsg, 'Incorrect enum value') !== false || strpos($errorMsg, 'Data truncated') !== false) {
        // Attempt to alter the column to include 'contacted'
        $alterSql = "ALTER TABLE job_applications MODIFY status ENUM('pending','reviewed','contacted','accepted','rejected') NOT NULL DEFAULT 'pending'";
        if ($conn->query($alterSql) === TRUE) {
            // Retry the update once
            $stmt->close();
            $stmt = $conn->prepare("UPDATE job_applications SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $application_id);
            if ($stmt->execute()) {
                header("Location: hr1main.php?section=applicant");
                exit();
            }
        }
    }

    echo "Error updating application status: " . $errorMsg;

    $stmt->close();
    $conn->close();
} else {
    header("Location: hr1main.php");
    exit();
}
?>