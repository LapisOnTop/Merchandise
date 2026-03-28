<?php
require_once __DIR__ . '/../includes/db.php';

$uploadDir = __DIR__ . '/../uploads/vehicles';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function ensureColumnExists($conn, $table, $column, $definition) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($result && $result->num_rows === 0) {
        $conn->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}

// Ensure vehicles table exists
$createTableSql = "CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(100) NOT NULL,
    vehicle_brand VARCHAR(100) DEFAULT NULL,
    model VARCHAR(100) NOT NULL,
    plate_number VARCHAR(50) NOT NULL,
    status ENUM('Available','Unavailable','In Delivery','Assigned') NOT NULL DEFAULT 'Available',
    conduction_sticker_number VARCHAR(100) DEFAULT NULL,
    vehicle_type VARCHAR(100) DEFAULT NULL,
    year_model VARCHAR(10) DEFAULT NULL,
    color VARCHAR(50) DEFAULT NULL,
    chassis_number VARCHAR(100) DEFAULT NULL,
    engine_number VARCHAR(100) DEFAULT NULL,
    fuel_type VARCHAR(50) DEFAULT NULL,
    transmission_type VARCHAR(50) DEFAULT NULL,
    seating_capacity INT DEFAULT NULL,
    cargo_capacity DECIMAL(10,2) DEFAULT NULL,
    or_number VARCHAR(100) DEFAULT NULL,
    cr_number VARCHAR(100) DEFAULT NULL,
    registration_date DATE DEFAULT NULL,
    registration_expiry_date DATE DEFAULT NULL,
    insurance_provider VARCHAR(150) DEFAULT NULL,
    insurance_policy_number VARCHAR(100) DEFAULT NULL,
    insurance_expiry_date DATE DEFAULT NULL,
    roadworthiness_status VARCHAR(50) DEFAULT NULL,
    purchase_date DATE DEFAULT NULL,
    purchase_cost DECIMAL(12,2) DEFAULT NULL,
    supplier_dealer_name VARCHAR(150) DEFAULT NULL,
    or_cr_file VARCHAR(500) DEFAULT NULL,
    insurance_certificate_file VARCHAR(500) DEFAULT NULL,
    vehicle_photo_file VARCHAR(500) DEFAULT NULL,
    maintenance_records_file VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
$conn->query($createTableSql);

// Ensure columns exist on older tables
$columnsToEnsure = [
    'brand' => 'VARCHAR(100) NOT NULL',
    'vehicle_brand' => 'VARCHAR(100) DEFAULT NULL',
    'model' => 'VARCHAR(100) NOT NULL',
    'plate_number' => 'VARCHAR(50) NOT NULL',
    'status' => "ENUM('Available','Unavailable','In Delivery','Assigned') NOT NULL DEFAULT 'Available'",
    'conduction_sticker_number' => 'VARCHAR(100) DEFAULT NULL',
    'vehicle_type' => 'VARCHAR(100) DEFAULT NULL',
    'year_model' => 'VARCHAR(10) DEFAULT NULL',
    'color' => 'VARCHAR(50) DEFAULT NULL',
    'chassis_number' => 'VARCHAR(100) DEFAULT NULL',
    'engine_number' => 'VARCHAR(100) DEFAULT NULL',
    'fuel_type' => 'VARCHAR(50) DEFAULT NULL',
    'transmission_type' => 'VARCHAR(50) DEFAULT NULL',
    'seating_capacity' => 'INT DEFAULT NULL',
    'cargo_capacity' => 'DECIMAL(10,2) DEFAULT NULL',
    'or_number' => 'VARCHAR(100) DEFAULT NULL',
    'cr_number' => 'VARCHAR(100) DEFAULT NULL',
    'registration_date' => 'DATE DEFAULT NULL',
    'registration_expiry_date' => 'DATE DEFAULT NULL',
    'insurance_provider' => 'VARCHAR(150) DEFAULT NULL',
    'insurance_policy_number' => 'VARCHAR(100) DEFAULT NULL',
    'insurance_expiry_date' => 'DATE DEFAULT NULL',
    'roadworthiness_status' => 'VARCHAR(50) DEFAULT NULL',
    'purchase_date' => 'DATE DEFAULT NULL',
    'purchase_cost' => 'DECIMAL(12,2) DEFAULT NULL',
    'supplier_dealer_name' => 'VARCHAR(150) DEFAULT NULL',
    'or_cr_file' => 'VARCHAR(500) DEFAULT NULL',
    'insurance_certificate_file' => 'VARCHAR(500) DEFAULT NULL',
    'vehicle_photo_file' => 'VARCHAR(500) DEFAULT NULL',
    'maintenance_records_file' => 'VARCHAR(500) DEFAULT NULL',
    'updated_at' => 'TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP',
];

foreach ($columnsToEnsure as $col => $def) {
    ensureColumnExists($conn, 'vehicles', $col, $def);
}

