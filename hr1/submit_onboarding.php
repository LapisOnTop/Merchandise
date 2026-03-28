<?php
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = $_POST['application_id'] ?? null;

    if (!$application_id) {
        http_response_code(400);
        echo json_encode(["error" => "Missing application_id"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE job_applications SET onboarding_status = 'completed' WHERE id = ?");
    $stmt->bind_param('i', $application_id);

    if ($stmt->execute()) {
        // Transfer data to employees table
        $insert_stmt = $conn->prepare("INSERT INTO employees (job_id, full_name, email, phone, home_address, date_of_birth, gender, civil_status, nationality, resume_path, valid_id_path, application_date, status, sss_number, philhealth_number, pagibig_number, bir_tax_form, nbi_clearance_path, psa_birth_certificate_path, onboarding_status, department, position) SELECT job_id, full_name, email, phone, home_address, date_of_birth, gender, civil_status, nationality, resume_path, valid_id_path, application_date, status, sss_number, philhealth_number, pagibig_number, bir_tax_form, nbi_clearance_path, psa_birth_certificate_path, onboarding_status, department, position FROM job_applications WHERE id = ?");
        $insert_stmt->bind_param('i', $application_id);
        $insert_stmt->execute();
        $insert_stmt->close();

        echo json_encode(["success" => true]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Database update failed"]);
    }

    $stmt->close();
}

$conn->close();
