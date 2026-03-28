<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../includes/db.php';

// Handle assignment submission (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'assign_dispatch') {
    header('Content-Type: application/json; charset=utf-8');

    $request_id = intval($_POST['request_id']);
    $vehicle_id = intval($_POST['vehicle_id']);
    $driver_id = intval($_POST['driver_id']);

    if (!$request_id || !$vehicle_id || !$driver_id) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameter(s).']);
        exit;
    }

    // Get vehicle request details
    $req_result = $conn->query("SELECT request_id, department, needed_at, destination, pickup_location, items_transport, goods_type, special_instructions, estimated_weight, box_count, vehicle_size, special_features, notes, created_at FROM vehicle_requests WHERE id = $request_id");
    if ($req_result && $req_result->num_rows > 0) {
        $req_data = $req_result->fetch_assoc();

        // Get vehicle information
        $vehicle_result = $conn->query("SELECT brand, model, plate_number FROM vehicles WHERE id = $vehicle_id");
        $vehicle_data = $vehicle_result ? $vehicle_result->fetch_assoc() : null;
        $vehicle_name = $vehicle_data ? $vehicle_data['brand'] . ' ' . $vehicle_data['model'] . ' (' . $vehicle_data['plate_number'] . ')' : '';

        // Get driver information
        $driver_result = $conn->query("SELECT full_name FROM employees WHERE id = $driver_id");
        $driver_data = $driver_result ? $driver_result->fetch_assoc() : null;
        $driver_name = $driver_data ? $driver_data['full_name'] : '';

        $update_sql = "UPDATE vehicle_requests SET add_vehicle = ?, add_driver = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssi", $vehicle_name, $driver_name, $request_id);

        if ($stmt->execute()) {
            // Update vehicle status to 'Assigned'
            $update_vehicle = $conn->prepare("UPDATE vehicles SET status = 'Assigned' WHERE id = ?");
            $update_vehicle->bind_param("i", $vehicle_id);
            $update_vehicle->execute();

            // Update driver work_status to 'assigned'
            $update_driver = $conn->prepare("UPDATE employees SET work_status = 'assigned' WHERE id = ?");
            $update_driver->bind_param("i", $driver_id);
            $update_driver->execute();

            echo json_encode(['success' => true, 'message' => 'Assignment saved successfully to dispatch']);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Error saving assignment: ' . $conn->error]);
            exit;
        }
    }

    echo json_encode(['success' => false, 'message' => 'Vehicle request not found.']);
    exit;
}

// Fetch vehicle requests for reservation table
$vehicle_requests_result = $conn->query("SELECT id, request_id, department, needed_at, destination, pickup_location, items_transport, goods_type, special_instructions, estimated_weight, box_count, vehicle_size, special_features, notes, created_at FROM vehicle_requests WHERE add_vehicle IS NULL OR add_vehicle = '' ORDER BY id DESC");
$vehicle_requests = $vehicle_requests_result ? $vehicle_requests_result->fetch_all(MYSQLI_ASSOC) : [];

// Fetch available vehicles (status = 'Available')
$vehicles_result = $conn->query("SELECT id, brand, model, plate_number, vehicle_type, status FROM vehicles WHERE status = 'Available'");
$available_vehicles = $vehicles_result ? $vehicles_result->fetch_all(MYSQLI_ASSOC) : [];

// Fetch drivers (employees with position 'Driver' and available status)
$drivers_result = $conn->query("SELECT id, full_name FROM employees WHERE position = 'Driver' AND work_status = 'available'");
$drivers = $drivers_result ? $drivers_result->fetch_all(MYSQLI_ASSOC) : [];

$current_page = strtolower(basename(__FILE__));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>
    <link rel="stylesheet" href="../includes/styles.css">
