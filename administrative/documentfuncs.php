<?php
require_once '../includes/db.php';

function uploadDocument($conn, $title, $category, $uploadedBy, $file) {
    $dir = 'uploads/documents/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $fileName = time() . '_' . basename($file['name']);
    $filePath = $dir . $fileName;
    if (!move_uploaded_file($file['tmp_name'], $filePath)) return false;
    $stmt = $conn->prepare("INSERT INTO documents (title, category, file_path, file_name, uploaded_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $title, $category, $filePath, $file['name'], $uploadedBy);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

function getDocuments($conn) {
    $stmt = $conn->prepare("SELECT * FROM documents ORDER BY uploaded_at DESC");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $result;
}

function updateDocumentStatus($conn, $id, $status) {
    $stmt = $conn->prepare("UPDATE documents SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload') {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $uploadedBy = trim($_POST['uploaded_by'] ?? 'Admin');
        if ($title && $category && isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
            echo uploadDocument($conn, $title, $category, $uploadedBy, $_FILES['file']) ? 'success' : 'error';
        } else {
            echo 'error';
        }
    }

    if (in_array($action, ['approve', 'archive', 'restore'])) {
        $id = (int)($_POST['id'] ?? 0);
        $map = ['approve' => 'approved', 'archive' => 'archived', 'restore' => 'pending'];
        echo $id && updateDocumentStatus($conn, $id, $map[$action]) ? 'success' : 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'fetch') {
    header('Content-Type: application/json');
    echo json_encode(getDocuments($conn));
}
?>