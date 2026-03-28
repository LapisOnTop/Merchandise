<?php
include '../includes/db.php';

// Check if products table exists
$result = $conn->query("SHOW TABLES LIKE 'products'");
if ($result->num_rows == 0) {
    echo "<script>window.location.href = '../includes/db.php';</script>";
    exit;
}

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
if ($product_id == 0) {
    echo "<script>alert('Invalid product ID'); window.location.href = 'IntryTracking_StckCtrl.php';</script>";
    exit;
}

// Get product details
$product_sql = "SELECT p.*, pc.category_name FROM products p LEFT JOIN product_categories pc ON p.category_id = pc.id WHERE p.id = $product_id";
$product_result = $conn->query($product_sql);
if ($product_result->num_rows == 0) {
    echo "<script>alert('Product not found'); window.location.href = 'IntryTracking_StckCtrl.php';</script>";
    exit;
}
$product = $product_result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Details - <?php echo htmlspecialchars($product['product_name']); ?></title>
    <link rel="stylesheet" href="../includes/styles.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .page-header h2 {
            margin: 0;
            color: #333;
        }

        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .back-btn:hover {
            background: #5a6268;
        }

        .product-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .summary-item {
            text-align: center;
        }

        .summary-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .summary-label {
            color: #666;
            font-size: 14px;
        }

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
        }

        .filter-bar input {
            padding: 8px 12px;
            border: 1px solid #d0d0d0;
            border-radius: 4px;
            font-size: 14px;
            background: white;
        }

        .filter-bar-spacer {
            flex: 1;
        }

        .add-btn {
            background: white;
            color: #333;
            border: 1px solid #d0d0d0;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .add-btn:hover {
            background: #f8f9fa;
            border-color: #999;
        }

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

        .edit-btn, .delete-btn {
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state p {
            font-size: 16px;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="brand">
        <h1>Core 1 - Supply Chain</h1>
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
                <li><a href="stocktrasnfer_adjstmnt.php"><span class="nav-text">Stock Transfer</span></a></li>
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
    <div class="page-header">
        <h2>📦 Inventory Details - <?php echo htmlspecialchars($product['product_name']); ?></h2>
        <a href="IntryTracking_StckCtrl.php" class="back-btn">← Back to Stock Control</a>
    </div>

    <!-- Product Summary -->
    <div class="product-summary">
        <div class="summary-item">
            <div class="summary-value"><?php echo htmlspecialchars($product['sku']); ?></div>
            <div class="summary-label">SKU</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">
                <?php
                $inventory_sql = "SELECT COALESCE(SUM(inventory_quantity), 0) as total_inv, COALESCE(SUM(store_quantity), 0) as total_store FROM inventory WHERE product_id = $product_id";
                $inventory_result = $conn->query($inventory_sql);
                $inventory_data = $inventory_result->fetch_assoc();
                $total_inventory = $inventory_data['total_inv'];
                $total_store = $inventory_data['total_store'];
                $grand_total = $total_inventory + $total_store;
                echo $total_inventory;
                ?>
            </div>
            <div class="summary-label">Total Inventory Qty</div>
        </div>
        <div class="summary-item">
            <div class="summary-value"><?php echo $total_store; ?></div>
            <div class="summary-label">Total Store Qty</div>
        </div>
        <div class="summary-item">
            <div class="summary-value"><?php echo $grand_total; ?></div>
            <div class="summary-label">Grand Total</div>
        </div>
        <div class="summary-item">
            <div class="summary-value"><?php echo $product['reorder_level']; ?></div>
            <div class="summary-label">Reorder Level</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-bar-item">
            <input type="text" id="searchInput" placeholder="Search by batch number...">
        </div>
        <div class="filter-bar-spacer"></div>
        <button class="add-btn" onclick="openInventoryModal()">+ Add Batch</button>
    </div>

    <!-- Table Section -->
    <div class="table-section">
        <div class="table-container">
            <table id="inventoryTable">
                <thead>
                    <tr>
                        <th>Batch Number</th>
                        <th>Inventory Qty</th>
                        <th>Store Qty</th>
                        <th>Total Qty</th>
                        <th>Expiry Date</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="inventoryTableBody">
                    <?php
                    $batches_sql = "SELECT * FROM inventory WHERE product_id = $product_id ORDER BY created_at DESC";
                    $batches_result = $conn->query($batches_sql);
                    
                    if ($batches_result->num_rows > 0) {
                        while ($batch = $batches_result->fetch_assoc()) {
                            $batch_total = $batch['inventory_quantity'] + $batch['store_quantity'];
                            echo "<tr>
                                <td>" . htmlspecialchars($batch['batch_number'] ?: '-') . "</td>
                                <td>" . $batch['inventory_quantity'] . "</td>
                                <td>" . $batch['store_quantity'] . "</td>
                                <td>" . $batch_total . "</td>
                                <td>" . ($batch['expiry_date'] ? date('M d, Y', strtotime($batch['expiry_date'])) : '-') . "</td>
                                <td>" . date('M d, Y', strtotime($batch['created_at'])) . "</td>
                                <td>
                                    <button class='edit-btn' onclick='editBatch(" . $batch['id'] . ")'>Edit</button>
                                    <button class='delete-btn' onclick='deleteBatch(" . $batch['id'] . ")'>Delete</button>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'><div class='empty-state'><p>No inventory batches found for this product.</p></div></td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Inventory Modal -->
<div id="inventoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="inventoryModalTitle">Add Inventory Batch</h3>
            <span class="close" onclick="closeInventoryModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="inventoryForm" onsubmit="saveInventory(event)">
                <input type="hidden" id="inventoryId">
                <input type="hidden" id="productId" value="<?php echo $product_id; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="batchNumber">Batch Number</label>
                        <input type="text" id="batchNumber" placeholder="Enter batch number">
                    </div>
                    <div class="form-group">
                        <label for="expiryDate">Expiry Date</label>
                        <input type="date" id="expiryDate">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="inventoryQuantity">Inventory Quantity *</label>
                        <input type="number" id="inventoryQuantity" min="0" required placeholder="0">
                    </div>
                    <div class="form-group">
                        <label for="storeQuantity">Store Quantity *</label>
                        <input type="number" id="storeQuantity" min="0" required placeholder="0">
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="submit-btn cancel" onclick="closeInventoryModal()">Cancel</button>
                    <button type="submit" class="submit-btn">Save Batch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="notification" id="notification"></div>

<script>
    // Sidebar functionality (same as main page)
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarToggle = document.getElementById('sidebarToggle');

    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    });

    const navSections = document.querySelectorAll('.nav-section');
    const setSectionOpen = (section, isOpen) => {
        const title = section.querySelector('.nav-section-title');
        const arrow = title.querySelector('.nav-icon');
        section.classList.toggle('open', isOpen);
        title.setAttribute('data-expanded', isOpen);
        arrow.textContent = isOpen ? '▾' : '▸';
    };

    navSections.forEach((section) => {
        const title = section.querySelector('.nav-section-title');
        title.addEventListener('click', () => {
            const expanded = title.getAttribute('data-expanded') === 'true';
            setSectionOpen(section, !expanded);
        });
        setSectionOpen(section, false);
    });

    function openInventoryModal() {
        document.getElementById('inventoryModalTitle').textContent = 'Add Inventory Batch';
        document.getElementById('inventoryForm').reset();
        document.getElementById('inventoryId').value = '';
        document.getElementById('inventoryModal').style.display = 'block';
    }

    function closeInventoryModal() {
        document.getElementById('inventoryModal').style.display = 'none';
    }

    function editBatch(inventoryId) {
        // Fetch batch details and populate modal
        fetch('inventory_operations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=get_batch&id=' + inventoryId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const batch = data.batch;
                document.getElementById('inventoryModalTitle').textContent = 'Edit Inventory Batch';
                document.getElementById('batchNumber').value = batch.batch_number || '';
                document.getElementById('inventoryQuantity').value = batch.inventory_quantity || 0;
                document.getElementById('storeQuantity').value = batch.store_quantity || 0;
                document.getElementById('expiryDate').value = batch.expiry_date || '';
                document.getElementById('inventoryId').value = batch.id;
                document.getElementById('inventoryModal').style.display = 'block';
            }
        });
    }

    function deleteBatch(inventoryId) {
        if (confirm('Are you sure you want to delete this batch?')) {
            fetch('inventory_operations.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=delete_batch&id=' + inventoryId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }

    function saveInventory(event) {
        event.preventDefault();
        
        const formData = new FormData();
        formData.append('action', 'add_inventory');
        formData.append('product_id', document.getElementById('productId').value);
        formData.append('batch_number', document.getElementById('batchNumber').value);
        formData.append('inventory_quantity', document.getElementById('inventoryQuantity').value);
        formData.append('store_quantity', document.getElementById('storeQuantity').value);
        formData.append('expiry_date', document.getElementById('expiryDate').value);
        
        const inventoryId = document.getElementById('inventoryId').value;
        if (inventoryId) {
            formData.append('inventory_id', inventoryId);
            formData.set('action', 'update_inventory');
        }

        fetch('inventory_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeInventoryModal();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }

    // Real-time search
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        const rows = document.querySelectorAll('#inventoryTableBody tr');
        
        rows.forEach(row => {
            const batchNumber = row.cells[0].textContent.toLowerCase();
            row.style.display = batchNumber.includes(searchText) ? '' : 'none';
        });
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('inventoryModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
</script>

<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
    }

    .modal-content {
        background-color: #fff;
        margin: 3% auto;
        border-radius: 12px;
        width: 90%;
        max-width: 650px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 25px 30px;
        border-bottom: 2px solid #f0f0f0;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }

    .modal-header h3 {
        margin: 0;
        color: #1a1a1a;
        font-size: 20px;
        font-weight: 700;
    }

    .close {
        font-size: 28px;
        cursor: pointer;
        color: #999;
    }

    .close:hover {
        color: #333;
    }

    .modal-body {
        padding: 30px;
    }

    .modal-body .form-group {
        margin-bottom: 20px;
    }

    .modal-body .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .modal-body .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 700;
        color: #1a1a1a;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .modal-body .form-group input {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #d0d0d0;
        border-radius: 6px;
        font-size: 14px;
        box-sizing: border-box;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
    }

    .modal-actions .submit-btn {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        border: none;
        padding: 12px 32px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
    }

    .modal-actions .submit-btn.cancel {
        background: white;
        color: #333;
        border: 1px solid #d0d0d0;
    }

    .notification {
        display: none;
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        background: #28a745;
        color: white;
        border-radius: 6px;
        z-index: 2000;
    }
</style>

</body>
</html>