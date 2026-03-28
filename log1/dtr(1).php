<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents & Tracking Reports - Logistics 1</title>
    <link rel="stylesheet" href="../includes/styles.css">
    <style>
        .inventory-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .inventory-table th, .inventory-table td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 13px; }
        .inventory-table th { background-color: #f2f2f2; }
        .btn { padding: 5px 10px; margin: 2px; border: none; cursor: pointer; border-radius: 3px; font-size: 12px; text-decoration: none; display: inline-block; }
        .btn-add    { background-color: #4CAF50; color: white; }
        .btn-view   { background-color: #007bff; color: white; }
        .card { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status-pending { background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: 600; }
        .status-approved { background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: 600; }
        .status-rejected { background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: 600; }
    </style>
</head>
<body>
<?php
session_start();
include '../includes/db.php';

$pos = $conn->query("SELECT id, po_number, supplier_name, expected_delivery_date, status, total_profit, total_cost FROM purchase_orders ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC) ?: [];
?>

<div class="sidebar" id="sidebar">
    <div class="brand">
        <h1>Logistic 1</h1>
        <button id="sidebarToggle" aria-label="Toggle navigation">☰</button>
    </div>
    <ul class="nav-list">
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="true">
                <span class="nav-section-icon" aria-hidden="true">📂</span>
                <span class="nav-section-label">Smart Warehousing System</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="sws.php"><span class="nav-text">Warehouse</span></a></li>
                <li><a href="psm.php"><span class="nav-text">Procurement</span></a></li>
                <li><a href="dtr.php" class="active"><span class="nav-text">Documents</span></a></li>
            </ul>
        </li>
    </ul>
</div>

<div class="main" id="mainContent">
    <div class="page-header">
        <h2>Documents & Tracking Reports</h2>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3>Purchase Orders Tracking</h3>
           
        </div>
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>PO Number</th>
                    <th>Supplier</th>
                    <th>Expected Delivery</th>
                    <th>Status</th>
                    <th>Profit</th>
                    <th>Cost</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pos)): ?>
                    <tr><td colspan="7" style="text-align: center; color: #999; padding: 40px;">No purchase orders yet. <a href="../core1/PurchaseOrder.php">Create one</a>.</td></tr>
                <?php else: ?>
                    <?php foreach ($pos as $po): ?>
                    <tr>
                        <td><?= htmlspecialchars($po['id']) ?></td>
                        <td><strong><?= htmlspecialchars($po['po_number']) ?></strong></td>
                        <td><?= htmlspecialchars($po['supplier_name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($po['expected_delivery_date'] ?: '—') ?></td>
                        <td><span class="status-<?= strtolower($po['status']) ?>"><?= htmlspecialchars($po['status']) ?></span></td>
                        <td style="color: #28a745; font-weight: bold;">₱<?= number_format($po['total_profit'], 2) ?></td>
                        <td>₱<?= number_format($po['total_cost'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
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
        // Open SWS section by default
        setSectionOpen(section, true);
    });
</script>
</body>
</html>
