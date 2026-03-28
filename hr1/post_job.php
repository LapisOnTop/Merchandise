<?php
include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $department = $_POST['department'];
    $branch = $_POST['branch_select'] === 'other' ? $_POST['branch'] : $_POST['branch_select'];
    $position = $_POST['position'];
    $num_applicants = $_POST['num_applicants'];
    $requirements = $_POST['requirements'];

    // Validate required fields
    if (empty($department) || empty($branch) || empty($position) || empty($num_applicants) || empty($requirements)) {
        die("Error: All fields are required");
    }

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO job_postings (department, branch, position, num_applicants, requirements) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $department, $branch, $position, $num_applicants, $requirements);

    if ($stmt->execute()) {
        // Redirect back to the main page with recruitment section active
        header("Location: hr1main.php?section=recruitment");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "This page should be accessed via POST method.";
}
?>