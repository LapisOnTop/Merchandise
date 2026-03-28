<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatch</title>
    <link rel="stylesheet" href="../includes/styles.css">
</head>
<body>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../includes/db.php';
$current_page = strtolower(basename(__FILE__));

// When the Dispatch button is pressed, update vehicle and driver statuses
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'dispatch_trip') {
    $dispatch_id = isset($_POST['dispatch_id']) ? (int) $_POST['dispatch_id'] : 0;

    if ($dispatch_id > 0) {
        // Get the dispatch record details from vehicle_requests
        $stmt = $conn->prepare("SELECT id, add_vehicle, add_driver FROM vehicle_requests WHERE id = ?");
        $stmt->bind_param("i", $dispatch_id);

        if ($stmt->execute()) {
            $result_dispatch = $stmt->get_result();
            if ($result_dispatch && $result_dispatch->num_rows > 0) {
                $dispatch = $result_dispatch->fetch_assoc();

                // Update vehicle status to 'Delivering'
                $vehicle_name = $dispatch['add_vehicle'];
                $conn->query("UPDATE vehicles SET status = 'Delivering' WHERE CONCAT(brand, ' ', model, ' (', plate_number, ')') = '" . $conn->real_escape_string($vehicle_name) . "'");

                // Update driver work_status to 'delivering'
                $driver_name = $dispatch['add_driver'];
                $conn->query("UPDATE employees SET work_status = 'delivering' WHERE full_name = '" . $conn->real_escape_string($driver_name) . "' AND position = 'Driver'");

                // Mark the request as dispatched
                $conn->query("UPDATE vehicle_requests SET dispatched = 1 WHERE id = " . $dispatch_id);
            }
        }

        $stmt->close();
    }

    // Simple redirect to avoid form resubmission and refresh the list
    header("Location: dispatch.php");
    exit;
}
// Fetch dispatch data from vehicle_requests — those assigned but not yet dispatched
// Fetch dispatch data from vehicle_requests — those assigned but not yet dispatched
$sql = "SELECT id, request_id, vehicle_size, pickup_location, destination, add_vehicle, add_driver FROM vehicle_requests WHERE (add_vehicle IS NOT NULL AND add_vehicle != '') AND (add_driver IS NOT NULL AND add_driver != '') AND dispatched = 0";
$result = $conn->query($sql);
?>


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
        <h2>Dispatch Dashboard</h2>
    </div>

    <div class="card">
        <h3>Dispatch Management</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Request ID</th>
                    <th>Vehicle Size</th>
                    <th>Pickup Location</th>
                    <th>Destination</th>
                    <th>Assigned Vehicle</th>
                    <th>Assigned Driver</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['request_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['vehicle_size']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['pickup_location']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['destination']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['add_vehicle']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['add_driver']) . "</td>";
                        echo "<td>";
                        echo "<form method=\"POST\" style=\"display:inline;\">";
                        echo "<input type=\"hidden\" name=\"action\" value=\"dispatch_trip\">";
                        echo "<input type=\"hidden\" name=\"dispatch_id\" value=\"" . htmlspecialchars($row['id']) . "\">";
                        echo "<button type=\"submit\" class=\"action-button\">Dispatch</button>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No dispatch records found.</td></tr>";
                }
                ?>
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
