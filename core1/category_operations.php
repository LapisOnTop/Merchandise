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
    if ($action === 'add_category') {
        // Add new category
        $category_name = $conn->real_escape_string($_POST['category_name']);
        $description = $conn->real_escape_string($_POST['description']);

        // Check if category already exists
        $checkSql = "SELECT id FROM product_categories WHERE category_name = '$category_name'";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult->num_rows > 0) {
            throw new Exception("Category already exists!");
        }

        $sql = "INSERT INTO product_categories (category_name, description, status) 
                VALUES ('$category_name', '$description', 'active')";

        if ($conn->query($sql)) {
            $response['success'] = true;
            $response['message'] = 'Category added successfully!';
            $response['category_id'] = $conn->insert_id;
        } else {
            throw new Exception("Error adding category: " . $conn->error);
        }

    } elseif ($action === 'update_category') {
        // Update existing category
        $category_id = intval($_POST['category_id']);
        $category_name = $conn->real_escape_string($_POST['category_name']);
        $description = $conn->real_escape_string($_POST['description']);

        // Check if category name is used by another category
        $checkSql = "SELECT id FROM product_categories WHERE category_name = '$category_name' AND id != $category_id";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult->num_rows > 0) {
            throw new Exception("Category name already exists!");
        }

        $sql = "UPDATE product_categories SET 
                category_name = '$category_name', 
                description = '$description' 
                WHERE id = $category_id";

        if ($conn->query($sql)) {
            $response['success'] = true;
            $response['message'] = 'Category updated successfully!';
        } else {
            throw new Exception("Error updating category: " . $conn->error);
        }

    } elseif ($action === 'delete_category') {
        // Delete category
        $category_id = intval($_POST['category_id']);

        // Check if category has products
        $checkSql = "SELECT COUNT(*) as count FROM products WHERE category_id = $category_id";
        $checkResult = $conn->query($checkSql);
        $row = $checkResult->fetch_assoc();

        if ($row['count'] > 0) {
            throw new Exception("Cannot delete category with existing products!");
        }

        $sql = "DELETE FROM product_categories WHERE id = $category_id";

        if ($conn->query($sql)) {
            $response['success'] = true;
            $response['message'] = 'Category deleted successfully!';
        } else {
            throw new Exception("Error deleting category: " . $conn->error);
        }

    } elseif ($action === 'get_categories') {
        // Get all categories
        $sql = "SELECT c.id, c.category_name, c.description, c.status, COUNT(p.id) as product_count 
                FROM product_categories c 
                LEFT JOIN products p ON c.id = p.category_id 
                GROUP BY c.id 
                ORDER BY c.category_name ASC";

        $result = $conn->query($sql);
        $categories = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }

        $response['success'] = true;
        $response['categories'] = $categories;

    } elseif ($action === 'get_category') {
        // Get single category
        $category_id = intval($_POST['category_id']);

        $sql = "SELECT * FROM product_categories WHERE id = $category_id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $response['success'] = true;
            $response['category'] = $result->fetch_assoc();
        } else {
            throw new Exception("Category not found!");
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
