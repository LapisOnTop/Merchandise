<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Management</title>
    <link rel="stylesheet" href="../includes/styles.css">
</head>
<body>

<?php $current_page = strtolower(basename(__FILE__)); ?>

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
                <li class="<?php echo ($current_page == 'reservation.php') ? 'active' : ''; ?>"><a href="reservation.php"><span class="nav-text">Reservation</span></a></li>
                <li class="<?php echo ($current_page == 'dispatch.php') ? 'active' : ''; ?>"><a href="dispatch.php"><span class="nav-text">Dispatch</span></a></li>
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
                <li class="<?php echo ($current_page == 'driver_performance.php') ? 'active' : ''; ?>"><a href="driver_performance.php"><span class="nav-text">Driver Performance</span></a></li>
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
        <h2>Driver Management Dashboard</h2>
    </div>

    <div class="card">
        <h3>Driver List</h3>
        <?php
        include '../includes/db.php';

        // Ensure work_status exists (for availability tracking)
        $conn->query("ALTER TABLE employees ADD COLUMN IF NOT EXISTS work_status ENUM('available','unavailable','assigned','delivering') NOT NULL DEFAULT 'available'");

        $sql = "SELECT id, job_id, full_name, email, phone, home_address, date_of_birth, gender, civil_status, nationality, application_date, sss_number, philhealth_number, pagibig_number, position, work_status FROM employees WHERE position = 'Driver'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Job ID</th><th>Full Name</th><th>Contact No.</th><th>Status</th><th>Actions</th></tr>";
            while($row = $result->fetch_assoc())  {
                $employeeJson = htmlspecialchars(json_encode($row, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP));
                echo "<tr data-employee='" . $employeeJson . "'><td>" . $row["id"] . "</td><td>" . $row["job_id"] . "</td><td>" . $row["full_name"] . "</td><td>" . $row["phone"] . "</td><td>" . ucfirst($row["work_status"]) . "</td><td><button class='action-btn action-btn-view view-btn' type='button' title='View'>";
                echo "<svg width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>";
                echo "<path d='M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z'></path>";
                echo "<circle cx='12' cy='12' r='3'></circle>";
                echo "</svg>";
                echo "</button></td></tr>";
            }
            echo "</table>";
        } else {
            echo "No drivers found.";
        }

        $conn->close();
        ?>
    </div>
</div>

<!-- View Employee Modal -->
<div id="viewModal" class="modal" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <h3>View Employee Details</h3>
            </div>
            <span class="close" id="closeViewModal" aria-label="Close">×</span>
        </div>
        <div class="form-section">
            <div class="form-group"><label>ID</label><div class="field-value" id="viewId"></div></div>
            <div class="form-group"><label>Job ID</label><div class="field-value" id="viewJobId"></div></div>
            <div class="form-group"><label>Full Name</label><div class="field-value" id="viewFullName"></div></div>
            <div class="form-group"><label>Email</label><div class="field-value" id="viewEmail"></div></div>
            <div class="form-group"><label>Phone</label><div class="field-value" id="viewPhone"></div></div>
            <div class="form-group"><label>Home Address</label><div class="field-value" id="viewHomeAddress"></div></div>
            <div class="form-group"><label>Date of Birth</label><div class="field-value" id="viewDateOfBirth"></div></div>
            <div class="form-group"><label>Gender</label><div class="field-value" id="viewGender"></div></div>
            <div class="form-group"><label>Civil Status</label><div class="field-value" id="viewCivilStatus"></div></div>
            <div class="form-group"><label>Nationality</label><div class="field-value" id="viewNationality"></div></div>
            <div class="form-group"><label>Status</label><div class="field-value" id="viewWorkStatus"></div></div>
            <div class="form-group"><label>Application Date</label><div class="field-value" id="viewApplicationDate"></div></div>
            <div class="form-group"><label>SSS Number</label><div class="field-value" id="viewSSSNumber"></div></div>
            <div class="form-group"><label>PhilHealth Number</label><div class="field-value" id="viewPhilHealthNumber"></div></div>
            <div class="form-group"><label>Pag-Ibig Number</label><div class="field-value" id="viewPagIbigNumber"></div></div>
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

    // View Modal Functions
    const viewModal = document.getElementById('viewModal');
    const closeViewModal = document.getElementById('closeViewModal');
    const closeViewBtn = document.getElementById('closeViewBtn');

    const viewFields = {
        id: document.getElementById('viewId'),
        jobId: document.getElementById('viewJobId'),
        fullName: document.getElementById('viewFullName'),
        email: document.getElementById('viewEmail'),
        phone: document.getElementById('viewPhone'),
        homeAddress: document.getElementById('viewHomeAddress'),
        dateOfBirth: document.getElementById('viewDateOfBirth'),
        gender: document.getElementById('viewGender'),
        civilStatus: document.getElementById('viewCivilStatus'),
        nationality: document.getElementById('viewNationality'),
        workStatus: document.getElementById('viewWorkStatus'),
        applicationDate: document.getElementById('viewApplicationDate'),
        sssNumber: document.getElementById('viewSSSNumber'),
        philHealthNumber: document.getElementById('viewPhilHealthNumber'),
        pagIbigNumber: document.getElementById('viewPagIbigNumber'),
    };

    const openModal = (modal) => {
        modal.style.display = 'block';
        modal.setAttribute('aria-hidden', 'false');
    };

    const closeModal = (modal) => {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    };

    function openViewModalByRow(row) {
        if (!row || !row.dataset.employee) return;
        let data;
        try {
            data = JSON.parse(row.dataset.employee);
        } catch {
            return;
        }
        viewFields.id.textContent = data.id || '';
        viewFields.jobId.textContent = data.job_id || '';
        viewFields.fullName.textContent = data.full_name || '';
        viewFields.email.textContent = data.email || '';
        viewFields.phone.textContent = data.phone || '';
        viewFields.homeAddress.textContent = data.home_address || '';
        viewFields.dateOfBirth.textContent = data.date_of_birth || '';
        viewFields.gender.textContent = data.gender || '';
        viewFields.civilStatus.textContent = data.civil_status || '';
        viewFields.nationality.textContent = data.nationality || '';
        viewFields.workStatus.textContent = data.work_status ? data.work_status.charAt(0).toUpperCase() + data.work_status.slice(1) : '';
        viewFields.applicationDate.textContent = data.application_date || '';
        viewFields.sssNumber.textContent = data.sss_number || '';
        viewFields.philHealthNumber.textContent = data.philhealth_number || '';
        viewFields.pagIbigNumber.textContent = data.pagibig_number || '';
        openModal(viewModal);
    }

    // Attach click handlers to view buttons
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr');
            openViewModalByRow(row);
        });
    });

    closeViewModal.addEventListener('click', () => closeModal(viewModal));
    closeViewBtn.addEventListener('click', () => closeModal(viewModal));

    window.addEventListener('click', (event) => {
        if (event.target === viewModal) {
            closeModal(viewModal);
        }
    });
</script>
</body>
</html>