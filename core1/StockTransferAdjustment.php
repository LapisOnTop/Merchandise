<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Transfer & Adjustment</title>
    <link rel="stylesheet" href="../includes/styles.css">
    <style>
        /* Navigation Bar */
        .nav-bar {
            display: flex;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            padding: 10px 20px;
            margin-bottom: 20px;
        }

        .nav-bar h2 {
            margin: 0;
            color: #333;
        }

        .nav-bar .spacer {
            flex: 1;
        }

        /* Filter Bar */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e8eaed;
        }

        .filter-bar-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-bar-item label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            white-space: nowrap;
        }

        .filter-bar select,
        .filter-bar input {
            padding: 8px 12px;
            border: 1px solid #d0d0d0;
            border-radius: 4px;
            font-size: 14px;
            background: white;
            transition: all 0.2s ease;
        }

        .filter-bar select:hover,
        .filter-bar input:hover {
            border-color: #999;
        }

        .filter-bar select:focus,
        .filter-bar input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
        }

        .filter-bar select {
            min-width: 150px;
        }

        .filter-bar input {
            min-width: 200px;
        }

        .filter-bar-spacer {
            flex: 1;
        }

        .add-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .add-btn:hover {
            background: #218838;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }

        .edit-btn {
            background: #007bff;
            color: white;
        }

        .edit-btn:hover {
            background: #0056b3;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 600px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #007bff;
            outline: none;
        }

        .submit-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .submit-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<?php
include '../includes/db.php';

// Auto-create stock_adjustments table if needed
function ensureAdjustmentTable($conn) {
    $result = $conn->query("SHOW TABLES LIKE 'stock_adjustments'");
    if ($result->num_rows == 0) {
        $conn->query("CREATE TABLE stock_adjustments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            product_id INT NOT NULL,
            batch_number VARCHAR(50),
            adjustment_type ENUM('transfer', 'adjustment') NOT NULL,
            quantity INT NOT NULL,
            from_location VARCHAR(50),
            to_location VARCHAR(50),
            reason TEXT,
            adjustment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by VARCHAR(100),
            KEY idx_product (product_id),
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        // Check if batch_number column exists, add if not
        $columns = $conn->query("SHOW COLUMNS FROM stock_adjustments LIKE 'batch_number'");
        if ($columns->num_rows == 0) {
            $conn->query("ALTER TABLE stock_adjustments ADD COLUMN batch_number VARCHAR(50) AFTER product_id");
        }
    }
}

ensureAdjustmentTable($conn);

// Check for success message
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = 'Adjustment added successfully!';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add_adjustment') {
        $product_id = intval($_POST['product_id']);
        $batch_number = $conn->real_escape_string($_POST['batch_number'] ?? '');
        $adjustment_type = $conn->real_escape_string($_POST['adjustment_type']);
        $quantity = intval($_POST['quantity']);
        $from_location = $conn->real_escape_string($_POST['from_location'] ?? '');
        $to_location = $conn->real_escape_string($_POST['to_location'] ?? '');
        $reason = $conn->real_escape_string($_POST['reason']);

        $sql = "INSERT INTO stock_adjustments (product_id, batch_number, adjustment_type, quantity, from_location, to_location, reason)
                VALUES ($product_id, '$batch_number', '$adjustment_type', $quantity, '$from_location', '$to_location', '$reason')";

        if ($conn->query($sql)) {
            // Update inventory quantities if transfer
            if ($adjustment_type == 'transfer') {
                if ($from_location == 'inventory' && $to_location == 'store') {
                    $conn->query("UPDATE inventory SET inventory_quantity = inventory_quantity - $quantity, store_quantity = store_quantity + $quantity WHERE product_id = $product_id AND batch_number = '$batch_number'");
                } elseif ($from_location == 'store' && $to_location == 'inventory') {
                    $conn->query("UPDATE inventory SET store_quantity = store_quantity - $quantity, inventory_quantity = inventory_quantity + $quantity WHERE product_id = $product_id AND batch_number = '$batch_number'");
                }
            }
            // Redirect to prevent form resubmission loop
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
            exit();
        } else {
            echo "<script>alert('Error: " . addslashes($conn->error) . "');</script>";
        }
    }
}

// Fetch adjustments
$adjustments = [];
$result = $conn->query("SELECT sa.*, p.product_name, p.sku FROM stock_adjustments sa LEFT JOIN products p ON sa.product_id = p.id ORDER BY sa.adjustment_date DESC");
while ($row = $result->fetch_assoc()) {
    $adjustments[] = $row;
}

