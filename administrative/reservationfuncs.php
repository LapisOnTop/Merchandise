<?php
require_once '../includes/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'fetch') {
    $res = $conn->query("SELECT * FROM facility_reservations ORDER BY created_at DESC");
    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;
    echo json_encode($rows);

} elseif ($action === 'add') {
    $stmt = $conn->prepare("INSERT INTO facility_reservations (room, type, date, duration, requester, rank, status) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param('sssssss', $_POST['room'], $_POST['type'], $_POST['date'], $_POST['duration'], $_POST['requester'], $_POST['rank'], $_POST['status']);
    echo $stmt->execute() ? 'success' : 'error';

} elseif ($action === 'approve') {
    $stmt = $conn->prepare("UPDATE facility_reservations SET status='approved' WHERE id=?");
    $stmt->bind_param('i', $_POST['id']);
    echo $stmt->execute() ? 'success' : 'error';

} elseif ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM facility_reservations WHERE id=?");
    $stmt->bind_param('i', $_POST['id']);
    echo $stmt->execute() ? 'success' : 'error';
}