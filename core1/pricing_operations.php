<?php
include '../includes/db.php';

// Auto-create pricing tables if needed
function ensurePricingTables($conn) {
    // Check if pricing table exists
    $result = $conn->query("SHOW TABLES LIKE 'pricing'");
    if ($result->num_rows == 0) {
        $conn->query("CREATE TABLE pricing (
            id INT PRIMARY KEY AUTO_INCREMENT,
            product_id INT NOT NULL UNIQUE,
            min_price DECIMAL(12, 2),
            max_price DECIMAL(12, 2),
            markup_percentage DECIMAL(5, 2) DEFAULT 0.00,
            margin_percentage DECIMAL(5, 2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_product (product_id),
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    // Check if pricing history table exists
    $result = $conn->query("SHOW TABLES LIKE 'pricing_history'");
    if ($result->num_rows == 0) {
        $conn->query("CREATE TABLE pricing_history (
            id INT PRIMARY KEY AUTO_INCREMENT,
            product_id INT NOT NULL,
            cost_price DECIMAL(12, 2),
            selling_price DECIMAL(12, 2),
            markup_percentage DECIMAL(5, 2),
            margin_percentage DECIMAL(5, 2),
            changed_by VARCHAR(100),
            change_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_product (product_id),
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}

ensurePricingTables($conn);

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : '';
$response = array('success' => false, 'message' => '');

try {
    if ($action === 'get_pricing') {
        // Get all products with pricing information and inventory quantities
        $sql = "SELECT 
                    p.id,
                    p.product_name,
                    p.sku,
                    p.category_id,
                    p.cost_price,
                    p.store_price,
                    pc.category_name,
                    COALESCE(pr.markup_percentage, 0) as markup_percentage,
                    COALESCE(pr.margin_percentage, 0) as margin_percentage,
                    COALESCE(SUM(inv.inventory_quantity), 0) as total_inventory_qty,
                    COALESCE(SUM(inv.store_quantity), 0) as total_store_qty
                FROM products p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                LEFT JOIN pricing pr ON p.id = pr.product_id
                LEFT JOIN inventory inv ON p.id = inv.product_id
                GROUP BY p.id, p.product_name, p.sku, p.category_id, p.cost_price, p.store_price, pc.category_name, pr.markup_percentage, pr.margin_percentage
                ORDER BY p.product_name ASC";
        
        $result = $conn->query($sql);
        $pricing = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Calculate markup and margin from cost and store prices
                $costPrice = floatval($row['cost_price']);
                $sellingPrice = floatval($row['store_price']);
                
                if ($costPrice > 0 && $sellingPrice > 0) {
                    $markup = round((($sellingPrice - $costPrice) / $costPrice) * 100, 2);
                    $margin = round((($sellingPrice - $costPrice) / $sellingPrice) * 100, 2);
                } else {
                    $markup = 0;
                    $margin = 0;
                }
                
                $row['markup_percentage'] = $markup;
                $row['margin_percentage'] = $margin;
                $pricing[] = $row;
            }
        }
        
        // Calculate average metrics and totals (based on inventory quantities)
        $totalMargin = 0;
        $totalMarkup = 0;
        $totalCost = 0;
        $totalProfit = 0;
        $count = 0;
        
        foreach ($pricing as $item) {
            if ($item['store_price'] > 0 && $item['cost_price'] > 0) {
                $count++;
                $totalMargin += $item['margin_percentage'];
                $totalMarkup += $item['markup_percentage'];
                
                // Calculate total cost based on inventory quantity
                $inventoryQty = intval($item['total_inventory_qty']);
                $itemCost = floatval($item['cost_price']) * $inventoryQty;
                $itemRevenue = floatval($item['store_price']) * $inventoryQty;
                $itemProfit = $itemRevenue - $itemCost;
                
                $totalCost += $itemCost;
                $totalProfit += $itemProfit;
            }
        }
        
        $averageMargin = $count > 0 ? round($totalMargin / $count, 2) : 0;
        $averageMarkup = $count > 0 ? round($totalMarkup / $count, 2) : 0;
        
        $response['success'] = true;
        $response['pricing'] = $pricing;
        $response['averageMargin'] = $averageMargin;
        $response['averageMarkup'] = $averageMarkup;
        $response['productsWithPricing'] = $count;
        $response['totalCost'] = round($totalCost, 2);
        $response['potentialProfit'] = round($totalProfit, 2);
        
    } elseif ($action === 'add_pricing') {
        $product_id = intval($_POST['product_id']);
        $cost_price = floatval($_POST['cost_price']);
        $selling_price = floatval($_POST['selling_price']);
        
        // Calculate markup and margin
        $markup_percentage = 0;
        $margin_percentage = 0;
        
        if ($cost_price > 0) {
            $markup_percentage = round((($selling_price - $cost_price) / $cost_price) * 100, 2);
            $margin_percentage = round((($selling_price - $cost_price) / $selling_price) * 100, 2);
        }
        
        // Update cost_price and store_price in products table
        $productSQL = "UPDATE products SET cost_price = $cost_price, store_price = $selling_price WHERE id = $product_id";
        if (!$conn->query($productSQL)) {
            throw new Exception("Error updating product prices: " . $conn->error);
        }
        
        // Check if pricing record exists and update margins
        $check = $conn->query("SELECT id FROM pricing WHERE product_id = $product_id");
        
        if ($check->num_rows > 0) {
            $sql = "UPDATE pricing SET 
                    markup_percentage = $markup_percentage,
                    margin_percentage = $margin_percentage
                    WHERE product_id = $product_id";
        } else {
            $sql = "INSERT INTO pricing (product_id, markup_percentage, margin_percentage)
                    VALUES ($product_id, $markup_percentage, $margin_percentage)";
        }
        
        if ($conn->query($sql)) {
            // Add to history
            $historySQL = "INSERT INTO pricing_history (product_id, cost_price, selling_price, markup_percentage, margin_percentage, changed_by)
                          VALUES ($product_id, $cost_price, $selling_price, $markup_percentage, $margin_percentage, 'Admin')";
            $conn->query($historySQL);
            
            $response['success'] = true;
            $response['message'] = 'Pricing updated successfully!';
        } else {
            throw new Exception("Error updating pricing: " . $conn->error);
        }
        
    } elseif ($action === 'get_pricing_history') {
        $product_id = intval($_POST['product_id']);
        $sql = "SELECT * FROM pricing_history WHERE product_id = $product_id ORDER BY change_date DESC LIMIT 10";
        
        $result = $conn->query($sql);
        $history = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $history[] = $row;
            }
        }
        
        $response['success'] = true;
        $response['history'] = $history;
        
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
