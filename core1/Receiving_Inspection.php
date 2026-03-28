<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receiving & Inspection</title>
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

        .filter-bar {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e8eaed;
        }

        .filter-bar-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-bar input {
            padding: 8px 12px;
            border: 1px solid #d0d0d0;
            border-radius: 4px;
            font-size: 14px;
            width: 320px;
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

        .action-btn.view {
            background: #6c757d;
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

        .auto-calculated {
            background: #f8f9fa;
            color: #666;
        }

        /* Tab Navigation Styles */
        .tab-navigation {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: #666;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            color: #007bff;
            border-bottom-color: #007bff;
        }

        .tab-btn:hover {
            color: #007bff;
            background: #f8f9fa;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Report Modal Styles */
        .report-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .summary-item {
            flex: 1;
            margin-right: 20px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .report-table th,
        .report-table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .report-table th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #f8d7da;
            color: #721c24;
        }

        /* Info Banner Styles */
        .info-banner {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 4px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .info-banner p {
            margin: 0;
            color: #1565c0;
            font-size: 14px;
        }

        .info-banner strong {
            color: #0d47a1;
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
                    <li><a href="Receiving_Inspection.php" class="active"><span class="nav-text">Receiving & Inspection</span></a></li>
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
            <h2>Receiving & Inspection</h2>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <button class="tab-btn active" onclick="showTab('inspection', this)">Inspection</button>
            <button class="tab-btn" onclick="showTab('reports', this)">Reports</button>
        </div>

        <!-- Inspection Tab -->
        <div id="inspectionTab" class="tab-content active">
            <div class="filter-bar">
                <div class="filter-bar-item">
                    <label for="searchPO">Search Approved POs</label>
                    <input type="text" id="searchPO" placeholder="Search by PO number or supplier..." onkeyup="filterPOs()">
                </div>
                <div class="filter-bar-item">
                    <button class="add-btn" onclick="loadApprovedPOs()">Refresh</button>
                </div>
            </div>

            <div class="info-banner">
                <p><strong>Note:</strong> Only approved purchase orders that haven't been inspected yet are shown here. Once inspected, POs are marked as completed and moved to the Reports tab.</p>
            </div>

            <div class="table-container">
                <table id="poTable">
                    <thead>
                        <tr>
                            <th>PO #</th>
                            <th>Supplier</th>
                            <th>Expected Delivery</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="poTableBody">
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <p>No approved purchase orders found.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Reports Tab -->
        <div id="reportsTab" class="tab-content">
            <div class="filter-bar">
                <div class="filter-bar-item">
                    <label for="searchReport">Search Reports</label>
                    <input type="text" id="searchReport" placeholder="Search by PO number or supplier..." onkeyup="filterReports()">
                </div>
                <div class="filter-bar-item">
                    <button class="add-btn" onclick="loadReports()">Refresh</button>
                </div>
            </div>

            <div class="table-container">
                <table id="reportsTable">
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>PO #</th>
                            <th>Supplier</th>
                            <th>Inspection Date</th>
                            <th>Items</th>
                            <th>Received</th>
                            <th>Rejected</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="reportsTableBody">
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <p>No inspection reports found.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Inspection Modal -->
        <div id="inspectionModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="inspectionModalTitle">Inspect Purchase Order</h3>
                    <span class="close" onclick="closeInspectionModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>PO Number</label>
                            <input type="text" id="inspectPONumber" readonly class="auto-calculated">
                        </div>
                        <div class="form-group">
                            <label>Supplier</label>
                            <input type="text" id="inspectSupplier" readonly class="auto-calculated">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Expected Delivery</label>
                            <input type="text" id="inspectExpected" readonly class="auto-calculated">
                        </div>
                        <div class="form-group">
                            <label>Inspection Date</label>
                            <input type="text" id="inspectionDate" readonly class="auto-calculated" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Items to Inspect</label>
                        <table class="items-table" id="inspectionItemsTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Batch #</th>
                                    <th>Expiry</th>
                                    <th>Ordered</th>
                                    <th>Received</th>
                                    <th>Rejected</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody id="inspectionItemsBody"></tbody>
                        </table>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="submit-btn cancel" onclick="closeInspectionModal()">Cancel</button>
                        <button type="button" class="submit-btn" onclick="saveInspection()">Save Inspection</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="notification" id="notification"></div>
    </div>

    <script>
        let approvedPOs = [];
        let currentPO = null;

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
            showTab('inspection');
        };

        function loadApprovedPOs() {
            fetch('receiving_inspection_operations.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=get_approved_pos'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    approvedPOs = data.orders;
                    displayApprovedPOs();
                } else {
                    showNotification('Unable to load approved purchase orders', 'error');
                }
            })
            .catch(err => {
                console.error('Error loading POs:', err);
                showNotification('Error loading purchase orders', 'error');
            });
        }

        function displayApprovedPOs() {
            const tbody = document.getElementById('poTableBody');
            const search = document.getElementById('searchPO').value.toLowerCase();
            const filtered = approvedPOs.filter(po => {
                return (po.po_number && po.po_number.toLowerCase().includes(search)) ||
                       (po.supplier_name && po.supplier_name.toLowerCase().includes(search));
            });

            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5"><div class="empty-state"><p>No approved purchase orders found.</p></div></td></tr>`;
                return;
            }

            tbody.innerHTML = filtered.map(po => {
                const created = new Date(po.created_at).toLocaleDateString();
                const expected = po.expected_delivery_date ? new Date(po.expected_delivery_date).toLocaleDateString() : '-';
                return `<tr>
                    <td><strong>${po.po_number}</strong></td>
                    <td>${po.supplier_name || '-'}</td>
                    <td>${expected}</td>
                    <td>${created}</td>
                    <td><button class="action-btn view" onclick="openInspectionModal(${po.id})">Inspect</button></td>
                </tr>`;
            }).join('');
        }

        function filterPOs() {
            displayApprovedPOs();
        }

        function openInspectionModal(poId) {
            currentPO = approvedPOs.find(po => po.id == poId);
            if (!currentPO) {
                showNotification('Purchase order not found', 'error');
                return;
            }

            document.getElementById('inspectPONumber').value = currentPO.po_number;
            document.getElementById('inspectSupplier').value = currentPO.supplier_name || '';
            document.getElementById('inspectExpected').value = currentPO.expected_delivery_date || '';
            document.getElementById('inspectionDate').value = new Date().toLocaleDateString();

            loadPOItems(poId);
            document.getElementById('inspectionModal').style.display = 'block';
        }

        function closeInspectionModal() {
            document.getElementById('inspectionModal').style.display = 'none';
        }

        function loadPOItems(poId) {
            fetch('receiving_inspection_operations.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=get_po_items&po_id=' + poId
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderInspectionItems(data.items);
                } else {
                    showNotification('Unable to load PO items', 'error');
                }
            })
            .catch(err => {
                console.error('Error loading PO items:', err);
                showNotification('Unable to load PO items', 'error');
            });
        }

        function renderInspectionItems(items) {
            const tbody = document.getElementById('inspectionItemsBody');
            if (!items || items.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8"><div class="empty-state"><p>No items to inspect.</p></div></td></tr>`;
                return;
            }

            tbody.innerHTML = items.map(item => {
                return `<tr data-po-item="${item.id}" data-product="${item.product_id}">
                    <td>${item.product_name || 'Unknown'}</td>
                    <td><input type="text" class="batch-number" placeholder="Enter batch number" value=""></td>
                    <td><input type="date" class="expiry-date" value=""></td>
                    <td>${item.quantity}</td>
                    <td><input type="number" min="0" max="${item.quantity}" class="received-qty" value="${item.quantity}" onchange="updateRejected(this)"></td>
                    <td><input type="number" min="0" max="${item.quantity}" class="rejected-qty" value="0" onchange="updateReceived(this)"></td>
                    <td>
                        <select class="inspection-status">
                            <option value="Passed">Passed</option>
                            <option value="Failed">Failed</option>
                        </select>
                    </td>
                    <td><input type="text" class="inspection-notes" placeholder="Condition notes, damage, quality issues, etc."></td>
                </tr>`;
            }).join('');
        }

        function updateRejected(input) {
            const row = input.closest('tr');
            const ordered = parseInt(row.cells[3].textContent);
            const received = parseInt(input.value) || 0;
            const rejected = ordered - received;
            row.querySelector('.rejected-qty').value = Math.max(0, rejected);
        }

        function updateReceived(input) {
            const row = input.closest('tr');
            const ordered = parseInt(row.cells[3].textContent);
            const rejected = parseInt(input.value) || 0;
            const received = ordered - rejected;
            row.querySelector('.received-qty').value = Math.max(0, received);
        }

        function saveInspection() {
            const rows = Array.from(document.querySelectorAll('#inspectionItemsBody tr'));
            const items = rows.map(row => {
                const ordered = parseInt(row.cells[3].textContent);
                const received = parseInt(row.querySelector('.received-qty').value, 10) || 0;
                const rejected = parseInt(row.querySelector('.rejected-qty').value, 10) || 0;

                if (received + rejected !== ordered) {
                    throw new Error('Received + Rejected quantity must equal Ordered quantity');
                }

                return {
                    po_item_id: row.getAttribute('data-po-item'),
                    product_id: row.getAttribute('data-product'),
                    batch_number: row.querySelector('.batch-number').value.trim(),
                    expiry_date: row.querySelector('.expiry-date').value,
                    received_qty: received,
                    rejected_qty: rejected,
                    inspection_status: row.querySelector('.inspection-status').value,
                    inspection_notes: row.querySelector('.inspection-notes').value.trim()
                };
            });

            // Validate required fields
            for (let item of items) {
                if (!item.batch_number) {
                    showNotification('Batch number is required for all items', 'error');
                    return;
                }
                if (!item.expiry_date) {
                    showNotification('Expiry date is required for all items', 'error');
                    return;
                }
            }

            const formData = new FormData();
            formData.append('action', 'save_inspection');
            formData.append('po_id', currentPO.id);
            formData.append('items', JSON.stringify(items));

            fetch('receiving_inspection_operations.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeInspectionModal();
                    loadApprovedPOs();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(err => {
                console.error('Error saving inspection:', err);
                showNotification('Error saving inspection.', 'error');
            });
        }

        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = 'notification ' + type;
            notification.style.display = 'block';
            setTimeout(() => { notification.style.display = 'none'; }, 3000);
        }

        // Tab switching functionality
        function showTab(tabName, buttonElement = null) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Show selected tab
            document.getElementById(tabName + 'Tab').classList.add('active');

            // Activate the corresponding button
            if (buttonElement) {
                buttonElement.classList.add('active');
            } else {
                // Find button by tab name
                const targetButton = Array.from(buttons).find(btn =>
                    btn.textContent.toLowerCase().includes(tabName.toLowerCase())
                );
                if (targetButton) {
                    targetButton.classList.add('active');
                }
            }

            // Load data for the selected tab
            if (tabName === 'inspection') {
                loadApprovedPOs();
            } else if (tabName === 'reports') {
                loadReports();
            }
        }

        // Reports functionality
        function loadReports() {
            fetch('receiving_inspection_operations.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=get_reports'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    displayReports(data.reports);
                } else {
                    showNotification('Unable to load inspection reports', 'error');
                }
            })
            .catch(err => {
                console.error('Error loading reports:', err);
                showNotification('Error loading inspection reports', 'error');
            });
        }

        function displayReports(reports) {
            const tbody = document.getElementById('reportsTableBody');
            const search = document.getElementById('searchReport').value.toLowerCase();
            const filtered = reports.filter(report => {
                return (report.po_number && report.po_number.toLowerCase().includes(search)) ||
                       (report.supplier_name && report.supplier_name.toLowerCase().includes(search));
            });

            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9"><div class="empty-state"><p>No inspection reports found.</p></div></td></tr>`;
                return;
            }

            tbody.innerHTML = filtered.map(report => {
                const inspectionDate = new Date(report.inspection_date).toLocaleDateString();
                const statusClass = report.status === 'Completed' ? 'status-completed' : 'status-pending';
                return `<tr>
                    <td><strong>${report.id}</strong></td>
                    <td>${report.po_number}</td>
                    <td>${report.supplier_name || '-'}</td>
                    <td>${inspectionDate}</td>
                    <td>${report.total_items}</td>
                    <td>${report.total_received}</td>
                    <td>${report.total_rejected}</td>
                    <td><span class="status ${statusClass}">${report.status}</span></td>
                    <td><button class="action-btn view" onclick="viewReport(${report.id})">View</button></td>
                </tr>`;
            }).join('');
        }

        function filterReports() {
            loadReports();
        }

        function viewReport(reportId) {
            fetch('receiving_inspection_operations.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=get_report_details&report_id=' + reportId
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showReportModal(data.report);
                } else {
                    showNotification('Unable to load report details', 'error');
                }
            })
            .catch(err => {
                console.error('Error loading report details:', err);
                showNotification('Error loading report details', 'error');
            });
        }

        function showReportModal(report) {
            // Create report modal HTML
            const modalHtml = `
                <div id="reportModal" class="modal" style="display: block;">
                    <div class="modal-content" style="max-width: 800px;">
                        <div class="modal-header">
                            <h3>Inspection Report - ${report.po_number}</h3>
                            <span class="close" onclick="closeReportModal()">&times;</span>
                        </div>
                        <div class="modal-body">
                            <div class="report-summary">
                                <div class="summary-row">
                                    <div class="summary-item"><strong>PO Number:</strong> ${report.po_number}</div>
                                    <div class="summary-item"><strong>Supplier:</strong> ${report.supplier_name || '-'}</div>
                                </div>
                                <div class="summary-row">
                                    <div class="summary-item"><strong>Inspection Date:</strong> ${new Date(report.inspection_date).toLocaleDateString()}</div>
                                    <div class="summary-item"><strong>Status:</strong> <span class="status ${report.status === 'Completed' ? 'status-completed' : 'status-pending'}">${report.status}</span></div>
                                </div>
                                <div class="summary-row">
                                    <div class="summary-item"><strong>Total Items:</strong> ${report.total_items}</div>
                                    <div class="summary-item"><strong>Total Received:</strong> ${report.total_received}</div>
                                    <div class="summary-item"><strong>Total Rejected:</strong> ${report.total_rejected}</div>
                                </div>
                            </div>
                            <h4>Inspection Details</h4>
                            <div class="table-container">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Batch #</th>
                                            <th>Expiry Date</th>
                                            <th>Ordered</th>
                                            <th>Received</th>
                                            <th>Rejected</th>
                                            <th>Status</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${report.details.map(item => `
                                            <tr>
                                                <td>${item.product_name}</td>
                                                <td>${item.batch_number}</td>
                                                <td>${item.expiry_date ? new Date(item.expiry_date).toLocaleDateString() : '-'}</td>
                                                <td>${item.ordered_qty}</td>
                                                <td>${item.received_qty}</td>
                                                <td>${item.rejected_qty}</td>
                                                <td><span class="status ${item.inspection_status === 'Passed' ? 'status-completed' : 'status-pending'}">${item.inspection_status}</span></td>
                                                <td>${item.inspection_notes || '-'}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-actions">
                            <button class="submit-btn cancel" onclick="closeReportModal()">Close</button>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal if present
            const existingModal = document.getElementById('reportModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        function closeReportModal() {
            const modal = document.getElementById('reportModal');
            if (modal) {
                modal.remove();
            }
        }

        window.onclick = function(event) {
            const inspectionModal = document.getElementById('inspectionModal');
            const reportModal = document.getElementById('reportModal');
            if (event.target === inspectionModal) {
                closeInspectionModal();
            }
            if (reportModal && event.target === reportModal) {
                closeReportModal();
            }
        }
    </script>
</body>
</html>