</head>
<body>

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
                <li class="<?php echo ($current_page == 'vehicle_management.php') ? 'active' : ''; ?>"><a href="vehicle_management.php"><span class="nav-text">Vehicle Management</span></a></li>
                <li class="<?php echo ($current_page == 'driver_management.php') ? 'active' : ''; ?>"><a href="driver_management.php"><span class="nav-text">Driver Management</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">👥</span>
                <span class="nav-section-label">Vehicle Reservation & Dispatch</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li class="<?php echo ($current_page == 'reservation.php') ? 'active' : ''; ?>"><a href="reservation.php"><span class="nav-text">Reservation Management</span></a></li>
                <li class="<?php echo ($current_page == 'dispatch.php') ? 'active' : ''; ?>"><a href="dispatch.php"><span class="nav-text">Dispatch Management</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">📊</span>
                <span class="nav-section-label">Driver & Trip Performance Monitoring</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li class="<?php echo ($current_page == 'trip_logs.php') ? 'active' : ''; ?>"><a href="trip_logs.php"><span class="nav-text">Trip Logs</span></a></li>
                <li class="<?php echo ($current_page == 'driver_performance.php') ? 'active' : ''; ?>"><a href="driver_performance.php"><span class="nav-text">Driver Performance Management</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">🧩</span>
                <span class="nav-section-label">Transport Cost Analysis</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li class="<?php echo ($current_page == 'cost_reports.php') ? 'active' : ''; ?>"><a href="cost_reports.php"><span class="nav-text">Cost Reports</span></a></li>
                <li class="<?php echo ($current_page == 'route_optimization.php') ? 'active' : ''; ?>"><a href="route_optimization.php"><span class="nav-text">Route Optimization</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">⚙️</span>
                <span class="nav-section-label">Settings</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li class="<?php echo ($current_page == 'change_password.php') ? 'active' : ''; ?>"><a href="change_password.php"><span class="nav-text">Change Password</span></a></li>
                <li class="<?php echo ($current_page == 'logout.php') ? 'active' : ''; ?>"><a href="logout.php"><span class="nav-text">Log out</span></a></li>
            </ul>
        </li>
    </ul>
</div>

<div class="main" id="mainContent">
    <div class="page-header">
        <h2>Reservation Dashboard</h2>
    </div>

    <div class="card">
        <h3>Vehicle Requests</h3>
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Request ID</th>
                    <th>Vehicle Size</th>
                    <th>Pick-up Location</th>
                    <th>Destination</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($vehicle_requests) > 0): ?>
                    <?php foreach ($vehicle_requests as $req): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($req['id']); ?></td>
                            <td><?php echo htmlspecialchars($req['request_id']); ?></td>
                            <td><?php echo htmlspecialchars($req['vehicle_size']); ?></td>
                            <td><?php echo htmlspecialchars($req['pickup_location']); ?></td>
                            <td><?php echo htmlspecialchars($req['destination']); ?></td>
                            <td style="text-align: center;">
                                <button class="action-btn action-btn-view" type="button" onclick="openViewModal(<?php echo $req['id']; ?>)" title="View">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="action-btn action-btn-assign" type="button" onclick="assignRequest(<?php echo $req['id']; ?>)" title="Assign">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 12l2 2 4-4"></path>
                                        <path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; color:#999;">No vehicle requests found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- View Vehicle Request Modal -->
<div id="viewModal" class="modal" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <h3>Vehicle Request Details</h3>
            </div>
            <span class="close" id="closeViewModal" aria-label="Close">&times;</span>
        </div>
        <div class="form-section">
            <div class="form-group"><label>ID</label><div class="field-value" id="view_id"></div></div>
            <div class="form-group"><label>Request ID</label><div class="field-value" id="view_request_id"></div></div>
            <div class="form-group"><label>Department</label><div class="field-value" id="view_department"></div></div>
            <div class="form-group"><label>Needed At</label><div class="field-value" id="view_needed_at"></div></div>
            <div class="form-group"><label>Destination</label><div class="field-value" id="view_destination"></div></div>
            <div class="form-group"><label>Pick-up Location</label><div class="field-value" id="view_pickup_location"></div></div>
            <div class="form-group"><label>Items to Transport</label><div class="field-value" id="view_items_transport"></div></div>
            <div class="form-group"><label>Goods Type</label><div class="field-value" id="view_goods_type"></div></div>
            <div class="form-group"><label>Special Instructions</label><div class="field-value" id="view_special_instructions"></div></div>
            <div class="form-group"><label>Estimated Weight</label><div class="field-value" id="view_estimated_weight"></div></div>
            <div class="form-group"><label>Box Count</label><div class="field-value" id="view_box_count"></div></div>
            <div class="form-group"><label>Vehicle Size</label><div class="field-value" id="view_vehicle_size"></div></div>
            <div class="form-group"><label>Special Features</label><div class="field-value" id="view_special_features"></div></div>
            <div class="form-group"><label>Notes</label><div class="field-value" id="view_notes"></div></div>
            <div class="form-group"><label>Submitted At</label><div class="field-value" id="view_created_at"></div></div>
        </div>
    </div>
