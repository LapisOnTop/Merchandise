<?php
require_once '../includes/db.php';
header('Content-Type: application/json');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add') {
    $stmt = $conn->prepare("INSERT INTO visitor_log (name, contact, reason) VALUES (?,?,?)");
    $stmt->bind_param('sss', $_POST['name'], $_POST['contact'], $_POST['reason']);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO visitor_audit_trail (visitor_id, visitor_name, action) VALUES (?,?,'Visitor Checked In')");
    $stmt->bind_param('is', $id, $_POST['name']);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'id' => $id]);
}

elseif ($action === 'leave') {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("UPDATE visitor_log SET status='Left', time_out=NOW() WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO visitor_audit_trail (visitor_id, visitor_name, action) SELECT id, name, 'Visitor Left' FROM visitor_log WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
}

elseif ($action === 'list') {
    $result = $conn->query("SELECT * FROM visitor_log ORDER BY time_in DESC");
    $rows = [];
    while ($r = $result->fetch_assoc()) $rows[] = $r;
    echo json_encode($rows);
}

elseif ($action === 'audit') {
    $result = $conn->query("SELECT * FROM visitor_audit_trail ORDER BY timestamp DESC");
    $rows = [];
    while ($r = $result->fetch_assoc()) $rows[] = $r;
    echo json_encode($rows);
}

$conn->close();
?>