<?php
include '../includes/db.php';

// Check if products table exists
$result = $conn->query("SHOW TABLES LIKE 'products'");
if ($result->num_rows == 0) {
    echo "<script>window.location.href = '../includes/db.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stock Control</title>
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

        .low-stock-badge {
            background: #f44336;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            min-width: 30px;
            text-align: center;
        }

        /* Filter Bar - HR1 Style */
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
            min-width: 300px;
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
            white-space: nowrap;
        }

        .add-btn:hover {
            background: #f8f9fa;
            border-color: #999;
        }

        /* Table Styles */
        .table-section h3 {
            margin-bottom: 15px;
            color: #333;
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

        .status-active {
            color: #28a745;
            font-weight: 500;
        }

        .status-low {
            color: #ffc107;
            font-weight: 500;
        }

        .status-inactive {
            color: #dc3545;
            font-weight: 500;
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

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: #fff;
            margin: 3% auto;
            border-radius: 12px;
            width: 90%;
            max-width: 650px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
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
            transition: all 0.2s ease;
            line-height: 1;
        }

        .close:hover {
            color: #333;
            transform: rotate(90deg);
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

        .modal-body .form-row.full {
            grid-template-columns: 1fr;
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

        .modal-body .form-group input,
        .modal-body .form-group select,
        .modal-body .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d0d0d0;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: #fff;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .modal-body .form-group input::placeholder {
            color: #999;
        }

        .modal-body .form-group input:hover,
        .modal-body .form-group select:hover,
        .modal-body .form-group textarea:hover {
            border-color: #999;
        }

        .modal-body .form-group input:focus,
        .modal-body .form-group select:focus,
        .modal-body .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .modal-body .form-group textarea {
            resize: vertical;
            min-height: 90px;
            font-family: inherit;
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
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.25);
        }

        .modal-actions .submit-btn:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.35);
        }

        .modal-actions .submit-btn.cancel {
            background: white;
            color: #333;
            border: 1px solid #d0d0d0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .modal-actions .submit-btn.cancel:hover {
            background: #f8f9fa;
            border-color: #999;
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: slideInRight 0.3s ease;
        }

        @keyframes slideInRight {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .notification.error {
            background: #dc3545;
        }

        .notification.success {
            background: #28a745;
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
                <li><a href="IntryTracking_StckCtrl.php"><span class="nav-text">Stock Control</span></a></li>
                <li><a href="inventory_tracking.php"><span class="nav-text">Inventory Tracking</span></a></li>
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
        <h2>📦 Stock Control</h2>
    </div>

    <!-- Low Stock Alert -->
    <div id="lowStockAlert" class="alert alert-warning" style="display: none; margin-bottom: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px;">
        <strong>⚠️ Low Stock Alert:</strong> <span id="lowStockCount">0</span> products are below reorder level
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-bar-item">
            <input type="text" id="searchInput" placeholder="Search by Product name or SKU...">
        </div>
        <div class="filter-bar-item">
            <label for="categoryFilter">Category:</label>
            <select id="categoryFilter" onchange="applyFilters()">
                <option value="">All Categories</option>
            </select>
        </div>
        <div class="filter-bar-spacer"></div>
        <a href="inventory_tracking.php" class="add-btn" style="text-decoration: none;">View Inventory Tracking</a>
    </div>

    <!-- Table Section -->
    <div class="table-section">
        <div class="table-container">
            <table id="inventoryTable">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Barcode</th>
                        <th>Inventory Quantity</th>
                        <th>Store Quantity</th>
                    </tr>
                </thead>
                <tbody id="inventoryTableBody">
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <p>Loading inventory...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    let allInventory = [];
    let allCategories = [];

    // Sidebar navigation functionality
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

    // Load inventory on page load
    window.onload = function() {
        loadInventory();
        loadCategories();
    };

    function loadInventory() {
        fetch('inventory_operations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_inventory'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allInventory = data.inventory;
                allProducts = [...new Map(data.inventory.map(item => [item.product_id, item])).values()];
                displayInventory(allInventory);
                updateLowStockBadge();
                populateProductSelect();
            } else {
                showNotification('Error loading inventory: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading inventory', 'error');
        });
    }

    function loadCategories() {
        fetch('../core1/category_operations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_categories'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.categories) {
                allCategories = data.categories;
                populateCategoryFilter();
            }
        })
        .catch(error => console.error('Error loading categories:', error));
    }

    function populateCategoryFilter() {
        const select = document.getElementById('categoryFilter');
        const existingOptions = select.querySelectorAll('option');
        existingOptions.forEach((opt, idx) => {
            if (idx > 0) opt.remove();
        });
        
        allCategories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.category_name;
            select.appendChild(option);
        });
    }

    function populateProductSelect() {
        const select = document.getElementById('productSelect');
        const firstOption = select.firstElementChild;
        select.innerHTML = '';
        select.appendChild(firstOption);
        
        allProducts.forEach(product => {
            const option = document.createElement('option');
            option.value = product.product_id || product.id;
            option.textContent = product.product_name;
            select.appendChild(option);
        });
    }

    function displayInventory(inventory) {
        const tbody = document.getElementById('inventoryTableBody');
        
        if (inventory.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><p>No products found.</p></div></td></tr>';
            return;
        }

        tbody.innerHTML = inventory.map(item => {
            const totalInventoryQty = parseInt(item.total_inventory_quantity || 0);
            const totalStoreQty = parseInt(item.total_store_quantity || 0);
            
            return `<tr>
                <td>${item.product_name || 'N/A'}</td>
                <td>${item.sku || 'N/A'}</td>
                <td>${item.barcode || '-'}</td>
                <td>${totalInventoryQty}</td>
                <td>${totalStoreQty}</td>
            </tr>`;
        }).join('');
    }

    function updateLowStockBadge() {
        const lowStockCount = allInventory.filter(item => {
            const totalQty = parseInt(item.total_inventory_quantity || 0) + parseInt(item.total_store_quantity || 0);
            const reorderLevel = parseInt(item.reorder_level || 10);
            return totalQty <= reorderLevel && totalQty > 0;
        }).length;
        
        const alert = document.getElementById('lowStockAlert');
        if (lowStockCount > 0) {
            document.getElementById('lowStockCount').textContent = lowStockCount;
            alert.style.display = 'block';
        } else {
            alert.style.display = 'none';
        }
    }

    function openInventoryModal() {
        document.getElementById('inventoryModalTitle').textContent = 'Add Inventory';
        document.getElementById('inventoryForm').reset();
        document.getElementById('inventoryId').value = '';
        document.getElementById('productId').value = '';
        document.getElementById('inventoryModal').style.display = 'block';
    }

    function closeInventoryModal() {
        document.getElementById('inventoryModal').style.display = 'none';
    }

    function viewInventoryDetails(productId) {
        // For now, redirect to a detailed view or open a modal with batch details
        // This could be enhanced to show individual batch information
        window.location.href = 'inventory_details.php?product_id=' + productId;
    }

    function updateProductInfo() {
        const productId = document.getElementById('productSelect').value;
        const product = allProducts.find(p => (p.product_id || p.id) == productId);
        if (product) {
            document.getElementById('inventorySKU').value = product.sku || '';
            document.getElementById('productId').value = product.product_id || product.id;
        }
    }

    function saveInventory(event) {
        event.preventDefault();
        
        const productId = document.getElementById('productId').value;
        if (!productId) {
            showNotification('Please select a product', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'add_inventory');
        formData.append('product_id', productId);
        formData.append('batch_number', document.getElementById('batchNumber').value);
        formData.append('inventory_quantity', document.getElementById('inventoryQuantity').value);
        formData.append('store_quantity', document.getElementById('storeQuantity').value);
        formData.append('expiry_date', document.getElementById('expiryDate').value);

        fetch('inventory_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                closeInventoryModal();
                loadInventory();
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error saving inventory', 'error');
        });
    }

    function applyFilters() {
        const searchText = document.getElementById('searchInput').value.toLowerCase();
        const categoryId = document.getElementById('categoryFilter').value;

        const filtered = allInventory.filter(item => {
            const matchesSearch = 
                (item.product_name && item.product_name.toLowerCase().includes(searchText)) ||
                (item.sku && item.sku.toLowerCase().includes(searchText));
            
            const matchesCategory = !categoryId || item.category_id == categoryId;

            return matchesSearch && matchesCategory;
        });

        displayInventory(filtered);
    }

    // Real-time search
    document.getElementById('searchInput').addEventListener('keyup', applyFilters);
</script>
</body>
</html>
