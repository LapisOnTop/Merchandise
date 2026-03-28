<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="website.css">
    <title>Careers</title>
</head>
<body>
    <header class="header">
    <div class="container header-inner">
      <div class="logo">
        <h1><i class="fa-solid fa-chart-line"></i> Mcores </h1>
      </div>

      <div class="menu-toggle" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>

      <nav class="nav">
        <a href="website.php#home">Home</a>
        <a href="website.php#about">About</a>
        <a href="website.php#contact">Contact</a>
        <a href="career.php" target="_blank" class="btn">Careers</a>
      </nav>
    </div>
  </header>

  <main class="career-page">
    <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
    <div class="success-message">
      <div class="message-content">
        <span class="message-icon">✅</span>
        <div>
          <h3>Application Submitted Successfully!</h3>
          <p>Thank you for your application. We will review your submission and get back to you soon.</p>
        </div>
        <button class="close-message" onclick="this.parentElement.parentElement.style.display='none'">&times;</button>
      </div>
    </div>
    <?php endif; ?>

    <section class="career-hero">
      <div class="career-inner">
        <div class="career-header">
          <h1>Career Opportunities</h1>
          <p>Find your next role and join our growing team</p>
        </div>

        <div class="search-wrap">
          <input class="search-input" type="text" placeholder="Search jobs by title, department, or location..." />
          <button class="search-btn">🔍 Search</button>
        </div>

        <div class="jobs-grid">
          <?php
          include '../includes/db.php';

          $sql = "SELECT * FROM job_postings ORDER BY created_at DESC";
          $result = $conn->query($sql);

          if ($result->num_rows > 0) {
              while($row = $result->fetch_assoc()) {
                  echo '<article class="job-card">';
                  echo '<div class="job-head"><h3>' . htmlspecialchars($row["position"]) . '</h3></div>';
                  echo '<div class="job-meta">🏢 ' . htmlspecialchars($row["department"]) . ' • 📍 ' . htmlspecialchars($row["branch"]) . ' • ⏱ Full-time • 👥 ' . $row["num_applicants"] . ' positions</div>';
                  echo '<p>' . nl2br(htmlspecialchars($row["requirements"])) . '</p>';
                  echo '<button class="apply-btn" data-job-id="' . $row["id"] . '" data-position="' . htmlspecialchars($row["position"]) . '" data-department="' . htmlspecialchars($row["department"]) . '" data-branch="' . htmlspecialchars($row["branch"]) . '">✈ Apply Now</button>';
                  echo '</article>';
              }
          } else {
              echo '<p>No job postings available at the moment.</p>';
          }

          $conn->close();
          ?>
        </div>
      </div>
    </section>
  </main>

  <!-- Application Modal -->
  <div id="applicationModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Apply for Position</h2>
        <span class="close-modal">&times;</span>
      </div>

      <form id="applicationForm" action="submit_application.php" method="post" enctype="multipart/form-data">
        <input type="hidden" id="job_id" name="job_id">

        <div class="form-section">
          <h3>Personal Information</h3>

          <div class="form-row">
            <div class="form-group">
              <label for="full_name">Full Name *</label>
              <input type="text" id="full_name" name="full_name" required>
            </div>

            <div class="form-group">
              <label for="email">Email Address *</label>
              <input type="email" id="email" name="email" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="phone">Phone Number *</label>
              <input type="tel" id="phone" name="phone" required>
            </div>

            <div class="form-group">
              <label for="date_of_birth">Date of Birth *</label>
              <input type="date" id="date_of_birth" name="date_of_birth" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="gender">Gender *</label>
              <select id="gender" name="gender" required>
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
                <option value="prefer_not_to_say">Prefer not to say</option>
              </select>
            </div>

            <div class="form-group">
              <label for="civil_status">Civil Status *</label>
              <select id="civil_status" name="civil_status" required>
                <option value="">Select Civil Status</option>
                <option value="single">Single</option>
                <option value="married">Married</option>
                <option value="divorced">Divorced</option>
                <option value="widowed">Widowed</option>
                <option value="separated">Separated</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group full-width">
              <label for="nationality">Nationality *</label>
              <input type="text" id="nationality" name="nationality" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group full-width">
              <label for="home_address">Home Address *</label>
              <textarea id="home_address" name="home_address" rows="3" required placeholder="Enter your complete home address"></textarea>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3>Document Upload</h3>

          <div class="form-row">
            <div class="form-group">
              <label for="resume">Digital Resume (PDF) *</label>
              <input type="file" id="resume" name="resume" accept=".pdf" required>
              <small class="file-hint">Upload your resume in PDF format (Max: 5MB)</small>
            </div>

            <div class="form-group">
              <label for="valid_id">Valid ID Photo *</label>
              <input type="file" id="valid_id" name="valid_id" accept="image/*" required>
              <small class="file-hint">Upload a clear photo of your valid ID (Max: 2MB)</small>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
          <button type="submit" class="submit-btn">Submit Application</button>
        </div>
      </form>
    </div>
  </div>