$successMessage = '';
$errorMessage = '';

function handleFileUpload($fieldName, $uploadDir) {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $fileName = basename($_FILES[$fieldName]['name']);
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $unique = time() . '_' . bin2hex(random_bytes(6));
    $destName = "$unique.$ext";
    $targetPath = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $destName;

    if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {
        return 'uploads/vehicles/' . $destName;
    }

    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $plateNumber = trim($_POST['plate_number'] ?? '');
    $status = $_POST['status'] ?? 'Available';
    $conductionSticker = trim($_POST['conduction_sticker_number'] ?? '');
    $vehicleType = trim($_POST['vehicle_type'] ?? '');
    $yearModel = trim($_POST['year_model'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $chassisNumber = trim($_POST['chassis_number'] ?? '');
    $engineNumber = trim($_POST['engine_number'] ?? '');
    $fuelType = trim($_POST['fuel_type'] ?? '');
    $transmissionType = trim($_POST['transmission_type'] ?? '');
    $seatingCapacity = intval($_POST['seating_capacity'] ?? 0) ?: null;
    $cargoCapacity = is_numeric($_POST['cargo_capacity'] ?? '') ? floatval($_POST['cargo_capacity']) : null;
    $orNumber = trim($_POST['or_number'] ?? '');
    $crNumber = trim($_POST['cr_number'] ?? '');
    $registrationDate = $_POST['registration_date'] ?? null;
    $registrationExpiryDate = $_POST['registration_expiry_date'] ?? null;
    $insuranceProvider = trim($_POST['insurance_provider'] ?? '');
    $insurancePolicyNumber = trim($_POST['insurance_policy_number'] ?? '');
    $insuranceExpiryDate = $_POST['insurance_expiry_date'] ?? null;
    $roadworthinessStatus = trim($_POST['roadworthiness_status'] ?? '');
    $purchaseDate = $_POST['purchase_date'] ?? null;
    $purchaseCost = is_numeric($_POST['purchase_cost'] ?? '') ? floatval($_POST['purchase_cost']) : null;
    $supplierDealerName = trim($_POST['supplier_dealer_name'] ?? '');

    $orCrFile = handleFileUpload('or_cr_file', $uploadDir);
    $insuranceCertificateFile = handleFileUpload('insurance_certificate_file', $uploadDir);
    $vehiclePhotoFile = handleFileUpload('vehicle_photo_file', $uploadDir);
    $maintenanceRecordsFile = handleFileUpload('maintenance_records_file', $uploadDir);

    if ($action === 'add_vehicle') {
        if ($brand === '' || $model === '' || $plateNumber === '' || $status === '') {
            $errorMessage = 'Please fill out all required fields.';
        } else {
            $stmt = $conn->prepare('INSERT INTO vehicles (brand, vehicle_brand, model, plate_number, status, conduction_sticker_number, vehicle_type, year_model, color, chassis_number, engine_number, fuel_type, transmission_type, seating_capacity, cargo_capacity, or_number, cr_number, registration_date, registration_expiry_date, insurance_provider, insurance_policy_number, insurance_expiry_date, roadworthiness_status, purchase_date, purchase_cost, supplier_dealer_name, or_cr_file, insurance_certificate_file, vehicle_photo_file, maintenance_records_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssssssssssssidsssssssssdsssss', $brand, $brand, $model, $plateNumber, $status, $conductionSticker, $vehicleType, $yearModel, $color, $chassisNumber, $engineNumber, $fuelType, $transmissionType, $seatingCapacity, $cargoCapacity, $orNumber, $crNumber, $registrationDate, $registrationExpiryDate, $insuranceProvider, $insurancePolicyNumber, $insuranceExpiryDate, $roadworthinessStatus, $purchaseDate, $purchaseCost, $supplierDealerName, $orCrFile, $insuranceCertificateFile, $vehiclePhotoFile, $maintenanceRecordsFile);

            if ($stmt->execute()) {
                $successMessage = 'Vehicle added successfully.';
            } else {
                $errorMessage = 'Failed to add vehicle. Please try again.';
            }
            $stmt->close();
        }
    }

    if ($action === 'update_vehicle') {
        $id = intval($_POST['vehicle_id'] ?? 0);
        if ($id <= 0) {
            $errorMessage = 'Invalid vehicle selected.';
        } else {
            $existingResult = $conn->query('SELECT * FROM vehicles WHERE id = ' . $id);
            $existing = $existingResult ? $existingResult->fetch_assoc() : null;

            $orCrFile = $orCrFile ?: ($existing['or_cr_file'] ?? null);
            $insuranceCertificateFile = $insuranceCertificateFile ?: ($existing['insurance_certificate_file'] ?? null);
            $vehiclePhotoFile = $vehiclePhotoFile ?: ($existing['vehicle_photo_file'] ?? null);
            $maintenanceRecordsFile = $maintenanceRecordsFile ?: ($existing['maintenance_records_file'] ?? null);

            $stmt = $conn->prepare('UPDATE vehicles SET brand = ?, vehicle_brand = ?, model = ?, plate_number = ?, status = ?, conduction_sticker_number = ?, vehicle_type = ?, year_model = ?, color = ?, chassis_number = ?, engine_number = ?, fuel_type = ?, transmission_type = ?, seating_capacity = ?, cargo_capacity = ?, or_number = ?, cr_number = ?, registration_date = ?, registration_expiry_date = ?, insurance_provider = ?, insurance_policy_number = ?, insurance_expiry_date = ?, roadworthiness_status = ?, purchase_date = ?, purchase_cost = ?, supplier_dealer_name = ?, or_cr_file = ?, insurance_certificate_file = ?, vehicle_photo_file = ?, maintenance_records_file = ? WHERE id = ?');
            $stmt->bind_param('ssssssssssssssidsssssssssdssssi', $brand, $brand, $model, $plateNumber, $status, $conductionSticker, $vehicleType, $yearModel, $color, $chassisNumber, $engineNumber, $fuelType, $transmissionType, $seatingCapacity, $cargoCapacity, $orNumber, $crNumber, $registrationDate, $registrationExpiryDate, $insuranceProvider, $insurancePolicyNumber, $insuranceExpiryDate, $roadworthinessStatus, $purchaseDate, $purchaseCost, $supplierDealerName, $orCrFile, $insuranceCertificateFile, $vehiclePhotoFile, $maintenanceRecordsFile, $id);

            if ($stmt->execute()) {
                $successMessage = 'Vehicle updated successfully.';
            } else {
                $errorMessage = 'Failed to update vehicle. Please try again.';
            }
            $stmt->close();
        }
    }
}

// Load vehicles for display
$vehicles = [];
$result = $conn->query('SELECT * FROM vehicles ORDER BY id DESC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Look up assigned driver from vehicle_requests
        $vehicle_label = $row['brand'] . ' ' . $row['model'] . ' (' . $row['plate_number'] . ')';
        $driver_query = $conn->query("SELECT add_driver FROM vehicle_requests WHERE add_vehicle = '" . $conn->real_escape_string($vehicle_label) . "' ORDER BY id DESC LIMIT 1");
        $row['assigned_driver'] = '';
        if ($driver_query && $driver_query->num_rows > 0) {
            $dr = $driver_query->fetch_assoc();
            if ($row['status'] === 'Assigned' || $row['status'] === 'Delivering') {
                $row['assigned_driver'] = $dr['add_driver'] ?? '';
            }
        }
        $vehicles[] = $row;
    }
    $result->free();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Management - Fleet & Vehicle</title>
    <link rel="stylesheet" href="../includes/styles.css">
</head>
<body class="vehicle-page">

<div class="sidebar" id="sidebar">
    <div class="brand">
        <h1>Logistic 2</h1>
        <button id="sidebarToggle" aria-label="Toggle navigation">☰</button>
    </div>
    <ul class="nav-list">
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">📂</span>
                <span class="nav-section-label">Fleet & Vehicle Management</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="vehicle_management.php" class="active"><span class="nav-text">Vehicle Management</span></a></li>
                <li><a href="driver_management.php"><span class="nav-text">Driver Management</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">👥</span>
                <span class="nav-section-label">Vehicle Reservation & Dispatch</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="reservation.php"><span class="nav-text">Reservation Management</span></a></li>
                <li><a href="dispatch.php"><span class="nav-text">Dispatch Management</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">📊</span>
                <span class="nav-section-label">Driver & Trip Performance</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="trip_logs.php"><span class="nav-text">Trip Logs</span></a></li>
                <li><a href="driver_performance.php"><span class="nav-text">Driver Performance Management</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">🧩</span>
                <span class="nav-section-label">Transport Cost analysis</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="route_optimization.php"><span class="nav-text">Route Optimization</span></a></li>
                <li><a href="cost_reports.php"><span class="nav-text">Cost Reports</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">⚙️</span>
                <span class="nav-section-label">Example</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="#"><span class="nav-text">Example</span></a></li>
                <li><a href="#"><span class="nav-text">Example</span></a></li>
            </ul>
        </li>
    </ul>
</div>

<div class="main" id="mainContent">
    <div class="page-header">
        <h2>Vehicle Management</h2>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Fleet & Vehicle Management</h3>
        </div>
        <?php if ($successMessage): ?>
            <div class="success-message" id="successMessage">
                <div class="message-content">
                    <div class="message-icon">✅</div>
                    <div>
                        <h3>Success</h3>
                        <p><?= htmlspecialchars($successMessage) ?></p>
                    </div>
                    <button class="close-message" onclick="document.getElementById('successMessage').remove();">×</button>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="success-message" style="background: linear-gradient(135deg, #ff5f5f 0%, #ff8a8a 100%);" id="errorMessage">
                <div class="message-content">
                    <div class="message-icon">⚠️</div>
                    <div>
                        <h3>Error</h3>
                        <p><?= htmlspecialchars($errorMessage) ?></p>
                    </div>
                    <button class="close-message" onclick="document.getElementById('errorMessage').remove();">×</button>
                </div>
            </div>
        <?php endif; ?>

        <div class="filter-search-container">
            <div class="filter-group">
                <label for="vehicleTypeFilter">Filter by status:</label>
                <select id="vehicleTypeFilter" class="filter-select">
                    <option value="">All</option>
                    <option value="Van">Van</option>
                    <option value="Box_Truck">Box Truck</option>
                    <option value="Trailer">Trailer</option>
                </select>
            </div>
            <div class="search-group">
                <label for="searchInput">Search:</label>
                <input type="text" id="searchInput" class="search-input" placeholder="Search by name, department, position...">
            </div>
            <button id="openAddVehicle" class="btn btn-primary add-vehicle-btn">Add Vehicle</button>
        </div>

        <div class="table-container" style="overflow-x:auto; margin-top:20px;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Plate Number</th>
                        <th>Vehicle Type</th>
                        <th>Status</th>
                        <th>Assigned Driver</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($vehicles) === 0): ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding: 20px;">No vehicles found. Use "Add Vehicle" to create one.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($vehicles as $vehicle): ?>
                        <?php $vehicleJson = htmlspecialchars(json_encode($vehicle, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP)); ?>
                        <tr data-vehicle="<?= $vehicleJson ?>">
                            <td><?= htmlspecialchars($vehicle['id']) ?></td>
                                <td><?= htmlspecialchars($vehicle['brand']) ?></td>
                                <td><?= htmlspecialchars($vehicle['model']) ?></td>
                                <td><?= htmlspecialchars($vehicle['plate_number']) ?></td>
                                <td><?= htmlspecialchars($vehicle['vehicle_type'] ?? '') ?></td>
                                <td>
                                    <?php
                                        $status = $vehicle['status'];
                                        $badgeColor = 'rgba(40, 167, 69, 0.15)';
                                        $textColor = '#28a745';
                                        if ($status === 'Unavailable') {
                                            $badgeColor = 'rgba(220, 53, 69, 0.15)';
                                            $textColor = '#dc3545';
                                        } elseif ($status === 'Delivering') {
                                            $badgeColor = 'rgba(59, 130, 246, 0.15)';
                                            $textColor = '#3b82f6';
                                        } elseif ($status === 'Assigned') {
                                            $badgeColor = 'rgba(245, 158, 11, 0.15)';
                                            $textColor = '#f59e0b';
                                        }
                                    ?>
                                    <span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; background: <?= $badgeColor ?>; color: <?= $textColor ?>; font-weight:600; font-size:13px;">
                                        <?= htmlspecialchars($status) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($vehicle['assigned_driver'])): ?>
                                        <span style="font-weight:600; color:#1e40af;"><?= htmlspecialchars($vehicle['assigned_driver']) ?></span>
                                    <?php else: ?>
                                        <span style="color:#999;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <button class="action-btn action-btn-view" type="button" data-vehicle-id="<?= $vehicle['id'] ?>" onclick="openViewModal(<?= $vehicle['id'] ?>)" title="View">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add / Edit Vehicle Modal -->