// Fetch products for dropdown
$products = [];
$result = $conn->query("SELECT id, product_name, sku FROM products ORDER BY product_name");
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Fetch batches for selected product (will be loaded via AJAX)
$batches = [];
$result = $conn->query("SELECT DISTINCT batch_number FROM inventory WHERE batch_number IS NOT NULL AND batch_number != '' ORDER BY batch_number");
while ($row = $result->fetch_assoc()) {
    $batches[] = $row['batch_number'];
}
?>

<div class="sidebar" id="sidebar">
    <div class="brand">
        <h1>Core Transaction 1</h1>
        <button id="sidebarToggle" aria-label="Toggle navigation">☰</button>
    </div>
    <ul class="nav-list">
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">🏠</span>
                <span class="nav-section-label">Dashboard</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="core1main.php"><span class="nav-text">Overview</span></a></li>
                <li><a href="core1main.php"><span class="nav-text">Reports</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">📦</span>
                <span class="nav-section-label">Supply Chain</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="Product_Catalog.php"><span class="nav-text">Product Catalog</span></a></li>
                <li><a href="IntryTracking_StckCtrl.php"><span class="nav-text">Inventory Tracking</span></a></li>
                <li><a href="PricingandCosting.php"><span class="nav-text">Pricing & Costing</span></a></li>
                <li><a href="PurchaseOrder.php"><span class="nav-text">Purchase Orders</span></a></li>
                <li><a href="Receiving_Inspection.php"><span class="nav-text">Receiving & Inspection</span></a></li>
                <li><a href="StockTransferAdjustment.php"><span class="nav-text">Stock Transfer & Adjustment</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">⚙️</span>
                <span class="nav-section-label">Settings</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="#"><span class="nav-text">Change Password</span></a></li>
                <li><a href="#"><span class="nav-text">Logout</span></a></li>
            </ul>
        </li>
    </ul>
</div>

<div class="main" id="mainContent">
    <div class="nav-bar">
        <h2>Stock Transfer & Adjustment</h2>
        <div class="spacer"></div>
    </div>

    <div class="filter-bar">
        <div class="filter-bar-item">
            <label for="filter-product">Product:</label>
            <select id="filter-product">
                <option value="">All Products</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['product_name'] . ' (' . $product['sku'] . ')'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-bar-item">
            <label for="filter-batch">Batch:</label>
            <select id="filter-batch">
                <option value="">All Batches</option>
                <?php foreach ($batches as $batch): ?>
                    <option value="<?php echo htmlspecialchars($batch); ?>"><?php echo htmlspecialchars($batch); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-bar-item">
            <label for="filter-type">Type:</label>
            <select id="filter-type">
                <option value="">All Types</option>
                <option value="transfer">Transfer</option>
                <option value="adjustment">Adjustment</option>
            </select>
        </div>
        <div class="filter-bar-item">
            <label for="filter-date">Date:</label>
            <input type="date" id="filter-date">
        </div>
        <div class="filter-bar-spacer"></div>
        <button class="add-btn" onclick="openModal()">Add New Adjustment</button>
    </div>

    <div class="table-container">
        <table id="adjustments-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Batch</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Reason</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($adjustments as $adj): ?>
                <tr>
                    <td><?php echo date('Y-m-d H:i', strtotime($adj['adjustment_date'])); ?></td>
                    <td><?php echo htmlspecialchars($adj['product_name'] . ' (' . $adj['sku'] . ')'); ?></td>
                    <td><?php echo htmlspecialchars($adj['batch_number'] ?? ''); ?></td>
                    <td><?php echo ucfirst($adj['adjustment_type']); ?></td>
                    <td><?php echo $adj['quantity']; ?></td>
                    <td><?php echo htmlspecialchars($adj['from_location']); ?></td>
                    <td><?php echo htmlspecialchars($adj['to_location']); ?></td>
                    <td><?php echo htmlspecialchars($adj['reason']); ?></td>
                    <td>
                        <button class="action-btn edit-btn">Edit</button>
                        <button class="action-btn delete-btn">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Adding Adjustment -->
