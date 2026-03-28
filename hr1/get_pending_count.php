<?php
include '../includes/db.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT COUNT(*) as count FROM job_applications WHERE status = 'pending'";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        echo json_encode(['count' => (int)$row['count']]);
    } else {
        echo json_encode(['count' => 0]);
    }
} catch (Exception $e) {
    echo json_encode(['count' => 0, 'error' => $e->getMessage()]);
}

$conn->close();
?>