</div>

<!-- Assign Vehicle Request Modal -->
<div id="assignModal" class="modal" aria-hidden="true">
    <div class="modal-content" style="max-height: 80vh; overflow-y: auto;">
        <div class="modal-header">
            <div class="modal-title">
                <h3>Assign Vehicle & Driver</h3>
            </div>
            <span class="close" id="closeAssignModal" aria-label="Close">&times;</span>
        </div>
        <div class="form-section">
            <div class="form-group"><label>ID</label><div class="field-value" id="assign_id"></div></div>
            <div class="form-group"><label>Request ID</label><div class="field-value" id="assign_request_id"></div></div>
            <div class="form-group"><label>Department</label><div class="field-value" id="assign_department"></div></div>
            <div class="form-group"><label>Needed At</label><div class="field-value" id="assign_needed_at"></div></div>
            <div class="form-group"><label>Destination</label><div class="field-value" id="assign_destination"></div></div>
            <div class="form-group"><label>Pick-up Location</label><div class="field-value" id="assign_pickup_location"></div></div>
            <div class="form-group"><label>Items to Transport</label><div class="field-value" id="assign_items_transport"></div></div>
            <div class="form-group"><label>Goods Type</label><div class="field-value" id="assign_goods_type"></div></div>
            <div class="form-group"><label>Special Instructions</label><div class="field-value" id="assign_special_instructions"></div></div>
            <div class="form-group"><label>Estimated Weight</label><div class="field-value" id="assign_estimated_weight"></div></div>
            <div class="form-group"><label>Box Count</label><div class="field-value" id="assign_box_count"></div></div>
            <div class="form-group"><label>Vehicle Size</label><div class="field-value" id="assign_vehicle_size"></div></div>
            <div class="form-group"><label>Special Features</label><div class="field-value" id="assign_special_features"></div></div>
            <div class="form-group"><label>Notes</label><div class="field-value" id="assign_notes"></div></div>
            <div class="form-group"><label>Submitted At</label><div class="field-value" id="assign_created_at"></div></div>
            
            <hr style="margin: 20px 0;">
            <h4>Assignment</h4>
            
            <div class="form-group">
                <label for="assign_vehicle_select">Assign Vehicle <span style="color: red;">*</span></label>
                <select id="assign_vehicle_select" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" required>
                    <option value="">-- Select a vehicle --</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="assign_driver_select">Assign Driver <span style="color: red;">*</span></label>
                <select id="assign_driver_select" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" required>
                    <option value="">-- Select a driver --</option>
                </select>
            </div>
        </div>
        <div class="modal-actions" style="padding: 20px; text-align: right; border-top: 1px solid #e0e0e0;">
            <button type="button" class="btn btn-secondary" id="closeAssignBtn" style="margin-right: 10px;">Cancel</button>
            <button type="button" class="btn btn-primary" id="submitAssignBtn">Assign</button>
        </div>
    </div>
</div>

