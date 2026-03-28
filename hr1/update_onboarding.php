<?php
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $application_id = $_POST['application_id'];
    $sss_number = $_POST['sss_number'];
    $philhealth_number = $_POST['philhealth_number'];
    $pagibig_number = $_POST['pagibig_number'];

    // Handle file uploads
    $bir_tax_form_path = null;
    $nbi_clearance_path = '';
    $psa_birth_certificate_path = '';

    // BIR Tax Form upload (store as file path)
    if (isset($_FILES['bir_tax_form']) && $_FILES['bir_tax_form']['error'] == 0) {
        $bir_file = $_FILES['bir_tax_form'];
        $bir_extension = pathinfo($bir_file['name'], PATHINFO_EXTENSION);
        $bir_filename = 'bir_' . $application_id . '_' . time() . '.' . $bir_extension;
        $bir_target_path = '../uploads/applications/' . $bir_filename;

        if (move_uploaded_file($bir_file['tmp_name'], $bir_target_path)) {
            $bir_tax_form_path = 'uploads/applications/' . $bir_filename;
        }
    }

    // Preserve existing values when no new files/values are provided
    $stmt = $conn->prepare("SELECT sss_number, philhealth_number, pagibig_number, bir_tax_form, nbi_clearance_path, psa_birth_certificate_path, onboarding_status FROM job_applications WHERE id = ?");
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $stmt->bind_result($existing_sss, $existing_philhealth, $existing_pagibig, $existing_bir, $existing_nbi, $existing_psa, $existing_status);
    $stmt->fetch();
    $stmt->close();

    if (is_null($bir_tax_form_path)) {
        $bir_tax_form_path = $existing_bir;
    }
    if (empty($nbi_clearance_path)) {
        $nbi_clearance_path = $existing_nbi;
    }
    if (empty($psa_birth_certificate_path)) {
        $psa_birth_certificate_path = $existing_psa;
    }

    // Determine onboarding status based on filled requirements
    $final_sss = trim($sss_number);
    $final_philhealth = trim($philhealth_number);
    $final_pagibig = trim($pagibig_number);

    // Count submitted requirements (6 total)
    $submitted_count = 0;
    if ($final_sss !== '') $submitted_count++;
    if ($final_philhealth !== '') $submitted_count++;
    if ($final_pagibig !== '') $submitted_count++;
    if (!empty($bir_tax_form_path)) $submitted_count++;
    if (!empty($nbi_clearance_path)) $submitted_count++;
    if (!empty($psa_birth_certificate_path)) $submitted_count++;

    // Update status based on count
    if ($submitted_count === 6) {
        $onboarding_status = 'completed';
    } elseif ($submitted_count >= 1) {
        $onboarding_status = 'in_progress';
    } else {
        $onboarding_status = 'pending';
    }

    // NBI Clearance upload
    if (isset($_FILES['nbi_clearance']) && $_FILES['nbi_clearance']['error'] == 0) {
        $nbi_file = $_FILES['nbi_clearance'];
        $nbi_extension = pathinfo($nbi_file['name'], PATHINFO_EXTENSION);
        $nbi_filename = 'nbi_' . $application_id . '_' . time() . '.' . $nbi_extension;
        $nbi_target_path = '../uploads/applications/' . $nbi_filename;

        if (move_uploaded_file($nbi_file['tmp_name'], $nbi_target_path)) {
            $nbi_clearance_path = 'uploads/applications/' . $nbi_filename;
        }
    }

    // PSA Birth Certificate upload
    if (isset($_FILES['psa_birth_certificate']) && $_FILES['psa_birth_certificate']['error'] == 0) {
        $psa_file = $_FILES['psa_birth_certificate'];
        $psa_extension = pathinfo($psa_file['name'], PATHINFO_EXTENSION);
        $psa_filename = 'psa_' . $application_id . '_' . time() . '.' . $psa_extension;
        $psa_target_path = '../uploads/applications/' . $psa_filename;

        if (move_uploaded_file($psa_file['tmp_name'], $psa_target_path)) {
            $psa_birth_certificate_path = 'uploads/applications/' . $psa_filename;
        }
    }

    // Check if onboarding columns exist, and add them if they don't
    $check_columns = $conn->query("SHOW COLUMNS FROM job_applications LIKE 'sss_number'");
    if ($check_columns->num_rows == 0) {
        // Columns don't exist, try to add them
        $alter_queries = [
            "ALTER TABLE `job_applications` ADD COLUMN IF NOT EXISTS `sss_number` VARCHAR(20) DEFAULT NULL",
            "ALTER TABLE `job_applications` ADD COLUMN IF NOT EXISTS `philhealth_number` VARCHAR(20) DEFAULT NULL",
            "ALTER TABLE `job_applications` ADD COLUMN IF NOT EXISTS `pagibig_number` VARCHAR(20) DEFAULT NULL",
            "ALTER TABLE `job_applications` ADD COLUMN IF NOT EXISTS `bir_tax_form` VARCHAR(500) DEFAULT NULL",
            "ALTER TABLE `job_applications` ADD COLUMN IF NOT EXISTS `nbi_clearance_path` VARCHAR(500) DEFAULT NULL",
            "ALTER TABLE `job_applications` ADD COLUMN IF NOT EXISTS `psa_birth_certificate_path` VARCHAR(500) DEFAULT NULL",
            "ALTER TABLE `job_applications` ADD COLUMN IF NOT EXISTS `onboarding_status` ENUM('pending','in_progress','completed') DEFAULT 'pending'"
        ];

        $columns_added = 0;
        foreach ($alter_queries as $query) {
            if ($conn->query($query) === TRUE) {
                $columns_added++;
            }
        }

        if ($columns_added < 7) {
            // Not all columns were added successfully
            header("Location: hr1main.php?section=onboarding&error=db_update_failed");
            exit();
        } else {
            // Columns were added successfully, continue with the update
            // We'll redirect with success at the end
        }
    }

    // Prepare update query
    $sql = "UPDATE job_applications SET
            sss_number = ?,
            philhealth_number = ?,
            pagibig_number = ?,
            bir_tax_form = ?,
            onboarding_status = ?";

    $params = [$sss_number, $philhealth_number, $pagibig_number, $bir_tax_form_path, $onboarding_status];
    $types = "sssss";

    if (!empty($nbi_clearance_path)) {
        $sql .= ", nbi_clearance_path = ?";
        $params[] = $nbi_clearance_path;
        $types .= "s";
    }

    if (!empty($psa_birth_certificate_path)) {
        $sql .= ", psa_birth_certificate_path = ?";
        $params[] = $psa_birth_certificate_path;
        $types .= "s";
    }

    $sql .= " WHERE id = ?";
    $params[] = $application_id;
    $types .= "i";

    $update_stmt = $conn->prepare($sql);
    $update_stmt->bind_param($types, ...$params);

    if ($update_stmt->execute()) {
        // Recalculate status based on actually saved data
        $select_stmt = $conn->prepare("SELECT sss_number, philhealth_number, pagibig_number, bir_tax_form, nbi_clearance_path, psa_birth_certificate_path FROM job_applications WHERE id = ?");
        $select_stmt->bind_param("i", $application_id);
        $select_stmt->execute();
        $select_stmt->bind_result($saved_sss, $saved_philhealth, $saved_pagibig, $saved_bir, $saved_nbi, $saved_psa);
        $select_stmt->fetch();
        $select_stmt->close();

        $submitted_count = 0;
        if (!empty(trim($saved_sss))) $submitted_count++;
        if (!empty(trim($saved_philhealth))) $submitted_count++;
        if (!empty(trim($saved_pagibig))) $submitted_count++;
        if (!empty($saved_bir)) $submitted_count++;
        if (!empty($saved_nbi)) $submitted_count++;
        if (!empty($saved_psa)) $submitted_count++;

        if ($submitted_count === 6) {
            $final_status = 'completed';
        } elseif ($submitted_count >= 1) {
            $final_status = 'in_progress';
        } else {
            $final_status = 'pending';
        }

        // Update status based on saved data
        $status_stmt = $conn->prepare("UPDATE job_applications SET onboarding_status = ? WHERE id = ?");
        $status_stmt->bind_param("si", $final_status, $application_id);
        $status_stmt->execute();
        $status_stmt->close();

        $update_stmt->close();

        header("Location: hr1main.php?section=onboarding&success=1");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
        $update_stmt->close();
    }
}

$conn->close();
?>