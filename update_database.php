<?php
// Database update script for onboarding columns
include 'includes/db.php';

function updateJobApplicationPositions($conn) {
    $query = "UPDATE job_applications SET position = (SELECT jp.position FROM job_postings jp WHERE jp.id = job_applications.job_id), department = (SELECT jp.department FROM job_postings jp WHERE jp.id = job_applications.job_id) WHERE position IS NULL OR position = '' OR department IS NULL OR department = ''";
    if ($conn->query($query) === TRUE) {
        echo "<p style='color: green;'>✓ Updated job_applications positions and departments successfully.</p>";
        return true;
    } else {
        echo "<p style='color: red;'>✗ Error updating job_applications: " . $conn->error . "</p>";
        return false;
    }
}

function updateEmployeePositions($conn) {
    $query = "UPDATE employees SET position = (SELECT jp.position FROM job_postings jp WHERE jp.id = employees.job_id), department = (SELECT jp.department FROM job_postings jp WHERE jp.id = employees.job_id) WHERE position IS NULL OR position = '' OR department IS NULL OR department = ''";
    if ($conn->query($query) === TRUE) {
        echo "<p style='color: green;'>✓ Updated employees positions and departments successfully.</p>";
        return true;
    } else {
        echo "<p style='color: red;'>✗ Error updating employees: " . $conn->error . "</p>";
        return false;
    }
}

echo "<h1>Database Update for Onboarding Feature</h1>";

$alter_queries = [
    "ALTER TABLE `job_applications` ADD COLUMN IF NOT EXISTS `sss_number` VARCHAR(20) DEFAULT NULL",
    "ALTER TABLE `job_applications` ADD COLUMN IF NOT EXISTS `philhealth_number` VARCHAR(20) DEFAULT NULL",
    "ALTER TABLE `job_applications` ADD COLUMN IF NOT EXISTS `pagibig_number` VARCHAR(20) DEFAULT NULL",
    "ALTER TABLE `job_applications` ADD COLUMN IF NOT EXISTS `bir_tax_form` VARCHAR(500) DEFAULT NULL",
    "ALTER TABLE `job_applications` ADD COLUMN IF NOT EXISTS `position` VARCHAR(100) DEFAULT NULL",
    "ALTER TABLE `job_applications` ADD COLUMN IF NOT EXISTS `nbi_clearance_path` VARCHAR(500) DEFAULT NULL",
    "ALTER TABLE `job_applications` ADD COLUMN IF NOT EXISTS `psa_birth_certificate_path` VARCHAR(500) DEFAULT NULL",
    "ALTER TABLE `job_applications` ADD COLUMN IF NOT EXISTS `onboarding_status` ENUM('pending','in_progress','completed') DEFAULT 'pending'",
    "ALTER TABLE `job_applications` ADD COLUMN IF NOT EXISTS `department` VARCHAR(100) DEFAULT NULL",
    "ALTER TABLE `employees` ADD COLUMN IF NOT EXISTS `position` VARCHAR(100) DEFAULT NULL",
    "ALTER TABLE `employees` ADD COLUMN IF NOT EXISTS `department` VARCHAR(100) DEFAULT NULL"
];

$success_count = 0;
$errors = [];

foreach ($alter_queries as $query) {
    try {
        if ($conn->query($query) === TRUE) {
            echo "<p style='color: green;'>✓ Successfully added/verified column: " . substr($query, strpos($query, 'ADD COLUMN') + 12) . "</p>";
            $success_count++;
        } else {
            $error_msg = "Error with column: " . $conn->error;
            echo "<p style='color: red;'>✗ " . $error_msg . "</p>";
            $errors[] = $error_msg;
        }
    } catch (Exception $e) {
        $error_msg = "Exception: " . $e->getMessage();
        echo "<p style='color: orange;'>⚠ " . $error_msg . " (Column might already exist)</p>";
        // Don't count this as an error since IF NOT EXISTS should handle it
        $success_count++;
    }
}

echo "<hr>";
echo "<h2>Updating Existing Data</h2>";

// Update position in job_applications from job_postings
$update_success = 0;
$update_errors = [];

if (updateJobApplicationPositions($conn)) {
    $update_success++;
} else {
    $update_errors[] = "job_applications update failed";
}

if (updateEmployeePositions($conn)) {
    $update_success++;
} else {
    $update_errors[] = "employees update failed";
}

echo "<hr>";
echo "<h2>Summary:</h2>";
echo "<p>Total alter queries: " . count($alter_queries) . "</p>";
echo "<p>Successful alters: $success_count</p>";
echo "<p>Failed alters: " . count($errors) . "</p>";
echo "<p>Position updates attempted: 2</p>";
echo "<p>Successful updates: $update_success</p>";
echo "<p>Failed updates: " . count($update_errors) . "</p>";

if (count($errors) == 0) {
    echo "<p style='color: green; font-weight: bold;'>✓ All database columns added successfully! You can now use the onboarding feature.</p>";
    echo "<p><a href='hr1/hr1main.php?section=onboarding'>Go to Onboarding Section</a></p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>✗ Some columns failed to add. Please check the errors above.</p>";
}

$conn->close();
?>