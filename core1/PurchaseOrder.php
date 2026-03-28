<?php
include '../includes/db.php';

// Ensure products table exists
$result = $conn->query("SHOW TABLES LIKE 'products'");
if ($result->num_rows == 0) {
    echo "<script>window.location.href = '../includes/db.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Management</title>
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

        .add-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .add-btn:hover {
            background: #0056b3;
        }

        .filter-bar {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e8eaed;
        }

        .filter-bar input {
            padding: 8px 12px;
            border: 1px solid #d0d0d0;
            border-radius: 4px;
            font-size: 14px;
            width: 320px;
        }

        .filter-bar-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-bar-spacer {
            flex: 1;
        }

        .filter-btn {
            padding: 8px 14px;
            border: 1px solid #d0d0d0;
            border-radius: 6px;
            background: white;
            color: #333;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .filter-btn.active {
            background: #007bff;
            border-color: #007bff;
            color: white;
        }

        .filter-btn:hover {
            background: #f8f9fa;
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

        .status-pending {
            color: #ffc107;
            font-weight: 600;
        }

        .status-approved {
            color: #28a745;
            font-weight: 600;
        }

        .status-rejected {
            color: #dc3545;
            font-weight: 600;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }

        .action-btn.view {
            background: #6c757d;
            color: white;
        }

        .action-btn.approve {
            background: #28a745;
            color: white;
        }

        .action-btn.reject {
            background: #dc3545;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.25s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: #fff;
            margin: 3% auto;
            border-radius: 12px;
            width: 92%;
            max-width: 900px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.25s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 28px;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .close {
            font-size: 28px;
            cursor: pointer;
            color: #999;
            transition: all 0.2s ease;
        }

        .close:hover {
            color: #333;
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 24px 28px 32px;
        }

        .modal-body .form-group {
            margin-bottom: 18px;
        }

        .modal-body .form-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 18px;
        }

        .modal-body .form-row.full {
            grid-template-columns: 1fr;
        }

        .modal-body label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #333;
        }

        .modal-body input,
        .modal-body select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d0d0d0;
            border-radius: 6px;
            font-size: 14px;
            background: #fff;
            transition: all 0.2s ease;
        }

        .modal-body input:focus,
        .modal-body select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .items-table th,
        .items-table td {
            padding: 10px 12px;
            border: 1px solid #e0e0e0;
            text-align: left;
        }

        .items-table th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .items-table input {
            width: 100%;
            box-sizing: border-box;
        }

        .summary-row {
            display: flex;
            justify-content: flex-end;
            gap: 30px;
            margin-top: 10px;
            font-weight: 600;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 22px;
            padding-top: 16px;
            border-top: 1px solid #f0f0f0;
        }

        .modal-actions .submit-btn {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
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

        .empty-state {
            text-align: center;
            padding: 40px 20px;
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
        <h2>Purchase Order Management</h2>
    </div>

    <div class="filter-bar">
        <div class="filter-bar-item">
            <button class="filter-btn active" data-status="All" onclick="setStatusFilter('All')">All</button>
            <button class="filter-btn" data-status="Pending" onclick="setStatusFilter('Pending')">Pending</button>
            <button class="filter-btn" data-status="Approved" onclick="setStatusFilter('Approved')">Approved</button>
            <button class="filter-btn" data-status="Rejected" onclick="setStatusFilter('Rejected')">Rejected</button>
        </div>
        <div class="filter-bar-item">
            <label for="searchPO">Search</label>
            <input type="text" id="searchPO" placeholder="Search by PO number or supplier..." onkeyup="filterPOs()">
        </div>
        <div class="filter-bar-spacer"></div>
        <button class="add-btn" onclick="openPOModal()">+ New Purchase Order</button>
    </div>

    <div class="table-container">
        <table id="poTable">
            <thead>
                <tr>
                    <th>PO #</th>
                    <th>Created</th>
                    <th>Expected Delivery</th>
                    <th>Status</th>
                    <th>Total Cost</th>
                    <th>Potential Profit</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="poTableBody">
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <p>Loading purchase orders...</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- PO Modal -->
<div id="poModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="poModalTitle">Create Purchase Order</h3>
            <span class="close" onclick="closePOModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="poForm" onsubmit="savePurchaseOrder(event)">
                <div class="form-row full">
                    <div class="form-group">
                        <label for="supplierName">Supplier (optional)</label>
                        <input type="text" id="supplierName" placeholder="Supplier name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="expectedDelivery">Expected Delivery Date</label>
                        <input type="date" id="expectedDelivery" required>
                    </div>
                    <div class="form-group">
                        <label for="poNumber">PO Number</label>
                        <input type="text" id="poNumber" readonly class="auto-calculated" value="(auto generated)">
                    </div>
                </div>

                <div class="form-group">
                    <label>Order Items</label>
                    <table class="items-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Product</th>
                                <th style="width: 12%;">Quantity</th>
                                <th style="width: 15%;">Unit Cost</th>
                                <th style="width: 15%;">Unit Price</th>
                                <th style="width: 15%;">Line Cost</th>
                                <th style="width: 15%;">Line Price</th>
                                <th style="width: 8%;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                    <button type="button" class="add-btn" style="margin-top: 10px;" onclick="addItemRow()">+ Add Item</button>
                </div>

                <div class="summary-row">
                    <div>Total Cost: $<span id="poTotalCost">0.00</span></div>
                    <div>Potential Profit: $<span id="poTotalProfit">0.00</span></div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="submit-btn cancel" onclick="closePOModal()">Cancel</button>
                    <button type="submit" class="submit-btn">Save Purchase Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="notification" id="notification"></div>

<script>
    let allProducts = [];
    let allPOs = [];
    let currentEditingPO = null;
    let currentStatusFilter = 'All';

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

    window.onload = function() {
        loadProducts();
        loadPOs();
    };

    function loadProducts() {
        fetch('purchase_order_operations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=get_products'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                allProducts = data.products;
            }
        })
        .catch(err => console.error(err));
    }

    function loadPOs() {
        fetch('purchase_order_operations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=get_po_list'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                allPOs = data.orders;
                displayPOs(allPOs);
            } else {
                showNotification('Unable to load purchase orders', 'error');
            }
        })
        .catch(err => console.error(err));
    }

    function displayPOs(orders) {
        const tbody = document.getElementById('poTableBody');
        const search = document.getElementById('searchPO').value.toLowerCase();

        const filteredOrders = orders.filter(order => {
            const matchesStatus = currentStatusFilter === 'All' || order.status === currentStatusFilter;
            const matchesSearch = (order.po_number && order.po_number.toLowerCase().includes(search)) ||
                                  (order.supplier_name && order.supplier_name.toLowerCase().includes(search));
            return matchesStatus && matchesSearch;
        });

        if (filteredOrders.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><p>No purchase orders found.</p></div></td></tr>`;
            return;
        }

        tbody.innerHTML = filteredOrders.map(order => {
            const statusClass = order.status === 'Approved' ? 'status-approved' : order.status === 'Rejected' ? 'status-rejected' : 'status-pending';
            const created = new Date(order.created_at).toLocaleDateString();
            const expected = order.expected_delivery_date ? new Date(order.expected_delivery_date).toLocaleDateString() : '-';

            return `<tr>
                <td><strong>${order.po_number}</strong></td>
                <td>${created}</td>
                <td>${expected}</td>
                <td class="${statusClass}">${order.status}</td>
                <td>$${parseFloat(order.total_cost || 0).toFixed(2)}</td>
                <td>$${parseFloat(order.total_profit || 0).toFixed(2)}</td>
                <td>
                    <button class="action-btn view" onclick="viewPO(${order.id})">View</button>
                    ${order.status === 'Pending' ? `<button class="action-btn approve" onclick="setPOStatus(${order.id}, 'Approved')">Approve</button>
                    <button class="action-btn reject" onclick="setPOStatus(${order.id}, 'Rejected')">Reject</button>` : ''}
                </td>
            </tr>`;
        }).join('');
    }

    function filterPOs() {
        displayPOs(allPOs);
    }

    function setStatusFilter(status) {
        currentStatusFilter = status;
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-status') === status);
        });
        displayPOs(allPOs);
    }

    function openPOModal() {
        currentEditingPO = null;
        document.getElementById('poModalTitle').textContent = 'Create Purchase Order';
        document.getElementById('poForm').reset();
        document.getElementById('poNumber').value = '(auto generated)';
        document.getElementById('expectedDelivery').value = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        document.getElementById('supplierName').value = '';
        clearItems();
        addItemRow();
        calculatePOTotals();
        document.getElementById('poModal').style.display = 'block';
    }

    function closePOModal() {
        document.getElementById('poModal').style.display = 'none';
    }

    function clearItems() {
        document.getElementById('itemsBody').innerHTML = '';
    }

    function addItemRow(item = {}) {
        const row = document.createElement('tr');
        const productOptions = allProducts.map(p => `<option value="${p.id}">${p.product_name} (${p.sku})</option>`).join('');
        const productId = item.product_id || '';
        const qty = item.quantity || 1;
        const unitCost = item.unit_cost ? parseFloat(item.unit_cost).toFixed(2) : '';
        const unitPrice = item.unit_price ? parseFloat(item.unit_price).toFixed(2) : '';

        row.innerHTML = `
            <td>
                <select class="item-product" onchange="onProductChange(this)">
                    <option value="">Select product...</option>
                    ${productOptions}
                </select>
            </td>
            <td><input type="number" min="1" class="item-qty" value="${qty}" onchange="updateLine(this)"></td>
            <td><input type="number" step="0.01" class="item-cost" value="${unitCost}" onchange="updateLine(this)"></td>
            <td><input type="number" step="0.01" class="item-price" value="${unitPrice}" onchange="updateLine(this)"></td>
            <td class="line-cost">$0.00</td>
            <td class="line-price">$0.00</td>
            <td><button type="button" class="action-btn reject" onclick="removeItemRow(this)">×</button></td>
        `;

        document.getElementById('itemsBody').appendChild(row);

        if (productId) {
            row.querySelector('.item-product').value = productId;
        }

        if (productId) {
            populateRowFromProduct(row, productId);
        }

        updateLine(row.querySelector('.item-qty'));
    }

    function populateRowFromProduct(row, productId) {
        const product = allProducts.find(p => p.id == productId);
        if (!product) return;

        const costInput = row.querySelector('.item-cost');
        const priceInput = row.querySelector('.item-price');
        costInput.value = parseFloat(product.cost_price || 0).toFixed(2);
        priceInput.value = parseFloat(product.store_price || 0).toFixed(2);
        updateLine(costInput);
    }

    function onProductChange(select) {
        const row = select.closest('tr');
        populateRowFromProduct(row, select.value);
    }

    function updateLine(element) {
        const row = element.closest('tr');
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const unitCost = parseFloat(row.querySelector('.item-cost').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.item-price').value) || 0;

        const lineCost = qty * unitCost;
        const linePrice = qty * unitPrice;

        row.querySelector('.line-cost').textContent = '$' + lineCost.toFixed(2);
        row.querySelector('.line-price').textContent = '$' + linePrice.toFixed(2);

        calculatePOTotals();
    }

    function removeItemRow(button) {
        button.closest('tr').remove();
        calculatePOTotals();
    }

    function calculatePOTotals() {
        const rows = Array.from(document.querySelectorAll('#itemsBody tr'));
        let totalCost = 0;
        let totalProfit = 0;

        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const unitCost = parseFloat(row.querySelector('.item-cost').value) || 0;
            const unitPrice = parseFloat(row.querySelector('.item-price').value) || 0;

            totalCost += qty * unitCost;
            totalProfit += qty * (unitPrice - unitCost);
        });

        document.getElementById('poTotalCost').textContent = totalCost.toFixed(2);
        document.getElementById('poTotalProfit').textContent = totalProfit.toFixed(2);
    }

    function savePurchaseOrder(event) {
        event.preventDefault();

        const supplierName = document.getElementById('supplierName').value.trim();
        const expectedDelivery = document.getElementById('expectedDelivery').value;
        const rows = Array.from(document.querySelectorAll('#itemsBody tr'));
        const items = [];

        rows.forEach(row => {
            const productId = row.querySelector('.item-product').value;
            if (!productId) return;

            const quantity = parseInt(row.querySelector('.item-qty').value, 10) || 0;
            const unitCost = parseFloat(row.querySelector('.item-cost').value) || 0;
            const unitPrice = parseFloat(row.querySelector('.item-price').value) || 0;

            if (quantity <= 0) return;

            items.push({
                product_id: productId,
                quantity,
                unit_cost: unitCost,
                unit_price: unitPrice
            });
        });

        if (items.length === 0) {
            showNotification('Add at least one product to save the purchase order.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'create_po');
        formData.append('supplier_name', supplierName);
        formData.append('expected_delivery_date', expectedDelivery);
        formData.append('items', JSON.stringify(items));

        fetch('purchase_order_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                closePOModal();
                loadPOs();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showNotification('Error saving purchase order.', 'error');
        });
    }

    function viewPO(poId) {
        fetch('purchase_order_operations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=get_po_details&po_id=' + poId
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const order = data.order;
                const items = data.items;

                const details = `PO #${order.po_number} (Status: ${order.status})\nExpected Delivery: ${order.expected_delivery_date || '-'}\n\nItems:\n`;
                const itemLines = items.map(item => {
                    const name = item.product_name || 'Unknown product';
                    const qty = item.quantity;
                    const unitCost = parseFloat(item.unit_cost).toFixed(2);
                    const unitPrice = parseFloat(item.unit_price).toFixed(2);
                    return `• ${name} | Qty: ${qty} | Cost: $${unitCost} | Price: $${unitPrice}`;
                }).join('\n');

                alert(details + itemLines);
            } else {
                showNotification('Unable to load purchase order details.', 'error');
            }
        })
        .catch(err => console.error(err));
    }

    function setPOStatus(poId, status) {
        const formData = new FormData();
        formData.append('action', 'update_po_status');
        formData.append('po_id', poId);
        formData.append('status', status);

        fetch('purchase_order_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                loadPOs();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showNotification('Unable to update order status.', 'error');
        });
    }

    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.className = 'notification ' + type;
        notification.style.display = 'block';

        setTimeout(() => { notification.style.display = 'none'; }, 3000);
    }

    window.onclick = function(event) {
        const modal = document.getElementById('poModal');
        if (event.target === modal) {
            closePOModal();
        }
    }
</script>
</body>
</html>