<div id="adjustmentModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Add New Adjustment</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_adjustment">
            <div class="form-group">
                <label for="product_id">Product:</label>
                <select name="product_id" id="product_id" required onchange="loadBatches()">
                    <option value="">Select Product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['product_name'] . ' (' . $product['sku'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" id="batch-group" style="display: none;">
                <label for="batch_number">Batch Number:</label>
                <select name="batch_number" id="batch_number">
                    <option value="">Select Batch</option>
                </select>
            </div>
            <div class="form-group">
                <label for="adjustment_type">Adjustment Type:</label>
                <select name="adjustment_type" id="adjustment_type" required onchange="toggleLocations()">
                    <option value="transfer">Transfer</option>
                    <option value="adjustment">Adjustment</option>
                </select>
            </div>
            <div class="form-group">
                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" id="quantity" required min="1">
            </div>
            <div class="form-group" id="from-location-group">
                <label for="from_location">From Location:</label>
                <select name="from_location" id="from_location">
                    <option value="inventory">Inventory</option>
                    <option value="store">Store</option>
                </select>
            </div>
            <div class="form-group" id="to-location-group">
                <label for="to_location">To Location:</label>
                <select name="to_location" id="to_location">
                    <option value="store">Store</option>
                    <option value="inventory">Inventory</option>
                </select>
            </div>
            <div class="form-group">
                <label for="reason">Reason:</label>
                <textarea name="reason" id="reason" rows="3"></textarea>
            </div>
            <button type="submit" class="submit-btn">Add Adjustment</button>
        </form>
    </div>
</div>

<script>
    // Show success message if exists
    <?php if ($success_message): ?>
        alert('<?php echo addslashes($success_message); ?>');
    <?php endif; ?>

    // Sidebar toggle
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarToggle = document.getElementById('sidebarToggle');

    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    });

    // Nav sections toggle
    const navSections = document.querySelectorAll('.nav-section');

    const setSectionOpen = (section, isOpen) => {
        const title = section.querySelector('.nav-section-title');
        const arrow = title.querySelector('.nav-icon');

        section.classList.toggle('open', isOpen);
        title.setAttribute('data-expanded', isOpen);
        arrow.textContent = isOpen ? '▾' : '▸';
    };

    navSections.forEach(section => {
        const title = section.querySelector('.nav-section-title');
        title.addEventListener('click', () => {
            const isOpen = title.getAttribute('data-expanded') === 'true';
            setSectionOpen(section, !isOpen);
        });
    });

    // Open default section
    const supplyChainSection = document.querySelector('.nav-section:nth-child(2)');
    setSectionOpen(supplyChainSection, true);

    // Modal functions
    function openModal() {
        document.getElementById('adjustmentModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('adjustmentModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('adjustmentModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    // Toggle location fields based on type
    function toggleLocations() {
        const type = document.getElementById('adjustment_type').value;
        const fromGroup = document.getElementById('from-location-group');
        const toGroup = document.getElementById('to-location-group');
        const batchGroup = document.getElementById('batch-group');

        if (type === 'transfer') {
            fromGroup.style.display = 'block';
            toGroup.style.display = 'block';
            batchGroup.style.display = 'block';
        } else {
            fromGroup.style.display = 'none';
            toGroup.style.display = 'none';
            batchGroup.style.display = 'none';
        }
    }

    // Load batches for selected product
    function loadBatches() {
        const productId = document.getElementById('product_id').value;
        const batchSelect = document.getElementById('batch_number');
        
        if (!productId) {
            batchSelect.innerHTML = '<option value="">Select Batch</option>';
            return;
        }

        // Fetch batches via AJAX
        fetch('get_batches.php?product_id=' + productId)
            .then(response => response.json())
            .then(data => {
                batchSelect.innerHTML = '<option value="">Select Batch</option>';
                data.batches.forEach(batch => {
                    const option = document.createElement('option');
                    option.value = batch.batch_number;
                    option.textContent = batch.batch_number + ' (Inv: ' + batch.inventory_quantity + ', Store: ' + batch.store_quantity + ')';
                    batchSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error loading batches:', error));
    }

    // Initialize
    toggleLocations();

    // Filter functionality
    function filterTable() {
        const productFilter = document.getElementById('filter-product').value.toLowerCase();
        const batchFilter = document.getElementById('filter-batch').value.toLowerCase();
        const typeFilter = document.getElementById('filter-type').value.toLowerCase();
        const dateFilter = document.getElementById('filter-date').value;
        
        const table = document.getElementById('adjustments-table');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            const productText = cells[1].textContent.toLowerCase();
            const batchText = cells[2].textContent.toLowerCase();
            const typeText = cells[3].textContent.toLowerCase();
            const dateText = cells[0].textContent.split(' ')[0]; // Get date part only
            
            let showRow = true;
            
            if (productFilter && !productText.includes(productFilter)) showRow = false;
            if (batchFilter && !batchText.includes(batchFilter)) showRow = false;
            if (typeFilter && !typeText.includes(typeFilter)) showRow = false;
            if (dateFilter && dateText !== dateFilter) showRow = false;
            
            rows[i].style.display = showRow ? '' : 'none';
        }
    }

    // Add event listeners to filters
    document.getElementById('filter-product').addEventListener('change', filterTable);
    document.getElementById('filter-batch').addEventListener('change', filterTable);
    document.getElementById('filter-type').addEventListener('change', filterTable);
    document.getElementById('filter-date').addEventListener('change', filterTable);
</script>

</body>
</html>
