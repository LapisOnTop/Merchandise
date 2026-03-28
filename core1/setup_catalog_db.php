<?php
include '../includes/db.php';

$tableCreated = false;
$errors = [];

// Drop tables if they exist with foreign key conflicts
// First drop product_movements if it exists
$conn->query("DROP TABLE IF EXISTS product_movements");

// Then drop products if it exists
$conn->query("DROP TABLE IF EXISTS products");

// Then drop product_categories if it exists
$conn->query("DROP TABLE IF EXISTS product_categories");

try {
    // Create categories table
    $createCategoriesTable = "CREATE TABLE product_categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        category_name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($createCategoriesTable)) {
        $errors[] = "Error creating categories table: " . $conn->error;
    }

    // Create products table
    $createProductsTable = "CREATE TABLE products (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($createProductsTable)) {
        $errors[] = "Error creating products table: " . $conn->error;
    }

    // Create product_movements table
    $createMovementsTable = "CREATE TABLE product_movements (
        id INT PRIMARY KEY AUTO_INCREMENT,
        product_id INT NOT NULL,
        movement_type ENUM('in', 'out', 'adjustment') DEFAULT 'adjustment',
        quantity INT NOT NULL,
        reference_number VARCHAR(100),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_product (product_id),
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($createMovementsTable)) {
        $errors[] = "Error creating movements table: " . $conn->error;
    }

    if (empty($errors)) {
        $tableCreated = true;
        // Insert sample categories
        $sampleCategories = [
            "Electronics",
            "Furniture",
            "Clothing",
            "Books",
            "Office Supplies"
        ];
        
        foreach ($sampleCategories as $category) {
            $cat_name = $conn->real_escape_string($category);
            $conn->query("INSERT INTO product_categories (category_name, status) VALUES ('$cat_name', 'active')");
        }
    }

} catch (Exception $e) {
    $errors[] = "Exception: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalog - Database Setup</title>
    <link rel="stylesheet" href="../includes/styles.css">
    <style>
        .setup-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .setup-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 10px;
        }
        .setup-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
        }
        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .error-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .info-box {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
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
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.4);
        }
        .status-check {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            font-size: 14px;
        }
        .status-item {
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .status-icon {
            font-size: 20px;
            min-width: 25px;
        }
    </style>
</head>
<body>
<div class="setup-container">
    <div class="setup-title">🔧 Product Catalog Setup</div>
    <div class="setup-subtitle">Database Initialization</div>
    
    <?php if ($tableCreated): ?>
        <div class="success-box">
            <strong>✅ Setup Completed Successfully!</strong><br>
            <br>
            All database tables have been created and populated with sample data.
            <br><br>
            <strong>Sample Categories Added:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Electronics</li>
                <li>Furniture</li>
                <li>Clothing</li>
                <li>Books</li>
                <li>Office Supplies</li>
            </ul>
        </div>
        
        <div class="status-check">
            <div class="status-item">
                <span class="status-icon">✅</span>
                <span><strong>product_categories</strong> table created</span>
            </div>
            <div class="status-item">
                <span class="status-icon">✅</span>
                <span><strong>products</strong> table created</span>
            </div>
            <div class="status-item">
                <span class="status-icon">✅</span>
                <span><strong>product_movements</strong> table created</span>
            </div>
        </div>
        
        <p style="text-align: center; margin-top: 30px;">
            <a href="Product_Catalog.php" class="button">Go to Product Catalog →</a>
        </p>
    
    <?php elseif (!empty($errors)): ?>
        <div class="error-box">
            <strong>❌ Setup Failed</strong><br>
            <br>
            <?php foreach ($errors as $error): ?>
                <div>• <?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
        
        <div class="info-box">
            <strong>Troubleshooting:</strong><br>
            • Make sure XAMPP MySQL server is running<br>
            • Check that the database "system" exists<br>
            • Verify your database credentials in <code>includes/db.php</code>
        </div>
        
        <p style="text-align: center; margin-top: 20px;">
            <a href="setup_catalog_db.php" class="button">Retry Setup</a>
        </p>
    
    <?php else: ?>
        <div class="info-box">
            <strong>ℹ️ Database Check</strong><br>
            Running setup query execution...
        </div>
    <?php endif; ?>
</div>
</body>
</html>

<?php $conn->close(); ?>