<style>
    body { margin:0; font-family: Arial, sans-serif; background:#f4f7fd; }
    .career-page { padding: 1rem; }
    .career-hero { width:100%; display:flex; justify-content:center; margin-top:1rem; }
    .career-inner { width:min(1000px, 100%); background:#fff; border-radius:16px; box-shadow:0 14px 40px rgba(0,0,0,0.06); padding:1.5rem; }
    .career-header { text-align:center; margin-bottom:1rem; }
    .career-header h1 { margin:0; font-size:2rem; color:#0f2a66; font-weight:700; }
    .career-header p { margin-top:.3rem; color:#5f6b85; font-size:1rem; }
    .search-wrap { display:flex; gap:.5rem; margin-bottom:1.2rem; }
    .search-input { flex:1; border:1px solid #dde4f2; border-radius:999px; padding:.9rem 1rem; font-size:1rem; background:#fff; }
    .search-btn { border:none; background:#0f2a66; color:#fff; border-radius:999px; padding:.7rem 1.1rem; font-weight:700; cursor:pointer; }
    .jobs-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(250px,1fr)); gap:1rem; }
    .job-card { border:1px solid #edf2ff; background:#fff; border-radius:14px; padding:1rem; display:flex; flex-direction:column; gap:.35rem; box-shadow:0 8px 24px rgba(0,0,0,0.05); }
    .job-head h3 { margin:0; font-size:1.5rem; color:#0a2254; }
    .job-meta { font-size:.85rem; color:#6f7794; display:flex; flex-wrap:wrap; gap:.4rem; margin-top:0.2rem; }
    .job-card p { margin:.25rem 0 .6rem; color:#2b3250; font-size:.95rem; }
    .apply-btn { margin-top:auto; border:none; background:#1e2532; color:#fff; border-radius:999px; font-weight:700; padding:.55rem .85rem; cursor:pointer; font-size:.95rem; transition:transform .15s ease; }
    .apply-btn:hover { transform:translateY(-1px); }
    @media (max-width: 840px) { .career-inner { padding:1rem; } .search-wrap { flex-wrap:wrap; } }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px);
    }

    .modal-content {
      background-color: #fff;
      margin: 2% auto;
      padding: 0;
      border-radius: 12px;
      width: 90%;
      max-width: 700px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
      animation: modalFadeIn 0.3s ease;
    }

    @keyframes modalFadeIn {
      from { opacity: 0; transform: translateY(-50px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .modal-header {
      background: linear-gradient(135deg, #1e2532 0%, #2c3e50 100%);
      color: white;
      padding: 20px 30px;
      border-radius: 12px 12px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header h2 {
      margin: 0;
      font-size: 1.5rem;
      font-weight: 600;
    }

    .close-modal {
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
      color: #fff;
      opacity: 0.8;
      transition: opacity 0.2s;
    }

    .close-modal:hover {
      opacity: 1;
    }

    /* Form Styles */
    #applicationForm {
      padding: 30px;
    }

    .form-section {
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 1px solid #eee;
    }

    .form-section:last-child {
      border-bottom: none;
      margin-bottom: 20px;
    }

    .form-section h3 {
      margin: 0 0 20px 0;
      color: #1e2532;
      font-size: 1.2rem;
      font-weight: 600;
    }

    .form-row {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
    }

    .form-group {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .form-group.full-width {
      flex: 1 1 100%;
    }

    .form-group label {
      margin-bottom: 8px;
      font-weight: 600;
      color: #2c3e50;
      font-size: 0.9rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      padding: 12px 16px;
      border: 2px solid #e1e8ed;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.3s ease;
      background-color: #fff;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #1e2532;
      box-shadow: 0 0 0 3px rgba(30, 37, 50, 0.1);
    }

    .form-group textarea {
      resize: vertical;
      min-height: 80px;
    }

    .file-hint {
      font-size: 0.8rem;
      color: #6c757d;
      margin-top: 4px;
    }

    .form-actions {
      display: flex;
      gap: 15px;
      justify-content: flex-end;
      padding-top: 20px;
      border-top: 1px solid #eee;
    }

    .cancel-btn,
    .submit-btn {
      padding: 12px 24px;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .cancel-btn {
      background-color: #6c757d;
      color: white;
    }

    .cancel-btn:hover {
      background-color: #5a6268;
      transform: translateY(-1px);
    }

    .submit-btn {
      background: linear-gradient(135deg, #1e2532 0%, #2c3e50 100%);
      color: white;
    }

    .submit-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(30, 37, 50, 0.3);
    }

    /* Success Message Styles */
    .success-message {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1001;
      max-width: 400px;
      animation: slideInRight 0.4s ease;
    }

    .message-content {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
      display: flex;
      align-items: flex-start;
      gap: 15px;
    }

    .message-icon {
      font-size: 24px;
      flex-shrink: 0;
    }

    .message-content h3 {
      margin: 0 0 5px 0;
      font-size: 1.1rem;
      font-weight: 600;
    }

    .message-content p {
      margin: 0;
      font-size: 0.9rem;
      opacity: 0.9;
    }

    .close-message {
      background: none;
      border: none;
      color: white;
      font-size: 20px;
      cursor: pointer;
      opacity: 0.8;
      transition: opacity 0.2s;
      flex-shrink: 0;
    }

    .close-message:hover {
      opacity: 1;
    }

    @keyframes slideInRight {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    @media (max-width: 768px) {
      .success-message {
        left: 20px;
        right: 20px;
        max-width: none;
      }
    }
  </style>

  <script>
    // Modal functionality
    const modal = document.getElementById('applicationModal');
    const closeBtn = document.querySelector('.close-modal');

    // Apply button click handlers
    document.addEventListener('DOMContentLoaded', function() {
      const applyButtons = document.querySelectorAll('.apply-btn');

      applyButtons.forEach(button => {
        button.addEventListener('click', function() {
          const jobData = this.dataset;

          // Populate hidden fields
          document.getElementById('job_id').value = jobData.jobId;

          // Update modal title
          document.querySelector('.modal-header h2').textContent = `Apply for ${jobData.position}`;

          // Show modal
          modal.style.display = 'block';
          document.body.style.overflow = 'hidden';
        });
      });
    });

    // Close modal functions
    function closeModal() {
      modal.style.display = 'none';
      document.body.style.overflow = 'auto';
      document.getElementById('applicationForm').reset();
    }

    closeBtn.addEventListener('click', closeModal);

    window.addEventListener('click', function(event) {
      if (event.target === modal) {
        closeModal();
      }
    });

    // Form validation and file size checks
    document.getElementById('applicationForm').addEventListener('submit', function(e) {
      const resumeFile = document.getElementById('resume').files[0];
      const idFile = document.getElementById('valid_id').files[0];

      // Check file sizes
      if (resumeFile && resumeFile.size > 5 * 1024 * 1024) { // 5MB
        alert('Resume file size must be less than 5MB');
        e.preventDefault();
        return;
      }

      if (idFile && idFile.size > 2 * 1024 * 1024) { // 2MB
        alert('ID photo file size must be less than 2MB');
        e.preventDefault();
        return;
      }

      // Check file types
      if (resumeFile && !resumeFile.type.includes('pdf')) {
        alert('Resume must be a PDF file');
        e.preventDefault();
        return;
      }

      if (idFile && !idFile.type.startsWith('image/')) {
        alert('Valid ID must be an image file');
        e.preventDefault();
        return;
      }
    });

    // Menu toggle functionality
    function toggleMenu() {
      const nav = document.querySelector('.nav');
      nav.classList.toggle('active');
    }
  </script>
</body>
</html>