<div id="vehicleModal" class="modal" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <h3 id="vehicleModalTitle">Add New Vehicle</h3>
            </div>
            <span class="close" id="closeVehicleModal" aria-label="Close">×</span>
        </div>
        <form method="post" id="vehicleForm" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction" value="add_vehicle">
            <input type="hidden" name="vehicle_id" id="vehicleId" value="">

            <div class="form-section" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                <h4 style="grid-column: 1 / -1; margin-top: 0;">SECTION 1: Basic Vehicle Information</h4>

                <div class="form-group">
                    <label for="vehicle_id_display">Vehicle ID</label>
                    <input type="text" id="vehicle_id_display" disabled placeholder="Auto-generated">
                </div>

                <div class="form-group">
                    <label for="plate_number">Plate Number <span style="color: #d00;">*</span></label>
                    <input type="text" id="plate_number" name="plate_number" required>
                </div>

                <div class="form-group">
                    <label for="conduction_sticker_number">Conduction Sticker Number <span style="color: #d00;">*</span></label>
                    <input type="text" id="conduction_sticker_number" name="conduction_sticker_number" required>
                </div>

                <div class="form-group">
                    <label for="vehicle_type">Vehicle Type <span style="color: #d00;">*</span></label>
                    <div class="custom-select">
                        <select id="vehicle_type" name="vehicle_type" required>
                            <option value="">Select Type</option>
                            <option value="Van">Van</option>
                            <option value="Box_Truck">Box Truck</option>
                            <option value="Trailer">Trailer</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="brand">Brand <span style="color: #d00;">*</span></label>
                    <input type="text" id="brand" name="brand" required>
                </div>

                <div class="form-group">
                    <label for="model">Model <span style="color: #d00;">*</span></label>
                    <input type="text" id="model" name="model" required>
                </div>

                <div class="form-group">
                    <label for="year_model">Year Model <span style="color: #d00;">*</span></label>
                    <input type="text" id="year_model" name="year_model" required>
                </div>

                <div class="form-group">
                    <label for="color">Color <span style="color: #d00;">*</span></label>
                    <input type="text" id="color" name="color" required>
                </div>

                <div class="form-group">
                    <label for="chassis_number">Chassis Number <span style="color: #d00;">*</span></label>
                    <input type="text" id="chassis_number" name="chassis_number" required>
                </div>

                <div class="form-group">
                    <label for="engine_number">Engine Number <span style="color: #d00;">*</span></label>
                    <input type="text" id="engine_number" name="engine_number" required>
                </div>

                <div class="form-group">
                    <label for="fuel_type">Fuel Type <span style="color: #d00;">*</span></label>
                    <div class="custom-select">
                        <select id="fuel_type" name="fuel_type" required>
                            <option value="">Select Fuel Type</option>
                            <option value="Gasoline">Gasoline</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Electric">Electric</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="transmission_type">Transmission Type <span style="color: #d00;">*</span></label>
                    <div class="custom-select">
                        <select id="transmission_type" name="transmission_type" required>
                            <option value="">Select Transmission</option>
                            <option value="Automatic">Automatic</option>
                            <option value="Manual">Manual</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="seating_capacity">Seating Capacity <span style="color: #d00;">*</span></label>
                    <div class="custom-select">
                        <select id="seating_capacity" name="seating_capacity" required>
                            <option value="">Select Capacity</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="cargo_capacity">Cargo Capacity (kg) <span style="color: #d00;">*</span></label>
                    <div class="custom-number-input">
                        <input type="number" step="0.01" id="cargo_capacity" name="cargo_capacity" required>
                        <div class="number-spinner">
                            <button type="button" class="spinner-up" data-target="cargo_capacity" data-step="50"></button>
                            <button type="button" class="spinner-down" data-target="cargo_capacity" data-step="50"></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-top: 16px;">
                <h4 style="grid-column: 1 / -1; margin-top: 0;">SECTION 2: Registration & Legal Details</h4>

                <div class="form-group">
                    <label for="or_number">OR Number</label>
                    <input type="text" id="or_number" name="or_number">
                </div>

                <div class="form-group">
                    <label for="cr_number">CR Number</label>
                    <input type="text" id="cr_number" name="cr_number">
                </div>

                <div class="form-group">
                    <label for="registration_date">Registration Date <span style="color: #d00;">*</span></label>
                    <input type="date" id="registration_date" name="registration_date" required>
                </div>

                <div class="form-group">
                    <label for="registration_expiry_date">Registration Expiry Date <span style="color: #d00;">*</span></label>
                    <input type="date" id="registration_expiry_date" name="registration_expiry_date" required>
                </div>

                <div class="form-group">
                    <label for="insurance_provider">Insurance Provider <span style="color: #d00;">*</span></label>
                    <input type="text" id="insurance_provider" name="insurance_provider" required>
                </div>

                <div class="form-group">
                    <label for="insurance_policy_number">Insurance Policy Number <span style="color: #d00;">*</span></label>
                    <input type="text" id="insurance_policy_number" name="insurance_policy_number" required>
                </div>

                <div class="form-group">
                    <label for="insurance_expiry_date">Insurance Expiry Date <span style="color: #d00;">*</span></label>
                    <input type="date" id="insurance_expiry_date" name="insurance_expiry_date" required>
                </div>

                <div class="form-group">
                    <label for="roadworthiness_status">Roadworthiness Status <span style="color: #d00;">*</span></label>
                    <div class="custom-select">
                        <select id="roadworthiness_status" name="roadworthiness_status" required>
                            <option value="">Select Status</option>
                            <option value="Roadworthy">Roadworthy</option>
                            <option value="Not Roadworthy">Not Roadworthy</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-top: 16px;">
                <h4 style="grid-column: 1 / -1; margin-top: 0;">SECTION 3: Financial Information</h4>

                <div class="form-group">
                    <label for="purchase_date">Purchase Date <span style="color: #d00;">*</span></label>
                    <input type="date" id="purchase_date" name="purchase_date" required>
                </div>

                <div class="form-group">
                    <label for="purchase_cost">Purchase Cost <span style="color: #d00;">*</span></label>
                    <div class="custom-number-input">
                        <input type="number" id="purchase_cost" name="purchase_cost" step="0.01" required>
                        <div class="number-spinner">
                            <button type="button" class="spinner-up" data-target="purchase_cost"></button>
                            <button type="button" class="spinner-down" data-target="purchase_cost"></button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="supplier_dealer_name">Supplier / Dealer Name</label>
                    <input type="text" id="supplier_dealer_name" name="supplier_dealer_name">
                </div>
            </div>

            <div class="form-section" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-top: 16px;">
                <h4 style="grid-column: 1 / -1; margin-top: 0;">SECTION 4: Upload Documents</h4>

                <div class="form-group">
                    <label for="or_cr_file">Upload OR/CR</label>
                    <input type="file" id="or_cr_file" name="or_cr_file" accept="image/*,application/pdf">
                </div>

                <div class="form-group">
                    <label for="insurance_certificate_file">Upload Insurance Certificate</label>
                    <input type="file" id="insurance_certificate_file" name="insurance_certificate_file" accept="image/*,application/pdf">
                </div>

                <div class="form-group">
                    <label for="vehicle_photo_file">Upload Vehicle Photo</label>
                    <input type="file" id="vehicle_photo_file" name="vehicle_photo_file" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="maintenance_records_file">Upload Maintenance Records</label>
                    <input type="file" id="maintenance_records_file" name="maintenance_records_file" accept="image/*,application/pdf">
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary btn-danger" id="backVehicle">Back</button>
                <button type="button" class="btn btn-secondary" id="resetVehicle">Reset</button>
                <button type="submit" class="btn btn-primary" id="saveVehicle">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- View Vehicle Modal -->
