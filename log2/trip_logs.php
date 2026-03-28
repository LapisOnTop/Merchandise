<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Logs</title>
    <link rel="stylesheet" href="../includes/styles.css">
    <style>
        .status-ontime { color: #16a34a; font-weight: 600; background: #dcfce7; padding: 3px 10px; border-radius: 12px; font-size: 12px; }
        .status-late { color: #dc2626; font-weight: 600; background: #fee2e2; padding: 3px 10px; border-radius: 12px; font-size: 12px; }
    </style>
</head>
<body>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../includes/db.php';
$current_page = strtolower(basename(__FILE__));

// Fetch trip logs from vehicle_requests (dispatched records)
$trip_logs_sql = "SELECT add_driver AS driver_name, destination, needed_at, created_at FROM vehicle_requests WHERE dispatched = 1 ORDER BY id DESC";
$trip_logs_result = $conn->query($trip_logs_sql);
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
        <h2>Trip Logs Dashboard</h2>
    </div>

    <div class="card">
        <h3>Trip Logs</h3>
        <table>
            <thead>
                <tr>
                    <th>Driver</th>
                    <th>Destination</th>
                    <th>Needed At</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($trip_logs_result && $trip_logs_result->num_rows > 0) {
                    while ($log = $trip_logs_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($log['driver_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($log['destination']) . "</td>";
                        echo "<td>" . date('M d, Y h:i A', strtotime($log['needed_at'])) . "</td>";
                        
                        // Status: compare created_at (dispatched) vs needed_at (deadline)
                        $dispatched_time = new DateTime($log['created_at']);
                        $needed_time = new DateTime($log['needed_at']);
                        if ($dispatched_time <= $needed_time) {
                            echo "<td><span class='status-ontime'>✓ On-Time</span></td>";
                        } else {
                            echo "<td><span class='status-late'>✗ Late</span></td>";
                        }
                        
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No trip logs found.</td></tr>";
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
