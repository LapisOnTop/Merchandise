<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procurement &amp; Sourcing - Logistics 1</title>
    <link rel="stylesheet" href="../includes/styles.css">
    <style>
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 13px; }
        .table th { background-color: #f2f2f2; }
        .btn { padding: 6px 10px; margin: 2px; border: none; cursor: pointer; border-radius: 3px; font-size: 12px; }
        .btn-add    { background-color: #4CAF50; color: white; }
        .btn-delete { background-color: #f44336; color: white; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
        .card { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status-pending  { background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 3px; font-size: 12px; }
        .status-approved { background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 3px; font-size: 12px; }
        .status-rejected { background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 3px; font-size: 12px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); background: white; padding: 25px; border-radius: 5px; width: 80%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
        .modal-close { float: right; cursor: pointer; font-size: 24px; line-height: 1; }
    </style>
</head>
<body>
<?php
session_start();

include '../includes/db.php';



function addSupplierToDB($supplier, $conn) {
    // Check duplicate email
    $stmt = $conn->prepare("SELECT id FROM sourcing_management WHERE email = ?");
    $stmt->bind_param("s", $supplier['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return "Email already exists.";
    }
    // Insert
    $stmt = $conn->prepare("INSERT INTO sourcing_management (name, company_name, contact_person, email, phone, business_reg_number, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $supplier['name'], $supplier['company_name'], $supplier['contact_person'], $supplier['email'], $supplier['phone'], $supplier['business_reg_number'], $supplier['address']);
    if ($stmt->execute()) {
        return true;
    }
    return $conn->error;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: log1login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_supplier'])) {
        $supplier = [
            'name'                 => $_POST['name'],
            'company_name'         => $_POST['company_name'] ?? '',
            'contact_person'       => $_POST['contact_person'] ?? '',
            'email'                => $_POST['email'],
            'phone'                => $_POST['phone'] ?? '',
            'business_reg_number'  => $_POST['business_reg_number'] ?? '',
            'address'              => $_POST['address'] ?? '',
        ];
        $result = addSupplierToDB($supplier, $conn);
        if ($result === true) {
            $message = 'Supplier added successfully to database!';
        } elseif (is_string($result)) {
            $message = 'Error: ' . $result;
        } else {
            $message = 'Failed to add supplier.';
        }
    }
}

$suppliers = $conn->query("SELECT * FROM sourcing_management ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC) ?: [];
?>

<div class="sidebar" id="sidebar">
    <div class="brand">
        <h1>Logistic 1</h1>
        <button id="sidebarToggle" aria-label="Toggle navigation">☰</button>
    </div>
    <ul class="nav-list">
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">📂</span>
                <span class="nav-section-label">Smart Warehousing System</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="sws.php"><span class="nav-text">Warehouse</span></a></li>
                <li><a href="log1main.php"><span class="nav-text">Main Dashboard</span></a></li>
                <li><a href="psm.php"><span class="nav-text">Procurement</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">👥</span>
                <span class="nav-section-label">Logistics</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="plt.php"><span class="nav-text">Tracker</span></a></li>
                <li><a href="alms.php"><span class="nav-text">Assets</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">⚙️</span>
                <span class="nav-section-label">Account</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="log1login.php"><span class="nav-text">Logout</span></a></li>
            </ul>
        </li>
    </ul>
</div>

<div class="main" id="mainContent">
    <div class="page-header">
        <h2>Procurement &amp; Sourcing Management</h2>
    </div>

    <?php if ($message): ?>
        <div style="background:#d4edda; color:#155724; padding:10px; margin:10px 0; border-radius:3px;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    

   

    <!-- Supplier Management -->
    <div class="card">
        <h3>Suppliers Management</h3>
        <div style="margin-bottom: 20px;">
            <button class="btn btn-add" onclick="showSupplierModal()">Add New Supplier</button>
        </div>

        <table class="table" style="font-size:12px;">
            <thead>
                <tr><th>Supplier Name</th><th>Company Name</th><th>Contact Person</th><th>Email</th><th>Phone</th><th>Business Reg #</th><th>Address</th></tr>
            </thead>
            <tbody>
                <?php if (count($suppliers) > 0): ?>
                    <?php foreach ($suppliers as $s): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($s['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($s['company_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($s['contact_person'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($s['email'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($s['phone'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($s['business_reg_number'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($s['address'] ?? '—'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; color:#999;">No suppliers added yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal" id="supplierModal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('supplierModal')">&times;</span>
        <h3>Add New Supplier</h3>
        <form method="POST">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label>Supplier Name: <span style="color:red;">*</span></label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Company Name:</label>
                    <input type="text" name="company_name">
                </div>
                <div class="form-group">
                    <label>Contact Person:</label>
                    <input type="text" name="contact_person">
                </div>
                <div class="form-group">
                    <label>Email Address:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Business Registration Number:</label>
                    <input type="text" name="business_reg_number">
                </div>
                <div class="form-group">
                    <label>Phone:</label>
                    <input type="text" name="phone">
                </div>
            </div>
            <div class="form-group">
                <label>Address Information:</label>
                <textarea name="address" style="height:80px;"></textarea>
            </div>
            <button type="submit" name="add_supplier" class="btn btn-add">Add Supplier</button>
            <button type="button" class="btn" onclick="closeModal('supplierModal')" style="background:#6c757d; color:white;">Cancel</button>
        </form>
    </div>
</div>

<script>
    const sidebar       = document.getElementById('sidebar');
    const mainContent   = document.getElementById('mainContent');
    const sidebarToggle = document.getElementById('sidebarToggle');

    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    });

    document.querySelectorAll('.nav-section').forEach(section => {
        section.classList.add('open');
        section.querySelector('.nav-section-title').setAttribute('data-expanded', 'true');
    });

    function showSupplierModal() {
        document.getElementById('supplierModal').style.display = 'block';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    window.addEventListener('click', function(e) {
        const modal = document.getElementById('supplierModal');
        if (e.target === modal) modal.style.display = 'none';
    });
</script>
</body>
</html>
