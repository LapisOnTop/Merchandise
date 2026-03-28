<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Human Resources 2</title>
    <link rel="stylesheet" href="../includes/styles.css">
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="brand">
        <h1>Human Resource 2</h1>
        <button id="sidebarToggle" aria-label="Toggle navigation">☰</button>
    </div>
    <ul class="nav-list">
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">📂</span>
                <span class="nav-section-label">Dashboard</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="#"><span class="nav-text">Learning Management</span></a></li>
                <li><a href="#"><span class="nav-text">Training Management</span></a></li>
                <li><a href="#"><span class="nav-text">Succession Planning</span></a></li>
            </ul>
        </li>
       
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">⚙️</span>
                <span class="nav-section-label">Settings</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="#"><span class="nav-text">Change Password</span></a></li>
                <li><a href="#"><span class="nav-text">Log out</span></a></li>
            </ul>
        </li>
    </ul>
</div>

<div class="main" id="mainContent">
    <div class="page-header">
        <h2>HR Dashboard</h2>
    </div>

    <div class="dashboard-content active" id="dashboardContent">
        <div class="card">
            <h3>Welcome</h3>
            <p>This is the HR2 workspace. Use the sidebar navigation to get started.</p>
        </div>
    </div>

    <div class="learning-management" id="learningManagementContent">
        <div class="employee-table-section">
            <h3>Employee List</h3>
            <div class="table-container">
                <table class="employee-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Job ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Email</th>
                            <th>Phone</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include '../includes/db.php';
                        
                        $sql = "SELECT id, job_id, full_name, department, email, phone FROM employees ORDER BY id ASC";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td><strong>" . $row["id"] . "</strong></td>";
                                echo "<td>" . $row["job_id"] . "</td>";
                                echo "<td><strong>" . htmlspecialchars($row["full_name"]) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($row["department"]) . "</td>";
                                echo "<td><a href='mailto:" . htmlspecialchars($row["email"]) . "'>" . htmlspecialchars($row["email"]) . "</a></td>";
                                echo "<td>" . htmlspecialchars($row["phone"]) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align: center; padding: 40px; color: #6c757d;'>No employees found</td></tr>";
                        }
                        
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="training-management" id="trainingManagementContent">
        <div class="card">
            <h3>Training Management</h3>
            <p>Training management features will be implemented here.</p>
        </div>
    </div>

    <div class="succession-planning" id="successionPlanningContent">
        <div class="card">
            <h3>Succession Planning</h3>
            <p>Succession planning features will be implemented here.</p>
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

    // Get content elements
    const dashboardContent = document.getElementById('dashboardContent');
    const learningManagementContent = document.getElementById('learningManagementContent');
    const trainingManagementContent = document.getElementById('trainingManagementContent');
    const successionPlanningContent = document.getElementById('successionPlanningContent');
    const navLinks = document.querySelectorAll('.nav-sublist a');

    // Function to show content based on navigation
    const showContent = (contentType, clickedLink = null) => {
        // Hide all content sections
        dashboardContent.classList.remove('active');
        learningManagementContent.classList.remove('active');
        trainingManagementContent.classList.remove('active');
        successionPlanningContent.classList.remove('active');

        // Remove active class from all nav links
        navLinks.forEach(link => link.classList.remove('active'));

        // Add active class to clicked link
        if (clickedLink) {
            clickedLink.classList.add('active');
        }

        // Update page header
        const pageHeader = document.querySelector('.page-header h2');
        
        // Show selected content
        switch(contentType) {
            case 'dashboard':
                dashboardContent.classList.add('active');
                pageHeader.textContent = 'HR Dashboard';
                break;
            case 'learning':
                learningManagementContent.classList.add('active');
                pageHeader.textContent = 'Learning Management';
                break;
            case 'training':
                trainingManagementContent.classList.add('active');
                pageHeader.textContent = 'Training Management';
                break;
            case 'succession':
                successionPlanningContent.classList.add('active');
                pageHeader.textContent = 'Succession Planning';
                break;
            default:
                dashboardContent.classList.add('active');
                pageHeader.textContent = 'HR Dashboard';
        }
    };

    // Add click event listeners to navigation links
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const navText = link.querySelector('.nav-text').textContent.toLowerCase();
            
            if (navText.includes('learning')) {
                showContent('learning', link);
            } else if (navText.includes('training')) {
                showContent('training', link);
            } else if (navText.includes('succession')) {
                showContent('succession', link);
            } else {
                showContent('dashboard', link);
            }
        });
    });

    navSections.forEach((section) => {
        const title = section.querySelector('.nav-section-title');
        title.addEventListener('click', () => {
            const expanded = title.getAttribute('data-expanded') === 'true';
            setSectionOpen(section, !expanded);
        });

        setSectionOpen(section, false);
    });

    // Initialize with dashboard active
    showContent('dashboard');
</script>
</body>
</html>
