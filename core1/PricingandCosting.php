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
    <title>Pricing & Costing Management</title>
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

        /* Dashboard Metrics */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .metric-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #007bff;
        }

        .metric-card.markup {
            border-left-color: #28a745;
        }

        .metric-card.margin {
            border-left-color: #ffc107;
        }

        .metric-card.products {
            border-left-color: #17a2b8;
        }

        .metric-label {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .metric-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
        }

        .metric-change {
            font-size: 12px;
            color: #666;
            margin-top: 8px;
        }

        /* Tab Navigation */
        .tab-navigation {
            display: flex;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px;
        }

        .tab-btn {
            padding: 12px 24px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            border-bottom-color: #007bff;
            color: #007bff;
        }

        .tab-btn:hover {
            background-color: #f8f9fa;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
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

        .filter-bar select:focus,
        .filter-bar input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
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

        .price-column {
            text-align: right;
            font-weight: 500;
        }

        .margin-positive {
            color: #28a745;
        }

        .margin-low {
            color: #ffc107;
        }

        .margin-negative {
            color: #dc3545;
        }

        .edit-btn, .history-btn {
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

        .history-btn {
            background: #6c757d;
            color: white;
        }

        .history-btn:hover {
            background: #5a6268;
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
        .modal-body .form-group select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d0d0d0;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: #fff;
            box-sizing: border-box;
        }

        .modal-body .form-group input::placeholder {
            color: #999;
        }

        .modal-body .form-group input:focus,
        .modal-body .form-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .auto-calculated {
            background: #f8f9fa;
            color: #666;
        }

        .calculation-display {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
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
        }

        .notification.error {
            background: #dc3545;
        }

        .notification.success {
            background: #28a745;
        }

        .history-table {
            font-size: 13px;
        }

        .history-table th,
        .history-table td {
            padding: 10px 12px;
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
        <h2>💰 Pricing & Costing Management</h2>
    </div>

    <!-- Dashboard Metrics -->
    <div class="metrics-grid">
        <div class="metric-card margin">
            <div class="metric-label">Average Margin</div>
            <div class="metric-value"><span id="avgMargin">0</span>%</div>
            <div class="metric-change">Profit margin across products</div>
        </div>
        <div class="metric-card markup">
            <div class="metric-label">Average Markup</div>
            <div class="metric-value"><span id="avgMarkup">0</span>%</div>
            <div class="metric-change">Markup percentage on cost</div>
        </div>
        <div class="metric-card products">
            <div class="metric-label">Products with Pricing</div>
            <div class="metric-value"><span id="productsCount">0</span></div>
            <div class="metric-change">Out of total products</div>
        </div>
        <div class="metric-card products">
            <div class="metric-label">Total Cost</div>
            <div class="metric-value">$<span id="totalCost">0.00</span></div>
            <div class="metric-change">Based on inventory quantity × cost price</div>
        </div>
        <div class="metric-card products">
            <div class="metric-label">Potential Profit</div>
            <div class="metric-value">$<span id="totalProfit">0.00</span></div>
            <div class="metric-change">Based on inventory quantity × margin</div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <button class="tab-btn active" onclick="showTab('pricing')">Pricing Management</button>
        <button class="tab-btn" onclick="showTab('history')">Price History</button>
    </div>

    <!-- Pricing Tab -->
    <div class="tab-content active" id="pricingTab">
        <div class="filter-bar">
            <div class="filter-bar-item">
                <input type="text" id="searchInput" placeholder="Search by Product name or SKU...">
            </div>
        </div>

        <div class="table-container">
            <table id="pricingTable">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th style="text-align: right;">Inventory Qty</th>
                        <th style="text-align: right;">Store Qty</th>
                        <th style="text-align: right;">Cost Price</th>
                        <th style="text-align: right;">Store Price</th>
                        <th style="text-align: right;">Markup %</th>
                        <th style="text-align: right;">Margin %</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="pricingTableBody">
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <p>Loading pricing data...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- History Tab -->
    <div class="tab-content" id="historyTab">
        <div style="text-align: center; padding: 40px; color: #999;">
            <p>Select a product to view its pricing history</p>
        </div>
        <div id="historyContent"></div>
    </div>
</div>

<!-- Pricing Modal -->
<div id="pricingModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="pricingModalTitle">Add/Update Pricing</h3>
            <span class="close" onclick="closePricingModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="pricingForm" onsubmit="savePricing(event)">
                <div class="form-group">
                    <label for="productSelect">Product Name *</label>
                    <select id="productSelect" required onchange="updateProductInfo()">
                        <option value="">Select a product...</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="skuDisplay">SKU (Auto-filled)</label>
                        <input type="text" id="skuDisplay" readonly class="auto-calculated">
                    </div>
                    <div class="form-group">
                        <label for="categoryDisplay">Category (Auto-filled)</label>
                        <input type="text" id="categoryDisplay" readonly class="auto-calculated">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="costPrice">Cost Price *</label>
                        <input type="number" id="costPrice" step="0.01" required placeholder="0.00" onchange="calculateMargins()">
                    </div>
                    <div class="form-group">
                        <label for="sellingPrice">Store Price (Selling Price) *</label>
                        <input type="number" id="sellingPrice" step="0.01" required placeholder="0.00" onchange="calculateMargins()">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="markupPercentage">Markup % (Auto-calculated)</label>
                        <input type="number" id="markupPercentage" step="0.01" readonly class="auto-calculated">
                        <div class="calculation-display" id="markupDisplay"></div>
                    </div>
                    <div class="form-group">
                        <label for="marginPercentage">Margin % (Auto-calculated)</label>
                        <input type="number" id="marginPercentage" step="0.01" readonly class="auto-calculated">
                        <div class="calculation-display" id="marginDisplay"></div>
                    </div>
                </div>



                <div class="modal-actions">
                    <button type="button" class="submit-btn cancel" onclick="closePricingModal()">Cancel</button>
                    <button type="submit" class="submit-btn">Save Pricing</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="notification" id="notification"></div>

<script>
    let allPricing = [];
    let allProducts = [];

    // Sidebar functionality
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

    // Load pricing on page load
    window.onload = function() {
        loadPricing();
    };

    function loadPricing() {
        fetch('pricing_operations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=get_pricing'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allPricing = data.pricing;
                allProducts = [...new Map(data.pricing.map(p => [p.id, p])).values()];
                displayPricing(allPricing);
                updateMetrics(data.averageMargin, data.averageMarkup, data.productsWithPricing, data.totalCost, data.potentialProfit);
                populateProductSelect();
            } else {
                showNotification('Error loading pricing: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading pricing', 'error');
        });
    }

    function updateMetrics(avgMargin, avgMarkup, productsCount, totalCost, potentialProfit) {
        document.getElementById('avgMargin').textContent = avgMargin;
        document.getElementById('avgMarkup').textContent = avgMarkup;
        document.getElementById('productsCount').textContent = productsCount;
        document.getElementById('totalCost').textContent = parseFloat(totalCost).toFixed(2);
        document.getElementById('totalProfit').textContent = parseFloat(potentialProfit).toFixed(2);
    }

    function displayPricing(pricing) {
        const tbody = document.getElementById('pricingTableBody');
        
        if (pricing.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9"><div class="empty-state"><p>No pricing data found.</p></div></td></tr>';
            return;
        }

        tbody.innerHTML = pricing.map(item => {
            const marginClass = item.margin_percentage > 30 ? 'margin-positive' : 
                               item.margin_percentage > 10 ? 'margin-low' : 'margin-negative';
            const inventoryQty = item.total_inventory_qty || 0;
            const storeQty = item.total_store_qty || 0;
            
            return `<tr>
                <td><strong>${item.product_name || 'N/A'}</strong></td>
                <td>${item.sku || 'N/A'}</td>
                <td class="price-column">${inventoryQty}</td>
                <td class="price-column">${storeQty}</td>
                <td class="price-column">$${parseFloat(item.cost_price).toFixed(2)}</td>
                <td class="price-column">$${parseFloat(item.store_price).toFixed(2)}</td>
                <td class="price-column">${item.markup_percentage}%</td>
                <td class="price-column ${marginClass}">${item.margin_percentage}%</td>
                <td>
                    <button class="edit-btn" onclick="editPricing(${item.id})">Edit</button>
                    <button class="history-btn" onclick="viewHistory(${item.id}, '${item.product_name}')">History</button>
                </td>
            </tr>`;
        }).join('');
    }

    function populateProductSelect() {
        const select = document.getElementById('productSelect');
        const firstOption = select.firstElementChild;
        select.innerHTML = '';
        select.appendChild(firstOption);
        
        allProducts.forEach(product => {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = product.product_name;
            select.appendChild(option);
        });
    }

    function openPricingModal(productId = null) {
        if (!productId) {
            // Adding new pricing - reset form and set any values if needed
            document.getElementById('pricingForm').reset();
        }
        document.getElementById('pricingModal').style.display = 'block';
    }

    function closePricingModal() {
        document.getElementById('pricingModal').style.display = 'none';
        document.getElementById('productSelect').disabled = false;
    }

    function editPricing(productId) {
        const pricing = allPricing.find(p => p.id === productId);
        const product = allProducts.find(p => p.id === productId);
        const productSelect = document.getElementById('productSelect');
        
        if (product) {
            // Set title based on whether pricing exists
            if (product.store_price > 0) {
                document.getElementById('pricingModalTitle').textContent = '✏️ Update Pricing';
                productSelect.disabled = true;
            } else {
                document.getElementById('pricingModalTitle').textContent = '➕ Add Pricing';
                productSelect.disabled = true;
                document.getElementById('pricingForm').reset();
            }
            
            // Always populate with product info
            productSelect.value = productId;
            document.getElementById('skuDisplay').value = product.sku || '';
            document.getElementById('categoryDisplay').value = product.category_name || '';
            
            // Populate with prices from products table
            document.getElementById('costPrice').value = product.cost_price || '';
            document.getElementById('sellingPrice').value = product.store_price || '';
            
            // Populate min/max from pricing table

            
            document.getElementById('pricingModal').style.display = 'block';
            calculateMargins();
        }
    }

    function updateProductInfo() {
        const productId = document.getElementById('productSelect').value;
        const product = allProducts.find(p => p.id == productId);
        if (product) {
            document.getElementById('skuDisplay').value = product.sku || '';
            document.getElementById('categoryDisplay').value = product.category_name || '';
            // Auto-populate with current cost and store prices
            document.getElementById('costPrice').value = product.cost_price || '';
            document.getElementById('sellingPrice').value = product.store_price || '';
            calculateMargins();
        }
    }

    function calculateMargins() {
        const costPrice = parseFloat(document.getElementById('costPrice').value) || 0;
        const sellingPrice = parseFloat(document.getElementById('sellingPrice').value) || 0;

        let markup = 0, margin = 0;

        if (costPrice > 0) {
            markup = ((sellingPrice - costPrice) / costPrice * 100).toFixed(2);
            margin = ((sellingPrice - costPrice) / sellingPrice * 100).toFixed(2);
        }

        document.getElementById('markupPercentage').value = markup;
        document.getElementById('marginPercentage').value = margin;
        document.getElementById('markupDisplay').textContent = `${markup}% markup on cost price`;
        document.getElementById('marginDisplay').textContent = `${margin}% profit margin on store price`;
    }

    function savePricing(event) {
        event.preventDefault();
        
        const productId = document.getElementById('productSelect').value;
        if (!productId) {
            showNotification('Please select a product', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'add_pricing');
        formData.append('product_id', productId);
        formData.append('cost_price', document.getElementById('costPrice').value);
        formData.append('selling_price', document.getElementById('sellingPrice').value);


        fetch('pricing_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                closePricingModal();
                loadPricing();
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error saving pricing', 'error');
        });
    }

    function viewHistory(productId, productName) {
        showTab('history');
        
        fetch('pricing_operations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=get_pricing_history&product_id=' + productId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayHistory(productName, data.history);
            } else {
                showNotification('Error loading history', 'error');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function displayHistory(productName, history) {
        let html = `<h3 style="margin-top: 20px; margin-bottom: 20px;">Price History for ${productName}</h3>`;
        
        if (history.length === 0) {
            html += '<div class="empty-state"><p>No price history found for this product</p></div>';
        } else {
            html += '<div class="table-container"><table class="history-table"><thead><tr>';
            html += '<th>Cost Price</th><th>Selling Price</th><th>Markup %</th><th>Margin %</th><th>Changed On</th>';
            html += '</tr></thead><tbody>';
            
            history.forEach(record => {
                const changeDate = new Date(record.change_date).toLocaleDateString();
                html += `<tr>
                    <td>$${parseFloat(record.cost_price).toFixed(2)}</td>
                    <td>$${parseFloat(record.selling_price).toFixed(2)}</td>
                    <td>${record.markup_percentage}%</td>
                    <td>${record.margin_percentage}%</td>
                    <td>${changeDate}</td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
        }
        
        document.getElementById('historyContent').innerHTML = html;
    }

    function showTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById(tabName + 'Tab').classList.add('active');
        event.target?.classList.add('active');
    }

    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.className = 'notification ' + type;
        notification.style.display = 'block';

        setTimeout(() => { notification.style.display = 'none'; }, 3000);
    }

    window.onclick = function(event) {
        const modal = document.getElementById('pricingModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }

    // Real-time search
    document.getElementById('searchInput')?.addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        const filtered = allPricing.filter(item => 
            (item.product_name && item.product_name.toLowerCase().includes(searchText)) ||
            (item.sku && item.sku.toLowerCase().includes(searchText))
        );
        displayPricing(filtered);
    });
</script>
</body>
</html>
