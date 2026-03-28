<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Human Resources 1</title>
    <link rel="stylesheet" href="../includes/styles.css">

    <!-- EmailJS SDK -->
    <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>

    <script>
        // EmailJS Configuration
        // Replace with your EmailJS account settings
        const EMAILJS_USER_ID = 'iXTAk-DTE-SuoASNH';  
        const EMAILJS_SERVICE_ID = 'service_o9bwb1q';  
        const EMAILJS_TEMPLATE_ID = 'template_taiygge';  

        // Initialize EmailJS
        window.addEventListener("load", function () {
            if (window.emailjs) {
                emailjs.init({
                    publicKey: EMAILJS_USER_ID
                });
                console.log("EmailJS initialized successfully.");
            } else {
                console.error("EmailJS SDK failed to load. Check your internet connection.");
            }
        });
    </script>
</head>

<body>

<?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
<div class="success-message">
  <div class="message-content">
    <span class="message-icon">✅</span>
    <div>
      <h3>Onboarding Information Updated!</h3>
      <p>The applicant's onboarding details have been successfully updated.</p>
    </div>
    <button class="close-message" onclick="this.parentElement.parentElement.style.display='none'">&times;</button>
  </div>
</div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] == 'db_update_failed'): ?>
<div class="error-message">
  <div class="message-content">
    <span class="message-icon">❌</span>
    <div>
      <h3>Database Update Failed</h3>
      <p>Unable to automatically add the required database columns. Please contact your system administrator or run the SQL commands manually.</p>
    </div>
    <button class="close-message" onclick="this.parentElement.parentElement.style.display='none'">&times;</button>
  </div>
</div>
<?php endif; ?>

<?php
// Function to get count of pending applications
function getPendingApplicationsCount() {
    include '../includes/db.php';
    $sql = "SELECT COUNT(*) as count FROM job_applications WHERE status = 'pending'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    return 0;
}

$pendingCount = getPendingApplicationsCount();
?>

<div class="sidebar" id="sidebar">
    <div class="brand">
        <h1>Human Resource 1</h1>
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
                <li><a href="#"><span class="nav-text">Perfomance Management</span></a></li>
                <li><a href="#"><span class="nav-text">Social Recognition</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">👥</span>
                <span class="nav-section-label">Job Hiring</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="#"><span class="nav-text">Recruitment Management</span></a></li>
                <li><a href="#" class="nav-item-with-badge"><span class="notification-badge" id="applicantBadge" <?php echo $pendingCount > 0 ? 'style="display: inline-block;"' : 'style="display: none;"'; ?>><?php echo $pendingCount; ?></span><span class="nav-text">Applicant Management</span></a></li>
                <li><a href="#"><span class="nav-text">New Hire-Onboarding</span></a></li>
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
                <li><a href="#"><span class="nav-text">Logout</span></a></li>
            </ul>
        </li>
    </ul>
