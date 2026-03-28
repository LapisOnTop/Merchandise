<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Warehousing System - Logistics 1</title>
    <link rel="stylesheet" href="../includes/styles.css">
    <style>
        .inventory-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .inventory-table th, .inventory-table td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 13px; }
        .inventory-table th { background-color: #f2f2f2; }
        .btn { padding: 5px 10px; margin: 2px; border: none; cursor: pointer; border-radius: 3px; font-size: 12px; }
        .btn-add    { background-color: #4CAF50; color: white; }
        .btn-edit   { background-color: #2196F3; color: white; }
        .btn-delete { background-color: #f44336; color: white; }
        .form-container { margin-top: 20px; background: #f9f9f9; padding: 15px; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
        .card { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); background: white; padding: 25px; border-radius: 5px; width: 80%; max-width: 500px; max-height: 90vh; overflow-y: auto; }
        .modal-close { float: right; cursor: pointer; font-size: 24px; line-height: 1; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 3px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<?php
session_start();

include '../includes/db.php';



// Create tables if not exists
$create_items = "CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(255),
    stock INT DEFAULT 0,
    threshold INT DEFAULT 0,
    expiry_date DATE
)";
$conn->query($create_items);

$create_stock_movements = "CREATE TABLE IF NOT EXISTS stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_id INT,
    movement_type ENUM('in','out'),
    quantity INT,
    reason TEXT,
    performed_by INT,
    performed_by_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($create_stock_movements);

$create_vehicle_requests = "CREATE TABLE IF NOT EXISTS vehicle_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id VARCHAR(50),
    department VARCHAR(100),
    purpose VARCHAR(255),
    needed_at DATETIME,
    pickup_location VARCHAR(255),
    destination VARCHAR(255),
    items_transport TEXT,
    goods_type VARCHAR(100),
    estimated_weight DECIMAL(8,2),
    box_count INT,
    special_instructions TEXT,
    vehicle_size VARCHAR(100),
    special_features VARCHAR(100),
    notes TEXT,
    requested_by_id INT,
    requested_by VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($create_vehicle_requests);

$message      = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_item'])) {
        $name      = $_POST['name'];
        $category  = $_POST['category'];
        $stock     = (int)$_POST['stock'];
        $threshold = (int)$_POST['threshold'];
        $expiry_date = $_POST['expiry_date'];

$sql = "INSERT INTO items (name, category, stock, threshold, expiry_date) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdis", $name, $category, $stock, $threshold, $expiry_date);
        if ($stmt->execute()) {
            $message = 'Item added successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error adding item: ' . $conn->error;
            $message_type = 'error';
        }
        $stmt->close();

    } elseif (isset($_POST['edit_item'])) {
        $id = (int)$_POST['id'];
        $name      = $_POST['name'];
        $category  = $_POST['category'];
        $stock     = (int)$_POST['stock'];
        $threshold = (int)$_POST['threshold'];
        $expiry_date = $_POST['expiry_date'];

$sql = "UPDATE items SET name=?, category=?, stock=?, threshold=?, expiry_date=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdisd", $name, $category, $stock, $threshold, $expiry_date, $id);
        if ($stmt->execute()) {
            $message = 'Item updated successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error updating item: ' . $conn->error;
            $message_type = 'error';
        }
        $stmt->close();

    } elseif (isset($_POST['delete_item'])) {
        $id = (int)$_POST['delete_item'];
        $sql = "DELETE FROM items WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("d", $id);
        if ($stmt->execute()) {
            $message = 'Item deleted successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error deleting item: ' . $conn->error;
            $message_type = 'error';
        }
        $stmt->close();

    } elseif (isset($_POST['stock_in'])) {
        $id       = (int)$_POST['id'];
        $quantity = (int)$_POST['stock_quantity'];
        $reason   = $_POST['reason'];

        // Get current stock
        $sql = "SELECT stock FROM items WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("d", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $item = $result->fetch_assoc();
            $new_stock = $item['stock'] + $quantity;
            $update_sql = "UPDATE items SET stock=? WHERE id=?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("dd", $new_stock, $id);
            $update_stmt->execute();
            $update_stmt->close();


            $sql = "INSERT INTO stock_movements (inventory_id, movement_type, quantity, reason, performed_by, performed_by_name) VALUES (?, 'in', ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisss", $id, $quantity, $reason, $_SESSION['user_id'], $_SESSION['full_name']);
            $stmt->execute();
            $stmt->close();

            $message      = 'Stock added successfully!';
            $message_type = 'success';
        } else {
            $message = 'Item not found!';
            $message_type = 'error';
        }
        $stmt->close();

    } elseif (isset($_POST['stock_out'])) {
        $id       = (int)$_POST['id'];
        $quantity = (int)$_POST['stock_quantity'];
        $reason   = $_POST['reason'];

        // Get current stock
        $sql = "SELECT stock FROM items WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("d", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $item = $result->fetch_assoc();
            if ($item['stock'] < $quantity) {
                $message      = 'Insufficient stock! Available: ' . $item['stock'];
                $message_type = 'error';
            } else {
                $new_stock = $item['stock'] - $quantity;
                $update_sql = "UPDATE items SET stock=? WHERE id=?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("dd", $new_stock, $id);
                $update_stmt->execute();
                $update_stmt->close();


                $sql = "INSERT INTO stock_movements (inventory_id, movement_type, quantity, reason, performed_by, performed_by_name) VALUES (?, 'out', ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iisss", $id, $quantity, $reason, $_SESSION['user_id'], $_SESSION['full_name']);
                $stmt->execute();
                $stmt->close();

                $message      = 'Stock removed successfully!';
                $message_type = 'success';
            }
        } else {
            $message = 'Item not found!';
            $message_type = 'error';
        }
        $stmt->close();
    } elseif (isset($_POST['request_vehicle'])) {
        $request_id      = trim($_POST['request_id'] ?? '');
        $department      = trim($_POST['department'] ?? '');
        $purpose         = $_POST['purpose'] ?? '';
        $needed_at       = $_POST['needed_at'] ?? '';
        $pickup_location = trim($_POST['pickup_location'] ?? '');
        $destination     = trim($_POST['destination'] ?? '');
        $items_transport = trim($_POST['items_transport'] ?? '');
        $goods_type      = $_POST['goods_type'] ?? '';
        $estimated_weight = (float)($_POST['estimated_weight'] ?? 0);
        $box_count       = (int)($_POST['box_count'] ?? 0);
        $special_instructions = trim($_POST['special_instructions'] ?? '');
        $vehicle_size    = $_POST['vehicle_size'] ?? '';
        $special_features = $_POST['special_features'] ?? '';
        $notes           = trim($_POST['notes'] ?? '');

        $sql = "INSERT INTO vehicle_requests (request_id, department, purpose, needed_at, pickup_location, destination, items_transport, goods_type, estimated_weight, box_count, special_instructions, vehicle_size, special_features, notes, requested_by_id, requested_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssdissssss", $request_id, $department, $purpose, $needed_at, $pickup_location, $destination, $items_transport, $goods_type, $estimated_weight, $box_count, $special_instructions, $vehicle_size, $special_features, $notes, $_SESSION['user_id'], $_SESSION['full_name']);
        $stmt->execute();
        $stmt->close();

        $message      = 'Vehicle request submitted!';
        $message_type = 'success';
    }
}

