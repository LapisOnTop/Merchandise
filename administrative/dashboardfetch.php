<?php
require_once '../includes/db.php';

$data = [];

$r = $conn->query("SELECT COUNT(*) as c FROM visitor_log");
$data['visitors'] = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) as c FROM documents");
$data['documents'] = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) as c FROM facility_reservations");
$data['reservations'] = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) as c FROM contracts");
$data['contracts'] = $r->fetch_assoc()['c'];

$stmt = $conn->prepare("SELECT name, DATE(time_in) as date, reason FROM visitor_log ORDER BY time_in DESC LIMIT 5");
$stmt->execute();
$data['recent_visitors'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("SELECT title, category, status FROM documents WHERE status = 'pending' ORDER BY uploaded_at DESC LIMIT 5");
$stmt->execute();
$data['pending_documents'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
header('Content-Type: application/json');
echo json_encode($data);