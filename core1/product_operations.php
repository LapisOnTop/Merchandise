<?php
include '../includes/db.php';

// Auto-create tables if they don't exist
function ensureTablesExist($conn) {
    // Check if product_categories table exists
    $result = $conn->query("SHOW TABLES LIKE 'product_categories'");
    if ($result->num_rows == 0) {
        // Create categories table
        $conn->query("CREATE TABLE product_categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            category_name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
    
    // Check if products table exists
    $result = $conn->query("SHOW TABLES LIKE 'products'");
    if ($result->num_rows == 0) {
        // Create products table
        $conn->query("CREATE TABLE products (
            id INT PRIMARY KEY AUTO_INCREMENT,
            sku VARCHAR(50) NOT NULL UNIQUE,
            barcode VARCHAR(100),
            product_name VARCHAR(255) NOT NULL,
            description TEXT,
            category_id INT NOT NULL,
            cost_price DECIMAL(10, 2) NOT NULL,
            store_price DECIMAL(10, 2) NOT NULL,
            reorder_level INT DEFAULT 0,
            stock_quantity INT DEFAULT 0,
            status ENUM('active', 'inactive', 'low_stock') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_category (category_id),
            FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}

ensureTablesExist($conn);

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : '';
$response = array('success' => false, 'message' => '');

try {
    if ($action === 'add_product') {
        // Add new product
        $sku = $conn->real_escape_string($_POST['sku']);
        $barcode = $conn->real_escape_string($_POST['barcode']);
        $product_name = $conn->real_escape_string($_POST['product_name']);
        $description = $conn->real_escape_string($_POST['description']);
        $category_id = intval($_POST['category_id']);
        $cost_price = floatval($_POST['cost_price']);
        $store_price = floatval($_POST['store_price']);
        $reorder_level = intval($_POST['reorder_level']);
        $stock_quantity = intval($_POST['stock_quantity'] ?? 0);

        // Check if SKU already exists
        $checkSql = "SELECT id FROM products WHERE sku = '$sku'";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult->num_rows > 0) {
            throw new Exception("SKU already exists!");
        }

        $sql = "INSERT INTO products (sku, barcode, product_name, description, category_id, cost_price, store_price, reorder_level, stock_quantity, status) 
                VALUES ('$sku', '$barcode', '$product_name', '$description', $category_id, $cost_price, $store_price, $reorder_level, $stock_quantity, 'active')";

        if ($conn->query($sql)) {
            $response['success'] = true;
            $response['message'] = 'Product added successfully!';
            $response['product_id'] = $conn->insert_id;
        } else {
            throw new Exception("Error adding product: " . $conn->error);
        }

    } elseif ($action === 'update_product') {
        // Update existing product
        $product_id = intval($_POST['product_id']);
        $sku = $conn->real_escape_string($_POST['sku']);
        $barcode = $conn->real_escape_string($_POST['barcode']);
        $product_name = $conn->real_escape_string($_POST['product_name']);
        $description = $conn->real_escape_string($_POST['description']);
        $category_id = intval($_POST['category_id']);
        $cost_price = floatval($_POST['cost_price']);
        $store_price = floatval($_POST['store_price']);
        $reorder_level = intval($_POST['reorder_level']);

        // Check if SKU is used by another product
        $checkSql = "SELECT id FROM products WHERE sku = '$sku' AND id != $product_id";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult->num_rows > 0) {
            throw new Exception("SKU already exists!");
        }

        $sql = "UPDATE products SET 
                sku = '$sku', 
                barcode = '$barcode', 
                product_name = '$product_name', 
                description = '$description', 
                category_id = $category_id, 
                cost_price = $cost_price, 
                store_price = $store_price, 
                reorder_level = $reorder_level 
                WHERE id = $product_id";

        if ($conn->query($sql)) {
            $response['success'] = true;
            $response['message'] = 'Product updated successfully!';
        } else {
            throw new Exception("Error updating product: " . $conn->error);
        }

    } elseif ($action === 'delete_product') {
        // Delete product
        $product_id = intval($_POST['product_id']);

        $sql = "DELETE FROM products WHERE id = $product_id";

        if ($conn->query($sql)) {
            $response['success'] = true;
            $response['message'] = 'Product deleted successfully!';
        } else {
            throw new Exception("Error deleting product: " . $conn->error);
        }

    } elseif ($action === 'get_products') {
        // Get all products
        $category_filter = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $status_filter = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : '';
        $search = isset($_POST['search']) ? $conn->real_escape_string($_POST['search']) : '';

        $sql = "SELECT p.*, c.category_name FROM products p 
                LEFT JOIN product_categories c ON p.category_id = c.id 
                WHERE 1=1";

        if ($category_filter > 0) {
            $sql .= " AND p.category_id = $category_filter";
        }
        if (!empty($status_filter)) {
            $sql .= " AND p.status = '$status_filter'";
        }
        if (!empty($search)) {
            $sql .= " AND (p.sku LIKE '%$search%' OR p.barcode LIKE '%$search%' OR p.product_name LIKE '%$search%')";
        }

        $sql .= " ORDER BY p.created_at DESC";

        $result = $conn->query($sql);
        $products = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        $response['success'] = true;
        $response['products'] = $products;

    } elseif ($action === 'get_product') {
        // Get single product
        $product_id = intval($_POST['product_id']);

        $sql = "SELECT * FROM products WHERE id = $product_id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $response['success'] = true;
            $response['product'] = $result->fetch_assoc();
        } else {
            throw new Exception("Product not found!");
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