<script>
    const vehicleRequests = <?php echo json_encode($vehicle_requests, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
    const availableVehicles = <?php echo json_encode($available_vehicles, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
    const drivers = <?php echo json_encode($drivers, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
    let currentAssignRequestId = null;
    console.log('vehicleRequests loaded', vehicleRequests);

    function openViewModal(id) {
        const request = vehicleRequests.find(r => Number(r.id) === Number(id));
        if (!request) return;

        document.getElementById('view_id').textContent = request.id || '';
        document.getElementById('view_request_id').textContent = request.request_id || '';
        document.getElementById('view_department').textContent = request.department || '';
        document.getElementById('view_needed_at').textContent = request.needed_at ? new Date(request.needed_at).toLocaleString() : '';
        document.getElementById('view_destination').textContent = request.destination || '';
        document.getElementById('view_pickup_location').textContent = request.pickup_location || '';
        document.getElementById('view_items_transport').textContent = request.items_transport || '';
        document.getElementById('view_goods_type').textContent = request.goods_type || '';
        document.getElementById('view_special_instructions').textContent = request.special_instructions || '';
        document.getElementById('view_estimated_weight').textContent = request.estimated_weight || '';
        document.getElementById('view_box_count').textContent = request.box_count || '';
        document.getElementById('view_vehicle_size').textContent = request.vehicle_size || '';
        document.getElementById('view_special_features').textContent = request.special_features || '';
        document.getElementById('view_notes').textContent = request.notes || '';
        document.getElementById('view_created_at').textContent = request.created_at ? new Date(request.created_at).toLocaleString() : '';

        document.getElementById('viewModal').style.display = 'block';
    }

    function assignRequest(id) {
        const request = vehicleRequests.find(r => Number(r.id) === Number(id));
        if (!request) return;

        currentAssignRequestId = id;

        document.getElementById('assign_id').textContent = request.id || '';
        document.getElementById('assign_request_id').textContent = request.request_id || '';
        document.getElementById('assign_department').textContent = request.department || '';
        document.getElementById('assign_needed_at').textContent = request.needed_at ? new Date(request.needed_at).toLocaleString() : '';
        document.getElementById('assign_destination').textContent = request.destination || '';
        document.getElementById('assign_pickup_location').textContent = request.pickup_location || '';
        document.getElementById('assign_items_transport').textContent = request.items_transport || '';
        document.getElementById('assign_goods_type').textContent = request.goods_type || '';
        document.getElementById('assign_special_instructions').textContent = request.special_instructions || '';
        document.getElementById('assign_estimated_weight').textContent = request.estimated_weight || '';
        document.getElementById('assign_box_count').textContent = request.box_count || '';
        document.getElementById('assign_vehicle_size').textContent = request.vehicle_size || '';
        document.getElementById('assign_special_features').textContent = request.special_features || '';
        document.getElementById('assign_notes').textContent = request.notes || '';
        document.getElementById('assign_created_at').textContent = request.created_at ? new Date(request.created_at).toLocaleString() : '';

        // Populate vehicle dropdown
        const vehicleSelect = document.getElementById('assign_vehicle_select');
        vehicleSelect.innerHTML = '<option value="">-- Select a vehicle --</option>';
        availableVehicles.forEach(vehicle => {
            const option = document.createElement('option');
            option.value = vehicle.id;
            option.textContent = vehicle.brand + ' ' + vehicle.model + ' (' + vehicle.plate_number + ')';
            vehicleSelect.appendChild(option);
        });

        // Populate driver dropdown - exclude already assigned drivers
        const driverSelect = document.getElementById('assign_driver_select');
        driverSelect.innerHTML = '<option value="">-- Select a driver --</option>';
        drivers.forEach(driver => {
            const option = document.createElement('option');
            option.value = driver.id;
            option.textContent = driver.full_name;
            driverSelect.appendChild(option);
        });

        document.getElementById('assignModal').style.display = 'block';
    }

    function closeAssignModal() {
        document.getElementById('assignModal').style.display = 'none';
    }

    function closeViewModal() {
        document.getElementById('viewModal').style.display = 'none';
    }

    document.getElementById('closeViewModal').addEventListener('click', closeViewModal);
    document.getElementById('viewModal').addEventListener('click', (event) => {
        if (event.target === document.getElementById('viewModal')) {
            closeViewModal();
        }
    });

    document.getElementById('closeAssignModal').addEventListener('click', closeAssignModal);
    document.getElementById('closeAssignBtn').addEventListener('click', closeAssignModal);
    document.getElementById('assignModal').addEventListener('click', (event) => {
        if (event.target === document.getElementById('assignModal')) {
            closeAssignModal();
        }
    });

    // Submit assignment
    document.getElementById('submitAssignBtn').addEventListener('click', () => {
        const vehicleId = document.getElementById('assign_vehicle_select').value;
        const driverId = document.getElementById('assign_driver_select').value;

        if (!vehicleId || !driverId) {
            alert('Please select both a vehicle and a driver.');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'assign_dispatch');
        formData.append('request_id', currentAssignRequestId);
        formData.append('vehicle_id', vehicleId);
        formData.append('driver_id', driverId);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Assignment saved successfully to dispatch!');
                closeAssignModal();
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the assignment.');
        });
    });

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
</script>
</body>
</html>