$inventory_result = $conn->query("SELECT * FROM items");
$inventory = [];
if ($inventory_result) {
    while ($row = $inventory_result->fetch_assoc()) {
        $inventory[] = $row;
    }
}
$suppliers_result = $conn->query("SELECT * FROM suppliers");
$suppliers = $suppliers_result ? $suppliers_result->fetch_all(MYSQLI_ASSOC) : [];

$movements_result = $conn->query("SELECT * FROM stock_movements ORDER BY created_at DESC");
$movements = $movements_result ? $movements_result->fetch_all(MYSQLI_ASSOC) : [];

$vehicle_requests_result = $conn->query("SELECT * FROM vehicle_requests ORDER BY created_at DESC");
$vehicle_requests = $vehicle_requests_result ? $vehicle_requests_result->fetch_all(MYSQLI_ASSOC) : [];

// Determine next request_id number (formatted as REQ-0000)
$next_request_number = 1;
$max_req_result = $conn->query("SELECT MAX(CAST(SUBSTRING(request_id, 5) AS UNSIGNED)) AS max_seq FROM vehicle_requests WHERE request_id LIKE 'REQ-%'");
if ($max_req_result && ($row = $max_req_result->fetch_assoc())) {
    $max_seq = (int)($row['max_seq'] ?? 0);
    if ($max_seq >= 0) {
        $next_request_number = $max_seq + 1;
    }
}

$low_stock  = array_filter($inventory, fn($item) => isset($item['threshold']) && $item['stock'] <= $item['threshold']);
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
        <h2>Smart Warehousing System</h2>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    

    <!-- Add Item -->
    <div class="card">
        <h3>Inventory Management</h3>