</div>

    <div class="main" id="mainContent">
    <div class="page-header">
        <h2>Dashboard</h2>
    </div>

    <div class="dashboard-content active" id="dashboardContent">
        
    </div>

    <div class="recruitment" id="recruitmentContent">

        <div class="recruitment-content">
            <div class="job-form-section">
                <h3>Post New Job</h3>
                <form action="post_job.php" method="post">
                    <div class="form-group">
                        <label for="department">Department:</label>
                        <select name="department" id="department" required>
                            <option value="">Select Department</option>
                            <option value="administrative">Administrative</option>
                            <option value="core1">Core1</option>
                            <option value="core2">Core2</option>
                            <option value="finance">Finance</option>
                            <option value="hr1">HR1</option>
                            <option value="hr2">HR2</option>
                            <option value="hr3">HR3</option>
                            <option value="hr4">HR4</option>
                            <option value="log1">Log1</option>
                            <option value="log2">Log2</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="branch">Branch:</label>
                        <div class="branch-input-group">
                            <select name="branch_select" id="branch_select">
                                <option value="">Select Branch</option>
                                <option value="susano">Susano</option>
                                <option value="jordan">Jordan</option>
                                <option value="nitang">Nitang</option>
                                <option value="other">Other</option>
                            </select>
                            <input type="text" name="branch" id="branch_text" placeholder="Enter branch name" style="display: none;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="position">Position:</label>
                        <input type="text" name="position" id="position" required>
                    </div>

                    <div class="form-group">
                        <label for="num_applicants">Number of Wanted Applicants:</label>
                        <input type="number" name="num_applicants" id="num_applicants" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="requirements">Requirements:</label>
                        <textarea name="requirements" id="requirements" rows="4" required></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Post Job</button>
                </form>
            </div>

            <div class="jobs-table-section">
                <h3>Posted Jobs</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Department</th>
                                <th>Branch</th>
                                <th>Position</th>
                                <th>Number of Applicants</th>
                                <th>Requirements</th>
                                <th>Posted Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include '../includes/db.php';

                            $sql = "SELECT * FROM job_postings ORDER BY created_at DESC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row["id"] . "</td>";
                                    echo "<td>" . htmlspecialchars($row["department"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["branch"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["position"]) . "</td>";
                                    echo "<td>" . $row["num_applicants"] . "</td>";
                                    echo "<td>" . nl2br(htmlspecialchars($row["requirements"])) . "</td>";
                                    echo "<td>" . $row["created_at"] . "</td>";
                                    echo "<td class='actions-cell'>";
                                    echo "<button class='edit-btn' data-id='" . $row["id"] . "' data-department='" . htmlspecialchars($row["department"]) . "' data-branch='" . htmlspecialchars($row["branch"]) . "' data-position='" . htmlspecialchars($row["position"]) . "' data-num-applicants='" . $row["num_applicants"] . "' data-requirements='" . htmlspecialchars($row["requirements"]) . "'>Edit</button>";
                                    echo "<button class='delete-btn' data-id='" . $row["id"] . "'>Delete</button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8'>No jobs posted yet.</td></tr>";
                            }

                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Job Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <span class="modal-icon">✏️</span>
                    <h3>Edit Job Posting</h3>
                </div>
                <span class="close">&times;</span>
            </div>
            <form id="editForm" action="edit_job.php" method="post">
                <input type="hidden" name="job_id" id="edit_job_id">
                
                <div class="form-section">
                    <div class="form-group">
                        <label for="edit_department">
                            <span class="field-icon">🏢</span>
                            Department:
                        </label>
                        <select name="department" id="edit_department" required>
                            <option value="">Select Department</option>
                            <option value="administrative">Administrative</option>
                            <option value="core1">Core1</option>
                            <option value="core2">Core2</option>
                            <option value="finance">Finance</option>
                            <option value="hr1">HR1</option>
                            <option value="hr2">HR2</option>
                            <option value="hr3">HR3</option>
                            <option value="hr4">HR4</option>
                            <option value="log1">Log1</option>
                            <option value="log2">Log2</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_branch">
                            <span class="field-icon">📍</span>
                            Branch:
                        </label>
                        <div class="branch-input-group">
                            <select name="branch_select" id="edit_branch_select">
                                <option value="">Select Branch</option>
                                <option value="susano">Susano</option>
                                <option value="jordan">Jordan</option>
                                <option value="nitang">Nitang</option>
                                <option value="other">Other</option>
                            </select>
                            <input type="text" name="branch" id="edit_branch_text" placeholder="Enter branch name" style="display: none;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_position">
                            <span class="field-icon">👔</span>
                            Position:
                        </label>
                        <input type="text" name="position" id="edit_position" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_num_applicants">
                            <span class="field-icon">👥</span>
                            Number of Wanted Applicants:
                        </label>
                        <input type="number" name="num_applicants" id="edit_num_applicants" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_requirements">
                            <span class="field-icon">📋</span>
                            Requirements:
                        </label>
                        <textarea name="requirements" id="edit_requirements" rows="4" required placeholder="Enter job requirements..."></textarea>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="cancel-btn" onclick="closeModal()">
                        <span class="btn-icon">❌</span>
                        Cancel
                    </button>
                    <button type="submit" class="submit-btn">
                        <span class="btn-icon">💾</span>
                        Update Job
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Contact Modal (Interview Scheduling) -->
    <div id="contactModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <span class="modal-icon">📨</span>
                    <h3>Schedule Interview</h3>
                </div>
                <span class="close" id="contactClose">&times;</span>
            </div>
            <form id="contactForm">
                <div class="form-section">
                    <div class="form-group">
                        <label>Applicant</label>
                        <div id="contactApplicantName" class="field-value">Applicant</div>
                    </div>

                    <div class="form-group">
                        <label>Position</label>
                        <div id="contactPositionTitle" class="field-value">Position</div>
                    </div>

                    <div class="form-group">
                        <label for="contactDate">Interview Date</label>
                        <input type="date" id="contactDate" required>
                    </div>

                    <div class="form-group">
                        <label for="contactTime">Interview Time</label>
                        <input type="time" id="contactTime" required>
                    </div>

                    <div class="form-group">
                        <label for="contactLocation">Location / Platform</label>
                        <input type="text" id="contactLocation" placeholder="e.g., Zoom / 123 Main St" required>
                    </div>

                    <div class="form-group">
                        <label for="contactInterviewer">Interviewer (Name/Dept)</label>
                        <input type="text" id="contactInterviewer" placeholder="e.g., Jane Doe / HR" required>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" id="contactCancelBtn" class="cancel-btn">
                        <span class="btn-icon">❌</span>
                        Cancel
                    </button>
                    <button type="submit" class="submit-btn">
                        <span class="btn-icon">✉️</span>
                        Send Invitation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content confirmation-modal">
            <div class="modal-body">
                <p id="confirmationMessage">Are you sure?</p>
            </div>
            <div class="modal-actions">
                <button id="confirmNo" class="btn btn-secondary">Cancel</button>
                <button id="confirmYes" class="btn btn-primary">Confirm</button>
            </div>
        </div>
    </div>

    <div class="applicant-management" id="applicantContent">

        <div class="applicant-management-content">
            <div class="applications-table-section">
                <h3>All Job Applications</h3>

                <div class="table-controls">
                    <div class="filter-group">
                        <label for="statusFilter">Filter by status:</label>
                        <select id="statusFilter">
                            <option value="all">All</option>
                            <option value="pending">Pending</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="contacted">Contacted</option>
                            <option value="accepted">Accepted</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <label for="searchInput">Search:</label>
                        <input type="text" id="searchInput" placeholder="Search by name, email, job title...">
                    </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Job</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Gender</th>
                                <th>Status</th>
                                <th>Nationality</th>
                                <th>Resume</th>
                                <th>ID</th>
                                <th>Applied</th>
                                <th>App Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include '../includes/db.php';

                            $sql = "SELECT ja.*, jp.position as job_title, jp.department, jp.branch 
                                    FROM job_applications ja 
                                    LEFT JOIN job_postings jp ON ja.job_id = jp.id 
                                    ORDER BY ja.application_date DESC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row["id"] . "</td>";
                                    echo "<td>" . htmlspecialchars($row["job_title"] ?: 'N/A') . "</td>";
                                    echo "<td>" . htmlspecialchars($row["full_name"]) . "</td>";
                                    echo "<td><span class='compact-email'>" . htmlspecialchars($row["email"]) . "</span></td>";
                                    echo "<td>" . htmlspecialchars($row["phone"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["gender"]) . "</td>";
                                    
                                    // Application Status (moved up)
                                    $statusClass = '';
                                    switch(strtolower($row["status"])) {
                                        case 'pending':
                                            $statusClass = 'status-pending';
                                            break;
                                        case 'reviewed':
                                            $statusClass = 'status-reviewed';
                                            break;
                                        case 'contacted':
                                            $statusClass = 'status-contacted';
                                            break;
                                        case 'accepted':
                                            $statusClass = 'status-accepted';
                                            break;
                                        case 'rejected':
                                            $statusClass = 'status-rejected';
                                            break;
                                    }
                                    echo "<td><span class='status-badge " . $statusClass . "'>" . htmlspecialchars($row["status"]) . "</span></td>";
                                    
                                    echo "<td>" . htmlspecialchars($row["nationality"]) . "</td>";
                                    
                                    // Resume link - compact
                                    if (!empty($row["resume_path"])) {
                                        echo "<td><a href='../" . htmlspecialchars($row["resume_path"]) . "' target='_blank' class='file-link'>📄</a></td>";
                                    } else {
                                        echo "<td>—</td>";
                                    }
                                    
                                    // ID photo link - compact
                                    if (!empty($row["valid_id_path"])) {
                                        echo "<td><a href='../" . htmlspecialchars($row["valid_id_path"]) . "' target='_blank' class='file-link'>🖼️</a></td>";
                                    } else {
                                        echo "<td>—</td>";
                                    }
                                    
                                    echo "<td><span class='compact-date'>" . date('M d, Y', strtotime($row["application_date"])) . "</span></td>";
                                    
                                    // Civil Status (moved to end)
                                    echo "<td><span class='compact-status'>" . htmlspecialchars($row["civil_status"]) . "</span></td>";
                                    
                                    // Actions
                                    echo "<td class='actions-cell'>";
                                    echo "<select class='status-select' data-id='" . $row["id"] . "' data-current='" . $row["status"] . "'>";
                                    echo "<option value='pending'" . ($row["status"] == 'pending' ? ' selected' : '') . ">Pending</option>";
                                    echo "<option value='reviewed'" . ($row["status"] == 'reviewed' ? ' selected' : '') . ">Reviewed</option>";
                                    echo "<option value='contacted'" . ($row["status"] == 'contacted' ? ' selected' : '') . ">Contacted</option>";
                                    echo "<option value='accepted'" . ($row["status"] == 'accepted' ? ' selected' : '') . ">Accepted</option>";
                                    echo "<option value='rejected'" . ($row["status"] == 'rejected' ? ' selected' : '') . ">Rejected</option>";
                                    echo "</select>";

                                    // Show 'Contact' only when reviewed
                                    if (strtolower($row["status"]) === 'reviewed') {
                                        $email = htmlspecialchars($row["email"]);
                                        $applicantName = htmlspecialchars($row["full_name"]);
                                        $positionTitle = htmlspecialchars($row["job_title"] ?: 'the position');
                                        echo "<button class='contact-btn' data-id='" . $row["id"] . "' data-email='" . $email . "' data-name='" . $applicantName . "' data-position='" . $positionTitle . "'>📧 Contact</button>";
                                    }

                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='13'>No applications received yet.</td></tr>";
                            }

                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="onboarding" id="onboardingContent">

        <div class="onboarding-content">
            <div class="applications-table-section">
                

                <div class="table-controls">
                    <div class="filter-group">
                        <label for="onboardingStatusFilter">Filter by status:</label>
                        <select id="onboardingStatusFilter">
                            <option value="all">All</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <label for="onboardingSearchInput">Search:</label>
                        <input type="text" id="onboardingSearchInput" placeholder="Search by name, position, email...">
                    </div>
                </div>

                <div class="table-container">
                    <table id="onboardingTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Position</th>
                                <th>Department</th>
                                <th>SSS Number</th>
                                <th>PhilHealth</th>
                                <th>Pag-IBIG</th>
                                <th>BIR Tax Form</th>
                                <th>NBI Clearance</th>
                                <th>PSA Birth Certificate</th>
                                <th>Onboarding Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include '../includes/db.php';

                            $sql = "SELECT ja.*, jp.position as job_title, jp.department, jp.branch 
                                    FROM job_applications ja 
                                    LEFT JOIN job_postings jp ON ja.job_id = jp.id 
                                    WHERE ja.status = 'accepted' 
                                    ORDER BY ja.application_date DESC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row["id"] . "</td>";
                                    echo "<td>" . htmlspecialchars($row["full_name"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["job_title"] ?: 'N/A') . "</td>";
                                    echo "<td>" . htmlspecialchars($row["department"] ?: 'N/A') . "</td>";
                                    echo "<td>" . htmlspecialchars(isset($row["sss_number"]) ? $row["sss_number"] : 'Not Set') . "</td>";
                                    echo "<td>" . htmlspecialchars(isset($row["philhealth_number"]) ? $row["philhealth_number"] : 'Not Set') . "</td>";
                                    echo "<td>" . htmlspecialchars(isset($row["pagibig_number"]) ? $row["pagibig_number"] : 'Not Set') . "</td>";
                                    // BIR Tax Form (stored as file path)
                                    if (!empty($row["bir_tax_form"]) && (strpos($row["bir_tax_form"], 'uploads/') === 0 || preg_match('/\\.(pdf|jpe?g|png|gif)$/i', $row["bir_tax_form"]))) {
                                        echo "<td><a href='../" . htmlspecialchars($row["bir_tax_form"]) . "' target='_blank' class='file-link'>📄 View</a></td>";
                                    } else {
                                        echo "<td>" . htmlspecialchars(isset($row["bir_tax_form"]) ? $row["bir_tax_form"] : 'Not Set') . "</td>";
                                    }
                                    
                                    // NBI Clearance
                                    if (!empty($row["nbi_clearance_path"])) {
                                        echo "<td><a href='../" . htmlspecialchars($row["nbi_clearance_path"]) . "' target='_blank' class='file-link'>📄 View</a></td>";
                                    } else {
                                        echo "<td>Not Uploaded</td>";
                                    }
                                    
                                    // PSA Birth Certificate
                                    if (!empty($row["psa_birth_certificate_path"])) {
                                        echo "<td><a href='../" . htmlspecialchars($row["psa_birth_certificate_path"]) . "' target='_blank' class='file-link'>📄 View</a></td>";
                                    } else {
                                        echo "<td>Not Uploaded</td>";
                                    }
                                    
                                    // Onboarding Status
                                    $statusClass = '';
                                    $onboardingStatus = isset($row["onboarding_status"]) ? $row["onboarding_status"] : 'pending';
                                    switch(strtolower($onboardingStatus)) {
                                        case 'pending':
                                            $statusClass = 'status-pending';
                                            break;
                                        case 'in_progress':
                                            $statusClass = 'status-contacted';
                                            break;
                                        case 'completed':
                                            $statusClass = 'status-accepted';
                                            break;
                                    }
                                    $statusText = ucfirst(str_replace('_', ' ', $onboardingStatus));
                                    echo "<td><span class='status-badge " . $statusClass . "'>" . $statusText . "</span></td>";
                                    
                                    // Actions
                                    echo "<td class='actions-cell'>";
                                    echo "<button class='edit-onboarding-btn' data-id='" . $row["id"] . "' data-name='" . htmlspecialchars($row["full_name"]) . "' data-sss='" . htmlspecialchars(isset($row["sss_number"]) ? $row["sss_number"] : '') . "' data-philhealth='" . htmlspecialchars(isset($row["philhealth_number"]) ? $row["philhealth_number"] : '') . "' data-pagibig='" . htmlspecialchars(isset($row["pagibig_number"]) ? $row["pagibig_number"] : '') . "' data-bir='" . htmlspecialchars(isset($row["bir_tax_form"]) ? $row["bir_tax_form"] : '') . "' data-nbi='" . htmlspecialchars(isset($row["nbi_clearance_path"]) ? $row["nbi_clearance_path"] : '') . "' data-psa='" . htmlspecialchars(isset($row["psa_birth_certificate_path"]) ? $row["psa_birth_certificate_path"] : '') . "' data-status='" . htmlspecialchars($onboardingStatus) . "'>Edit Info</button>";

                                    // Show submit button when onboarding is completed
                                    if (strtolower($onboardingStatus) === 'completed') {
                                        echo " <button class='submit-onboarding-btn' data-id='" . $row["id"] . "'>Submit</button>";
                                    }

                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='12'>No accepted applicants yet.</td></tr>";
                            }

                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Onboarding Edit Modal -->
    <div id="onboardingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <span class="modal-icon">📋</span>
                    <h3>Edit Onboarding Information</h3>
                </div>
                <span class="close" id="onboardingClose">&times;</span>
            </div>
            <form id="onboardingForm" action="update_onboarding.php" method="post" enctype="multipart/form-data">
                <input type="hidden" id="onboarding_application_id" name="application_id">
                
                <div class="form-section">
                    <h3>Onboarding Details</h3>

                    <div class="form-group">
                        <label for="onboarding_full_name">Full Name</label>
                        <input type="text" id="onboarding_full_name" readonly>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="sss_number">SSS Number</label>
                            <input type="text" id="sss_number" name="sss_number" placeholder="XX-XXXXXXX-X">
                        </div>

                        <div class="form-group">
                            <label for="philhealth_number">PhilHealth Number</label>
                            <input type="text" id="philhealth_number" name="philhealth_number" placeholder="XX-XXXXXXXXX-X">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="pagibig_number">Pag-IBIG Number</label>
                            <input type="text" id="pagibig_number" name="pagibig_number" placeholder="XXXX-XXXX-XXXX">
                        </div>

                        <div class="form-group">
                            <label for="bir_tax_form">BIR Tax Form</label>
                            <input type="file" id="bir_tax_form" name="bir_tax_form" accept=".pdf,image/*">
                            <small class="file-hint">Upload PDF or image (Max: 5MB)</small>
                            <div class="file-status" id="bir_file_status" style="display:none;">
                                Submitted: <a id="bir_file_view" href="#" target="_blank">View</a>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nbi_clearance">NBI Clearance</label>
                            <input type="file" id="nbi_clearance" name="nbi_clearance" accept=".pdf,image/*">
                            <small class="file-hint">Upload PDF or image (Max: 5MB)</small>
                            <div class="file-status" id="nbi_file_status" style="display:none;">
                                Submitted: <a id="nbi_file_view" href="#" target="_blank">View</a>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="psa_birth_certificate">PSA Birth Certificate</label>
                            <input type="file" id="psa_birth_certificate" name="psa_birth_certificate" accept=".pdf,image/*">
                            <small class="file-hint">Upload PDF or image (Max: 5MB)</small>
                            <div class="file-status" id="psa_file_status" style="display:none;">
                                Submitted: <a id="psa_file_view" href="#" target="_blank">View</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="cancel-btn" onclick="closeOnboardingModal()">
                        <span class="btn-icon">❌</span>
                        Cancel
                    </button>
                    <button type="submit" class="submit-btn">
                        <span class="btn-icon">💾</span>
                        Update Information
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="performance-management" id="performanceContent">

        <div class="performance-management-content">
            <div class="applications-table-section">
                <h3>Employee Performance Management</h3>

                <div class="table-controls">
                    <div class="filter-group">
                        <label for="performanceStatusFilter">Filter by status:</label>
                        <select id="performanceStatusFilter">
                            <option value="all">All</option>
                            <option value="pending">Pending Review</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <label for="performanceSearchInput">Search:</label>
                        <input type="text" id="performanceSearchInput" placeholder="Search by name, department, position...">
                    </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Table body will be populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<script>
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarToggle = document.getElementById('sidebarToggle');

    // Content sections
    const dashboardContent = document.getElementById('dashboardContent');
    const recruitmentContent = document.getElementById('recruitmentContent');
    const applicantContent = document.getElementById('applicantContent');
    const onboardingContent = document.getElementById('onboardingContent');
    const performanceContent = document.getElementById('performanceContent');

    // Navigation links
    const navLinks = document.querySelectorAll('.nav-list a');

    sidebarToggle.addEventListener('click', () => {
        const isMobile = window.innerWidth <= 768;
        if (isMobile) {
            sidebar.classList.toggle('active');
        } else {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('collapsed');
        }
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        const isMobile = window.innerWidth <= 768;
        if (isMobile && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target) && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
        }
    });

    const navSections = document.querySelectorAll('.nav-section');

    const setSectionOpen = (section, isOpen) => {
        const title = section.querySelector('.nav-section-title');
        const arrow = title.querySelector('.nav-icon');

        section.classList.toggle('open', isOpen);
        title.setAttribute('data-expanded', isOpen);
        arrow.textContent = isOpen ? '▾' : '▸';
    };

    // Function to show content based on navigation
    const showContent = (contentType, clickedLink = null) => {
        // Hide all content sections
        dashboardContent.classList.remove('active');
        recruitmentContent.classList.remove('active');
        applicantContent.classList.remove('active');
        onboardingContent.classList.remove('active');
        performanceContent.classList.remove('active');

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
                pageHeader.textContent = 'Dashboard';
                break;
            case 'recruitment':
                recruitmentContent.classList.add('active');
                pageHeader.textContent = 'Recruitment Management';
                // Re-attach event listeners when recruitment content is shown
                setTimeout(attachEventListeners, 100);
                break;
            case 'applicant':
                applicantContent.classList.add('active');
                pageHeader.textContent = 'Applicant Management';
                // Re-attach event listeners when applicant content is shown
                setTimeout(attachApplicantEventListeners, 100);
                break;
            case 'onboarding':
                onboardingContent.classList.add('active');
                pageHeader.textContent = 'New-Hire Onboarding';
                // Re-attach event listeners when onboarding content is shown
                setTimeout(attachOnboardingEventListeners, 100);
                break;
            case 'performance':
                performanceContent.classList.add('active');
                pageHeader.textContent = 'Performance Management';
                break;
            default:
                dashboardContent.classList.add('active');
                pageHeader.textContent = 'Dashboard';
        }
    };

    // Add click event listeners to navigation links
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const navText = link.querySelector('.nav-text').textContent.toLowerCase();
            
            if (navText.includes('recruitment')) {
                showContent('recruitment', link);
            } else if (navText.includes('applicant')) {
                showContent('applicant', link);
            } else if (navText.includes('onboarding') || navText.includes('new hire')) {
                showContent('onboarding', link);
            } else if (navText.includes('performance')) {
                showContent('performance', link);
            } else {
                showContent('dashboard', link);
            }
        });
    });

    navSections.forEach((section) => {
        const title = section.querySelector('.nav-section-title');
        const sectionLabel = title.querySelector('.nav-section-label').textContent;
        
        title.addEventListener('click', () => {
            const expanded = title.getAttribute('data-expanded') === 'true';
            setSectionOpen(section, !expanded);
        });

        // Open "Job Hiring" section by default
        if (sectionLabel === 'Job Hiring') {
            setSectionOpen(section, true);
        } else {
            setSectionOpen(section, false);
        }
    });

    // Check URL parameters to determine which section to show
    const urlParams = new URLSearchParams(window.location.search);
    const section = urlParams.get('section');

    if (section === 'recruitment') {
        showContent('recruitment');
    } else {
        // Initialize with dashboard active
        showContent('dashboard');
    }

    // Update applicant badge on page load
    updateApplicantBadge();

    // Branch selection logic
    const branchSelect = document.getElementById('branch_select');
    const branchText = document.getElementById('branch_text');

    branchSelect.addEventListener('change', function() {
        if (this.value === 'other') {
            branchText.style.display = 'block';
            branchText.required = true;
            branchText.focus();
        } else {
            branchText.style.display = 'none';
            branchText.required = false;
            branchText.value = '';
        }
    });

    // Edit modal functionality
    const editModal = document.getElementById('editModal');
    const closeBtn = document.querySelector('.close');

    // Function to attach event listeners to buttons
    function attachEventListeners() {
        // Remove existing event listeners by cloning and replacing
        const editBtns = document.querySelectorAll('.edit-btn');
        const deleteBtns = document.querySelectorAll('.delete-btn');

        // Edit button click handlers
        editBtns.forEach(btn => {
            // Remove existing listeners
            btn.replaceWith(btn.cloneNode(true));
        });
        
        // Re-select after cloning
        const newEditBtns = document.querySelectorAll('.edit-btn');
        newEditBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const jobData = this.dataset;
                
                // Populate modal with job data
                document.getElementById('edit_job_id').value = jobData.id;
                document.getElementById('edit_department').value = jobData.department;
                document.getElementById('edit_position').value = jobData.position;
                document.getElementById('edit_num_applicants').value = jobData.numApplicants;
                document.getElementById('edit_requirements').value = jobData.requirements;

                // Handle branch selection
                const branchSelect = document.getElementById('edit_branch_select');
                const branchText = document.getElementById('edit_branch_text');
                
                if (jobData.branch === 'susano' || jobData.branch === 'jordan' || jobData.branch === 'nitang') {
                    branchSelect.value = jobData.branch;
                    branchText.style.display = 'none';
                    branchText.required = false;
                    branchText.value = '';
                } else {
                    branchSelect.value = 'other';
                    branchText.style.display = 'block';
                    branchText.required = true;
                    branchText.value = jobData.branch;
                }

                editModal.style.display = 'block';
            });
        });

        // Delete button click handlers
        deleteBtns.forEach(btn => {
            // Remove existing listeners
            btn.replaceWith(btn.cloneNode(true));
        });
        
        // Re-select after cloning
        const newDeleteBtns = document.querySelectorAll('.delete-btn');
        newDeleteBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const jobId = this.dataset.id;
                showConfirmation('Are you sure you want to delete this job posting?', () => {
                    // Create form and submit for deletion
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'delete_job.php';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'job_id';
                    input.value = jobId;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }, () => {});
            });
        });
    }

    // Close modal
    closeBtn.addEventListener('click', closeModal);
    
    window.addEventListener('click', function(event) {
        if (event.target === editModal) {
            closeModal();
        }
    });

    function closeModal() {
        editModal.style.display = 'none';
    }

    // Function to update notification badge for pending applications
    function updateApplicantBadge() {
        fetch('get_pending_count.php')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('applicantBadge');
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error updating badge:', error);
            });
    }

    // Function to attach event listeners to applicant management controls
    function attachApplicantEventListeners() {
        attachApplicantFilters();

        // Status change handlers
        const statusSelects = document.querySelectorAll('.status-select');
        statusSelects.forEach(select => {
            select.addEventListener('change', function() {
                const applicationId = this.dataset.id;
                const newStatus = this.value;
                const currentStatus = this.dataset.current;

                if (newStatus !== currentStatus) {
                    showConfirmation(`Are you sure you want to change the application status to "${newStatus}"?`, () => {
                        // Create form and submit for status update
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'update_application_status.php';

                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'application_id';
                        idInput.value = applicationId;

                        const statusInput = document.createElement('input');
                        statusInput.type = 'hidden';
                        statusInput.name = 'status';
                        statusInput.value = newStatus;

                        form.appendChild(idInput);
                        form.appendChild(statusInput);
                        document.body.appendChild(form);
                        form.submit();
                    }, () => {
                        // Reset to previous value
                        this.value = currentStatus;
                    });
                }
            });
        });

        // Contact button handlers (shown only on reviewed applications)
        const contactBtns = document.querySelectorAll('.contact-btn');
        contactBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const email = this.dataset.email;
                const applicationId = this.dataset.id;

                if (!email) {
                    alert('No email address available to contact.');
                    return;
                }

                // Add confirmation before contacting
                showConfirmation('Are you sure you want to contact this applicant?', () => {
                    const applicantName = this.dataset.name || 'Applicant';
                    const positionTitle = this.dataset.position || 'the role';

                    openContactModal({
                        applicationId,
                        email,
                        name: applicantName,
                        position: positionTitle,
                    });
                }, () => {});

            });
        });

        // Contact modal logic is managed separately to avoid attaching duplicate event listeners.
    }

    // Function to attach event listeners to onboarding controls
    function attachOnboardingEventListeners() {
        attachOnboardingFilters();

        // Edit onboarding button handlers
        const editOnboardingBtns = document.querySelectorAll('.edit-onboarding-btn');
        editOnboardingBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const data = this.dataset;
                
                // Populate modal with applicant data
                document.getElementById('onboarding_application_id').value = data.id;
                document.getElementById('onboarding_full_name').value = data.name;
                document.getElementById('sss_number').value = data.sss;
                document.getElementById('philhealth_number').value = data.philhealth;
                document.getElementById('pagibig_number').value = data.pagibig;
                // File inputs cannot be pre-filled for security, so clear any previous selection.
                document.getElementById('bir_tax_form').value = '';

                const birHint = document.getElementById('bir_file_hint');
                const birStatus = document.getElementById('bir_file_status');
                const birView = document.getElementById('bir_file_view');
                const birValue = data.bir || '';

                if (birHint) {
                    birHint.textContent = birValue ? 'Current file: ' + birValue : '';
                }
                if (birStatus && birView) {
                    if (birValue) {
                        birStatus.style.display = 'block';
                        birView.href = '../' + birValue;
                    } else {
                        birStatus.style.display = 'none';
                        birView.href = '#';
                    }
                }

                const nbiStatus = document.getElementById('nbi_file_status');
                const nbiView = document.getElementById('nbi_file_view');
                const nbiValue = data.nbi || '';
                if (nbiStatus && nbiView) {
                    if (nbiValue) {
                        nbiStatus.style.display = 'block';
                        nbiView.href = '../' + nbiValue;
                    } else {
                        nbiStatus.style.display = 'none';
                        nbiView.href = '#';
                    }
                }

                const psaStatus = document.getElementById('psa_file_status');
                const psaView = document.getElementById('psa_file_view');
                const psaValue = data.psa || '';
                if (psaStatus && psaView) {
                    if (psaValue) {
                        psaStatus.style.display = 'block';
                        psaView.href = '../' + psaValue;
                    } else {
                        psaStatus.style.display = 'none';
                        psaView.href = '#';
                    }
                }

                openOnboardingModal();
            });
        });

        // Submit onboarding button handlers (only for completed onboarding)
        const submitOnboardingBtns = document.querySelectorAll('.submit-onboarding-btn');
        submitOnboardingBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const applicationId = this.dataset.id;

                if (!applicationId) return;

                if (!confirm('Mark onboarding as submitted?')) {
                    return;
                }

                fetch('submit_onboarding.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'application_id=' + encodeURIComponent(applicationId)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        window.location.reload();
                    } else {
                        alert('Unable to submit onboarding.');
                    }
                })
                .catch(() => {
                    alert('Unable to submit onboarding.');
                });
            });
        });
    }

    // Setup filtering and searching for the onboarding table
    function attachOnboardingFilters() {
        const statusFilter = document.getElementById('onboardingStatusFilter');
        const searchInput = document.getElementById('onboardingSearchInput');

        if (!statusFilter || !searchInput) return;

        const applyFilters = () => {
            const statusValue = statusFilter.value.toLowerCase();
            const searchValue = searchInput.value.trim().toLowerCase();

            const rows = document.querySelectorAll('#onboardingTable tbody tr');
            rows.forEach(row => {
                const statusBadge = row.querySelector('.status-badge');
                const statusText = statusBadge ? statusBadge.textContent.trim().toLowerCase() : '';
                const rowText = row.textContent.toLowerCase();

                const statusMatches = statusValue === 'all' || statusText === statusValue;
                const searchMatches = searchValue === '' || rowText.includes(searchValue);

                row.style.display = statusMatches && searchMatches ? '' : 'none';
            });
        };

        statusFilter.addEventListener('change', applyFilters);
        searchInput.addEventListener('input', applyFilters);

        // Apply filters immediately to ensure correct initial state
        applyFilters();
    }

    // Onboarding modal functions
    function openOnboardingModal() {
        const modal = document.getElementById('onboardingModal');
        if (modal) {
            modal.style.display = 'block';
        }
    }

    function closeOnboardingModal() {
        const modal = document.getElementById('onboardingModal');
        if (modal) {
            modal.style.display = 'none';
            document.getElementById('onboardingForm').reset();
        }
    }

    // Close modal event listeners
    const onboardingClose = document.getElementById('onboardingClose');
    if (onboardingClose) {
        onboardingClose.addEventListener('click', closeOnboardingModal);
    }

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('onboardingModal');
        if (event.target === modal) {
            closeOnboardingModal();
        }
    });

    // Custom Confirmation Modal
    (function() {
        const confirmationModal = document.getElementById('confirmationModal');
        const confirmationMessage = document.getElementById('confirmationMessage');
        const confirmYes = document.getElementById('confirmYes');
        const confirmNo = document.getElementById('confirmNo');

        let onYesCallback = null;
        let onNoCallback = null;

        function showConfirmation(message, onYes, onNo) {
            if (!confirmationModal) return;

            confirmationMessage.textContent = message;
            onYesCallback = onYes;
            onNoCallback = onNo;

            confirmationModal.style.display = 'block';
        }

        function closeConfirmationModal() {
            if (confirmationModal) {
                confirmationModal.style.display = 'none';
            }
            onYesCallback = null;
            onNoCallback = null;
        }

        if (confirmYes) {
            confirmYes.addEventListener('click', () => {
                if (onYesCallback) onYesCallback();
                closeConfirmationModal();
            });
        }

        if (confirmNo) {
            confirmNo.addEventListener('click', () => {
                if (onNoCallback) onNoCallback();
                closeConfirmationModal();
            });
        }

        window.addEventListener('click', (event) => {
            if (event.target === confirmationModal) {
                closeConfirmationModal();
            }
        });

        // Expose globally
        window.showConfirmation = showConfirmation;
    })();

    // Setup filtering and searching for the applicant table
    function attachApplicantFilters() {
        const statusFilter = document.getElementById('statusFilter');
        const searchInput = document.getElementById('searchInput');

        if (!statusFilter || !searchInput) return;

        const applyFilters = () => {
            const statusValue = statusFilter.value.toLowerCase();
            const searchValue = searchInput.value.trim().toLowerCase();

            const rows = document.querySelectorAll('.applications-table-section tbody tr');
            rows.forEach(row => {
                const statusBadge = row.querySelector('.status-badge');
                const statusText = statusBadge ? statusBadge.textContent.trim().toLowerCase() : '';
                const rowText = row.textContent.toLowerCase();

                const statusMatches = statusValue === 'all' || statusText === statusValue;
                const searchMatches = searchValue === '' || rowText.includes(searchValue);

                row.style.display = statusMatches && searchMatches ? '' : 'none';
            });
        };

        statusFilter.addEventListener('change', applyFilters);
        searchInput.addEventListener('input', applyFilters);

        // Apply filters immediately to ensure correct initial state
        applyFilters();
    }

    // Contact modal / EmailJS handling (initialized once)
    (function() {
        const contactModal = document.getElementById('contactModal');
        const contactForm = document.getElementById('contactForm');
        const contactClose = document.getElementById('contactClose');
        const contactCancel = document.getElementById('contactCancelBtn');

        function closeContactModal() {
            if (contactModal) {
                contactModal.style.display = 'none';
            }
        }

        function openContactModal({ applicationId, email, name, position }) {
            if (!contactModal) return;

            contactModal.dataset.applicationId = applicationId;
            contactModal.dataset.email = email;
            contactModal.dataset.name = name;
            contactModal.dataset.position = position;

            document.getElementById('contactApplicantName').textContent = name;
            document.getElementById('contactPositionTitle').textContent = position;
            document.getElementById('contactDate').value = '';
            document.getElementById('contactTime').value = '';
            document.getElementById('contactLocation').value = '';
            document.getElementById('contactInterviewer').value = '';

            contactModal.style.display = 'block';
        }

        // Expose openContactModal globally so event handlers can call it
        window.openContactModal = openContactModal;
        window.closeContactModal = closeContactModal;

        if (contactClose) {
            contactClose.addEventListener('click', closeContactModal);
        }

        if (contactCancel) {
            contactCancel.addEventListener('click', closeContactModal);
        }

        window.addEventListener('click', (event) => {
            if (event.target === contactModal) {
                closeContactModal();
            }
        });

        if (contactForm) {
            contactForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const email = contactModal?.dataset?.email;
                const applicantName = contactModal?.dataset?.name || 'Applicant';
                const positionTitle = contactModal?.dataset?.position || 'the role';
                const applicationId = contactModal?.dataset?.applicationId;
                const companyName = 'Mcores Inc.'; // Update to your actual company name

                const interviewDate = document.getElementById('contactDate').value.trim();
                const interviewTime = document.getElementById('contactTime').value.trim();
                const interviewLocation = document.getElementById('contactLocation').value.trim();
                const interviewer = document.getElementById('contactInterviewer').value.trim();

                if (!email || !interviewDate || !interviewTime || !interviewLocation || !interviewer) {
                    alert('Please fill out all fields before sending.');
                    return;
                }

                // Send email via EmailJS
                let errorMsg = '';
                if (!window.emailjs) {
                    errorMsg = 'EmailJS SDK is not loaded. Check your internet connection or script URL.';
                } else if (!EMAILJS_USER_ID || EMAILJS_USER_ID === 'YOUR_EMAILJS_USER_ID') {
                    errorMsg = 'EmailJS User ID is not configured. Please update EMAILJS_USER_ID in the script section.';
                } else if (!EMAILJS_SERVICE_ID || EMAILJS_SERVICE_ID === 'YOUR_EMAILJS_SERVICE_ID') {
                    errorMsg = 'EmailJS Service ID is not configured. Please update EMAILJS_SERVICE_ID in the script section.';
                } else if (!EMAILJS_TEMPLATE_ID || EMAILJS_TEMPLATE_ID === 'YOUR_EMAILJS_TEMPLATE_ID') {
                    errorMsg = 'EmailJS Template ID is not configured. Please update EMAILJS_TEMPLATE_ID in the script section.';
                }

                if (errorMsg) {
                    alert(errorMsg);
                    return;
                }

                const templateParams = {
                    to_email: email,
                    to_name: applicantName,
                    position: positionTitle,
                    company_name: companyName,
                    interview_date: interviewDate,
                    interview_time: interviewTime,
                    interview_location: interviewLocation,
                    interviewer: interviewer,
                };

                emailjs.send(EMAILJS_SERVICE_ID, EMAILJS_TEMPLATE_ID, templateParams)
                    .then(() => {
                        // On success, mark application as contacted and refresh
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'update_application_status.php';

                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'application_id';
                        idInput.value = applicationId;

                        const statusInput = document.createElement('input');
                        statusInput.type = 'hidden';
                        statusInput.name = 'status';
                        statusInput.value = 'contacted';

                        form.appendChild(idInput);
                        form.appendChild(statusInput);
                        document.body.appendChild(form);
                        form.submit();
                    })
                    .catch((error) => {
                        console.error('EmailJS send error:', error);
                        alert('Failed to send email. Please check your EmailJS configuration and try again.');
                    });
            });
        }
    })();
</script>
</body>
</html>
