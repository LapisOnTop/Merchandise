<?php
session_start();
include '../includes/db.php';
$current_page = strtolower(basename(__FILE__));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistic 2 - Admin</title>
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
                <li><a href="vehicle_management.php"><span class="nav-text">Vehicle Management</span></a></li>
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
                <li><a href="reservation.php"><span class="nav-text">Reservation</span></a></li>
                <li><a href="dispatch.php"><span class="nav-text">Dispatch</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">📊</span>
                <span class="nav-section-label">Driver & Trip Performance Monitoring</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="trip_logs.php"><span class="nav-text">Trip Logs</span></a></li>
                <li><a href="driver_performance.php"><span class="nav-text">Driver Performance</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">🧩</span>
                <span class="nav-section-label">Transport Cost Analysis</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="cost_reports.php"><span class="nav-text">Cost Reports</span></a></li>
                <li><a href="route_optimization.php"><span class="nav-text">Route Optimization</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">⚙️</span>
                <span class="nav-section-label">Settings</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="change_password.php"><span class="nav-text">Change Password</span></a></li>
                <li><a href="logout.php"><span class="nav-text">Log out</span></a></li>
            </ul>
        </li>
    </ul>
</div>

<div class="main" id="mainContent">
    <div class="page-header">
        <h2>Logistic 2 Admin Dashboard</h2>
    </div>

    <div class="card">
        <h3>Welcome, Admin</h3>
        <p>This is the Logistic 2 Admin workspace. Use the sidebar navigation to manage fleet, reservations, dispatch, and reports.</p>
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
