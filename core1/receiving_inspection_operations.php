<?php
include '../includes/db.php';

// Ensure receiving inspection table exists
function ensureReceivingTables($conn) {
    $result = $conn->query("SHOW TABLES LIKE 'receiving_inspections'");
    if ($result->num_rows == 0) {
        $conn->query("CREATE TABLE receiving_inspections (
            id INT PRIMARY KEY AUTO_INCREMENT,
            po_id INT NOT NULL,
            po_item_id INT NOT NULL,
            product_id INT NOT NULL,
            batch_number VARCHAR(100) DEFAULT NULL,
            expiry_date DATE DEFAULT NULL,
            received_qty INT DEFAULT 0,
            rejected_qty INT DEFAULT 0,
            inspection_status ENUM('Passed','Failed') DEFAULT 'Passed',
            inspection_notes TEXT,
            inspected_by VARCHAR(100) DEFAULT 'Admin',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (po_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
            FOREIGN KEY (po_item_id) REFERENCES purchase_order_items(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    // Create inspection reports table
    $result = $conn->query("SHOW TABLES LIKE 'inspection_reports'");
    if ($result->num_rows == 0) {
        $conn->query("CREATE TABLE inspection_reports (
            id INT PRIMARY KEY AUTO_INCREMENT,
            po_id INT NOT NULL,
            po_number VARCHAR(50) NOT NULL,
            supplier_name VARCHAR(255),
            inspection_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            total_items INT DEFAULT 0,
            total_received INT DEFAULT 0,
            total_rejected INT DEFAULT 0,
            overall_status ENUM('Passed','Failed','Partial') DEFAULT 'Passed',
            report_data JSON,
            created_by VARCHAR(100) DEFAULT 'Admin',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (po_id) REFERENCES purchase_orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}

ensureReceivingTables($conn);

header('Content-Type: application/json');
$action = isset($_POST['action']) ? $_POST['action'] : '';
$response = ['success' => false, 'message' => ''];

try {
    if ($action === 'get_approved_pos') {
        $sql = "SELECT po.id, po.po_number, po.supplier_name, po.expected_delivery_date, po.created_at
                FROM purchase_orders po
                LEFT JOIN inspection_reports ir ON po.id = ir.po_id
                WHERE po.status = 'Approved' AND ir.id IS NULL
                ORDER BY po.created_at DESC";
        $result = $conn->query($sql);
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        $response['success'] = true;
        $response['orders'] = $orders;

    } elseif ($action === 'get_po_items') {
        $po_id = intval($_POST['po_id']);
        $sql = "SELECT i.*, p.product_name, p.sku, p.store_price, p.cost_price
                FROM purchase_order_items i
                LEFT JOIN products p ON i.product_id = p.id
                WHERE i.po_id = $po_id";
        $result = $conn->query($sql);
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        $response['success'] = true;
        $response['items'] = $items;

    } elseif ($action === 'get_inspections') {
        $po_id = intval($_POST['po_id']);
        $sql = "SELECT ri.*, p.product_name, p.sku
                FROM receiving_inspections ri
                LEFT JOIN products p ON ri.product_id = p.id
                WHERE ri.po_id = $po_id
                ORDER BY ri.created_at DESC";
        $result = $conn->query($sql);
        $inspections = [];
        while ($row = $result->fetch_assoc()) {
            $inspections[] = $row;
        }
        $response['success'] = true;
        $response['inspections'] = $inspections;

    } elseif ($action === 'get_reports') {
        $sql = "SELECT id, po_id, po_number, supplier_name, inspection_date, total_items, total_received, total_rejected, overall_status, created_at
                FROM inspection_reports
                ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $reports = [];
        while ($row = $result->fetch_assoc()) {
            // Add a status field indicating the inspection is completed
            $row['status'] = 'Completed';
            $reports[] = $row;
        }
        $response['success'] = true;
        $response['reports'] = $reports;

    } elseif ($action === 'get_report_details') {
        $report_id = intval($_POST['report_id']);
        $sql = "SELECT * FROM inspection_reports WHERE id = $report_id";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $report = $result->fetch_assoc();
            $report['report_data'] = json_decode($report['report_data'], true);
            $response['success'] = true;
            $response['report'] = $report;
        } else {
            throw new Exception('Report not found');
        }

    } elseif ($action === 'save_inspection') {
        $po_id = intval($_POST['po_id']);
        $itemsJson = isset($_POST['items']) ? $_POST['items'] : '[]';
        $items = json_decode($itemsJson, true);

        if (!$items || !is_array($items) || count($items) === 0) {
            throw new Exception('Please provide inspection data.');
        }

        foreach ($items as $item) {
            $po_item_id = intval($item['po_item_id']);
            $product_id = intval($item['product_id']);
            $batch_number = $conn->real_escape_string($item['batch_number'] ?? '');
            $expiry_date = !empty($item['expiry_date']) ? $conn->real_escape_string($item['expiry_date']) : null;
            $received_qty = intval($item['received_qty']);
            $rejected_qty = intval($item['rejected_qty']);
            $inspection_status = in_array($item['inspection_status'], ['Passed', 'Failed']) ? $item['inspection_status'] : 'Passed';
            $inspection_notes = $conn->real_escape_string($item['inspection_notes'] ?? '');

            // Insert inspection record
            $sql = "INSERT INTO receiving_inspections (po_id, po_item_id, product_id, batch_number, expiry_date, received_qty, rejected_qty, inspection_status, inspection_notes)
                    VALUES ($po_id, $po_item_id, $product_id, '$batch_number', " . ($expiry_date ? "'$expiry_date'" : "NULL") . ", $received_qty, $rejected_qty, '$inspection_status', '$inspection_notes')";
            if (!$conn->query($sql)) {
                throw new Exception('Error saving inspection: ' . $conn->error);
            }

            // Update inventory based on received quantity
            // If an inventory record exists for same product+batch, add quantities, else create new
            $batchCond = $conn->real_escape_string($batch_number);
            $inventoryCheckSql = "SELECT id, inventory_quantity FROM inventory WHERE product_id = $product_id AND batch_number = '$batchCond'";
            $invRes = $conn->query($inventoryCheckSql);
            $newInventoryQty = $received_qty;

            if ($invRes && $invRes->num_rows > 0) {
                $invRow = $invRes->fetch_assoc();
                $newInventoryQty += intval($invRow['inventory_quantity']);
                $conn->query("UPDATE inventory SET inventory_quantity = $newInventoryQty, expiry_date = " . ($expiry_date ? "'$expiry_date'" : "NULL") . " WHERE id = " . intval($invRow['id']));
            } else {
                $conn->query("INSERT INTO inventory (product_id, batch_number, inventory_quantity, expiry_date) VALUES ($product_id, '$batchCond', $newInventoryQty, " . ($expiry_date ? "'$expiry_date'" : "NULL") . ")");
            }
        }

        // Generate inspection report
        $poInfoSql = "SELECT po_number, supplier_name FROM purchase_orders WHERE id = $po_id";
        $poResult = $conn->query($poInfoSql);
        $poInfo = $poResult->fetch_assoc();

        $totalItems = count($items);
        $totalReceived = array_sum(array_column($items, 'received_qty'));
        $totalRejected = array_sum(array_column($items, 'rejected_qty'));

        // Determine overall status
        $passedCount = count(array_filter($items, function($item) { return $item['inspection_status'] === 'Passed'; }));
        if ($passedCount === $totalItems) {
            $overallStatus = 'Passed';
        } elseif ($passedCount === 0) {
            $overallStatus = 'Failed';
        } else {
            $overallStatus = 'Partial';
        }

        // Prepare report data
        $reportData = [
            'po_id' => $po_id,
            'po_number' => $poInfo['po_number'],
            'supplier_name' => $poInfo['supplier_name'],
            'inspection_date' => date('Y-m-d H:i:s'),
            'items' => $items,
            'summary' => [
                'total_items' => $totalItems,
                'total_received' => $totalReceived,
                'total_rejected' => $totalRejected,
                'overall_status' => $overallStatus
            ]
        ];

        // Insert report
        $reportJson = $conn->real_escape_string(json_encode($reportData));
        $reportSql = "INSERT INTO inspection_reports (po_id, po_number, supplier_name, total_items, total_received, total_rejected, overall_status, report_data)
                      VALUES ($po_id, '{$poInfo['po_number']}', '{$poInfo['supplier_name']}', $totalItems, $totalReceived, $totalRejected, '$overallStatus', '$reportJson')";

        if (!$conn->query($reportSql)) {
            throw new Exception('Error generating inspection report: ' . $conn->error);
        }

        // Mark PO as completed after inspection
        $updatePoSql = "UPDATE purchase_orders SET status = 'Completed' WHERE id = $po_id";
        if (!$conn->query($updatePoSql)) {
            throw new Exception('Error updating PO status: ' . $conn->error);
        }

        $response['success'] = true;
        $response['message'] = 'Inspection completed successfully. Report generated.';
        $response['report_id'] = $conn->insert_id;

    } else {
        throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
