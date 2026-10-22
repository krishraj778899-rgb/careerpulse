<?php
require_once __DIR__ . '/config/db.php';
ensureDatabaseReady();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CareerPulse - Modern Job &amp; Internship Tracker Platform</title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
  
  <!-- CSS Stylesheet -->
  <link rel="stylesheet" href="assets/css/style.css">
  
  <!-- Chart.js from CDN for interactive data visualization -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>

  <!-- Navigation Bar -->
  <header class="navbar">
    <div class="container nav-inner">
      <a href="javascript:void(0)" class="nav-logo" onclick="App.switchTab('home')">
        <svg viewBox="0 0 540 120" style="height:44px; width:auto;">
          <defs>
            <linearGradient id="nbLogoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#2563EB" />
              <stop offset="100%" stop-color="#06B6D4" />
            </linearGradient>
          </defs>
          <rect x="15" y="10" width="100" height="100" rx="26" fill="url(#nbLogoGrad)" />
          <path d="M 37 72 L 49 72 L 57 46 L 65 82 L 73 60 L 81 72 L 93 72" fill="none" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
          <text x="135" y="82" font-family="system-ui, sans-serif" font-size="52" font-weight="800" fill="currentColor">Career<tspan fill="#2563EB">Pulse</tspan></text>
        </svg>
      </a>

      <!-- Dynamic navigation links based on user role -->
      <nav aria-label="Main Navigation">
        <ul class="nav-links" id="nav-links">
          <li><a href="javascript:void(0)" class="nav-link active" onclick="App.switchTab('home')">Home</a></li>
          <li><a href="javascript:void(0)" class="nav-link" onclick="App.switchTab('portal')">Browse Drives</a></li>
          <li><a href="javascript:void(0)" class="nav-link" onclick="App.switchTab('stats')">Placement Stats</a></li>
        </ul>
      </nav>

      <!-- Actions (Theme Toggle, Notifications, Profile Pill, Auth Buttons) -->
      <div class="nav-actions">
        <button type="button" class="theme-toggle-btn" id="theme-toggle-btn" title="Toggle Light/Dark Theme" aria-label="Toggle Light/Dark Theme">
          <span id="theme-icon">
            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
          </span>
        </button>
        <div id="nav-actions" class="flex items-center gap-2">
          <!-- Populated dynamically by App.renderNav() -->
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content Wrapper -->
  <main class="container" style="flex:1; padding-top:1.75rem; padding-bottom:3rem;">

    <!-- ==================== SECTION 1: PUBLIC HOME / LANDING ==================== -->
    <section id="section-home" class="app-section">
      <div class="hero">
        <div class="hero-badge">Next-Generation Campus Recruitment &amp; Tracking Platform</div>
        <h1 class="hero-title">Track Applications.<br><span class="gradient-text">Accelerate Your Dream Career.</span></h1>
        <p class="hero-desc">
          CareerPulse unifies students, company recruiters, and university administration into a synchronized, high-performance ecosystem. Featuring real-time eligibility checks, an interactive Kanban board, and intelligent ATS resume match scoring.
        </p>
        <div class="hero-actions">
          <button type="button" class="btn btn-primary btn-lg" onclick="App.switchTab('portal')">Explore Open Vacancies &rarr;</button>
          <button type="button" class="btn btn-secondary btn-lg" onclick="App.openModal('register-modal')">Register as Student / Company</button>
        </div>
      </div>

      <!-- Feature Highlight Cards -->
      <div class="stats-grid" style="margin-top:2.5rem;">
        <div class="stat-card">
          <div>
            <div class="stat-label">For Students</div>
            <h2 style="font-size:1.15rem; font-weight:700; margin:0.4rem 0;">Drag-and-Drop Kanban</h2>
            <p style="font-size:0.85rem; color:var(--text-secondary);">Manage applications from Wishlist to Offer with automated follow-up reminders and interview prep notes.</p>
          </div>
          <div class="stat-icon" style="background:var(--primary-light); color:var(--primary);">
            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          </div>
        </div>

        <div class="stat-card">
          <div>
            <div class="stat-label">For Students</div>
            <h2 style="font-size:1.15rem; font-weight:700; margin:0.4rem 0;">Eligibility &amp; ATS Matcher</h2>
            <p style="font-size:0.85rem; color:var(--text-secondary);">Instantly verify your CGPA and branch eligibility, and compare your skillset against job descriptions.</p>
          </div>
          <div class="stat-icon" style="background:var(--success-light); color:var(--success);">
            <svg class="svg-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
          </div>
        </div>

        <div class="stat-card">
          <div>
            <div class="stat-label">For Recruiters</div>
            <h2 style="font-size:1.15rem; font-weight:700; margin:0.4rem 0;">Talent Pipeline Management</h2>
            <p style="font-size:0.85rem; color:var(--text-secondary);">Post verified campus drives, inspect candidate resumes, shortlist, and schedule interviews with 1-click.</p>
          </div>
          <div class="stat-icon" style="background:var(--purple-light); color:var(--purple);">
            <svg class="svg-icon" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="9" y1="22" x2="9" y2="22.01"/><line x1="15" y1="22" x2="15" y2="22.01"/><line x1="8" y1="6" x2="8" y2="6.01"/><line x1="16" y1="6" x2="16" y2="6.01"/><line x1="8" y1="10" x2="8" y2="10.01"/><line x1="16" y1="10" x2="16" y2="10.01"/><line x1="8" y1="14" x2="8" y2="14.01"/><line x1="16" y1="14" x2="16" y2="14.01"/></svg>
          </div>
        </div>

        <div class="stat-card">
          <div>
            <div class="stat-label">For Administrators</div>
            <h2 style="font-size:1.15rem; font-weight:700; margin:0.4rem 0;">Analytics &amp; Moderation</h2>
            <p style="font-size:0.85rem; color:var(--text-secondary);">Real-time placement percentages by academic branch, drive moderation queue, and system audit logs.</p>
          </div>
          <div class="stat-icon" style="background:var(--warning-light); color:var(--warning);">
            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== SECTION 2: JOB & INTERNSHIP PORTAL ==================== -->
    <section id="section-portal" class="app-section" style="display:none;">
      <div class="flex items-center justify-between flex-wrap gap-4" style="margin-bottom:1.5rem;">
        <div>
          <h2 style="font-size:1.75rem; font-weight:800;">Campus Hiring Drives &amp; Internships</h2>
          <p style="color:var(--text-secondary); font-size:0.95rem;">Browse approved career opportunities. Automatic eligibility validation active.</p>
        </div>
        <div class="flex gap-2">
          <button type="button" class="btn btn-outline" onclick="App.openModal('keyword-matcher-modal')">
            <svg class="svg-icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            ATS Resume Matcher
          </button>
        </div>
      </div>

      <!-- Search & Filter Controls -->
      <div style="background:var(--bg-surface); padding:1.25rem; border-radius:var(--radius-lg); border:1px solid var(--border-color); margin-bottom:1.75rem; box-shadow:var(--shadow-sm);">
        <div class="grid grid-4 gap-3">
          <div>
            <label for="job-search-input" class="form-label">Search Keywords</label>
            <input type="text" id="job-search-input" class="form-control" placeholder="Role, company, skill (e.g. React)..." oninput="Student.loadJobs()">
          </div>
          <div>
            <label for="job-category-filter" class="form-label">Category</label>
            <select id="job-category-filter" class="form-control" onchange="Student.loadJobs()">
              <option value="All">All Categories</option>
              <option value="Software Engineering">Software Engineering</option>
              <option value="Cloud & DevOps">Cloud &amp; DevOps</option>
              <option value="Data Science & Analytics">Data Science &amp; Analytics</option>
              <option value="Artificial Intelligence">Artificial Intelligence</option>
              <option value="Design & Frontend">Design &amp; Frontend</option>
              <option value="Cybersecurity">Cybersecurity</option>
            </select>
          </div>
          <div>
            <label for="job-type-filter" class="form-label">Opportunity Type</label>
            <select id="job-type-filter" class="form-control" onchange="Student.loadJobs()">
              <option value="All">All Types</option>
              <option value="Full-time">Full-time</option>
              <option value="Internship">Internship</option>
              <option value="Contract">Contract</option>
            </select>
          </div>
          <div>
            <label for="job-workplace-filter" class="form-label">Workplace</label>
            <select id="job-workplace-filter" class="form-control" onchange="Student.loadJobs()">
              <option value="All">All Workplaces</option>
              <option value="Hybrid">Hybrid</option>
              <option value="Remote">Remote</option>
              <option value="On-site">On-site</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Jobs Grid -->
      <div class="jobs-grid" id="jobs-grid-container">
        <!-- Rendered by Student.renderJobsList() -->
      </div>
    </section>

    <!-- ==================== SECTION 3: STUDENT DASHBOARD ==================== -->
    <section id="section-student-dashboard" class="app-section" style="display:none;">
      <div class="flex items-center justify-between flex-wrap gap-4" style="margin-bottom:1.5rem;">
        <div>
          <h2 style="font-size:1.75rem; font-weight:800;">Student Performance Dashboard</h2>
          <p style="color:var(--text-secondary); font-size:0.95rem;">Track your personal application pipeline velocity, conversion rates, and deadlines.</p>
        </div>
        <div class="flex gap-2">
          <button type="button" class="btn btn-outline" onclick="Student.exportCSV()">
            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export CSV
          </button>
          <button type="button" class="btn btn-outline" onclick="Student.exportICS()">
            <svg class="svg-icon" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Export .ICS Calendar
          </button>
          <button type="button" class="btn btn-primary" onclick="App.openModal('add-external-modal')">+ Track External Application</button>
        </div>
      </div>

      <!-- Smart Reminders Alert Banner -->
      <div id="student-reminders-box" class="reminder-box" style="display:none;">
        <!-- Filled by Student.renderSmartReminders() -->
      </div>

      <!-- KPI Stat Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div>
            <div class="stat-label">Applications Submitted</div>
            <div class="stat-val" id="stat-student-total">0</div>
            <div style="font-size:0.8rem; color:var(--text-muted);">Active &amp; historic pipeline</div>
          </div>
          <div class="stat-icon" style="background:var(--primary-light); color:var(--primary);">
            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          </div>
        </div>

        <div class="stat-card">
          <div>
            <div class="stat-label">In Interview Loop</div>
            <div class="stat-val" id="stat-student-interview">0</div>
            <div style="font-size:0.8rem; color:var(--purple); font-weight:600;">Active technical rounds</div>
          </div>
          <div class="stat-icon" style="background:var(--purple-light); color:var(--purple);">
            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
          </div>
        </div>

        <div class="stat-card">
          <div>
            <div class="stat-label">Offers Received</div>
            <div class="stat-val" id="stat-student-offers">0</div>
            <div style="font-size:0.8rem; color:var(--success); font-weight:600;">Extended formal offers</div>
          </div>
          <div class="stat-icon" style="background:var(--success-light); color:var(--success);">
            <svg class="svg-icon" viewBox="0 0 24 24"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
          </div>
        </div>

        <div class="stat-card">
          <div>
            <div class="stat-label">Offer Conversion Rate</div>
            <div class="stat-val" id="stat-student-rate">0%</div>
            <div style="font-size:0.8rem; color:var(--text-muted);">Offers vs total applied</div>
          </div>
          <div class="stat-icon" style="background:var(--accent-cyan-light); color:var(--accent-cyan);">
            <svg class="svg-icon" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-2 gap-4" style="margin-bottom:2rem;">
        <div style="background:var(--bg-surface); padding:1.4rem; border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:var(--shadow-sm);">
          <h3 style="font-size:1.1rem; font-weight:700; margin-bottom:1rem;">Application Status Distribution</h3>
          <div style="height:250px; position:relative;">
            <canvas id="student-status-chart"></canvas>
          </div>
        </div>

        <div style="background:var(--bg-surface); padding:1.4rem; border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:var(--shadow-sm);">
          <h3 style="font-size:1.1rem; font-weight:700; margin-bottom:1rem;">Hiring Stage Funnel</h3>
          <div style="height:250px; position:relative;">
            <canvas id="student-funnel-chart"></canvas>
          </div>
        </div>
      </div>

      <!-- Quick Link to Kanban -->
      <div style="text-align:center; padding:1.5rem; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg);">
        <h3 style="font-size:1.15rem; font-weight:700;">Ready to organize your workflow?</h3>
        <p style="color:var(--text-secondary); margin:0.4rem 0 1rem;">Drag and drop cards between status columns, inspect interview notes, and review your status timeline.</p>
        <button type="button" class="btn btn-primary" onclick="App.switchTab('tracker')">Open Full Kanban Board &rarr;</button>
      </div>
    </section>

    <!-- ==================== SECTION 4: KANBAN TRACKER ==================== -->
    <section id="section-tracker" class="app-section" style="display:none;">
      <div class="flex items-center justify-between flex-wrap gap-4" style="margin-bottom:1.5rem;">
        <div>
          <h2 style="font-size:1.75rem; font-weight:800;">Interactive Application Kanban Board</h2>
          <p style="color:var(--text-secondary); font-size:0.95rem;">Drag cards across columns to update application stages in real time.</p>
        </div>
        <div class="flex gap-2">
          <button type="button" class="btn btn-outline btn-sm" onclick="Student.exportCSV()">
            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            CSV
          </button>
          <button type="button" class="btn btn-outline btn-sm" onclick="Student.exportICS()">
            <svg class="svg-icon" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Calendar (.ICS)
          </button>
          <button type="button" class="btn btn-primary btn-sm" onclick="App.openModal('add-external-modal')">+ Add Off-Campus App</button>
        </div>
      </div>

      <!-- 6 Status Kanban Columns -->
      <div class="kanban-board">
        <!-- 1. Wishlist -->
        <div class="kanban-column">
          <div class="kanban-header">
            <span>Wishlist / Saved</span>
            <span class="kanban-col-count" id="kanban-count-wishlist">0</span>
          </div>
          <div class="kanban-cards" id="kanban-col-wishlist" ondragover="Student.handleDragOver(event)" ondragleave="Student.handleDragLeave(event)" ondrop="Student.handleDrop(event, 'Wishlist')"></div>
        </div>

        <!-- 2. Applied -->
        <div class="kanban-column">
          <div class="kanban-header">
            <span>Applied</span>
            <span class="kanban-col-count" id="kanban-count-applied">0</span>
          </div>
          <div class="kanban-cards" id="kanban-col-applied" ondragover="Student.handleDragOver(event)" ondragleave="Student.handleDragLeave(event)" ondrop="Student.handleDrop(event, 'Applied')"></div>
        </div>

        <!-- 3. Screening -->
        <div class="kanban-column">
          <div class="kanban-header">
            <span>Screening / OA</span>
            <span class="kanban-col-count" id="kanban-count-screening">0</span>
          </div>
          <div class="kanban-cards" id="kanban-col-screening" ondragover="Student.handleDragOver(event)" ondragleave="Student.handleDragLeave(event)" ondrop="Student.handleDrop(event, 'Screening')"></div>
        </div>

        <!-- 4. Interviewing -->
        <div class="kanban-column">
          <div class="kanban-header">
            <span>Interviewing</span>
            <span class="kanban-col-count" id="kanban-count-interviewing">0</span>
          </div>
          <div class="kanban-cards" id="kanban-col-interviewing" ondragover="Student.handleDragOver(event)" ondragleave="Student.handleDragLeave(event)" ondrop="Student.handleDrop(event, 'Interviewing')"></div>
        </div>

        <!-- 5. Offered -->
        <div class="kanban-column">
          <div class="kanban-header">
            <span>Offered</span>
            <span class="kanban-col-count" id="kanban-count-offered">0</span>
          </div>
          <div class="kanban-cards" id="kanban-col-offered" ondragover="Student.handleDragOver(event)" ondragleave="Student.handleDragLeave(event)" ondrop="Student.handleDrop(event, 'Offered')"></div>
        </div>

        <!-- 6. Rejected -->
        <div class="kanban-column">
          <div class="kanban-header">
            <span>Archived / Rejected</span>
            <span class="kanban-col-count" id="kanban-count-rejected">0</span>
          </div>
          <div class="kanban-cards" id="kanban-col-rejected" ondragover="Student.handleDragOver(event)" ondragleave="Student.handleDragLeave(event)" ondrop="Student.handleDrop(event, 'Rejected')"></div>
        </div>
      </div>
    </section>

    <!-- ==================== SECTION 5: CALENDAR VIEW ==================== -->
    <section id="section-calendar" class="app-section" style="display:none;">
      <div class="flex items-center justify-between flex-wrap gap-4" style="margin-bottom:1.5rem;">
        <div>
          <h2 style="font-size:1.75rem; font-weight:800;">Interview &amp; Deadline Schedule</h2>
          <p style="color:var(--text-secondary); font-size:0.95rem;">Chronological schedule of technical rounds and upcoming campus drive deadlines.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="Student.exportICS()">
          <svg class="svg-icon" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Add to Google Calendar (.ICS)
        </button>
      </div>

      <div id="calendar-events-list">
        <!-- Rendered by Student.loadCalendar() -->
      </div>
    </section>

    <!-- ==================== SECTION 6: COMPANY DASHBOARD ==================== -->
    <section id="section-company-dashboard" class="app-section" style="display:none;">
      <div class="flex items-center justify-between flex-wrap gap-4" style="margin-bottom:1.5rem;">
        <div>
          <h2 style="font-size:1.75rem; font-weight:800;">Recruiter Management Console</h2>
          <p style="color:var(--text-secondary); font-size:0.95rem;">Manage campus vacancies, inspect student candidate resumes, and extend interview invites.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="App.openModal('post-job-modal')">+ Post New Vacancy</button>
      </div>

      <!-- Recruiter KPI Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div>
            <div class="stat-label">Active Vacancies</div>
            <div class="stat-val" id="stat-comp-vacancies">0</div>
            <div style="font-size:0.8rem; color:var(--text-muted);">Published campus drives</div>
          </div>
          <div class="stat-icon" style="background:var(--purple-light); color:var(--purple);">
            <svg class="svg-icon" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
          </div>
        </div>

        <div class="stat-card">
          <div>
            <div class="stat-label">Total Applicants</div>
            <div class="stat-val" id="stat-comp-applicants">0</div>
            <div style="font-size:0.8rem; color:var(--primary); font-weight:600;">Student submissions</div>
          </div>
          <div class="stat-icon" style="background:var(--primary-light); color:var(--primary);">
            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
        </div>

        <div class="stat-card">
          <div>
            <div class="stat-label">In Interview Loop</div>
            <div class="stat-val" id="stat-comp-interview">0</div>
            <div style="font-size:0.8rem; color:var(--warning); font-weight:600;">Shortlisted candidates</div>
          </div>
          <div class="stat-icon" style="background:var(--warning-light); color:var(--warning);">
            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
          </div>
        </div>

        <div class="stat-card">
          <div>
            <div class="stat-label">Offers Extended</div>
            <div class="stat-val" id="stat-comp-offers">0</div>
            <div style="font-size:0.8rem; color:var(--success); font-weight:600;">Selected campus hires</div>
          </div>
          <div class="stat-icon" style="background:var(--success-light); color:var(--success);">
            <svg class="svg-icon" viewBox="0 0 24 24"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
          </div>
        </div>
      </div>

      <!-- Pipeline Chart -->
      <div style="background:var(--bg-surface); padding:1.4rem; border-radius:var(--radius-lg); border:1px solid var(--border-color); margin-bottom:2rem; box-shadow:var(--shadow-sm);">
        <h3 style="font-size:1.1rem; font-weight:700; margin-bottom:1rem;">Candidate Hiring Funnel</h3>
        <div style="height:220px; position:relative;">
          <canvas id="company-funnel-chart"></canvas>
        </div>
      </div>

      <!-- Drives Table -->
      <div class="table-responsive">
        <div style="padding:1rem 1.25rem; font-weight:700; border-bottom:1px solid var(--border-color); font-size:1.05rem;">
          Your Active Campus Postings
        </div>
        <table class="table">
          <thead>
            <tr>
              <th>Role Title</th>
              <th>Location</th>
              <th>Compensation</th>
              <th>Eligibility</th>
              <th>Deadline</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="company-drives-tbody">
            <!-- Rendered by Company.renderDrivesTable() -->
          </tbody>
        </table>
      </div>
    </section>

    <!-- ==================== SECTION 7: COMPANY MANAGE DRIVES ==================== -->
    <section id="section-manage-drives" class="app-section" style="display:none;">
      <!-- Handled within Company Dashboard view -->
    </section>

    <!-- ==================== SECTION 8: COMPANY APPLICANTS REVIEW ==================== -->
    <section id="section-applicants" class="app-section" style="display:none;">
      <div class="flex items-center justify-between flex-wrap gap-4" style="margin-bottom:1.5rem;">
        <div>
          <h2 style="font-size:1.75rem; font-weight:800;">Candidate Review Center</h2>
          <p style="color:var(--text-secondary); font-size:0.95rem;">Filter applicants by drive, review GPA, view attached resumes, and update recruitment status.</p>
        </div>
        <div>
          <label for="applicant-drive-select" class="form-label">Select Job Drive</label>
          <select id="applicant-drive-select" class="form-control" style="min-width:280px;" onchange="Company.viewDriveApplicants(this.value)">
            <!-- Populated dynamically -->
          </select>
        </div>
      </div>

      <div id="applicants-list-container">
        <!-- Rendered by Company.renderApplicantsList() -->
      </div>
    </section>

    <!-- ==================== SECTION 9: ADMIN DASHBOARD ==================== -->
    <section id="section-admin-dashboard" class="app-section" style="display:none;">
      <div class="flex items-center justify-between flex-wrap gap-4" style="margin-bottom:1.5rem;">
        <div>
          <h2 style="font-size:1.75rem; font-weight:800;">University Placement Administration Control Panel</h2>
          <p style="color:var(--text-secondary); font-size:0.95rem;">Full visibility into student placement performance, recruiter activities, and campus drives.</p>
        </div>
        <div class="flex gap-2">
          <button type="button" class="btn btn-outline" onclick="App.switchTab('admin-moderation')">
            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            Drive Moderation Queue
          </button>
          <button type="button" class="btn btn-outline" onclick="App.switchTab('admin-users')">
            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            User Accounts
          </button>
          <button type="button" class="btn btn-primary" onclick="App.switchTab('stats')">
            <svg class="svg-icon" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            Public Placement Page
          </button>
        </div>
      </div>

      <!-- Pending Moderation Alert -->
      <div id="admin-pending-alert" class="reminder-box" style="display:none; background:var(--warning-light); border-left-color:var(--warning);">
        <div style="display:flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:50%; background:var(--warning-light); color:var(--warning);">
          <svg class="svg-icon" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <div style="flex:1;">
          <strong>Moderation Notice:</strong> There are <span id="admin-pending-count-text" style="font-weight:800;">0</span> company hiring drives waiting for administrative review and approval.
        </div>
        <button type="button" class="btn btn-sm btn-primary" onclick="App.switchTab('admin-moderation')">Review Now</button>
      </div>

      <!-- Admin Core Metrics -->
      <div class="stats-grid">
        <div class="stat-card">
          <div>
            <div class="stat-label">Registered Students</div>
            <div class="stat-val" id="stat-admin-students">0</div>
            <div style="font-size:0.8rem; color:var(--text-muted);">Active candidate pool</div>
          </div>
          <div class="stat-icon" style="background:var(--primary-light); color:var(--primary);">
            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          </div>
        </div>

        <div class="stat-card">
          <div>
            <div class="stat-label">Recruiting Companies</div>
            <div class="stat-val" id="stat-admin-companies">0</div>
            <div style="font-size:0.8rem; color:var(--purple); font-weight:600;">Partner corporate accounts</div>
          </div>
          <div class="stat-icon" style="background:var(--purple-light); color:var(--purple);">
            <svg class="svg-icon" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="9" y1="22" x2="9" y2="22.01"/><line x1="15" y1="22" x2="15" y2="22.01"/><line x1="8" y1="6" x2="8" y2="6.01"/><line x1="16" y1="6" x2="16" y2="6.01"/><line x1="8" y1="10" x2="8" y2="10.01"/><line x1="16" y1="10" x2="16" y2="10.01"/><line x1="8" y1="14" x2="8" y2="14.01"/><line x1="16" y1="14" x2="16" y2="14.01"/></svg>
          </div>
        </div>

        <div class="stat-card">
          <div>
            <div class="stat-label">Active Drives</div>
            <div class="stat-val" id="stat-admin-jobs">0</div>
            <div style="font-size:0.8rem; color:var(--success); font-weight:600;"><span id="stat-admin-pending">0</span> pending review</div>
          </div>
          <div class="stat-icon" style="background:var(--success-light); color:var(--success);">
            <svg class="svg-icon" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
          </div>
        </div>

        <div class="stat-card">
          <div>
            <div class="stat-label">Total Applications</div>
            <div class="stat-val" id="stat-admin-applications">0</div>
            <div style="font-size:0.8rem; color:var(--text-muted);"><span id="stat-admin-offers">0</span> offers extended</div>
          </div>
          <div class="stat-icon" style="background:var(--accent-cyan-light); color:var(--accent-cyan);">
            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          </div>
        </div>

        <div class="stat-card">
          <div>
            <div class="stat-label">Campus Placement Rate</div>
            <div class="stat-val" id="stat-admin-placement-rate" style="color:var(--primary);">0%</div>
            <div style="font-size:0.8rem; color:var(--success); font-weight:600;">Placed vs Total registered</div>
          </div>
          <div class="stat-icon" style="background:var(--primary-light); color:var(--primary);">
            <svg class="svg-icon" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
          </div>
        </div>
      </div>

      <!-- Branch Distribution Chart -->
      <div style="background:var(--bg-surface); padding:1.4rem; border-radius:var(--radius-lg); border:1px solid var(--border-color); margin-bottom:2rem; box-shadow:var(--shadow-sm);">
        <h3 style="font-size:1.15rem; font-weight:700; margin-bottom:1rem;">Placement Percentage by Academic Branch</h3>
        <div style="height:280px; position:relative;">
          <canvas id="admin-branch-chart"></canvas>
        </div>
      </div>

      <!-- Grid: Top Recruiters & Popular Drives -->
      <div class="grid grid-2 gap-4" style="margin-bottom:2rem;">
        <div class="table-responsive">
          <div style="padding:1rem 1.25rem; font-weight:700; border-bottom:1px solid var(--border-color); font-size:1rem;">
            Top Recruiting Corporate Partners
          </div>
          <table class="table">
            <thead>
              <tr>
                <th>Recruiter</th>
                <th>Drives</th>
                <th>Applications</th>
                <th>Hires</th>
              </tr>
            </thead>
            <tbody id="admin-recruiters-tbody"></tbody>
          </table>
        </div>

        <div class="table-responsive">
          <div style="padding:1rem 1.25rem; font-weight:700; border-bottom:1px solid var(--border-color); font-size:1rem;">
            Hiring Drives Volume
          </div>
          <table class="table">
            <thead>
              <tr>
                <th>Drive Title</th>
                <th>Deadline</th>
                <th>Submissions</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody id="admin-drives-tbody"></tbody>
          </table>
        </div>
      </div>

      <!-- Recent Audit Logs Feed -->
      <div style="background:var(--bg-surface); padding:1.4rem; border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:var(--shadow-sm);">
        <div class="flex items-center justify-between" style="margin-bottom:1rem;">
          <h3 style="font-size:1.1rem; font-weight:700;">Live System Audit Trail</h3>
          <button type="button" class="btn btn-sm btn-outline" onclick="App.switchTab('admin-logs')">View All Logs</button>
        </div>
        <div id="admin-recent-logs"></div>
      </div>
    </section>

    <!-- ==================== SECTION 10: ADMIN MODERATION QUEUE ==================== -->
    <section id="section-admin-moderation" class="app-section" style="display:none;">
      <div class="flex items-center justify-between flex-wrap gap-4" style="margin-bottom:1.5rem;">
        <div>
          <h2 style="font-size:1.75rem; font-weight:800;">Campus Drive Moderation Queue</h2>
          <p style="color:var(--text-secondary); font-size:0.95rem;">Review company job and internship postings before they become visible to students.</p>
        </div>
        <button type="button" class="btn btn-secondary" onclick="App.switchTab('admin-dashboard')">&larr; Back to Admin Dashboard</button>
      </div>

      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Position Title</th>
              <th>Company</th>
              <th>Compensation</th>
              <th>Eligibility</th>
              <th>Deadline</th>
              <th>Review Actions</th>
            </tr>
          </thead>
          <tbody id="admin-pending-tbody">
            <!-- Rendered by Admin.renderPendingTable() -->
          </tbody>
        </table>
      </div>
    </section>

    <!-- ==================== SECTION 11: ADMIN USER ACCOUNTS ==================== -->
    <section id="section-admin-users" class="app-section" style="display:none;">
      <div class="flex items-center justify-between flex-wrap gap-4" style="margin-bottom:1.5rem;">
        <div>
          <h2 style="font-size:1.75rem; font-weight:800;">User &amp; Organization Management</h2>
          <p style="color:var(--text-secondary); font-size:0.95rem;">Monitor student and company accounts, toggle active status, and verify corporate entities.</p>
        </div>
        <div class="flex gap-2">
          <select id="admin-user-role-filter" class="form-control" style="width:140px;" onchange="Admin.loadUsers()">
            <option value="All">All Roles</option>
            <option value="student">Students</option>
            <option value="company">Companies</option>
          </select>
          <input type="text" id="admin-user-search" class="form-control" placeholder="Search accounts..." oninput="Admin.loadUsers()">
        </div>
      </div>

      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Name &amp; Email</th>
              <th>Role</th>
              <th>Academic / Company Info</th>
              <th>Account Status</th>
              <th>Created</th>
              <th>Moderation</th>
            </tr>
          </thead>
          <tbody id="admin-users-tbody"></tbody>
        </table>
      </div>
    </section>

    <!-- ==================== SECTION 12: ADMIN AUDIT LOGS ==================== -->
    <section id="section-admin-logs" class="app-section" style="display:none;">
      <div class="flex items-center justify-between flex-wrap gap-4" style="margin-bottom:1.5rem;">
        <div>
          <h2 style="font-size:1.75rem; font-weight:800;">System Event &amp; Security Audit Logs</h2>
          <p style="color:var(--text-secondary); font-size:0.95rem;">Immutable logging of platform actions, logins, status changes, and drive approvals.</p>
        </div>
        <button type="button" class="btn btn-secondary" onclick="App.switchTab('admin-dashboard')">&larr; Back to Dashboard</button>
      </div>

      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Action Type</th>
              <th>Originating User</th>
              <th>Event Details</th>
              <th>IP Address</th>
              <th>Timestamp</th>
            </tr>
          </thead>
          <tbody id="admin-logs-tbody"></tbody>
        </table>
      </div>
    </section>

    <!-- ==================== SECTION 13: PUBLIC PLACEMENT STATS ==================== -->
    <section id="section-stats" class="app-section" style="display:none;">
      <div class="hero" style="padding:2.5rem 0 1.5rem;">
        <div class="hero-badge">📊 Institutional Placement Insights</div>
        <h1 class="hero-title" style="font-size:2.5rem;">Campus Placement &amp; Hiring Statistics</h1>
        <p class="hero-desc">Transparent reporting on student outcomes, department placement percentages, and top hiring partners.</p>
      </div>

      <div class="stats-grid" style="margin-bottom:2rem;">
        <div class="stat-card">
          <div>
            <div class="stat-label">Students Placed</div>
            <div class="stat-val" id="pub-stat-placed" style="color:var(--success);">0</div>
            <div style="font-size:0.8rem; color:var(--text-muted);">Unique students receiving offers</div>
          </div>
          <div class="stat-icon" style="background:var(--success-light); color:var(--success);">🎓</div>
        </div>

        <div class="stat-card">
          <div>
            <div class="stat-label">Overall Placement Rate</div>
            <div class="stat-val" id="pub-stat-rate" style="color:var(--primary);">0%</div>
            <div style="font-size:0.8rem; color:var(--text-muted);">Institutional average</div>
          </div>
          <div class="stat-icon" style="background:var(--primary-light); color:var(--primary);">📈</div>
        </div>

        <div class="stat-card">
          <div>
            <div class="stat-label">Approved Campus Drives</div>
            <div class="stat-val" id="pub-stat-drives">0</div>
            <div style="font-size:0.8rem; color:var(--text-muted);">Full-time &amp; Internships</div>
          </div>
          <div class="stat-icon" style="background:var(--purple-light); color:var(--purple);">💼</div>
        </div>

        <div class="stat-card">
          <div>
            <div class="stat-label">Recruiting Companies</div>
            <div class="stat-val" id="pub-stat-companies">0</div>
            <div style="font-size:0.8rem; color:var(--text-muted);">Global &amp; regional employers</div>
          </div>
          <div class="stat-icon" style="background:var(--accent-cyan-light); color:var(--accent-cyan);">🏢</div>
        </div>
      </div>

      <!-- Public Branch Chart -->
      <div style="background:var(--bg-surface); padding:1.4rem; border-radius:var(--radius-lg); border:1px solid var(--border-color); margin-bottom:2rem; box-shadow:var(--shadow-sm);">
        <h3 style="font-size:1.15rem; font-weight:700; margin-bottom:1rem;">Department-wise Placement Percentages</h3>
        <div style="height:280px; position:relative;">
          <canvas id="public-branch-chart"></canvas>
        </div>
      </div>

      <!-- Top Recruiters Cards -->
      <h3 style="font-size:1.25rem; font-weight:800; margin-bottom:1rem;">Leading Corporate Recruiters</h3>
      <div class="grid grid-3 gap-3" id="public-recruiters-grid"></div>
    </section>

  </main>

  <!-- ==================== MODALS ==================== -->

  <!-- 1. Login Modal -->
  <div class="modal-overlay" id="login-modal">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title">Sign In to CareerPulse</h3>
        <button type="button" class="modal-close" onclick="App.closeModal('login-modal')">&times;</button>
      </div>
      <form onsubmit="Auth.handleLoginForm(event)">
        <div class="modal-body">
          <div class="form-group">
            <label for="login-email" class="form-label">Email Address</label>
            <input type="email" id="login-email" name="email" class="form-control" placeholder="alex@university.edu" required>
          </div>
          <div class="form-group">
            <label for="login-password" class="form-label">Password</label>
            <input type="password" id="login-password" name="password" class="form-control" placeholder="••••••••" required>
          </div>
          <div style="font-size:0.85rem; color:var(--text-secondary); margin-top:0.5rem;">
            Tip: You can also use the 1-Click Demo Switcher in the top banner!
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="App.closeModal('login-modal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Sign In &rarr;</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 2. Registration Modal -->
  <div class="modal-overlay" id="register-modal">
    <div class="modal modal-lg">
      <div class="modal-header">
        <h3 class="modal-title">Create CareerPulse Account</h3>
        <button type="button" class="modal-close" onclick="App.closeModal('register-modal')">&times;</button>
      </div>
      <form onsubmit="Auth.handleRegisterForm(event)">
        <div class="modal-body">
          <div class="form-group">
            <label for="reg-role" class="form-label">Registering As</label>
            <select id="reg-role" name="role" class="form-control" onchange="Auth.toggleRoleFields(this.value)">
              <option value="student">Student / Job Seeker</option>
              <option value="company">Corporate Recruiter / Company</option>
            </select>
          </div>

          <div class="grid grid-2 gap-3">
            <div class="form-group">
              <label for="reg-name" class="form-label">Full Name / Contact Name</label>
              <input type="text" id="reg-name" name="name" class="form-control" placeholder="e.g. Jordan Lee" required>
            </div>
            <div class="form-group">
              <label for="reg-email" class="form-label">Official Email</label>
              <input type="email" id="reg-email" name="email" class="form-control" placeholder="e.g. jordan@university.edu" required>
            </div>
          </div>

          <div class="form-group">
            <label for="reg-password" class="form-label">Password (Min 6 characters)</label>
            <input type="password" id="reg-password" name="password" class="form-control" placeholder="••••••••" required>
          </div>

          <!-- Student specific fields -->
          <div id="reg-student-fields">
            <div class="grid grid-3 gap-3">
              <div class="form-group">
                <label for="reg-dept" class="form-label">Department / Branch</label>
                <select id="reg-dept" name="department" class="form-control">
                  <option value="Computer Science">Computer Science</option>
                  <option value="Information Technology">Information Technology</option>
                  <option value="Electronics & Communication">Electronics &amp; Communication</option>
                  <option value="Electrical Engineering">Electrical Engineering</option>
                  <option value="Mechanical Engineering">Mechanical Engineering</option>
                </select>
              </div>
              <div class="form-group">
                <label for="reg-cgpa" class="form-label">Current CGPA (out of 10)</label>
                <input type="number" id="reg-cgpa" name="cgpa" class="form-control" step="0.01" min="0" max="10" value="8.50">
              </div>
              <div class="form-group">
                <label for="reg-grad-year" class="form-label">Graduation Year</label>
                <input type="number" id="reg-grad-year" name="graduation_year" class="form-control" value="2026">
              </div>
            </div>
            <div class="form-group">
              <label for="reg-skills" class="form-label">Key Skills (comma-separated)</label>
              <input type="text" id="reg-skills" name="skills" class="form-control" placeholder="React, Node.js, Python, SQL, Git">
            </div>
          </div>

          <!-- Company specific fields -->
          <div id="reg-company-fields" style="display:none;">
            <div class="grid grid-2 gap-3">
              <div class="form-group">
                <label for="reg-comp-name" class="form-label">Company Name</label>
                <input type="text" id="reg-comp-name" name="company_name" class="form-control" placeholder="e.g. Acme Tech">
              </div>
              <div class="form-group">
                <label for="reg-industry" class="form-label">Industry</label>
                <select id="reg-industry" name="industry" class="form-control">
                  <option value="Technology">Technology &amp; Software</option>
                  <option value="Fintech">Fintech &amp; Banking</option>
                  <option value="E-Commerce">E-Commerce &amp; Retail</option>
                  <option value="Healthcare">Healthcare &amp; Biotech</option>
                  <option value="Consulting">Consulting &amp; Analytics</option>
                </select>
              </div>
            </div>
            <div class="grid grid-2 gap-3">
              <div class="form-group">
                <label for="reg-website" class="form-label">Company Website</label>
                <input type="url" id="reg-website" name="website" class="form-control" placeholder="https://example.com">
              </div>
              <div class="form-group">
                <label for="reg-location" class="form-label">Hiring Office Location</label>
                <input type="text" id="reg-location" name="location" class="form-control" placeholder="Bengaluru, India">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="App.closeModal('register-modal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Create Account &rarr;</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 3. Job Details Modal -->
  <div class="modal-overlay" id="job-detail-modal">
    <div class="modal modal-lg">
      <div class="modal-header">
        <div>
          <h3 class="modal-title" id="job-detail-title">Job Title</h3>
          <div style="font-size:0.85rem; color:var(--text-secondary);" id="job-detail-company">Company HQ</div>
        </div>
        <button type="button" class="modal-close" onclick="App.closeModal('job-detail-modal')">&times;</button>
      </div>
      <div class="modal-body">
        <div class="grid grid-3 gap-3" style="margin-bottom:1.25rem; background:var(--bg-surface-alt); padding:1rem; border-radius:var(--radius-md);">
          <div>
            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase;">Compensation</div>
            <div style="font-weight:700; color:var(--success);" id="job-detail-salary">₹0</div>
          </div>
          <div>
            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase;">Application Deadline</div>
            <div style="font-weight:700;" id="job-detail-deadline">Date</div>
          </div>
          <div>
            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase;">Minimum Criteria</div>
            <div style="font-weight:700;" id="job-detail-cgpa">7.0 CGPA</div>
          </div>
        </div>

        <div style="font-size:0.85rem; color:var(--primary); font-weight:600; margin-bottom:1rem;" id="job-detail-branches">Eligible Branches</div>

        <h4 style="font-size:1rem; font-weight:700; margin-bottom:0.4rem;">Position Overview</h4>
        <div id="job-detail-desc" style="font-size:0.92rem; color:var(--text-secondary); line-height:1.6; margin-bottom:1.25rem;"></div>

        <h4 style="font-size:1rem; font-weight:700; margin-bottom:0.4rem;">Requirements &amp; Qualifications</h4>
        <div id="job-detail-reqs" style="font-size:0.92rem; color:var(--text-secondary); line-height:1.6; margin-bottom:1.25rem;"></div>

        <h4 style="font-size:1rem; font-weight:700; margin-bottom:0.4rem;">Target Skills</h4>
        <div id="job-detail-skills" style="font-size:0.88rem; color:var(--text-secondary);"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="App.closeModal('job-detail-modal')">Close</button>
        <button type="button" class="btn btn-primary" id="job-detail-apply-btn">Apply Directly</button>
      </div>
    </div>
  </div>

  <!-- 4. Quick Apply Modal -->
  <div class="modal-overlay" id="apply-modal">
    <div class="modal">
      <div class="modal-header">
        <div>
          <h3 class="modal-title" id="apply-modal-title">Apply for Position</h3>
          <div style="font-size:0.85rem; color:var(--text-secondary);" id="apply-modal-subtitle">Role</div>
        </div>
        <button type="button" class="modal-close" onclick="App.closeModal('apply-modal')">&times;</button>
      </div>
      <form onsubmit="Student.submitApplication(event)">
        <input type="hidden" id="apply-job-id" name="job_id">
        <div class="modal-body">
          <div id="apply-resume-notice" style="padding:0.75rem; background:var(--bg-surface-alt); border-radius:var(--radius-md); font-size:0.85rem; margin-bottom:1rem;">
            Resume check
          </div>
          <div class="form-group">
            <label for="apply-cover-note" class="form-label">Cover Note to Recruiter (Optional)</label>
            <textarea id="apply-cover-note" name="cover_note" class="form-control" rows="4" placeholder="Briefly highlight why you're a great fit for this role..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="App.closeModal('apply-modal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Confirm &amp; Submit Application &rarr;</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 5. ATS Resume Keyword Matcher Modal -->
  <div class="modal-overlay" id="keyword-matcher-modal">
    <div class="modal modal-lg">
      <div class="modal-header">
        <div>
          <h3 class="modal-title">Intelligent ATS Resume Keyword Matcher</h3>
          <div style="font-size:0.85rem; color:var(--text-secondary);">Analyze job requirements against your profile skills to maximize ATS pass rate.</div>
        </div>
        <button type="button" class="modal-close" onclick="App.closeModal('keyword-matcher-modal')">&times;</button>
      </div>
      <div class="modal-body">
        <div class="grid grid-2 gap-3" style="margin-bottom:1rem;">
          <div class="form-group">
            <label for="km-job-text" class="form-label">Paste Job Description or Requirements</label>
            <textarea id="km-job-text" class="form-control" rows="6" placeholder="Paste job description, requirements, or tech stack here..."></textarea>
          </div>
          <div class="form-group">
            <label for="km-skills-text" class="form-label">Your Skills &amp; Profile Technologies</label>
            <textarea id="km-skills-text" class="form-control" rows="6" placeholder="React, Python, SQL, Docker, Go..."></textarea>
          </div>
        </div>

        <button type="button" class="btn btn-primary" style="width:100%; margin-bottom:1.5rem;" onclick="Student.analyzeKeywordMatch()">
          ⚡ Run Match &amp; Skill Gap Analysis
        </button>

        <div style="background:var(--bg-surface-alt); padding:1.25rem; border-radius:var(--radius-lg); border:1px solid var(--border-color);">
          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
            <span style="font-weight:700; font-size:1.05rem;">Match Score: <span id="km-score-val" style="color:var(--primary);">0%</span></span>
            <div id="km-verdict" style="font-size:0.85rem; font-weight:600; color:var(--text-secondary);">Waiting for input</div>
          </div>
          <div class="match-bar-bg" style="height:12px; margin-bottom:1.25rem;">
            <div class="match-bar-fill" id="km-score-bar" style="width:0%;"></div>
          </div>

          <div class="grid grid-2 gap-3">
            <div>
              <div style="font-size:0.85rem; font-weight:700; margin-bottom:0.4rem; color:var(--success);">✅ Matching Keywords Found in Profile:</div>
              <div id="km-matched-tags" class="flex flex-wrap gap-1"></div>
            </div>
            <div>
              <div style="font-size:0.85rem; font-weight:700; margin-bottom:0.4rem; color:var(--warning);">⚠️ Missing Keywords / Skill Gaps:</div>
              <div id="km-missing-tags" class="flex flex-wrap gap-1"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="App.closeModal('keyword-matcher-modal')">Close</button>
      </div>
    </div>
  </div>

  <!-- 6. Application Details & Timeline Modal -->
  <div class="modal-overlay" id="app-details-modal">
    <div class="modal modal-lg">
      <div class="modal-header">
        <div>
          <h3 class="modal-title" id="app-modal-company">Company</h3>
          <div style="font-size:0.85rem; color:var(--text-secondary);" id="app-modal-role">Role Title</div>
        </div>
        <button type="button" class="modal-close" onclick="App.closeModal('app-details-modal')">&times;</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="app-modal-app-id">

        <!-- Status updater dropdown -->
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; padding:1rem; background:var(--bg-surface-alt); border-radius:var(--radius-md); margin-bottom:1.5rem;">
          <div class="flex items-center gap-2">
            <label for="app-modal-status-select" style="font-weight:700; font-size:0.9rem;">Current Status:</label>
            <select id="app-modal-status-select" class="form-control" style="width:160px;">
              <option value="Wishlist">Wishlist</option>
              <option value="Applied">Applied</option>
              <option value="Screening">Screening</option>
              <option value="Interviewing">Interviewing</option>
              <option value="Offered">Offered</option>
              <option value="Rejected">Rejected</option>
            </select>
            <button type="button" class="btn btn-sm btn-primary" onclick="Student.updateAppStatusFromModal()">Save Status</button>
          </div>
          <button type="button" class="btn btn-sm btn-danger" onclick="Student.deleteCurrentApplication()">Remove Application</button>
        </div>

        <!-- Tabs inside modal: Timeline vs Interview Notes -->
        <div class="tabs-nav">
          <button type="button" class="tab-btn active" onclick="this.parentElement.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active')); this.classList.add('active'); document.getElementById('tab-app-timeline').style.display='block'; document.getElementById('tab-app-notes').style.display='none';">
            📜 Application Status Timeline
          </button>
          <button type="button" class="tab-btn" onclick="this.parentElement.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active')); this.classList.add('active'); document.getElementById('tab-app-timeline').style.display='none'; document.getElementById('tab-app-notes').style.display='block';">
            📝 Interview Prep Notes &amp; Rounds
          </button>
        </div>

        <div id="tab-app-timeline">
          <div class="timeline" id="app-modal-timeline"></div>
        </div>

        <div id="tab-app-notes" style="display:none;">
          <div id="app-modal-notes-list"></div>

          <!-- Add Note Form -->
          <form onsubmit="Student.addInterviewNote(event)" style="background:var(--bg-surface-alt); padding:1rem; border-radius:var(--radius-md); border:1px solid var(--border-color); margin-top:1rem;">
            <h4 style="font-size:0.95rem; font-weight:700; margin-bottom:0.75rem;">+ Add Interview Round Note</h4>
            <div class="grid grid-3 gap-2">
              <div class="form-group">
                <label for="prep-round-name" class="form-label">Round Name</label>
                <input type="text" id="prep-round-name" name="round_name" class="form-control" placeholder="Technical Round 1" required>
              </div>
              <div class="form-group">
                <label for="prep-interview-date" class="form-label">Date</label>
                <input type="date" id="prep-interview-date" name="interview_date" class="form-control">
              </div>
              <div class="form-group">
                <label for="prep-result" class="form-label">Round Outcome</label>
                <select id="prep-result" name="result" class="form-control">
                  <option value="Pending">Pending</option>
                  <option value="Passed">Passed</option>
                  <option value="Failed">Failed</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label for="prep-questions-asked" class="form-label">Questions Asked (DSA, System Design, Behavioral)</label>
              <textarea id="prep-questions-asked" name="questions_asked" class="form-control" rows="2" placeholder="List questions asked by interviewer..."></textarea>
            </div>
            <div class="form-group">
              <label for="prep-notes" class="form-label">Key Takeaways &amp; Next Steps</label>
              <textarea id="prep-notes" name="notes" class="form-control" rows="2" placeholder="Reflections on what went well and what to improve..."></textarea>
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Save Interview Note</button>
          </form>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="App.closeModal('app-details-modal')">Close</button>
      </div>
    </div>
  </div>

  <!-- 7. Track External Application Modal -->
  <div class="modal-overlay" id="add-external-modal">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title">Track Off-Campus Application</h3>
        <button type="button" class="modal-close" onclick="App.closeModal('add-external-modal')">&times;</button>
      </div>
      <form onsubmit="Student.submitExternalApp(event)">
        <div class="modal-body">
          <div class="form-group">
            <label for="ext-company-name" class="form-label">Company Name</label>
            <input type="text" id="ext-company-name" name="company_name" class="form-control" placeholder="e.g. Netflix, Meta, Startup..." required>
          </div>
          <div class="form-group">
            <label for="ext-role-title" class="form-label">Role Title</label>
            <input type="text" id="ext-role-title" name="role_title" class="form-control" placeholder="e.g. Software Engineer Intern" required>
          </div>
          <div class="form-group">
            <label for="ext-status" class="form-label">Initial Status Column</label>
            <select id="ext-status" name="status" class="form-control">
              <option value="Wishlist">Wishlist / Bookmarked</option>
              <option value="Applied" selected>Applied</option>
              <option value="Screening">Screening / Online Assessment</option>
              <option value="Interviewing">Interviewing</option>
              <option value="Offered">Offered</option>
            </select>
          </div>
          <div class="form-group">
            <label for="ext-notes" class="form-label">Notes &amp; Referral Details</label>
            <textarea id="ext-notes" name="notes" class="form-control" rows="3" placeholder="Applied via referral on LinkedIn..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="App.closeModal('add-external-modal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Add to Personal Kanban &rarr;</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 8. Post Vacancy Modal (Company) -->
  <div class="modal-overlay" id="post-job-modal">
    <div class="modal modal-lg">
      <div class="modal-header">
        <h3 class="modal-title">Publish Campus Hiring Drive</h3>
        <button type="button" class="modal-close" onclick="App.closeModal('post-job-modal')">&times;</button>
      </div>
      <form onsubmit="Company.handlePostJob(event)">
        <div class="modal-body">
          <div class="form-group">
            <label for="post-job-title" class="form-label">Role Title</label>
            <input type="text" id="post-job-title" name="title" class="form-control" placeholder="e.g. Graduate Backend Engineer" required>
          </div>

          <div class="grid grid-3 gap-2">
            <div class="form-group">
              <label for="post-job-category" class="form-label">Category</label>
              <select id="post-job-category" name="category" class="form-control">
                <option value="Software Engineering">Software Engineering</option>
                <option value="Cloud & DevOps">Cloud &amp; DevOps</option>
                <option value="Data Science & Analytics">Data Science &amp; Analytics</option>
                <option value="Artificial Intelligence">Artificial Intelligence</option>
                <option value="Design & Frontend">Design &amp; Frontend</option>
                <option value="Cybersecurity">Cybersecurity</option>
              </select>
            </div>
            <div class="form-group">
              <label for="post-job-type" class="form-label">Opportunity Type</label>
              <select id="post-job-type" name="type" class="form-control">
                <option value="Full-time">Full-time</option>
                <option value="Internship">Internship</option>
                <option value="Contract">Contract</option>
              </select>
            </div>
            <div class="form-group">
              <label for="post-job-workplace" class="form-label">Workplace Policy</label>
              <select id="post-job-workplace" name="workplace_type" class="form-control">
                <option value="Hybrid">Hybrid</option>
                <option value="Remote">Remote</option>
                <option value="On-site">On-site</option>
              </select>
            </div>
          </div>

          <div class="grid grid-3 gap-2">
            <div class="form-group">
              <label for="post-job-location" class="form-label">Office Location</label>
              <input type="text" id="post-job-location" name="location" class="form-control" value="Bengaluru, India">
            </div>
            <div class="form-group">
              <label for="post-job-salary" class="form-label">Compensation / Stipend</label>
              <input type="text" id="post-job-salary" name="salary_range" class="form-control" placeholder="₹12,00,000 - ₹16,00,000 / yr" required>
            </div>
            <div class="form-group">
              <label for="post-job-deadline" class="form-label">Application Deadline</label>
              <input type="date" id="post-job-deadline" name="deadline" class="form-control" required>
            </div>
          </div>

          <div class="grid grid-3 gap-2">
            <div class="form-group">
              <label for="post-job-min-cgpa" class="form-label">Minimum CGPA</label>
              <input type="number" id="post-job-min-cgpa" name="min_cgpa" class="form-control" step="0.1" value="7.0">
            </div>
            <div class="form-group">
              <label for="post-job-branches" class="form-label">Eligible Branches</label>
              <input type="text" id="post-job-branches" name="eligible_branches" class="form-control" value="Computer Science, Information Technology">
            </div>
            <div class="form-group">
              <label for="post-job-years" class="form-label">Eligible Batches</label>
              <input type="text" id="post-job-years" name="eligible_years" class="form-control" value="2025, 2026">
            </div>
          </div>

          <div class="form-group">
            <label for="post-job-skills" class="form-label">Required Skills (comma-separated, for ATS Matcher)</label>
            <input type="text" id="post-job-skills" name="skills_required" class="form-control" placeholder="Python, Docker, SQL, REST APIs">
          </div>

          <div class="form-group">
            <label for="post-job-desc" class="form-label">Job Description</label>
            <textarea id="post-job-desc" name="description" class="form-control" rows="3" placeholder="Describe the responsibilities and project scope..." required></textarea>
          </div>

          <div class="form-group">
            <label for="post-job-reqs" class="form-label">Requirements &amp; Qualifications</label>
            <textarea id="post-job-reqs" name="requirements" class="form-control" rows="3" placeholder="• Degree requirements&#10;• Technical background&#10;• Experience..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="App.closeModal('post-job-modal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Drive for Publication &rarr;</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 9. Update Candidate Status Modal (Company) -->
  <div class="modal-overlay" id="update-applicant-modal">
    <div class="modal">
      <div class="modal-header">
        <div>
          <h3 class="modal-title">Update Candidate Status</h3>
          <div style="font-size:0.85rem; color:var(--text-secondary);" id="status-modal-student-name">Student Name</div>
        </div>
        <button type="button" class="modal-close" onclick="App.closeModal('update-applicant-modal')">&times;</button>
      </div>
      <form onsubmit="Company.submitStatusUpdate(event)">
        <input type="hidden" id="status-modal-app-id" name="application_id">
        <div class="modal-body">
          <div class="form-group">
            <label for="status-modal-select" class="form-label">New Recruitment Status</label>
            <select id="status-modal-select" name="status" class="form-control" onchange="
              const val = this.value;
              document.getElementById('status-interview-date-group').style.display = (val === 'Interviewing') ? 'block' : 'none';
              document.getElementById('status-salary-group').style.display = (val === 'Offered') ? 'block' : 'none';
            ">
              <option value="Screening">Move to Screening</option>
              <option value="Interviewing">Schedule Interview</option>
              <option value="Offered">Extend Formal Offer</option>
              <option value="Rejected">Reject Candidate</option>
            </select>
          </div>

          <div class="form-group" id="status-interview-date-group" style="display:none;">
            <label for="status-interview-date" class="form-label">Interview Date &amp; Time</label>
            <input type="datetime-local" id="status-interview-date" name="interview_date" class="form-control">
          </div>

          <div class="form-group" id="status-salary-group" style="display:none;">
            <label for="status-salary-offered" class="form-label">Offered Compensation Package</label>
            <input type="text" id="status-salary-offered" name="salary_offered" class="form-control" placeholder="e.g. ₹14,00,000 / yr + ₹1,00,000 Bonus">
          </div>

          <div class="form-group">
            <label for="status-remarks" class="form-label">Feedback / Note to Candidate</label>
            <textarea id="status-remarks" name="remarks" class="form-control" rows="3" placeholder="Sent to student's notification center..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="App.closeModal('update-applicant-modal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Save &amp; Notify Candidate &rarr;</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 10. Profile Edit Modal -->
  <div class="modal-overlay" id="profile-modal">
    <div class="modal modal-lg">
      <div class="modal-header">
        <h3 class="modal-title">Edit Your Profile</h3>
        <button type="button" class="modal-close" onclick="App.closeModal('profile-modal')">&times;</button>
      </div>
      <form onsubmit="Auth.handleProfileUpdate(event)">
        <div class="modal-body">
          <div class="grid grid-2 gap-3">
            <div class="form-group">
              <label for="profile-name" class="form-label">Full Name</label>
              <input type="text" id="profile-name" name="name" class="form-control" value="<?php echo htmlspecialchars(getCurrentUser()['name'] ?? ''); ?>">
            </div>
            <div class="form-group">
              <label for="profile-email" class="form-label">Email (Read-only)</label>
              <input type="email" id="profile-email" class="form-control" value="<?php echo htmlspecialchars(getCurrentUser()['email'] ?? ''); ?>" disabled>
            </div>
          </div>

          <!-- Dynamic role details -->
          <?php $cu = getCurrentUser(); ?>
          <?php if ($cu && $cu['role'] === 'student'): ?>
            <div class="grid grid-3 gap-3">
              <div class="form-group">
                <label for="profile-dept" class="form-label">Department / Branch</label>
                <input type="text" id="profile-dept" name="department" class="form-control" value="<?php echo htmlspecialchars($cu['department'] ?? 'Computer Science'); ?>">
              </div>
              <div class="form-group">
                <label for="profile-cgpa" class="form-label">CGPA</label>
                <input type="number" id="profile-cgpa" name="cgpa" class="form-control" step="0.01" value="<?php echo htmlspecialchars($cu['cgpa'] ?? '8.00'); ?>">
              </div>
              <div class="form-group">
                <label for="profile-grad-year" class="form-label">Graduation Year</label>
                <input type="number" id="profile-grad-year" name="graduation_year" class="form-control" value="<?php echo htmlspecialchars($cu['graduation_year'] ?? '2026'); ?>">
              </div>
            </div>

            <div class="form-group">
              <label for="profile-skills" class="form-label">Skills (comma-separated)</label>
              <input type="text" id="profile-skills" name="skills" class="form-control" value="<?php echo htmlspecialchars($cu['skills'] ?? ''); ?>">
            </div>

            <div class="grid grid-2 gap-3">
              <div class="form-group">
                <label for="profile-phone" class="form-label">Contact Phone</label>
                <input type="text" id="profile-phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($cu['phone'] ?? ''); ?>">
              </div>
              <div class="form-group">
                <label for="profile-github" class="form-label">GitHub URL</label>
                <input type="url" id="profile-github" name="github_url" class="form-control" value="<?php echo htmlspecialchars($cu['github_url'] ?? ''); ?>">
              </div>
            </div>

            <div class="form-group">
              <label for="profile-bio" class="form-label">Bio &amp; Career Objective</label>
              <textarea id="profile-bio" name="bio" class="form-control" rows="2"><?php echo htmlspecialchars($cu['bio'] ?? ''); ?></textarea>
            </div>

            <!-- Resume Upload box -->
            <div style="background:var(--bg-surface-alt); padding:1rem; border-radius:var(--radius-md); border:1px solid var(--border-color); margin-top:1rem;">
              <label for="resume-file-input" class="form-label">Attach / Replace Resume File (PDF, DOCX)</label>
              <div class="flex gap-2 items-center">
                <input type="file" id="resume-file-input" class="form-control" accept=".pdf,.doc,.docx,.txt">
                <button type="button" class="btn btn-outline" onclick="Auth.handleResumeUpload(event)">Upload</button>
              </div>
              <div id="current-resume-display" style="margin-top:0.5rem;">
                <?php if (!empty($cu['resume_filename'])): ?>
                  <span style="font-size:0.85rem; color:var(--success); font-weight:600;">
                    📄 Attached: <?php echo htmlspecialchars($cu['resume_filename']); ?>
                  </span>
                <?php endif; ?>
              </div>
            </div>

          <?php elseif ($cu && $cu['role'] === 'company'): ?>
            <div class="grid grid-2 gap-3">
              <div class="form-group">
                <label for="profile-comp-name" class="form-label">Company Name</label>
                <input type="text" id="profile-comp-name" name="company_name" class="form-control" value="<?php echo htmlspecialchars($cu['company_name'] ?? ''); ?>">
              </div>
              <div class="form-group">
                <label for="profile-website" class="form-label">Website</label>
                <input type="url" id="profile-website" name="website" class="form-control" value="<?php echo htmlspecialchars($cu['website'] ?? ''); ?>">
              </div>
            </div>
            <div class="grid grid-2 gap-3">
              <div class="form-group">
                <label for="profile-industry" class="form-label">Industry</label>
                <input type="text" id="profile-industry" name="industry" class="form-control" value="<?php echo htmlspecialchars($cu['industry'] ?? ''); ?>">
              </div>
              <div class="form-group">
                <label for="profile-location" class="form-label">Headquarters / Location</label>
                <input type="text" id="profile-location" name="location" class="form-control" value="<?php echo htmlspecialchars($cu['location'] ?? ''); ?>">
              </div>
            </div>
            <div class="form-group">
              <label for="profile-desc" class="form-label">Company Description</label>
              <textarea id="profile-desc" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($cu['description'] ?? ''); ?></textarea>
            </div>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="App.closeModal('profile-modal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Profile Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Toast Notification Container -->
  <div id="toast-container" aria-live="polite" aria-atomic="true"></div>

  <!-- Footer -->
  <footer style="background:var(--bg-surface); border-top:1px solid var(--border-color); padding:2.5rem 0; margin-top:auto;">
    <div class="container flex justify-between items-center flex-wrap gap-4">
      <div class="flex items-center gap-3">
        <svg viewBox="0 0 540 120" style="height:32px; width:auto;">
          <defs>
            <linearGradient id="ftLogoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#2563EB" />
              <stop offset="100%" stop-color="#06B6D4" />
            </linearGradient>
          </defs>
          <rect x="15" y="10" width="100" height="100" rx="26" fill="url(#ftLogoGrad)" />
          <path d="M 37 72 L 49 72 L 57 46 L 65 82 L 73 60 L 81 72 L 93 72" fill="none" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
          <text x="135" y="82" font-family="system-ui, sans-serif" font-size="52" font-weight="800" fill="currentColor">Career<tspan fill="#2563EB">Pulse</tspan></text>
        </svg>
        <span style="font-size:0.85rem; color:var(--text-muted);">
          &copy; <?php echo date('Y'); ?> CareerPulse Platform. Optimized for WAMP Server.
        </span>
      </div>
      <div class="flex gap-4" style="font-size:0.85rem; color:var(--text-secondary);">
        <a href="javascript:void(0)" onclick="App.switchTab('portal')">Job Portal</a>
        <a href="javascript:void(0)" onclick="App.switchTab('stats')">Placement Statistics</a>
        <a href="javascript:void(0)" onclick="App.openModal('keyword-matcher-modal')">ATS Matcher</a>
      </div>
    </div>
  </footer>

  <!-- Modular Scripts -->
  <script src="assets/js/app.js"></script>
  <script src="assets/js/auth.js"></script>
  <script src="assets/js/student.js"></script>
  <script src="assets/js/company.js"></script>
  <script src="assets/js/admin.js"></script>

</body>
</html>
