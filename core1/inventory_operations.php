<?php
include '../includes/db.php';

// Auto-create tables if needed
function ensureTables($conn) {
    // Check if inventory table exists
    $result = $conn->query("SHOW TABLES LIKE 'inventory'");
    if ($result->num_rows == 0) {
        $conn->query("CREATE TABLE inventory (
            id INT PRIMARY KEY AUTO_INCREMENT,
            product_id INT NOT NULL,
            batch_number VARCHAR(50),
            inventory_quantity INT DEFAULT 0,
            store_quantity INT DEFAULT 0,
            expiry_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_product (product_id),
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}

ensureTables($conn);

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : '';
$response = array('success' => false, 'message' => '');

try {
    if ($action === 'get_inventory') {
        // Get aggregated inventory with product details
        $sql = "SELECT 
                    p.id as product_id,
                    p.sku,
                    p.barcode,
                    p.product_name,
                    p.reorder_level,
                    p.status as product_status,
                    pc.category_name,
                    pc.id as category_id,
                    COALESCE(SUM(inv.inventory_quantity), 0) as total_inventory_quantity,
                    COALESCE(SUM(inv.store_quantity), 0) as total_store_quantity,
                    COUNT(inv.id) as batch_count
                FROM products p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                LEFT JOIN inventory inv ON p.id = inv.product_id
                GROUP BY p.id, p.sku, p.barcode, p.product_name, p.reorder_level, p.status, pc.category_name, pc.id
                ORDER BY p.product_name ASC";
        
        $result = $conn->query($sql);
        $inventory = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $inventory[] = $row;
            }
        }
        
        $response['success'] = true;
        $response['inventory'] = $inventory;
        
    } elseif ($action === 'add_inventory') {
        // Add or update inventory
        $product_id = intval($_POST['product_id']);
        $batch_number = $conn->real_escape_string($_POST['batch_number']);
        $inventory_quantity = intval($_POST['inventory_quantity']);
        $store_quantity = intval($_POST['store_quantity']);
        $expiry_date = $conn->real_escape_string($_POST['expiry_date']);
        
        // Check if inventory exists for this product and batch
        $check = $conn->query("SELECT id FROM inventory WHERE product_id = $product_id AND batch_number = '$batch_number'");
        
        if ($check->num_rows > 0) {
            // Update existing batch
            $sql = "UPDATE inventory SET 
                    inventory_quantity = inventory_quantity + $inventory_quantity,
                    store_quantity = store_quantity + $store_quantity,
                    expiry_date = '$expiry_date'
                    WHERE product_id = $product_id AND batch_number = '$batch_number'";
        } else {
            // Insert new batch
            $sql = "INSERT INTO inventory (product_id, batch_number, inventory_quantity, store_quantity, expiry_date)
                    VALUES ($product_id, '$batch_number', $inventory_quantity, $store_quantity, '$expiry_date')";
        }
        
        if ($conn->query($sql)) {
            $response['success'] = true;
            $response['message'] = 'Inventory updated successfully!';
        } else {
            throw new Exception("Error updating inventory: " . $conn->error);
        }
        
    } elseif ($action === 'update_inventory') {
        $inventory_id = intval($_POST['inventory_id']);
        $batch_number = $conn->real_escape_string($_POST['batch_number']);
        $inventory_quantity = intval($_POST['inventory_quantity']);
        $store_quantity = intval($_POST['store_quantity']);
        $expiry_date = $conn->real_escape_string($_POST['expiry_date']);
        
        $sql = "UPDATE inventory SET 
                batch_number = '$batch_number',
                inventory_quantity = $inventory_quantity,
                store_quantity = $store_quantity,
                expiry_date = '$expiry_date'
                WHERE id = $inventory_id";
        
        if ($conn->query($sql)) {
            $response['success'] = true;
            $response['message'] = 'Inventory batch updated successfully!';
        } else {
            throw new Exception("Error updating inventory batch: " . $conn->error);
        }
        
    } elseif ($action === 'get_batch') {
        $id = intval($_POST['id']);
        $sql = "SELECT * FROM inventory WHERE id = $id";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $response['success'] = true;
            $response['batch'] = $result->fetch_assoc();
        } else {
            throw new Exception("Batch not found");
        }
        
    } elseif ($action === 'get_inventory_tracking') {
        // Get detailed inventory with batch information
        $sql = "SELECT 
                    inv.id,
                    inv.product_id,
                    inv.batch_number,
                    inv.inventory_quantity,
                    inv.store_quantity,
                    inv.expiry_date,
                    inv.created_at,
                    p.sku,
                    p.product_name,
                    pc.category_name,
                    pc.id as category_id
                FROM inventory inv
                LEFT JOIN products p ON inv.product_id = p.id
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                ORDER BY p.product_name ASC, inv.created_at DESC";
        
        $result = $conn->query($sql);
        $inventory = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $inventory[] = $row;
            }
        }
        
        $response['success'] = true;
        $response['inventory'] = $inventory;
    } elseif ($action === 'delete_inventory') {
        $id = intval($_POST['id']);
        $sql = "DELETE FROM inventory WHERE id = $id";
        
        if ($conn->query($sql)) {
            $response['success'] = true;
            $response['message'] = 'Inventory record deleted successfully!';
        } else {
            throw new Exception("Error deleting inventory record: " . $conn->error);
        }
    } else {
        throw new Exception("Invalid action!");
    }
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
