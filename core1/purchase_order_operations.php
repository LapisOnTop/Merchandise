<?php
include '../includes/db.php';

// Ensure required tables exist
function ensurePurchaseOrderTables($conn) {
    // Purchase orders table
    $result = $conn->query("SHOW TABLES LIKE 'purchase_orders'");
    if ($result->num_rows == 0) {
        $conn->query("CREATE TABLE purchase_orders (
            id INT PRIMARY KEY AUTO_INCREMENT,
            po_number VARCHAR(64) NOT NULL UNIQUE,
            supplier_name VARCHAR(255) DEFAULT NULL,
            expected_delivery_date DATE DEFAULT NULL,
            status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
            total_cost DECIMAL(14, 2) DEFAULT 0.00,
            total_profit DECIMAL(14, 2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    // Purchase order items table
    $result = $conn->query("SHOW TABLES LIKE 'purchase_order_items'");
    if ($result->num_rows == 0) {
        $conn->query("CREATE TABLE purchase_order_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            po_id INT NOT NULL,
            product_id INT DEFAULT NULL,
            quantity INT NOT NULL DEFAULT 1,
            unit_cost DECIMAL(12, 2) DEFAULT 0.00,
            unit_price DECIMAL(12, 2) DEFAULT 0.00,
            total_cost DECIMAL(14, 2) DEFAULT 0.00,
            total_price DECIMAL(14, 2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (po_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}

ensurePurchaseOrderTables($conn);

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : '';
$response = ['success' => false, 'message' => ''];

try {
    if ($action === 'get_products') {
        $result = $conn->query("SELECT id, product_name, sku, cost_price, store_price FROM products ORDER BY product_name ASC");
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        $response['success'] = true;
        $response['products'] = $products;

    } elseif ($action === 'get_po_list') {
        $sql = "SELECT po.*, 
                       (SELECT COUNT(*) FROM purchase_order_items i WHERE i.po_id = po.id) AS item_count
                FROM purchase_orders po
                ORDER BY po.created_at DESC";
        $result = $conn->query($sql);
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        $response['success'] = true;
        $response['orders'] = $orders;

    } elseif ($action === 'get_po_details') {
        $po_id = intval($_POST['po_id']);
        $sql = "SELECT po.* FROM purchase_orders po WHERE po.id = $po_id";
        $order = $conn->query($sql)->fetch_assoc();

        $items = [];
        $itemResult = $conn->query("SELECT i.*, p.product_name, p.sku FROM purchase_order_items i LEFT JOIN products p ON i.product_id = p.id WHERE i.po_id = $po_id");
        while ($row = $itemResult->fetch_assoc()) {
            $items[] = $row;
        }

        $response['success'] = true;
        $response['order'] = $order;
        $response['items'] = $items;

    } elseif ($action === 'create_po') {
        $supplier_name = isset($_POST['supplier_name']) ? $conn->real_escape_string(trim($_POST['supplier_name'])) : null;
        $expected_delivery_date = isset($_POST['expected_delivery_date']) ? $conn->real_escape_string($_POST['expected_delivery_date']) : null;
        $itemsJson = isset($_POST['items']) ? $_POST['items'] : '[]';
        $items = json_decode($itemsJson, true);

        if (!$items || !is_array($items) || count($items) === 0) {
            throw new Exception('Please add at least one product to the purchase order.');
        }

        // Create the base purchase order
        $insertPoSql = "INSERT INTO purchase_orders (po_number, supplier_name, expected_delivery_date) VALUES ('', " . ($supplier_name ? "'$supplier_name'" : "NULL") . ", " . ($expected_delivery_date ? "'$expected_delivery_date'" : "NULL") . ")";
        if (!$conn->query($insertPoSql)) {
            throw new Exception('Unable to create purchase order: ' . $conn->error);
        }

        $poId = $conn->insert_id;
        $poNumber = 'PO-' . date('Ymd') . '-' . str_pad($poId, 4, '0', STR_PAD_LEFT);
        $conn->query("UPDATE purchase_orders SET po_number = '$poNumber' WHERE id = $poId");

        $totalCost = 0;
        $totalProfit = 0;

        foreach ($items as $item) {
            $productId = intval($item['product_id']);
            $quantity = max(1, intval($item['quantity']));
            $unitCost = floatval($item['unit_cost']);
            $unitPrice = floatval($item['unit_price']);
            $lineCost = $quantity * $unitCost;
            $linePrice = $quantity * $unitPrice;
            $lineProfit = $linePrice - $lineCost;

            $totalCost += $lineCost;
            $totalProfit += $lineProfit;

            $conn->query("INSERT INTO purchase_order_items (po_id, product_id, quantity, unit_cost, unit_price, total_cost, total_price)
                         VALUES ($poId, $productId, $quantity, $unitCost, $unitPrice, $lineCost, $linePrice)");
        }

        $conn->query("UPDATE purchase_orders SET total_cost = $totalCost, total_profit = $totalProfit WHERE id = $poId");

        $response['success'] = true;
        $response['message'] = 'Purchase order created successfully.';
        $response['po_id'] = $poId;
        $response['po_number'] = $poNumber;

    } elseif ($action === 'update_po_status') {
        $po_id = intval($_POST['po_id']);
        $status = $conn->real_escape_string($_POST['status']);

        if (!in_array($status, ['Pending', 'Approved', 'Rejected'])) {
            throw new Exception('Invalid status');
        }

        $conn->query("UPDATE purchase_orders SET status = '$status' WHERE id = $po_id");

        $response['success'] = true;
        $response['message'] = 'Purchase order status updated.';

    } else {
        throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
