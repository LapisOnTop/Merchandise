<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrative</title>
    <link rel="stylesheet" href="../includes/styles.css">
    <link rel="stylesheet" href="../includes/all.min.css">
    <style>
        :root {
            --active-blue: #2563eb;
            --text-dark: #1f2937;
            --text-gray: #6b7280;
            --bg-light: #f3f4f6;
            --success-green: #10b981;
            --status-active: #10b981;
            --status-dropped: #ef4444;
            --status-graduated: #6366f1;
            --status-leave: #f59e0b;
        }
        .page-section { display: none; }
        .page-section.active { display: block; }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin-bottom: 10px; }
        .subtitle { color: var(--text-gray); margin-bottom: 30px; }
        .summary-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .summary-item { display: flex; align-items: center; gap: 16px; margin: 0; }
        .icon-box { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; background: #eff6ff; color: #3b82f6; flex-shrink: 0; }
        .icon-blue { background: #eff6ff; color: #3b82f6; }
        .icon-gold { background: #fef3c7; color: #d97706; }
        .summary-text h3 { font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin: 0 0 4px; }
        .summary-text p { color: var(--text-gray); margin: 0; font-size: 0.9rem; }
        .table-container { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); margin-bottom: 24px; overflow: hidden; }
        .table-container table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .table-container th { background: #f8f9fa; padding: 12px 16px; text-align: left; font-weight: 600; color: var(--text-dark); border-bottom: 1px solid #e5e7eb; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .table-container td { padding: 14px 16px; color: #495057; border-bottom: 1px solid #f3f4f6; }
        .table-container tbody tr:last-child td { border-bottom: none; }
        .table-container tbody tr:hover { background: #f9fafb; }
        .section-title { font-size: 1.1rem; font-weight: 600; color: var(--text-dark); margin-bottom: 20px; margin-top: 10px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0; padding: 20px; }
        .student-name { font-weight: 600; color: var(--text-dark); }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 500; display: inline-block; }
        .status-active { background: #ecfdf5; color: #10b981; }
        .status-dropped { background: #fef2f2; color: #ef4444; }
        .status-graduated { background: #eef2ff; color: #6366f1; }
        .status-leave { background: #fffbeb; color: #f59e0b; }
        .action-btn { background: none; border: 1px solid #e5e7eb; border-radius: 6px; padding: 5px 10px; cursor: pointer; color: #6b7280; transition: 0.2s; font-size: 0.85rem; }
        .action-btn:hover { background: #f3f4f6; color: var(--text-dark); }
        .btn { padding: 10px 20px; border-radius: 6px; font-weight: 500; cursor: pointer; border: none; font-size: 0.9rem; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background-color: var(--active-blue); color: white; }
        .btn-primary:hover { background-color: #1d4ed8; }
        .btn-secondary { background-color: #f3f4f6; color: var(--text-dark); }
        .btn-secondary:hover { background-color: #e5e7eb; }
        .tabs { display: flex; gap: 4px; padding: 16px 20px 0; border-bottom: 1px solid #e5e7eb; }
        .tab-btn { padding: 8px 18px; border: none; background: none; border-radius: 6px 6px 0 0; cursor: pointer; font-weight: 500; color: var(--text-gray); font-size: 0.9rem; border-bottom: 2px solid transparent; transition: 0.2s; }
        .tab-btn.active { color: var(--active-blue); border-bottom-color: var(--active-blue); background: #eff6ff; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-weight: 600; font-size: 0.85rem; color: var(--text-dark); margin-bottom: 6px; }
        .form-input { width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.9rem; color: var(--text-dark); transition: 0.2s; background: white; }
        .form-input:focus { outline: none; border-color: var(--active-blue); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .form-input.error { border-color: #ef4444; }
        .read-only-field { background: #f3f4f6; color: #6b7280; cursor: not-allowed; }
        .grid-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .btn-footer { display: flex; justify-content: flex-end; gap: 10px; }
        .modal-card { padding: 24px; }
        .popup-enter { animation: popIn 0.2s ease-out; }
        @keyframes popIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .shake { animation: shakeError 0.4s ease-in-out; }
        @keyframes shakeError { 0%,100% { transform: translateX(0); } 20%,60% { transform: translateX(-6px); } 40%,80% { transform: translateX(6px); } }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="brand">
        <h1>Administrative</h1>
        <button id="sidebarToggle" aria-label="Toggle navigation">☰</button>
    </div>
    <ul class="nav-list">
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">📋</span>
                <span class="nav-section-label">Administrative</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="#" class="nav-link" data-page="dashboard"><span class="nav-text">Dashboard</span></a></li>
                <li><a href="#" class="nav-link" data-page="visitors"><span class="nav-text">Visitor Log</span></a></li>
                <li><a href="#" class="nav-link" data-page="documents"><span class="nav-text">Documents</span></a></li>
                <li><a href="#" class="nav-link" data-page="facilities"><span class="nav-text">Facilities</span></a></li>
                <li><a href="#" class="nav-link" data-page="legal"><span class="nav-text">Legal</span></a></li>
            </ul>
        </li>
    </ul>
</div>

<div class="main" id="mainContent">

    <div class="page-section active" id="page-dashboard">
        <h1 class="page-title">Dashboard</h1>
        <p class="subtitle">Welcome to the Administrative Merchandising Management System</p>

        <section style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
            <article class="summary-card" style="flex: 1; min-width: 200px;">
                <figure class="summary-item">
                    <picture class="icon-box icon-blue">
                        <i class="fas fa-user-friends"></i>
                    </picture>
                    <figcaption class="summary-text">
                        <h3 id="dash-total-visitors">0</h3>
                        <p>Total Visitors</p>
                    </figcaption>
                </figure>
            </article>

            <article class="summary-card" style="flex: 1; min-width: 200px;">
                <figure class="summary-item">
                    <picture class="icon-box" style="background-color: #fce7f3; color: #db2777;">
                        <i class="fas fa-file-alt"></i>
                    </picture>
                    <figcaption class="summary-text">
                        <h3 id="dash-total-documents">0</h3>
                        <p>Documents</p>
                    </figcaption>
                </figure>
            </article>

            <article class="summary-card" style="flex: 1; min-width: 200px;">
                <figure class="summary-item">
                    <picture class="icon-box" style="background-color: #ecfdf5; color: #10b981;">
                        <i class="fas fa-building"></i>
                    </picture>
                    <figcaption class="summary-text">
                        <h3 id="dash-total-reservations">0</h3>
                        <p>Reservations</p>
                    </figcaption>
                </figure>
            </article>

            <article class="summary-card" style="flex: 1; min-width: 200px;">
                <figure class="summary-item">
                    <picture class="icon-box icon-gold">
                        <i class="fas fa-balance-scale"></i>
                    </picture>
                    <figcaption class="summary-text">
                        <h3 id="dash-total-contracts">0</h3>
                        <p>Contracts</p>
                    </figcaption>
                </figure>
            </article>
        </section>

        <section class="table-container">
            <h2 class="section-title" style="margin: 20px 20px 10px;">Recent Visitors</h2>
            <table>
                <thead>
                    <tr>
                        <th>NAME</th>
                        <th>DATE</th>
                        <th>REASON</th>
                    </tr>
                </thead>
                <tbody id="dash-visitors-tbody">
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 24px; color: var(--text-gray);">No recent visitors</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="table-container">
            <h2 class="section-title" style="margin: 20px 20px 10px;">Pending Documents</h2>
            <table>
                <thead>
                    <tr>
                        <th>DOCUMENT</th>
                        <th>CATEGORY</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody id="dash-documents-tbody">
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 24px; color: var(--text-gray);">No pending documents</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </div>

    <div class="page-section" id="page-visitors">
        <h1 class="page-title">Visitor Log Book</h1>
        <p class="subtitle">Track and manage all visitor entries</p>

        <section class="table-container">
            <nav class="tabs">
                <button class="tab-btn active" id="vlog-tab">Visitor Entries</button>
                <button class="tab-btn" id="vaudit-tab">Audit Trail</button>
            </nav>

            <article id="vlog-view">
                <header class="page-header" style="padding: 20px; margin-bottom: 0;">
                    <h2 style="font-size: 1.1rem; font-weight: 600; color: var(--text-dark);">Visitor Entries</h2>
                    <button class="btn btn-primary" id="visitors-add-btn">
                        <i class="fas fa-plus"></i> Add Entry
                    </button>
                </header>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="visitors-tbody">
                        <tr id="visitors-empty-row">
                            <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-gray);">No visitor entries yet</td>
                        </tr>
                    </tbody>
                </table>
            </article>

            <article id="vaudit-view" hidden>
                <header class="page-header" style="padding: 20px; margin-bottom: 0;">
                    <h2 style="font-size: 1.1rem; font-weight: 600; color: var(--text-dark);">Audit Trail</h2>
                </header>
                <table>
                    <thead>
                        <tr>
                            <th>Visitor</th>
                            <th>Action</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody id="vaudit-tbody">
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 24px; color: var(--text-gray);">No audit records yet</td>
                        </tr>
                    </tbody>
                </table>
            </article>
        </section>
    </div>

    <div class="page-section" id="page-documents">
        <h1 class="page-title">Document Management</h1>
        <p class="subtitle">Upload, track, and manage organizational documents</p>

        <nav class="tabs">
            <button class="tab-btn active" id="docs-tab">Documents</button>
            <button class="tab-btn" id="audit-tab">Audit Trail</button>
        </nav>

        <section class="table-container" id="documents-view">
            <header class="page-header" style="padding: 20px 20px 0;">
                <h2 class="section-title">All Documents</h2>
                <button class="btn btn-primary" id="documents-add-btn">
                    <i class="fa-solid fa-plus"></i> Upload Document
                </button>
            </header>

            <table>
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Uploaded By</th>
                        <th>Date</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody id="documents-tbody">
                    <tr id="documents-empty-row">
                        <td colspan="6" style="text-align: center; padding: 24px; color: var(--text-gray);">No documents uploaded yet</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="table-container" id="audit-view" style="display: none;">
            <header class="page-header" style="padding: 20px 20px 0;">
                <h2 class="section-title">Audit Trail</h2>
            </header>

            <table>
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Action</th>
                        <th>By</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody id="audit-tbody">
                    <tr id="audit-empty-row">
                        <td colspan="4" style="text-align: center; padding: 24px; color: var(--text-gray);">No audit records yet</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </div>

    <div class="page-section" id="page-facilities">
        <h1 class="page-title">Facilities Reservation</h1>
        <p class="subtitle">Reserve rooms and manage facility bookings</p>

        <section class="table-container">
            <header class="page-header" style="padding: 20px 25px; margin-bottom: 0;">
                <h2 class="section-title" style="margin: 0;">Reservations</h2>
                <button class="btn btn-primary" id="facilities-add-btn">
                    <i class="fa-solid fa-plus"></i> New Reservation
                </button>
            </header>

            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Duration</th>
                        <th>Requester</th>
                        <th>Rank</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="facilities-tbody">
                    <tr id="facilities-empty-row">
                        <td colspan="8" style="text-align: center; padding: 24px; color: var(--text-gray);">No reservations yet</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </div>

    <div class="page-section" id="page-legal">
        <h1 class="page-title">Legal Management</h1>
        <p class="subtitle">Manage contracts, legal cases, and regulatory compliance</p>

        <section class="table-container">
            <nav class="tabs">
                <button class="tab-btn active" id="contracts-tab">Contract Tracking</button>
                <button class="tab-btn" id="matters-tab">Matter Management</button>
            </nav>

            <article id="contracts">
                <header class="page-header">
                    <h2 class="section-title">Contracts</h2>
                    <button class="btn btn-primary" id="contracts-add-btn"><i class="fa-solid fa-plus"></i> Add Contract</button>
                </header>
                <table>
                    <thead>
                        <tr>
                            <th>Contract ID</th>
                            <th>Parties</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="contracts-tbody">
                        <tr id="contracts-empty-row">
                            <td colspan="5" style="text-align: center; padding: 24px; color: var(--text-gray);">No contracts added yet</td>
                        </tr>
                    </tbody>
                </table>
            </article>

            <article id="matters" hidden>
                <header class="page-header">
                    <h2 class="section-title">Legal Matters</h2>
                    <button class="btn btn-primary" id="matters-add-btn"><i class="fa-solid fa-plus"></i> Add Matter</button>
                </header>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="matters-tbody">
                        <tr id="matters-empty-row">
                            <td colspan="6" style="text-align: center; padding: 24px; color: var(--text-gray);">No legal matters added yet</td>
                        </tr>
                    </tbody>
                </table>
            </article>
        </section>
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

    setSectionOpen(navSections[0], true);

    const navLinks = document.querySelectorAll('.nav-link');
    const pageSections = document.querySelectorAll('.page-section');

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const target = link.getAttribute('data-page');
            pageSections.forEach(s => s.classList.remove('active'));
            document.getElementById('page-' + target).classList.add('active');
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
        });
    });

    navLinks[0].classList.add('active');

    const docsTab = document.getElementById('docs-tab');
    const auditTab = document.getElementById('audit-tab');
    const docsView = document.getElementById('documents-view');
    const auditView = document.getElementById('audit-view');

    docsTab.addEventListener('click', () => {
        docsTab.classList.add('active');
        auditTab.classList.remove('active');
        docsView.style.display = 'block';
        auditView.style.display = 'none';
    });

    auditTab.addEventListener('click', () => {
        auditTab.classList.add('active');
        docsTab.classList.remove('active');
        docsView.style.display = 'none';
        auditView.style.display = 'block';
    });

    const contractsTab = document.getElementById('contracts-tab');
    const mattersTab = document.getElementById('matters-tab');
    const contractsPanel = document.getElementById('contracts');
    const mattersPanel = document.getElementById('matters');

    contractsTab.addEventListener('click', () => {
        contractsTab.classList.add('active');
        mattersTab.classList.remove('active');
        contractsPanel.hidden = false;
        mattersPanel.hidden = true;
    });

    mattersTab.addEventListener('click', () => {
        mattersTab.classList.add('active');
        contractsTab.classList.remove('active');
        contractsPanel.hidden = true;
        mattersPanel.hidden = false;
    });

    document.querySelectorAll('#facilities-tbody button').forEach(btn => {
        btn.addEventListener('click', () => btn.closest('tr').remove());
    });
    document.querySelectorAll('#contracts-tbody .action-btn[title="Delete"]').forEach(btn => {
        btn.addEventListener('click', () => btn.closest('tr').remove());
    });
    document.querySelectorAll('#matters-tbody .action-btn[title="Delete"]').forEach(btn => {
        btn.addEventListener('click', () => btn.closest('tr').remove());
    });

    const vlogTab = document.getElementById('vlog-tab');
    const vauditTab = document.getElementById('vaudit-tab');
    const vlogView = document.getElementById('vlog-view');
    const vauditView = document.getElementById('vaudit-view');

    vlogTab.addEventListener('click', () => {
        vlogTab.classList.add('active'); vauditTab.classList.remove('active');
        vlogView.hidden = false; vauditView.hidden = true;
    });

    vauditTab.addEventListener('click', () => {
        vauditTab.classList.add('active'); vlogTab.classList.remove('active');
        vauditView.hidden = false; vlogView.hidden = true;
        loadAudit();
    });

    function fmtDateTime(dtStr) {
        if (!dtStr) return '—';
        const d = new Date(dtStr);
        return d.toLocaleDateString('en-US') + ' ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }

    function appendVisitorRow(v) {
        const empty = document.getElementById('visitors-empty-row');
        if (empty) empty.remove();
        const tbody = document.getElementById('visitors-tbody');
        const isLeft = v.status === 'Left';
        const tr = document.createElement('tr');
        tr.dataset.id = v.id;
        tr.innerHTML = `
            <td><strong class="student-name">${v.name}</strong></td>
            <td>${v.contact}</td>
            <td>${new Date(v.time_in).toLocaleDateString('en-US')}</td>
            <td>${new Date(v.time_in).toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit'})}</td>
            <td><mark class="status-badge status-graduated">${v.reason}</mark></td>
            <td><mark class="status-badge ${isLeft ? 'status-dropped' : 'status-active'}">${v.status}</mark></td>
            <td>${isLeft ? '<span style="color:var(--text-gray);font-size:0.85rem;">—</span>' : `<button class="action-btn" style="color:#f59e0b;border-color:#f59e0b;" title="Mark as Left"><i class="fas fa-sign-out-alt"></i> Left</button>`}</td>
        `;
        if (!isLeft) {
            tr.querySelector('button').addEventListener('click', function () {
                const fd = new FormData();
                fd.append('action', 'leave');
                fd.append('id', v.id);
                fetch('visitorfuncs.php', { method: 'POST', body: fd })
                    .then(r => r.json()).then(res => {
                        if (res.success) {
                            tr.querySelector('td:nth-child(6) mark').className = 'status-badge status-dropped';
                            tr.querySelector('td:nth-child(6) mark').textContent = 'Left';
                            tr.querySelector('td:last-child').innerHTML = '<span style="color:var(--text-gray);font-size:0.85rem;">—</span>';
                        }
                    });
            });
        }
        tbody.appendChild(tr);
    }

    function loadAudit() {
        fetch('visitorfuncs.php?action=audit').then(r => r.json()).then(rows => {
            const tbody = document.getElementById('vaudit-tbody');
            tbody.innerHTML = rows.length ? '' : '<tr><td colspan="3" style="text-align:center;padding:24px;color:var(--text-gray);">No audit records yet</td></tr>';
            rows.forEach(a => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td><strong>${a.visitor_name}</strong></td><td>${a.action}</td><td>${fmtDateTime(a.timestamp)}</td>`;
                tbody.appendChild(tr);
            });
        });
    }

    fetch('visitorfuncs.php?action=list').then(r => r.json()).then(rows => rows.forEach(appendVisitorRow));

    (function () {
        const visitorModal = document.createElement('dialog');
        visitorModal.id = 'visitor-modal';
        visitorModal.style.cssText = 'border:none;border-radius:12px;padding:0;width:460px;box-shadow:0 4px 20px rgba(0,0,0,0.2);position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);margin:0;';
        visitorModal.innerHTML = `
            <article class="modal-card" style="width:100%;box-sizing:border-box;text-align:left;">
                <header class="page-header" style="margin-bottom:20px;padding-bottom:15px;border-bottom:1px solid #e5e7eb;">
                    <h2 style="font-size:1.1rem;font-weight:600;color:var(--text-dark);">Add Visitor Entry</h2>
                </header>
                <section class="form-group">
                    <label class="form-label" for="v-name">Visitor Name</label>
                    <input id="v-name" class="form-input" type="text" placeholder="Enter full name" required>
                </section>
                <section class="form-group">
                    <label class="form-label" for="v-contact">Contact Number</label>
                    <input id="v-contact" class="form-input" type="tel" placeholder="09XXXXXXXXX" maxlength="11" required>
                </section>
                <section class="grid-row">
                    <section class="form-group">
                        <label class="form-label" for="v-date">Date</label>
                        <input id="v-date" class="form-input read-only-field" type="text" maxlength="10" required readonly>
                    </section>
                    <section class="form-group">
                        <label class="form-label" for="v-time">Time</label>
                        <input id="v-time" class="form-input read-only-field" type="text" required readonly>
                    </section>
                </section>
                <section class="form-group">
                    <label class="form-label" for="v-reason">Reason for Visit</label>
                    <select id="v-reason" class="form-input" required>
                        <option value="" disabled selected>Select a reason</option>
                        <option>Maintenance</option>
                        <option>Restocking</option>
                        <option>Delivery</option>
                        <option>Security Inspection</option>
                        <option>Legal Consultation</option>
                        <option>Other</option>
                    </select>
                </section>
                <footer class="btn-footer" style="margin-top:20px;">
                    <button id="v-cancel" class="btn btn-secondary">Cancel</button>
                    <button id="v-save" class="btn btn-primary"><i class="fas fa-save"></i> Save Entry</button>
                </footer>
            </article>
        `;
        document.body.appendChild(visitorModal);

        document.getElementById('visitors-add-btn').addEventListener('click', () => {
            const now = new Date();
            visitorModal.querySelector('#v-date').value = `${String(now.getMonth() + 1).padStart(2, '0')}/${String(now.getDate()).padStart(2, '0')}/${now.getFullYear()}`;
            const hours = now.getHours(), minutes = String(now.getMinutes()).padStart(2, '0'), ampm = hours >= 12 ? 'PM' : 'AM';
            visitorModal.querySelector('#v-time').value = `${String(hours % 12 || 12).padStart(2, '0')}:${minutes} ${ampm}`;
            visitorModal.querySelector('article').classList.add('popup-enter');
            visitorModal.showModal();
        });

        visitorModal.querySelector('#v-cancel').addEventListener('click', () => visitorModal.close());

        visitorModal.querySelector('#v-save').addEventListener('click', () => {
            const fields = ['v-name', 'v-contact', 'v-reason'].map(id => visitorModal.querySelector(`#${id}`));
            const empty = fields.filter(f => !f.value.trim());
            if (empty.length) {
                empty.forEach(f => { f.classList.add('error', 'shake'); f.addEventListener('animationend', () => f.classList.remove('shake'), { once: true }); });
                return;
            }
            const fd = new FormData();
            fd.append('action', 'add');
            fd.append('name', fields[0].value.trim());
            fd.append('contact', fields[1].value.trim());
            fd.append('reason', fields[2].value.trim());
            fetch('visitorfuncs.php', { method: 'POST', body: fd })
                .then(r => r.json()).then(res => {
                    if (res.success) {
                        appendVisitorRow({ id: res.id, name: fd.get('name'), contact: fd.get('contact'), reason: fd.get('reason'), time_in: new Date().toISOString(), status: 'Inside' });
                        fields.forEach(f => { f.value = ''; f.classList.remove('error'); });
                        visitorModal.close();
                    }
                });
        });

        visitorModal.addEventListener('click', e => { if (e.target === visitorModal) visitorModal.close(); });
    })();

    (function () {
        const docModal = document.createElement('dialog');
        docModal.id = 'doc-modal';
        docModal.style.cssText = 'border:none;border-radius:12px;padding:0;width:480px;box-shadow:0 4px 20px rgba(0,0,0,0.2);position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);margin:0;';
        const today = new Date();
        const localDate = `${String(today.getMonth() + 1).padStart(2, '0')}/${String(today.getDate()).padStart(2, '0')}/${today.getFullYear()}`;
        docModal.innerHTML = `
            <article class="modal-card" style="width:100%;box-sizing:border-box;text-align:left;">
                <header class="page-header" style="margin-bottom:20px;padding-bottom:15px;border-bottom:1px solid #e5e7eb;">
                    <h2 style="font-size:1.1rem;font-weight:600;color:var(--text-dark);">Upload Document</h2>
                </header>
                <section class="form-group">
                    <label class="form-label">File</label>
                    <label style="display:flex;align-items:center;gap:12px;padding:10px 14px;border:2px dashed #e5e7eb;border-radius:6px;cursor:pointer;transition:0.2s;background:#f8fafc;" id="doc-file-label">
                        <i class="fas fa-upload" style="color:var(--text-gray);"></i>
                        <p id="doc-file-name" style="color:var(--text-gray);font-size:0.9rem;margin:0;">Choose a file…</p>
                        <input id="doc-file" type="file" style="display:none;" required>
                    </label>
                </section>
                <section class="grid-row">
                    <section class="form-group">
                        <label class="form-label" for="doc-category">Category</label>
                        <select id="doc-category" class="form-input" required>
                            <option value="" disabled selected>Select category</option>
                            <option>Legal</option>
                            <option>Facilities</option>
                            <option>Finance</option>
                        </select>
                    </section>
                    <section class="form-group">
                        <label class="form-label" for="doc-uploader">Uploaded By</label>
                        <input id="doc-uploader" class="form-input" type="text" placeholder="Enter name" required>
                    </section>
                </section>
                <section class="form-group">
                    <label class="form-label" for="doc-tags">Tags <p style="display:inline;color:var(--text-gray);font-size:0.8rem;">(comma-separated)</p></label>
                    <input id="doc-tags" class="form-input" type="text" placeholder="e.g. contract, urgent">
                </section>
                <section class="form-group">
                    <label class="form-label">Date</label>
                    <input class="form-input read-only-field" type="text" value="${localDate}" readonly>
                </section>
                <footer class="btn-footer" style="margin-top:20px;">
                    <button id="doc-cancel" class="btn btn-secondary">Cancel</button>
                    <button id="doc-save" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</button>
                </footer>
            </article>
        `;
        document.body.appendChild(docModal);

        const fileInput = docModal.querySelector('#doc-file');
        const fileLabel = docModal.querySelector('#doc-file-label');

        fileInput.addEventListener('change', () => {
            const name = fileInput.files[0]?.name || 'Choose a file…';
            docModal.querySelector('#doc-file-name').textContent = name;
            fileLabel.style.borderColor = fileInput.files[0] ? 'var(--success-green)' : '#e5e7eb';
            fileLabel.style.background = fileInput.files[0] ? '#f0fdf4' : '#f8fafc';
        });

        document.getElementById('documents-add-btn').addEventListener('click', () => {
            docModal.querySelector('article').classList.add('popup-enter');
            docModal.showModal();
        });

        docModal.querySelector('#doc-cancel').addEventListener('click', () => docModal.close());

        docModal.querySelector('#doc-save').addEventListener('click', () => {
            const file = fileInput.files[0];
            const category = docModal.querySelector('#doc-category');
            const uploader = docModal.querySelector('#doc-uploader');
            const fields = [category, uploader];
            const empty = fields.filter(f => !f.value.trim());
            if (!file) fileLabel.style.borderColor = '#ef4444';
            if (empty.length || !file) {
                empty.forEach(f => { f.classList.add('error', 'shake'); f.addEventListener('animationend', () => f.classList.remove('shake'), { once: true }); });
                return;
            }
            const tags = docModal.querySelector('#doc-tags').value.trim();
            const categoryColors = { Legal: 'background:#e0e7ff;color:#4338ca;', Facilities: 'background:#e0f2fe;color:#0369a1;', Finance: 'background:#fce7f3;color:#be185d;' };
            const tbody = document.getElementById('documents-tbody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong class="student-name">${file.name}</strong>${tags ? `<br><p style="font-size:0.75rem;color:var(--text-gray);margin-top:3px;">${tags}</p>` : ''}</td>
                <td><strong class="status-badge status-graduated" style="${categoryColors[category.value]}">${category.value}</strong></td>
                <td><strong class="status-badge status-leave">Pending</strong></td>
                <td>${uploader.value.trim()}</td>
                <td>${localDate}</td>
                <td style="text-align:center;"><button class="action-btn"><i class="fa-solid fa-box-archive"></i></button></td>
            `;
            tbody.appendChild(tr);
            [category, uploader, docModal.querySelector('#doc-tags')].forEach(f => { f.value = ''; f.classList.remove('error'); });
            fileInput.value = '';
            docModal.querySelector('#doc-file-name').textContent = 'Choose a file…';
            fileLabel.style.borderColor = '#e5e7eb';
            fileLabel.style.background = '#f8fafc';
            docModal.close();
        });

        docModal.addEventListener('click', e => { if (e.target === docModal) docModal.close(); });
    })();

    (function () {
        const resModal = document.createElement('dialog');
        resModal.id = 'res-modal';
        resModal.style.cssText = 'border:none;border-radius:12px;padding:0;width:480px;box-shadow:0 4px 20px rgba(0,0,0,0.2);position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);margin:0;';
        resModal.innerHTML = `
            <article class="modal-card" style="width:100%;box-sizing:border-box;text-align:left;">
                <header class="page-header" style="margin-bottom:20px;padding-bottom:15px;border-bottom:1px solid #e5e7eb;">
                    <h2 style="font-size:1.1rem;font-weight:600;color:var(--text-dark);">New Reservation</h2>
                </header>
                <section class="form-group">
                    <label class="form-label" for="r-room">Room</label>
                    <select id="r-room" class="form-input" required>
                        <optgroup label="Meeting &amp; Collaboration">
                            <option>Meeting Room A</option>
                            <option>Conference Hall</option>
                            <option>Board Room</option>
                        </optgroup>
                        <optgroup label="Merchandising">
                            <option>Showroom / Display Area</option>
                            <option>Buyer's Meeting Room</option>
                            <option>Training Room</option>
                        </optgroup>
                        <optgroup label="Operational">
                            <option>Receiving / Inspection Area</option>
                            <option>Loading Bay</option>
                        </optgroup>
                        <optgroup label="Executive">
                            <option>VIP Lounge</option>
                            <option>Executive Suite</option>
                        </optgroup>
                    </select>
                </section>
                <section class="form-group">
                    <label class="form-label" for="r-date">Date</label>
                    <div style="position:relative;display:flex;align-items:center;">
                        <input id="r-date" class="form-input" type="text" placeholder="MM/DD/YYYY" maxlength="10" readonly required style="cursor:pointer;padding-right:36px;">
                        <i id="r-cal-icon" class="fas fa-calendar-alt" style="position:absolute;right:12px;color:var(--text-gray);cursor:pointer;font-size:0.9rem;z-index:1;"></i>
                        <input id="r-date-picker" type="date" style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;">
                    </div>
                </section>
                <section class="form-group">
                    <label class="form-label" for="r-duration">Duration (hours)</label>
                    <input id="r-duration" class="form-input" type="number" min="1" placeholder="e.g. 2" required>
                </section>
                <section class="grid-row">
                    <section class="form-group">
                        <label class="form-label" for="r-rank">Requester Rank</label>
                        <select id="r-rank" class="form-input" required>
                            <option value="" disabled selected>Select rank</option>
                            <option>Staff</option>
                            <option>Manager</option>
                            <option>Director</option>
                        </select>
                    </section>
                    <section class="form-group">
                        <label class="form-label" for="r-name">Requester Name</label>
                        <input id="r-name" class="form-input" type="text" placeholder="Full name" required>
                    </section>
                </section>
                <p style="font-size:0.8rem;color:var(--text-gray);background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:10px 12px;margin-bottom:20px;">
                    <i class="fas fa-info-circle" style="margin-right:6px;color:var(--active-blue);"></i>
                    Regular rooms are auto-approved. VIP/Conference rooms require manager review.
                </p>
                <footer class="btn-footer" style="margin-top:0;">
                    <button id="r-cancel" class="btn btn-secondary">Cancel</button>
                    <button id="r-save" class="btn btn-primary"><i class="fas fa-save"></i> Save Entry</button>
                </footer>
            </article>
        `;
        document.body.appendChild(resModal);

        const rDateInput = resModal.querySelector('#r-date');
        const rDatePicker = resModal.querySelector('#r-date-picker');

        resModal.querySelector('#r-cal-icon').addEventListener('click', () => rDatePicker.showPicker());
        rDatePicker.addEventListener('change', e => {
            const [y, m, d] = e.target.value.split('-');
            rDateInput.value = `${m}/${d}/${y}`;
            rDateInput.classList.remove('error');
        });

        document.getElementById('facilities-add-btn').addEventListener('click', () => {
            resModal.querySelector('article').classList.add('popup-enter');
            resModal.showModal();
        });

        resModal.querySelector('#r-cancel').addEventListener('click', () => resModal.close());

        const typeStyles = {
            Regular: 'background:#dcfce7;color:#15803d;',
            VIP: 'background:#fef9c3;color:#a16207;',
            Conference: 'background:#e0e7ff;color:#4338ca;'
        };

        resModal.querySelector('#r-save').addEventListener('click', () => {
            const fields = ['r-room', 'r-date', 'r-duration', 'r-rank', 'r-name'].map(id => resModal.querySelector(`#${id}`));
            const empty = fields.filter(f => !f.value.trim());
            if (empty.length) {
                empty.forEach(f => { f.classList.add('error', 'shake'); f.addEventListener('animationend', () => f.classList.remove('shake'), { once: true }); });
                return;
            }
            const [roomVal, date, duration, rank, name] = fields.map(f => f.value.trim());
            const vipRooms = ['VIP Lounge', 'Executive Suite'];
            const confRooms = ['Conference Hall', 'Board Room'];
            let type = 'Regular';
            if (vipRooms.includes(roomVal)) type = 'VIP';
            else if (confRooms.includes(roomVal)) type = 'Conference';
            const isVipOrConf = type === 'VIP' || type === 'Conference';
            const status = isVipOrConf ? 'Pending Manager Review' : 'Approved';
            const statusClass = isVipOrConf ? 'status-leave' : 'status-active';
            const fd = new FormData();
            fd.append('action', 'add');
            fd.append('room', roomVal); fd.append('type', type); fd.append('date', date);
            fd.append('duration', duration); fd.append('requester', name); fd.append('rank', rank);
            fd.append('status', isVipOrConf ? 'pending' : 'approved');
            fetch('reservationfuncs.php', { method: 'POST', body: fd }).then(r => r.text()).then(res => {
                if (res.trim() !== 'success') return;
                fetch('reservationfuncs.php?action=fetch').then(r => r.json()).then(rows => {
                    const tbody = document.getElementById('facilities-tbody');
                    const emptyRow = document.getElementById('facilities-empty-row');
                    if (emptyRow) emptyRow.remove();
                    tbody.innerHTML = '';
                    rows.forEach(r => tbody.appendChild(buildResRow(r, typeStyles)));
                });
            });
            fields.forEach(f => { f.value = ''; f.classList.remove('error'); });
            rDatePicker.value = '';
            resModal.close();
        });

        resModal.addEventListener('click', e => { if (e.target === resModal) resModal.close(); });

        function buildResRow(r, typeStyles) {
            const isVipOrConf = r.type === 'VIP' || r.type === 'Conference';
            const statusClass = r.status === 'approved' ? 'status-active' : 'status-leave';
            const statusLabel = r.status === 'approved' ? 'Approved' : 'Pending Manager Review';
            const typeStyle = (typeStyles && typeStyles[r.type]) ? typeStyles[r.type] : '';
            const tr = document.createElement('tr');
            tr.dataset.id = r.id;
            tr.innerHTML = `
                <td class="student-name">${r.room}</td>
                <td><mark class="status-badge status-graduated" style="${typeStyle}">${r.type}</mark></td>
                <td>${r.date}</td>
                <td>${r.duration} hour${r.duration > 1 ? 's' : ''}</td>
                <td>${r.requester}</td>
                <td>${r.rank}</td>
                <td><mark class="status-badge ${statusClass}">${statusLabel}</mark></td>
                <td>
                    <button class="action-btn btn-approve" title="Approve" style="${r.status === 'approved' ? 'display:none;' : ''}"><i class="fa-solid fa-check"></i> Approve</button>
                    <button class="action-btn" style="color:#ef4444;border:none;background:none;"><i class="fa-solid fa-trash-can"></i></button>
                </td>`;
            tr.querySelector('.btn-approve').addEventListener('click', () => {
                const fd = new FormData(); fd.append('action', 'approve'); fd.append('id', tr.dataset.id);
                fetch('reservationfuncs.php', { method: 'POST', body: fd }).then(r => r.text()).then(res => {
                    if (res.trim() !== 'success') return;
                    tr.querySelector('td:nth-child(7) mark').className = 'status-badge status-active';
                    tr.querySelector('td:nth-child(7) mark').textContent = 'Approved';
                    tr.querySelector('.btn-approve').style.display = 'none';
                });
            });
            tr.querySelector('.action-btn:last-child').addEventListener('click', () => {
                const fd = new FormData(); fd.append('action', 'delete'); fd.append('id', tr.dataset.id);
                fetch('reservationfuncs.php', { method: 'POST', body: fd }).then(r => r.text()).then(res => {
                    if (res.trim() !== 'success') return;
                    tr.remove();
                    if (!document.querySelector('#facilities-tbody tr')) document.getElementById('facilities-tbody').innerHTML = '<tr id="facilities-empty-row"><td colspan="8" style="text-align:center;padding:24px;color:var(--text-gray);">No reservations yet</td></tr>';
                });
            });
            return tr;
        }

        fetch('reservationfuncs.php?action=fetch').then(r => r.json()).then(rows => {
            if (!rows.length) return;
            const emptyRow = document.getElementById('facilities-empty-row');
            if (emptyRow) emptyRow.remove();
            const tbody = document.getElementById('facilities-tbody');
            rows.forEach(r => tbody.appendChild(buildResRow(r, typeStyles)));
        });
    })();

    (function () {
        const LEGAL = 'legalfuncs.php?target=contracts';

        const ctrModal = document.createElement('dialog');
        ctrModal.id = 'ctr-modal';
        ctrModal.style.cssText = 'border:none;border-radius:12px;padding:0;width:480px;box-shadow:0 4px 20px rgba(0,0,0,0.2);position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);margin:0;';
        ctrModal.innerHTML = `
            <article class="modal-card" style="width:100%;box-sizing:border-box;text-align:left;">
                <header class="page-header" style="margin-bottom:20px;padding-bottom:15px;border-bottom:1px solid #e5e7eb;">
                    <h2 style="font-size:1.1rem;font-weight:600;color:var(--text-dark);">Add Contract</h2>
                </header>
                <section class="form-group">
                    <label class="form-label" for="c-id">Contract ID</label>
                    <input id="c-id" class="form-input" type="text" placeholder="CTR-XXX" required>
                </section>
                <section class="form-group">
                    <label class="form-label" for="c-parties">Parties</label>
                    <input id="c-parties" class="form-input" type="text" placeholder="Party A vs. Party B" required>
                </section>
                <section class="form-group">
                    <label class="form-label" for="c-expiry">Expiry Date</label>
                    <section style="position:relative;display:flex;align-items:center;">
                        <input id="c-expiry" class="form-input" type="text" placeholder="MM/DD/YYYY" maxlength="10" readonly required style="cursor:pointer;padding-right:36px;">
                        <i id="c-cal-icon" class="fa-solid fa-calendar-alt" style="position:absolute;right:12px;color:var(--text-gray);cursor:pointer;font-size:0.9rem;z-index:1;"></i>
                        <input id="c-date-picker" type="date" style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;">
                    </section>
                </section>
                <section class="form-group">
                    <label class="form-label">Status</label>
                    <input class="form-input read-only-field" id="c-status-display" type="text" value="Active" readonly>
                </section>
                <footer class="btn-footer" style="margin-top:20px;">
                    <button id="c-cancel" class="btn btn-secondary">Cancel</button>
                    <button id="c-save" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save</button>
                </footer>
            </article>
        `;
        document.body.appendChild(ctrModal);

        const cExpiry = ctrModal.querySelector('#c-expiry');
        const cPicker = ctrModal.querySelector('#c-date-picker');
        const cStatusDisplay = ctrModal.querySelector('#c-status-display');

        function buildContractRow(c) {
            const [y, m, d] = c.expiry_date.split('-');
            const tr = document.createElement('tr');
            tr.dataset.id = c.id;
            tr.innerHTML = `
                <td><strong>${c.contract_id}</strong></td>
                <td>${c.parties}</td>
                <td>${m}/${d}/${y}</td>
                <td><mark class="status-badge ${c.status === 'archived' ? 'status-dropped' : 'status-active'}">${c.status === 'archived' ? 'Archived' : 'Active'}</mark></td>
                <td>
                    <button class="action-btn btn-c-archive" title="Archive" ${c.status === 'archived' ? 'style="display:none;"' : ''}><i class="fa-solid fa-box-archive"></i> Archive</button>
                    <button class="action-btn btn-c-delete" title="Delete" style="color:#ef4444;"><i class="fa-solid fa-trash"></i></button>
                </td>`;
            tr.querySelector('.btn-c-archive').addEventListener('click', () => {
                const fd = new FormData(); fd.append('action', 'archive'); fd.append('target', 'contracts'); fd.append('id', tr.dataset.id);
                fetch(LEGAL, { method: 'POST', body: fd }).then(r => r.text()).then(res => {
                    if (res.trim() !== 'success') return;
                    tr.querySelector('td:nth-child(4) mark').className = 'status-badge status-dropped';
                    tr.querySelector('td:nth-child(4) mark').textContent = 'Archived';
                    tr.querySelector('.btn-c-archive').style.display = 'none';
                });
            });
            tr.querySelector('.btn-c-delete').addEventListener('click', () => {
                const fd = new FormData(); fd.append('action', 'delete'); fd.append('target', 'contracts'); fd.append('id', tr.dataset.id);
                fetch(LEGAL, { method: 'POST', body: fd }).then(r => r.text()).then(res => {
                    if (res.trim() !== 'success') return;
                    tr.remove();
                    if (!document.querySelector('#contracts-tbody tr')) document.getElementById('contracts-tbody').innerHTML = '<tr id="contracts-empty-row"><td colspan="5" style="text-align:center;padding:24px;color:var(--text-gray);">No contracts added yet</td></tr>';
                });
            });
            return tr;
        }

        fetch(LEGAL + '&action=fetch').then(r => r.json()).then(rows => {
            if (!rows.length) return;
            const emptyRow = document.getElementById('contracts-empty-row');
            if (emptyRow) emptyRow.remove();
            rows.forEach(c => document.getElementById('contracts-tbody').appendChild(buildContractRow(c)));
        });

        ctrModal.querySelector('#c-cal-icon').addEventListener('click', () => cPicker.showPicker());
        cPicker.addEventListener('change', e => {
            const [y, m, d] = e.target.value.split('-');
            cExpiry.value = `${m}/${d}/${y}`;
            cExpiry.classList.remove('error');
        });

        document.getElementById('contracts-add-btn').addEventListener('click', () => {
            ctrModal.querySelector('article').classList.add('popup-enter');
            ctrModal.showModal();
        });

        ctrModal.querySelector('#c-cancel').addEventListener('click', () => ctrModal.close());

        ctrModal.querySelector('#c-save').addEventListener('click', () => {
            const fields = ['c-id', 'c-parties', 'c-expiry'].map(id => ctrModal.querySelector(`#${id}`));
            const empty = fields.filter(f => !f.value.trim());
            if (empty.length) {
                empty.forEach(f => { f.classList.add('error', 'shake'); f.addEventListener('animationend', () => f.classList.remove('shake'), { once: true }); });
                return;
            }
            const fd = new FormData();
            fd.append('action', 'add');
            fd.append('target', 'contracts');
            fd.append('contract_id', fields[0].value.trim());
            fd.append('parties', fields[1].value.trim());
            fd.append('expiry_date', cPicker.value);
            fetch(LEGAL, { method: 'POST', body: fd }).then(r => r.text()).then(res => {
                if (res.trim() !== 'success') return;
                fetch(LEGAL + '&action=fetch').then(r => r.json()).then(rows => {
                    const tbody = document.getElementById('contracts-tbody');
                    const emptyRow = document.getElementById('contracts-empty-row');
                    if (emptyRow) emptyRow.remove();
                    tbody.innerHTML = '';
                    rows.forEach(c => tbody.appendChild(buildContractRow(c)));
                });
                fields.forEach(f => { f.value = ''; f.classList.remove('error'); });
                cPicker.value = '';
                cStatusDisplay.value = 'Active';
                ctrModal.close();
            });
        });

        ctrModal.addEventListener('click', e => { if (e.target === ctrModal) ctrModal.close(); });
    })();

    (function () {
        const MATTERS = 'legalfuncs.php?target=matters';
        const statusMap = {
            open:        { cls: 'status-active',    label: 'Open' },
            in_progress: { cls: 'status-leave',     label: 'In Progress' },
            resolved:    { cls: 'status-graduated', label: 'Resolved' }
        };

        const mtrModal = document.createElement('dialog');
        mtrModal.id = 'mtr-modal';
        mtrModal.style.cssText = 'border:none;border-radius:12px;padding:0;width:480px;box-shadow:0 4px 20px rgba(0,0,0,0.2);position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);margin:0;';
        mtrModal.innerHTML = `
            <article class="modal-card" style="width:100%;box-sizing:border-box;text-align:left;">
                <header class="page-header" style="margin-bottom:20px;padding-bottom:15px;border-bottom:1px solid #e5e7eb;">
                    <h2 style="font-size:1.1rem;font-weight:600;color:var(--text-dark);">Add Matter</h2>
                </header>
                <section class="form-group">
                    <label class="form-label" for="m-title">Title</label>
                    <input id="m-title" class="form-input" type="text" placeholder="Matter Title" required>
                </section>
                <section class="form-group">
                    <label class="form-label" for="m-type">Type</label>
                    <select id="m-type" class="form-input" required>
                        <option value="" disabled selected>Select type</option>
                        <option>Legal Case</option>
                        <option>Regulatory Compliance</option>
                    </select>
                </section>
                <section class="form-group">
                    <label class="form-label" for="m-assigned">Assigned To</label>
                    <input id="m-assigned" class="form-input" type="text" placeholder="Name" required>
                </section>
                <footer class="btn-footer" style="margin-top:20px;">
                    <button id="m-cancel" class="btn btn-secondary">Cancel</button>
                    <button id="m-save" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save</button>
                </footer>
            </article>
        `;
        document.body.appendChild(mtrModal);

        function buildMatterRow(m) {
            const s = statusMap[m.status] || statusMap.open;
            const date = m.created_at ? m.created_at.split(' ')[0] : '—';
            const tr = document.createElement('tr');
            tr.dataset.id = m.id;
            tr.innerHTML = `
                <td><strong>${m.title}</strong></td>
                <td><mark class="status-badge status-graduated">${m.type}</mark></td>
                <td><mark class="status-badge ${s.cls}">${s.label}</mark></td>
                <td>${m.assigned_to}</td>
                <td>${date}</td>
                <td>
                    <select class="action-btn m-status-select" style="cursor:pointer;">
                        <option value="open"        ${m.status==='open'        ? 'selected':''}>Open</option>
                        <option value="in_progress" ${m.status==='in_progress' ? 'selected':''}>In Progress</option>
                        <option value="resolved"    ${m.status==='resolved'    ? 'selected':''}>Resolved</option>
                    </select>
                    <button class="action-btn m-delete-btn" title="Delete" style="color:#ef4444;"><i class="fa-solid fa-trash"></i></button>
                </td>`;
            tr.querySelector('.m-status-select').addEventListener('change', function () {
                const fd = new FormData();
                fd.append('action', 'status');
                fd.append('target', 'matters');
                fd.append('id', tr.dataset.id);
                fd.append('status', this.value);
                fetch(MATTERS, { method: 'POST', body: fd }).then(r => r.text()).then(res => {
                    if (res.trim() !== 'success') return;
                    const ns = statusMap[this.value];
                    const badge = tr.querySelector('td:nth-child(3) mark');
                    badge.className = `status-badge ${ns.cls}`;
                    badge.textContent = ns.label;
                });
            });
            tr.querySelector('.m-delete-btn').addEventListener('click', () => {
                const fd = new FormData();
                fd.append('action', 'delete');
                fd.append('target', 'matters');
                fd.append('id', tr.dataset.id);
                fetch(MATTERS, { method: 'POST', body: fd }).then(r => r.text()).then(res => {
                    if (res.trim() !== 'success') return;
                    tr.remove();
                    if (!document.querySelector('#matters-tbody tr')) document.getElementById('matters-tbody').innerHTML = '<tr id="matters-empty-row"><td colspan="6" style="text-align:center;padding:24px;color:var(--text-gray);">No legal matters added yet</td></tr>';
                });
            });
            return tr;
        }

        fetch(MATTERS + '&action=fetch').then(r => r.json()).then(rows => {
            if (!rows.length) return;
            const emptyRow = document.getElementById('matters-empty-row');
            if (emptyRow) emptyRow.remove();
            rows.forEach(m => document.getElementById('matters-tbody').appendChild(buildMatterRow(m)));
        });

        document.getElementById('matters-add-btn').addEventListener('click', () => {
            mtrModal.querySelector('article').classList.add('popup-enter');
            mtrModal.showModal();
        });

        mtrModal.querySelector('#m-cancel').addEventListener('click', () => mtrModal.close());

        mtrModal.querySelector('#m-save').addEventListener('click', () => {
            const fields = ['m-title', 'm-type', 'm-assigned'].map(id => mtrModal.querySelector(`#${id}`));
            const empty = fields.filter(f => !f.value.trim());
            if (empty.length) {
                empty.forEach(f => { f.classList.add('error', 'shake'); f.addEventListener('animationend', () => f.classList.remove('shake'), { once: true }); });
                return;
            }
            const fd = new FormData();
            fd.append('action', 'add');
            fd.append('target', 'matters');
            fd.append('title', fields[0].value.trim());
            fd.append('type', fields[1].value.trim());
            fd.append('assigned_to', fields[2].value.trim());
            fetch(MATTERS, { method: 'POST', body: fd }).then(r => r.text()).then(res => {
                if (res.trim() !== 'success') return;
                fetch(MATTERS + '&action=fetch').then(r => r.json()).then(rows => {
                    const tbody = document.getElementById('matters-tbody');
                    const emptyRow = document.getElementById('matters-empty-row');
                    if (emptyRow) emptyRow.remove();
                    tbody.innerHTML = '';
                    rows.forEach(m => tbody.appendChild(buildMatterRow(m)));
                });
                fields.forEach(f => { f.value = ''; f.classList.remove('error'); });
                mtrModal.close();
            });
        });

        mtrModal.addEventListener('click', e => { if (e.target === mtrModal) mtrModal.close(); });
    })();

    (function () {
        const FUNCS = 'documentfuncs.php';
        const auditLog = [];

        function statusBadge(status) {
            const map = { pending: 'status-leave', approved: 'status-active', archived: 'status-dropped' };
            const label = status.charAt(0).toUpperCase() + status.slice(1);
            return `<mark class="status-badge ${map[status] || ''}">${label}</mark>`;
        }

        function addAuditRow(docTitle, action, by) {
            const emptyRow = document.getElementById('audit-empty-row');
            if (emptyRow) emptyRow.remove();
            const tbody = document.getElementById('audit-tbody');
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${docTitle}</td><td>${action}</td><td>${by}</td><td>${new Date().toLocaleString()}</td>`;
            tbody.prepend(tr);
        }

        function buildRow(doc) {
            const tr = document.createElement('tr');
            tr.dataset.id = doc.id;
            tr.dataset.title = doc.title;
            tr.dataset.by = doc.uploaded_by;
            tr.dataset.status = doc.status;
            const date = new Date(doc.uploaded_at).toLocaleDateString();
            tr.innerHTML = `
                <td><a href="../${doc.file_path}" target="_blank" style="color:var(--active-blue);font-weight:600;">${doc.title}</a><br><small style="color:var(--text-gray);">${doc.file_name}</small></td>
                <td>${doc.category}</td>
                <td>${statusBadge(doc.status)}</td>
                <td>${doc.uploaded_by}</td>
                <td>${date}</td>
                <td style="text-align:center;">
                    <button class="action-btn btn-approve" title="Approve" style="${doc.status === 'approved' ? 'display:none;' : ''}"><i class="fa-solid fa-check"></i> Approve</button>
                    <button class="action-btn btn-archive" title="Archive" style="${doc.status === 'archived' ? 'display:none;' : ''}"><i class="fa-solid fa-box-archive"></i> Archive</button>
                    <button class="action-btn btn-restore" title="Restore" style="${doc.status !== 'archived' ? 'display:none;' : ''}"><i class="fa-solid fa-rotate-left"></i> Restore</button>
                </td>`;

            tr.querySelector('.btn-approve').addEventListener('click', () => docAction(tr, 'approve'));
            tr.querySelector('.btn-archive').addEventListener('click', () => docAction(tr, 'archive'));
            tr.querySelector('.btn-restore').addEventListener('click', () => docAction(tr, 'restore'));
            return tr;
        }

        function docAction(tr, action) {
            const fd = new FormData();
            fd.append('action', action);
            fd.append('id', tr.dataset.id);
            fetch(FUNCS, { method: 'POST', body: fd })
                .then(r => r.text()).then(res => {
                    if (res.trim() !== 'success') return;
                    const map = { approve: 'approved', archive: 'archived', restore: 'pending' };
                    const newStatus = map[action];
                    tr.dataset.status = newStatus;
                    tr.querySelector('td:nth-child(3)').innerHTML = statusBadge(newStatus);
                    tr.querySelector('.btn-approve').style.display = newStatus === 'approved' ? 'none' : '';
                    tr.querySelector('.btn-archive').style.display = newStatus === 'archived' ? 'none' : '';
                    tr.querySelector('.btn-restore').style.display = newStatus === 'archived' ? '' : 'none';
                    addAuditRow(tr.dataset.title, action.charAt(0).toUpperCase() + action.slice(1), tr.dataset.by);
                });
        }

        function loadDocuments() {
            fetch(FUNCS + '?action=fetch')
                .then(r => r.json()).then(docs => {
                    const tbody = document.getElementById('documents-tbody');
                    const emptyRow = document.getElementById('documents-empty-row');
                    if (!docs.length) return;
                    if (emptyRow) emptyRow.remove();
                    docs.forEach(doc => tbody.appendChild(buildRow(doc)));
                });
        }

        loadDocuments();

        const docModal = document.createElement('dialog');
        docModal.id = 'doc-modal';
        docModal.style.cssText = 'border:none;border-radius:12px;padding:0;width:480px;box-shadow:0 4px 20px rgba(0,0,0,0.2);position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);margin:0;';
        docModal.innerHTML = `
            <article class="modal-card" style="width:100%;box-sizing:border-box;">
                <header class="page-header" style="margin-bottom:20px;padding-bottom:15px;border-bottom:1px solid #e5e7eb;">
                    <h2 style="font-size:1.1rem;font-weight:600;color:var(--text-dark);">Upload Document</h2>
                </header>
                <section class="form-group">
                    <label class="form-label">Title</label>
                    <input id="doc-title" class="form-input" type="text" placeholder="Document title">
                </section>
                <section class="form-group">
                    <label class="form-label">Category</label>
                    <select id="doc-category" class="form-input">
                        <option value="" disabled selected>Select category</option>
                        <option>Policy</option>
                        <option>Contract</option>
                        <option>Report</option>
                        <option>Memo</option>
                        <option>Other</option>
                    </select>
                </section>
                <section class="form-group">
                    <label class="form-label">Uploaded By</label>
                    <input id="doc-uploader" class="form-input" type="text" placeholder="Your name" value="Admin">
                </section>
                <section class="form-group">
                    <label class="form-label">File</label>
                    <input id="doc-file" class="form-input" type="file">
                </section>
                <footer class="btn-footer" style="margin-top:20px;">
                    <button id="doc-cancel" class="btn btn-secondary">Cancel</button>
                    <button id="doc-save" class="btn btn-primary"><i class="fa-solid fa-upload"></i> Upload</button>
                </footer>
            </article>`;
        document.body.appendChild(docModal);

        document.getElementById('documents-add-btn').addEventListener('click', () => {
            docModal.querySelector('article').classList.add('popup-enter');
            docModal.showModal();
        });

        docModal.querySelector('#doc-cancel').addEventListener('click', () => docModal.close());

        docModal.querySelector('#doc-save').addEventListener('click', () => {
            const title = docModal.querySelector('#doc-title');
            const category = docModal.querySelector('#doc-category');
            const uploader = docModal.querySelector('#doc-uploader');
            const file = docModal.querySelector('#doc-file');
            const fields = [title, category, uploader];
            const empty = fields.filter(f => !f.value.trim());
            if (empty.length || !file.files.length) {
                empty.forEach(f => { f.classList.add('error', 'shake'); f.addEventListener('animationend', () => f.classList.remove('shake'), { once: true }); });
                if (!file.files.length) { file.classList.add('error', 'shake'); file.addEventListener('animationend', () => file.classList.remove('shake'), { once: true }); }
                return;
            }
            const fd = new FormData();
            fd.append('action', 'upload');
            fd.append('title', title.value.trim());
            fd.append('category', category.value);
            fd.append('uploaded_by', uploader.value.trim());
            fd.append('file', file.files[0]);
            fetch(FUNCS, { method: 'POST', body: fd })
                .then(r => r.text()).then(res => {
                    if (res.trim() !== 'success') return;
                    const emptyRow = document.getElementById('documents-empty-row');
                    if (emptyRow) emptyRow.remove();
                    fetch(FUNCS + '?action=fetch')
                        .then(r => r.json()).then(docs => {
                            if (!docs.length) return;
                            const tbody = document.getElementById('documents-tbody');
                            tbody.innerHTML = '';
                            docs.forEach(doc => tbody.appendChild(buildRow(doc)));
                        });
                    addAuditRow(title.value.trim(), 'Uploaded', uploader.value.trim());
                    fields.forEach(f => { f.value = ''; f.classList.remove('error'); });
                    file.value = '';
                    docModal.close();
                });
        });

        docModal.addEventListener('click', e => { if (e.target === docModal) docModal.close(); });
    })();
</script>
<script>
    fetch('dashboardfetch.php')
        .then(r => r.json())
        .then(d => {
            document.getElementById('dash-total-visitors').textContent = d.visitors;
            document.getElementById('dash-total-documents').textContent = d.documents;
            document.getElementById('dash-total-reservations').textContent = d.reservations;
            document.getElementById('dash-total-contracts').textContent = d.contracts;

            if (d.recent_visitors.length) {
                const tb = document.getElementById('dash-visitors-tbody');
                tb.innerHTML = '';
                d.recent_visitors.forEach(v => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${v.name}</td><td>${v.date}</td><td>${v.reason}</td>`;
                    tb.appendChild(tr);
                });
            }

            if (d.pending_documents.length) {
                const tb = document.getElementById('dash-documents-tbody');
                tb.innerHTML = '';
                d.pending_documents.forEach(doc => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${doc.title}</td><td>${doc.category}</td><td><span class="status-badge status-${doc.status}">${doc.status.charAt(0).toUpperCase() + doc.status.slice(1)}</span></td>`;
                    tb.appendChild(tr);
                });
            }
        });
</script>
</body>
</html>