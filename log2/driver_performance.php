<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../includes/db.php';
$current_page = strtolower(basename(__FILE__));

// Query driver performance from vehicle_requests (dispatched records)
// On-Time = created_at <= needed_at, Late = created_at > needed_at
$sql = "SELECT 
            add_driver AS driver_name,
            COUNT(*) as total_trips,
            SUM(CASE WHEN created_at <= needed_at THEN 1 ELSE 0 END) as on_time_trips,
            SUM(CASE WHEN created_at > needed_at THEN 1 ELSE 0 END) as late_trips
        FROM vehicle_requests 
        WHERE dispatched = 1 AND add_driver IS NOT NULL AND add_driver != ''
        GROUP BY add_driver 
        ORDER BY add_driver ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Performance</title>
    <link rel="stylesheet" href="../includes/styles.css">
    <style>
        .performance-table th, .performance-table td { padding: 12px 10px; border-bottom: 1px solid #eee; text-align: center; }
        .performance-table th:first-child, .performance-table td:first-child { text-align: left; }
        .performance-table { width: 100%; border-collapse: collapse; }
        .stars { color: #fbbf24; font-size: 1.4em; letter-spacing: 2px; }
        .stars-dim { color: #d1d5db; }
        .stat-badge { display: inline-block; padding: 4px 14px; border-radius: 20px; font-weight: 600; font-size: 13px; }
        .stat-ontime { background: #dcfce7; color: #16a34a; }
        .stat-late { background: #fee2e2; color: #dc2626; }
        .stat-total { background: #dbeafe; color: #2563eb; }
        .percentage-bar { width: 100%; height: 8px; background: #f3f4f6; border-radius: 4px; overflow: hidden; margin-top: 4px; }
        .percentage-fill { height: 100%; border-radius: 4px; transition: width 0.4s ease; }
        .percentage-fill.good { background: linear-gradient(90deg, #22c55e, #16a34a); }
        .percentage-fill.average { background: linear-gradient(90deg, #f59e0b, #d97706); }
        .percentage-fill.poor { background: linear-gradient(90deg, #ef4444, #dc2626); }
        .percentage-text { font-size: 12px; font-weight: 600; margin-top: 2px; }
    </style>
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
        <h2>Driver Performance Dashboard</h2>
    </div>

    <div class="card" style="overflow-x: auto;">
        <h3>Driver Performance (Auto-Calculated)</h3>
        <table class="performance-table inventory-table">
            <thead>
                <tr>
                    <th>Driver Name</th>
                    <th>Total Trips</th>
                    <th>On-Time</th>
                    <th>Late</th>
                    <th>Rating</th>
                    <th>Stars</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $total = (int)$row['total_trips'];
                        $on_time = (int)$row['on_time_trips'];
                        $late = (int)$row['late_trips'];
                        
                        // Rating = (On-Time Trips / Total Trips) × 100
                        $rating = $total > 0 ? round(($on_time / $total) * 100) : 0;
                        
                        // Auto-calculate star rating from on-time %
                        if ($rating >= 90) { $star_count = 5; }
                        elseif ($rating >= 75) { $star_count = 4; }
                        elseif ($rating >= 60) { $star_count = 3; }
                        elseif ($rating >= 40) { $star_count = 2; }
                        else { $star_count = 1; }
                        
                        if ($total == 0) { $star_count = 0; }
                        
                        // Build star display
                        $stars_html = '';
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $star_count) {
                                $stars_html .= '<span class="stars">★</span>';
                            } else {
                                $stars_html .= '<span class="stars stars-dim">★</span>';
                            }
                        }
                        
                        // Determine bar color class
                        if ($rating >= 75) { $bar_class = 'good'; }
                        elseif ($rating >= 50) { $bar_class = 'average'; }
                        else { $bar_class = 'poor'; }
                        
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['driver_name']) . "</td>";
                        echo "<td><span class='stat-badge stat-total'>$total</span></td>";
                        echo "<td><span class='stat-badge stat-ontime'>$on_time</span></td>";
                        echo "<td><span class='stat-badge stat-late'>$late</span></td>";
                        echo "<td>";
                        echo "<span class='percentage-text'>{$rating}%</span>";
                        echo "<div class='percentage-bar'><div class='percentage-fill $bar_class' style='width:{$rating}%'></div></div>";
                        echo "</td>";
                        echo "<td>$stars_html</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center; color:#999;'>No driver performance data yet. Dispatch trips to see data here.</td></tr>";
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
