<?php
include '../includes/db.php';

// Check and auto-create tables if needed
function ensureTablesExist($conn) {
    $tables = [];
    
    // Check if product_categories table exists
    $result = $conn->query("SHOW TABLES LIKE 'product_categories'");
    if ($result->num_rows == 0) {
        $conn->query("CREATE TABLE product_categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            category_name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $tables[] = "product_categories";
    }
    
    // Check if products table exists
    $result = $conn->query("SHOW TABLES LIKE 'products'");
    if ($result->num_rows == 0) {
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
        $tables[] = "products";
    }
    
    // Check if product_movements table exists
    $result = $conn->query("SHOW TABLES LIKE 'product_movements'");
    if ($result->num_rows == 0) {
        $conn->query("CREATE TABLE product_movements (
            id INT PRIMARY KEY AUTO_INCREMENT,
            product_id INT NOT NULL,
            movement_type ENUM('in', 'out', 'adjustment') DEFAULT 'adjustment',
            quantity INT NOT NULL,
            reference_number VARCHAR(100),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_product (product_id),
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $tables[] = "product_movements";
    }
    
    return $tables;
}

$createdTables = ensureTablesExist($conn);
if (!empty($createdTables)) {
    // Insert sample categories if this is first run
    $checkCats = $conn->query("SELECT COUNT(*) as count FROM product_categories");
    $catRow = $checkCats->fetch_assoc();
    if ($catRow['count'] == 0) {
        $sampleCategories = ["Electronics", "Furniture", "Clothing", "Books", "Office Supplies"];
        foreach ($sampleCategories as $category) {
            $cat_name = $conn->real_escape_string($category);
            $conn->query("INSERT INTO product_categories (category_name, status) VALUES ('$cat_name', 'active')");
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Status</title>
    <style>
        * {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        body {
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 50px auto 0;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        h1 {
            color: #1a1a1a;
            margin: 0 0 30px 0;
            font-size: 28px;
            font-weight: 700;
        }
        .status-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #28a745;
        }
        .status-item.error {
            border-left-color: #dc3545;
        }
        .status-icon {
            font-size: 24px;
            margin-right: 15px;
            min-width: 30px;
        }
        .status-text {
            font-size: 15px;
            color: #333;
        }
        .info-box {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            margin-top: 20px;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.4);
        }
    </style>
</head>
<body>
<div class="container">
    <h1>📊 Database Status</h1>
    
    <?php if (!empty($createdTables)): ?>
        <div class="info-box">
            ✅ Tables created: <?php echo implode(', ', $createdTables); ?>
        </div>
    <?php endif; ?>
    
    <div class="status-item">
        <span class="status-icon">✅</span>
        <span class="status-text"><strong>product_categories</strong> table exists</span>
    </div>
    
    <div class="status-item">
        <span class="status-icon">✅</span>
        <span class="status-text"><strong>products</strong> table exists</span>
    </div>
    
    <div class="status-item">
        <span class="status-icon">✅</span>
        <span class="status-text"><strong>product_movements</strong> table exists</span>
    </div>
    
    <?php
    $result = $conn->query("SELECT COUNT(*) as count FROM product_categories");
    $row = $result->fetch_assoc();
    ?>
    
    <div class="status-item">
        <span class="status-icon">📦</span>
        <span class="status-text">Categories in database: <strong><?php echo $row['count']; ?></strong></span>
    </div>
    
    <?php
    $result = $conn->query("SELECT COUNT(*) as count FROM products");
    $row = $result->fetch_assoc();
    ?>
    
    <div class="status-item">
        <span class="status-icon">📋</span>
        <span class="status-text">Products in database: <strong><?php echo $row['count']; ?></strong></span>
    </div>
    
    <a href="Product_Catalog.php" class="button">Go to Product Catalog →</a>
</div>
</body>
</html>

<?php $conn->close(); ?>
