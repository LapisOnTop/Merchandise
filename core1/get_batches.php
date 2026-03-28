<?php
include '../includes/db.php';

header('Content-Type: application/json');

$product_id = intval($_GET['product_id'] ?? 0);

if ($product_id > 0) {
    $result = $conn->query("SELECT batch_number, inventory_quantity, store_quantity FROM inventory WHERE product_id = $product_id AND batch_number IS NOT NULL AND batch_number != '' ORDER BY batch_number");
    
    $batches = [];
    while ($row = $result->fetch_assoc()) {
        $batches[] = $row;
    }
    
    echo json_encode(['success' => true, 'batches' => $batches]);
} else {
    echo json_encode(['success' => false, 'batches' => []]);
}
?>