<div style="margin-bottom: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
        <button class="btn btn-add" onclick="showAddModal()">Add New Item</button>
        <button class="btn btn-add" onclick="showVehicleModal()">Request Vehicle</button>
        </div>

        <!-- Inventory Table -->
        <h4 style="margin-top:20px;">Current Inventory</h4>
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Name</th><th>Category</th><th>Stock</th><th>Threshold</th><th>Expiry Date</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($inventory) > 0): ?>
                    <?php foreach ($inventory as $item):
                        $is_low = isset($item['threshold']) && $item['stock'] <= $item['threshold'];
                        $status = $is_low ? '<span style="color:#dc3545;">⚠ Low Stock</span>' : '<span style="color:#28a745;">✓ OK</span>';
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($item['category'] ?? '—'); ?></td>
                        <td><?php echo $item['stock']; ?></td>
                        <td><?php echo $item['threshold']; ?></td>
                        <td><?php echo htmlspecialchars($item['expiry_date'] ?? '—'); ?></td>
                        <td><?php echo $status; ?></td>
                        <td>
                            <button class="btn btn-edit" onclick="showEditModal(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES); ?>)">Edit</button>
                            <form method="POST" style="display:inline;">
                                <button type="submit" name="delete_item" value="<?php echo $item['id']; ?>" class="btn btn-delete"
                                        onclick="return confirm('Delete this item?')">Delete</button>
                            </form>
                            <button class="btn btn-add"    onclick="showStockModal(<?php echo $item['id']; ?>, 'in')">Stock In</button>
                            <button class="btn btn-delete" onclick="showStockModal(<?php echo $item['id']; ?>, 'out')">Stock Out</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; color:#999;">No items in inventory</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Stock Movements -->
    <div class="card">
        <h3>Stock Movements History</h3>
        <table class="inventory-table">
            <thead>
                <tr><th>Date/Time</th><th>Type</th><th>Item ID</th><th>Quantity</th><th>Reason</th><th>Performed By</th></tr>
            </thead>
            <tbody>
                <?php if (count($movements) > 0): ?>
                    <?php foreach (array_reverse($movements) as $move): ?>
                    <tr>
                        <td><?php echo date('M d, H:i', strtotime($move['created_at'])); ?></td>
                        <td>
                            <span style="background:<?php echo $move['movement_type'] === 'in' ? '#28a745' : '#dc3545'; ?>; color:white; padding:2px 6px; border-radius:3px; font-size:11px;">
                                <?php echo ucfirst($move['movement_type']); ?>
                            </span>
                        </td>
                        <td>#<?php echo $move['inventory_id']; ?></td>
                        <td><?php echo $move['quantity']; ?></td>
                        <td><?php echo htmlspecialchars($move['reason']); ?></td>
                        <td><?php echo htmlspecialchars($move['performed_by_name']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; color:#999;">No movements recorded</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Vehicle Requests -->
    <div class="card">
        <h3>Vehicle Requests</h3>
        <table class="inventory-table">
            <thead>
                <tr><th>Submitted By</th><th>Purpose</th><th>Date Needed</th><th>Destination</th><th>Vehicle Size</th><th>Status</th><th>Submitted</th></tr>
            </thead>
            <tbody>
                <?php if (count($vehicle_requests) > 0): ?>
                    <?php foreach (array_reverse($vehicle_requests) as $req): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($req['requester']); ?></strong><br><small style="color:#666;"><?php echo htmlspecialchars($req['requested_by']); ?></small></td>
                        <td><?php echo htmlspecialchars($req['purpose']); ?></td>
                        <td><?php echo date('M d, Y - H:i', strtotime($req['needed_at'])); ?></td>
                        <td><?php echo htmlspecialchars($req['destination']); ?></td>
                        <td><span style="background:#2196F3; color:white; padding:2px 8px; border-radius:3px; font-size:11px;"><?php echo htmlspecialchars($req['vehicle_size']); ?></span></td>
                        <td><span style="background:#28a745; color:white; padding:2px 8px; border-radius:3px; font-size:11px;">Pending</span></td>
                        <td><?php echo date('M d, H:i', strtotime($req['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; color:#999;">No vehicle requests yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('editModal')">&times;</span>
        <h3>Edit Item</h3>
        <form method="POST">
            <input type="hidden" name="id" id="editId">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" id="editName" required>
                </div>
                <div class="form-group">
                    <label>Category:</label>
                    <input type="text" name="category" id="editCategory" required>
                </div>
                <div class="form-group">
                    <label>Stock:</label>
                    <input type="number" name="stock" id="editStock" required min="0">
                </div>
                <div class="form-group">
                    <label>Threshold:</label>
                    <input type="number" name="threshold" id="editThreshold" required min="0">
                </div>
                <div class="form-group">
                    <label>Expiry Date:</label>
                    <input type="date" name="expiry_date" id="editExpiryDate">
                </div>
            </div>
            <button type="submit" name="edit_item" class="btn btn-edit">Update Item</button>
            <button type="button" class="btn" onclick="closeModal('editModal')" style="background:#6c757d; color:white;">Cancel</button>
        </form>
    </div>
</div>

<!-- Stock In/Out Modal -->
<div class="modal" id="stockModal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('stockModal')">&times;</span>
        <h3 id="stockTitle">Stock In</h3>
        <form method="POST">
            <input type="hidden" name="id" id="stockItemId">
            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="stock_quantity" id="stockQty" required min="1">
            </div>
            <div class="form-group">
                <label>Reason:</label>
                <textarea name="reason" required style="height:60px;"></textarea>
            </div>
            <button type="submit" name="stock_in"  id="stockInBtn"  class="btn btn-add"    style="display:none;">Confirm Stock In</button>
            <button type="submit" name="stock_out" id="stockOutBtn" class="btn btn-delete" style="display:none;">Confirm Stock Out</button>
            <button type="button" class="btn" onclick="closeModal('stockModal')" style="background:#6c757d; color:white;">Cancel</button>
        </form>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('addModal')">&times;</span>
        <h3>Add New Item</h3>
        <form method="POST">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Category:</label>
                    <input type="text" name="category" required>
                </div>
                <div class="form-group">
                    <label>Stock:</label>
                    <input type="number" name="stock" required min="0">
                </div>
                <div class="form-group">
                    <label>Threshold:</label>
                    <input type="number" name="threshold" required min="0">
                </div>
                <div class="form-group">
                    <label>Expiry Date:</label>
                    <input type="date" name="expiry_date">
                </div>
            </div>
            <button type="submit" name="add_item" class="btn btn-add">Add Item</button>
            <button type="button" class="btn" onclick="closeModal('addModal')" style="background:#6c757d; color:white;">Cancel</button>
        </form>
    </div>
</div>

<!-- Vehicle Request Modal -->
<div class="modal" id="vehicleModal">
    <div class="modal-content" style="max-width: 700px; max-height: 95vh; overflow-y: auto;">
        <span class="modal-close" onclick="closeModal('vehicleModal')">&times;</span>
        <h3>Request Vehicle</h3>
        <form method="POST">
            <!-- SECTION 1: Basic Request Info -->
            <div style="margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
                <h4 style="margin: 0 0 15px 0; color: #0d6efd; font-size: 14px; text-transform: uppercase; font-weight: 700;">1. Basic Request Info</h4>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label>Auto Generated Request ID</label>
                        <input type="text" id="requestId" name="request_id" readonly style="background:#f5f5f5;">
                    </div>
                    <div class="form-group">
                        <label>Which Department is requesting? *</label>
                        <input type="text" name="department" value="Core 1" readonly style="background:#f5f5f5;">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>What is the purpose of this request? *</label>
                        <select name="purpose" required>
                            <option value="">Select purpose</option>
                            <option value="Stock Transfer">Stock Transfer</option>
                            <option value="Delivery">Delivery</option>
                            <option value="Pull-out">Pull-out</option>
                            <option value="Return">Return</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Schedule Details -->
            <div style="margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
                <h4 style="margin: 0 0 15px 0; color: #0d6efd; font-size: 14px; text-transform: uppercase; font-weight: 700;">2. Schedule Details</h4>
                <div class="form-group">
                    <label>When do you need the vehicle? (Date & Time) *</label>
                    <input type="datetime-local" name="needed_at" required>
                </div>
            </div>

            <!-- SECTION 3: Pickup & Destination -->
            <div style="margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
                <h4 style="margin: 0 0 15px 0; color: #0d6efd; font-size: 14px; text-transform: uppercase; font-weight: 700;">3. Pickup & Destination</h4>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label>Pick-up Location *</label>
                        <input type="text" name="pickup_location" value="Warehouse" readonly style="background:#f5f5f5;">
                    </div>
                    <div class="form-group">
                        <label>Destination *</label>
                        <select name="destination" required>
                            <option value="">Select destination</option>
                            <option value="Susano Branch">Susano Branch</option>
                            <option value="Jordan Branch">Jordan Branch</option>
                            <option value="Nitang Branch">Nitang Branch</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: Cargo/Load Information -->
            <div style="margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
                <h4 style="margin: 0 0 15px 0; color: #0d6efd; font-size: 14px; text-transform: uppercase; font-weight: 700;">4. Cargo/Load Information</h4>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group" style="grid-column: span 2;">
                        <label>What items will be transported? *</label>
                        <textarea name="items_transport" required style="height:70px;"></textarea>
                    </div>
                    <div class="form-group">
                        <label>What type of goods are these? *</label>
                        <select name="goods_type" required>
                            <option value="">Select type</option>
                            <option value="General">General</option>
                            <option value="Fragile">Fragile</option>
                            <option value="Perishable">Perishable</option>
                            <option value="Hazardous">Hazardous</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estimated Total Weight (kg) *</label>
                        <input type="number" name="estimated_weight" min="0" step="50" required>
                    </div>
                    <div class="form-group">
                        <label>Box/Item Quantity *</label>
                        <input type="number" name="box_count" min="0" required>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Special Instructions</label>
                        <textarea name="special_instructions" style="height:70px; resize: vertical;"></textarea>
                    </div>
                </div>
            </div>

            <!-- SECTION 5: Vehicle Requirements -->
            <div style="margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
                <h4 style="margin: 0 0 15px 0; color: #0d6efd; font-size: 14px; text-transform: uppercase; font-weight: 700;">5. Vehicle Requirements</h4>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label>What size of vehicle is needed? *</label>
                        <select name="vehicle_size" required>
                            <option value="">Select size</option>
                            <option value="Small (Van)">Small (Van)</option>
                            <option value="Medium (Box Truck)">Medium (Box Truck)</option>
                            <option value="Large (Trailer)">Large (Trailer)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Do you need special vehicle features?</label>
                        <select name="special_features">
                            <option value="">Select option</option>
                            <option value="None">None</option>
                            <option value="Refrigerated">Refrigerated</option>
                            <option value="Closed Van">Closed Van</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION 6: Additional Notes -->
            <div style="margin-bottom: 20px;">
                <h4 style="margin: 0 0 15px 0; color: #0d6efd; font-size: 14px; text-transform: uppercase; font-weight: 700;">6. Additional Notes</h4>
                <div class="form-group">
                    <label>Any Special Instruction or remarks?</label>
                    <textarea name="notes" style="height:80px; resize: vertical;"></textarea>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px; border-top: 2px solid #e9ecef; padding-top: 15px;">
                <button type="submit" name="request_vehicle" class="btn btn-add">Submit Request</button>
                <button type="button" class="btn" onclick="closeModal('vehicleModal')" style="background:#6c757d; color:white;">Cancel</button>
            </div>
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

    function showEditModal(item) {
        document.getElementById('editId').value          = item.id;
        document.getElementById('editName').value        = item.name;
        document.getElementById('editCategory').value    = item.category || '';
        document.getElementById('editStock').value       = item.stock;
        document.getElementById('editThreshold').value   = item.threshold;
        document.getElementById('editExpiryDate').value  = item.expiry_date || '';
        document.getElementById('editModal').style.display = 'block';
    }

    function showStockModal(id, type) {
        document.getElementById('stockItemId').value       = id;
        document.getElementById('stockTitle').textContent  = type === 'in' ? 'Stock In' : 'Stock Out';
        document.getElementById('stockInBtn').style.display  = type === 'in'  ? 'inline-block' : 'none';
        document.getElementById('stockOutBtn').style.display = type === 'out' ? 'inline-block' : 'none';
        document.getElementById('stockQty').value = '';
        document.getElementById('stockModal').style.display = 'block';
    }

    function showAddModal() {
        document.getElementById('addModal').style.display = 'block';
    }

    let nextRequestIdNumber = <?php echo $next_request_number; ?>;

    function formatRequestId(number) {
        return `REQ-${String(number).padStart(4, '0')}`;
    }

    function showVehicleModal() {
        document.getElementById('vehicleModal').style.display = 'block';
        document.getElementById('requestId').value = formatRequestId(nextRequestIdNumber);
        nextRequestIdNumber += 1;
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    window.addEventListener('click', function(e) {
        ['editModal', 'stockModal', 'addModal', 'vehicleModal'].forEach(id => {
            const modal = document.getElementById(id);
            if (e.target === modal) modal.style.display = 'none';
        });
    });
</script>
</body>
</html>
