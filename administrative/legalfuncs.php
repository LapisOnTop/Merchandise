<?php
require_once '../includes/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$target = $_GET['target'] ?? $_POST['target'] ?? '';

if ($action === 'fetch' && $target === 'contracts') {
    $r = $conn->query("SELECT * FROM contracts ORDER BY created_at DESC");
    $rows = [];
    while ($row = $r->fetch_assoc()) $rows[] = $row;
    echo json_encode($rows);
    exit;
}

if ($action === 'fetch' && $target === 'matters') {
    $r = $conn->query("SELECT * FROM matters ORDER BY created_at DESC");
    $rows = [];
    while ($row = $r->fetch_assoc()) $rows[] = $row;
    echo json_encode($rows);
    exit;
}

if ($action === 'add' && $target === 'contracts') {
    $s = $conn->prepare("INSERT INTO contracts (contract_id, parties, expiry_date) VALUES (?,?,?)");
    $s->bind_param('sss', $_POST['contract_id'], $_POST['parties'], $_POST['expiry_date']);
    echo $s->execute() ? 'success' : 'error';
    exit;
}

if ($action === 'add' && $target === 'matters') {
    $s = $conn->prepare("INSERT INTO matters (title, type, status, assigned_to) VALUES (?,?,'open',?)");
    $s->bind_param('sss', $_POST['title'], $_POST['type'], $_POST['assigned_to']);
    echo $s->execute() ? 'success' : 'error';
    exit;
}

if ($action === 'archive') {
    $s = $conn->prepare("UPDATE contracts SET status='archived' WHERE id=?");
    $s->bind_param('i', $_POST['id']);
    echo $s->execute() ? 'success' : 'error';
    exit;
}

if ($action === 'status') {
    $s = $conn->prepare("UPDATE matters SET status=? WHERE id=?");
    $s->bind_param('si', $_POST['status'], $_POST['id']);
    echo $s->execute() ? 'success' : 'error';
    exit;
}

if ($action === 'delete' && $target === 'contracts') {
    $s = $conn->prepare("DELETE FROM contracts WHERE id=?");
    $s->bind_param('i', $_POST['id']);
    echo $s->execute() ? 'success' : 'error';
    exit;
}

if ($action === 'delete' && $target === 'matters') {
    $s = $conn->prepare("DELETE FROM matters WHERE id=?");
    $s->bind_param('i', $_POST['id']);
    echo $s->execute() ? 'success' : 'error';
    exit;
}