<div id="viewModal" class="modal" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <h3>View Vehicle</h3>
            </div>
            <span class="close" id="closeViewModal" aria-label="Close">×</span>
        </div>
        <div class="form-section">
            <div class="form-group"><label>ID</label><div class="field-value" id="viewId"></div></div>
            <div class="form-group"><label>Brand</label><div class="field-value" id="viewBrand"></div></div>
            <div class="form-group"><label>Model</label><div class="field-value" id="viewModel"></div></div>
            <div class="form-group"><label>Plate Number</label><div class="field-value" id="viewPlate"></div></div>
            <div class="form-group"><label>Status</label><div class="field-value" id="viewStatus"></div></div>
            <div class="form-group"><label>Vehicle Type</label><div class="field-value" id="viewType"></div></div>
            <div class="form-group"><label>Year Model</label><div class="field-value" id="viewYear"></div></div>
            <div class="form-group"><label>Color</label><div class="field-value" id="viewColor"></div></div>
            <div class="form-group"><label>Conduction Sticker #</label><div class="field-value" id="viewConduction"></div></div>
            <div class="form-group"><label>Chassis #</label><div class="field-value" id="viewChassis"></div></div>
            <div class="form-group"><label>Engine #</label><div class="field-value" id="viewEngine"></div></div>
            <div class="form-group"><label>Fuel Type</label><div class="field-value" id="viewFuel"></div></div>
            <div class="form-group"><label>Transmission</label><div class="field-value" id="viewTransmission"></div></div>
            <div class="form-group"><label>Seating Capacity</label><div class="field-value" id="viewSeating"></div></div>
            <div class="form-group"><label>Cargo Capacity</label><div class="field-value" id="viewCargo"></div></div>
            <div class="form-group"><label>OR Number</label><div class="field-value" id="viewOrNumber"></div></div>
            <div class="form-group"><label>CR Number</label><div class="field-value" id="viewCrNumber"></div></div>
            <div class="form-group"><label>Registration Date</label><div class="field-value" id="viewRegistrationDate"></div></div>
            <div class="form-group"><label>Registration Expiry</label><div class="field-value" id="viewRegistrationExpiry"></div></div>
            <div class="form-group"><label>Insurance Provider</label><div class="field-value" id="viewInsuranceProvider"></div></div>
            <div class="form-group"><label>Insurance Policy #</label><div class="field-value" id="viewInsurancePolicy"></div></div>
            <div class="form-group"><label>Insurance Expiry</label><div class="field-value" id="viewInsuranceExpiry"></div></div>
            <div class="form-group"><label>Roadworthiness Status</label><div class="field-value" id="viewRoadworthiness"></div></div>
            <div class="form-group"><label>Purchase Date</label><div class="field-value" id="viewPurchaseDate"></div></div>
            <div class="form-group"><label>Purchase Cost</label><div class="field-value" id="viewPurchaseCost"></div></div>
            <div class="form-group"><label>Supplier/Dealer</label><div class="field-value" id="viewSupplier"></div></div>
            <div class="form-group"><label>Created At</label><div class="field-value" id="viewCreatedAt"></div></div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-primary" id="closeViewBtn">Close</button>
        </div>
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

        setSectionOpen(section, false);
    });

    // Open the section containing the active page on load
    const activeNavLink = document.querySelector('.nav-sublist a.active') || document.querySelector('.nav-sublist li.active > a');
    if (activeNavLink) {
        const activeSection = activeNavLink.closest('.nav-section');
        if (activeSection) {
            setSectionOpen(activeSection, true);
        }
    }


    const openAddVehicle = document.getElementById('openAddVehicle');
    const vehicleModal = document.getElementById('vehicleModal');
    const viewModal = document.getElementById('viewModal');
    const closeVehicleModal = document.getElementById('closeVehicleModal');
    const closeViewModal = document.getElementById('closeViewModal');
    const vehicleForm = document.getElementById('vehicleForm');

    const modalFields = {
        id: document.getElementById('vehicleId'),
        idDisplay: document.getElementById('vehicle_id_display'),
        brand: document.getElementById('brand'),
        model: document.getElementById('model'),
        plate: document.getElementById('plate_number'),
        status: document.getElementById('status'),
        conductionSticker: document.getElementById('conduction_sticker_number'),
        vehicleType: document.getElementById('vehicle_type'),
        yearModel: document.getElementById('year_model'),
        color: document.getElementById('color'),
        chassis: document.getElementById('chassis_number'),
        engine: document.getElementById('engine_number'),
        fuelType: document.getElementById('fuel_type'),
        transmission: document.getElementById('transmission_type'),
        seatingCapacity: document.getElementById('seating_capacity'),
        cargoCapacity: document.getElementById('cargo_capacity'),
        orNumber: document.getElementById('or_number'),
        crNumber: document.getElementById('cr_number'),
        registrationDate: document.getElementById('registration_date'),
        registrationExpiryDate: document.getElementById('registration_expiry_date'),
        insuranceProvider: document.getElementById('insurance_provider'),
        insurancePolicyNumber: document.getElementById('insurance_policy_number'),
        insuranceExpiryDate: document.getElementById('insurance_expiry_date'),
        roadworthiness: document.getElementById('roadworthiness_status'),
        purchaseDate: document.getElementById('purchase_date'),
        purchaseCost: document.getElementById('purchase_cost'),
        supplierDealer: document.getElementById('supplier_dealer_name'),
        orCrFile: document.getElementById('or_cr_file'),
        insuranceCertificateFile: document.getElementById('insurance_certificate_file'),
        vehiclePhotoFile: document.getElementById('vehicle_photo_file'),
        maintenanceRecordsFile: document.getElementById('maintenance_records_file'),
        action: document.getElementById('formAction'),
        title: document.getElementById('vehicleModalTitle'),
    };

    const viewFields = {
        id: document.getElementById('viewId'),
        brand: document.getElementById('viewBrand'),
        model: document.getElementById('viewModel'),
        plate: document.getElementById('viewPlate'),
        status: document.getElementById('viewStatus'),
        type: document.getElementById('viewType'),
        year: document.getElementById('viewYear'),
        color: document.getElementById('viewColor'),
        conduction: document.getElementById('viewConduction'),
        chassis: document.getElementById('viewChassis'),
        engine: document.getElementById('viewEngine'),
        fuel: document.getElementById('viewFuel'),
        transmission: document.getElementById('viewTransmission'),
        seating: document.getElementById('viewSeating'),
        cargo: document.getElementById('viewCargo'),
        orNumber: document.getElementById('viewOrNumber'),
        crNumber: document.getElementById('viewCrNumber'),
        registrationDate: document.getElementById('viewRegistrationDate'),
        registrationExpiry: document.getElementById('viewRegistrationExpiry'),
        insuranceProvider: document.getElementById('viewInsuranceProvider'),
        insurancePolicy: document.getElementById('viewInsurancePolicy'),
        insuranceExpiry: document.getElementById('viewInsuranceExpiry'),
        roadworthiness: document.getElementById('viewRoadworthiness'),
        purchaseDate: document.getElementById('viewPurchaseDate'),
        purchaseCost: document.getElementById('viewPurchaseCost'),
        supplier: document.getElementById('viewSupplier'),
        createdAt: document.getElementById('viewCreatedAt'),
    };

    const vehicleData = <?php echo json_encode($vehicles, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_PARTIAL_OUTPUT_ON_ERROR) ?: '[]'; ?>;

    function openViewModal(id) {
        console.log('openViewModal called with', id);
        const data = getVehicleData(id);
        if (!data) {
            console.warn('Vehicle not found for id', id);
            return;
        }

        viewFields.id.textContent = data.id || '';
        viewFields.brand.textContent = data.brand || '';
        viewFields.model.textContent = data.model || '';
        viewFields.plate.textContent = data.plate_number || '';
        viewFields.status.textContent = data.status || '';
        viewFields.type.textContent = data.vehicle_type || '';
        viewFields.year.textContent = data.year_model || '';
        viewFields.color.textContent = data.color || '';
        viewFields.conduction.textContent = data.conduction_sticker_number || '';
        viewFields.chassis.textContent = data.chassis_number || '';
        viewFields.engine.textContent = data.engine_number || '';
        viewFields.fuel.textContent = data.fuel_type || '';
        viewFields.transmission.textContent = data.transmission_type || '';
        viewFields.seating.textContent = data.seating_capacity || '';
        viewFields.cargo.textContent = data.cargo_capacity || '';
        viewFields.orNumber.textContent = data.or_number || '';
        viewFields.crNumber.textContent = data.cr_number || '';
        viewFields.registrationDate.textContent = data.registration_date || '';
        viewFields.registrationExpiry.textContent = data.registration_expiry_date || '';
        viewFields.insuranceProvider.textContent = data.insurance_provider || '';
        viewFields.insurancePolicy.textContent = data.insurance_policy_number || '';
        viewFields.insuranceExpiry.textContent = data.insurance_expiry_date || '';
        viewFields.roadworthiness.textContent = data.roadworthiness_status || '';
        viewFields.purchaseDate.textContent = data.purchase_date || '';
        viewFields.purchaseCost.textContent = data.purchase_cost || '';
        viewFields.supplier.textContent = data.supplier_dealer_name || '';
        viewFields.createdAt.textContent = data.created_at || '';

        openModal(viewModal);
    }

    // Ensure view buttons work even if inline handlers are missing or removed by templating
    document.querySelectorAll('.action-btn-view[data-vehicle-id]').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-vehicle-id');
            if (id) {
                openViewModal(id);
            }
        });
    });

    const openModal = (modal) => {
        modal.style.display = 'block';
        modal.setAttribute('aria-hidden', 'false');
    };

    const closeModal = (modal) => {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    };

    const backVehicle = document.getElementById('backVehicle');
    const resetVehicle = document.getElementById('resetVehicle');

    const resetForm = () => {
        vehicleForm.reset();
        modalFields.id.value = '';
        modalFields.idDisplay.value = 'Auto-generated';
        modalFields.action.value = 'add_vehicle';
        modalFields.title.textContent = 'Add New Vehicle';
    };

    openAddVehicle.addEventListener('click', () => {
        resetForm();
        openModal(vehicleModal);
    });

    backVehicle.addEventListener('click', () => closeModal(vehicleModal));
    resetVehicle.addEventListener('click', resetForm);
    closeVehicleModal.addEventListener('click', () => closeModal(vehicleModal));

    closeViewModal.addEventListener('click', () => closeModal(viewModal));
    document.getElementById('closeViewBtn').addEventListener('click', () => closeModal(viewModal));

    window.addEventListener('click', (event) => {
        if (event.target === vehicleModal) {
            closeModal(vehicleModal);
        }
        if (event.target === viewModal) {
            closeModal(viewModal);
        }
    });

    function getVehicleData(id) {
        if (!Array.isArray(vehicleData)) return null;
        return vehicleData.find(v => Number(v.id) === Number(id)) || null;
    }


    // Number Spinner Handler
    document.querySelectorAll('.number-spinner button').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = button.getAttribute('data-target');
            const input = document.getElementById(targetId);
            
            if (!input) return;
            
            const step = parseFloat(button.getAttribute('data-step')) || 1000;
            let currentValue = parseFloat(input.value) || 0;
            
            if (button.classList.contains('spinner-up')) {
                currentValue += step;
            } else if (button.classList.contains('spinner-down')) {
                currentValue = Math.max(0, currentValue - step);
            }
            
            input.value = currentValue;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });

    // Filter and Search functionality
    const vehicleTypeFilter = document.getElementById('vehicleTypeFilter');
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('table tbody tr');

    function filterAndSearch() {
        const selectedVehicleType = vehicleTypeFilter.value.toLowerCase();
        const searchTerm = searchInput.value.toLowerCase();

        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length === 0) return; // Skip empty message row

            const id = cells[0]?.textContent.toLowerCase() || '';
            const brand = cells[1]?.textContent.toLowerCase() || '';
            const model = cells[2]?.textContent.toLowerCase() || '';
            const plateNumber = cells[3]?.textContent.toLowerCase() || '';
            const vehicleType = cells[4]?.textContent.toLowerCase() || '';

            // Filter by Vehicle Type
            const vehicleTypeMatch = !selectedVehicleType || vehicleType.includes(selectedVehicleType);

            // Search in ID, Brand, Model, Plate Number
            const searchMatch = !searchTerm || 
                id.includes(searchTerm) ||
                brand.includes(searchTerm) ||
                model.includes(searchTerm) ||
                plateNumber.includes(searchTerm);

            row.style.display = (vehicleTypeMatch && searchMatch) ? '' : 'none';
        });
    }

    vehicleTypeFilter.addEventListener('change', filterAndSearch);
    searchInput.addEventListener('keyup', filterAndSearch);
</script>

</body>
</html>
