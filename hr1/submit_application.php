<?php
include '../includes/db.php';

// Create job_applications table if it doesn't exist
$table_sql = "CREATE TABLE IF NOT EXISTS job_applications (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    job_id INT(11) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    home_address TEXT NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say') NOT NULL,
    civil_status ENUM('single', 'married', 'divorced', 'widowed', 'separated') NOT NULL,
    nationality VARCHAR(50) NOT NULL,
    resume_path VARCHAR(255),
    valid_id_path VARCHAR(255),
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'reviewed', 'accepted', 'rejected') DEFAULT 'pending'
)";

if ($conn->query($table_sql) !== TRUE) {
    die("Error creating table: " . $conn->error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $job_id = $_POST['job_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $home_address = $_POST['home_address'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $civil_status = $_POST['civil_status'];
    $nationality = $_POST['nationality'];

    // Validate required fields
    if (empty($job_id) || empty($full_name) || empty($email) || empty($phone) || empty($home_address) ||
        empty($date_of_birth) || empty($gender) || empty($civil_status) || empty($nationality)) {
        die("Error: All required fields must be filled");
    }

    // Validate that job_id exists in job_postings table
    $check_job = $conn->prepare("SELECT id FROM job_postings WHERE id = ?");
    $check_job->bind_param("i", $job_id);
    $check_job->execute();
    $check_job->store_result();

    if ($check_job->num_rows == 0) {
        die("Error: Invalid job posting ID. The job you're applying for doesn't exist.");
    }
    $check_job->close();

    // Get position and department from job_postings
    $position_stmt = $conn->prepare("SELECT position, department FROM job_postings WHERE id = ?");
    $position_stmt->bind_param("i", $job_id);
    $position_stmt->execute();
    $position_stmt->bind_result($position, $department);
    $position_stmt->fetch();
    $position_stmt->close();

    // Handle file uploads
    $resume_path = '';
    $valid_id_path = '';

    $upload_dir = '../uploads/applications/';

    // Create upload directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Handle resume upload
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        $resume_name = time() . '_resume_' . basename($_FILES['resume']['name']);
        $resume_path = $upload_dir . $resume_name;

        if ($_FILES['resume']['size'] > 5 * 1024 * 1024) { // 5MB
            die("Error: Resume file size must be less than 5MB");
        }

        if (move_uploaded_file($_FILES['resume']['tmp_name'], $resume_path)) {
            $resume_path = 'uploads/applications/' . $resume_name;
        } else {
            die("Error uploading resume file");
        }
    }

    // Handle valid ID upload
    if (isset($_FILES['valid_id']) && $_FILES['valid_id']['error'] == 0) {
        $id_name = time() . '_id_' . basename($_FILES['valid_id']['name']);
        $valid_id_path = $upload_dir . $id_name;

        if ($_FILES['valid_id']['size'] > 2 * 1024 * 1024) { // 2MB
            die("Error: ID photo file size must be less than 2MB");
        }

        if (move_uploaded_file($_FILES['valid_id']['tmp_name'], $valid_id_path)) {
            $valid_id_path = 'uploads/applications/' . $id_name;
        } else {
            die("Error uploading ID photo file");
        }
    }

    // Insert application into database
    $stmt = $conn->prepare("INSERT INTO job_applications (
        job_id, position, department, full_name, email, phone, home_address, date_of_birth, gender, civil_status, nationality, resume_path, valid_id_path
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("issssssssssss",
        $job_id, $position, $department, $full_name, $email, $phone, $home_address, $date_of_birth, $gender, $civil_status, $nationality, $resume_path, $valid_id_path
    );

    if ($stmt->execute()) {
        // Success - redirect back to careers page with success message
        header("Location: career.php?success=1");
        exit();
    } else {
        echo "Error submitting application: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: career.php");
    exit();
}
?>