<?php
require_once __DIR__ . '/config/db.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CareerPulse - Public Campus Placement &amp; Hiring Report</title>
  <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>

  <!-- Minimal Header -->
  <header class="navbar">
    <div class="container nav-inner">
      <a href="index.php" class="nav-logo">
        <svg viewBox="0 0 540 120" style="height:40px; width:auto;">
          <defs>
            <linearGradient id="stLogoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#2563EB" />
              <stop offset="100%" stop-color="#06B6D4" />
            </linearGradient>
          </defs>
          <rect x="15" y="10" width="100" height="100" rx="26" fill="url(#stLogoGrad)" />
          <path d="M 37 72 L 49 72 L 57 46 L 65 82 L 73 60 L 81 72 L 93 72" fill="none" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
          <text x="135" y="82" font-family="system-ui, sans-serif" font-size="52" font-weight="800" fill="currentColor">Career<tspan fill="#2563EB">Pulse</tspan></text>
        </svg>
      </a>

      <div class="flex items-center gap-3">
        <a href="index.php" class="btn btn-outline btn-sm">&larr; Return to Main Platform</a>
      </div>
    </div>
  </header>

  <main class="container" style="flex:1; padding-top:2.5rem; padding-bottom:4rem;">
    <div class="hero" style="padding:1.5rem 0 2.5rem;">
      <div class="hero-badge">Official Campus Placement Report</div>
      <h1 class="hero-title" style="font-size:2.8rem;">Placement Outcomes &amp; Corporate Partnerships</h1>
      <p class="hero-desc">Real-time transparent analytics powered by the CareerPulse Acceleration Engine.</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
      <div class="stat-card">
        <div>
          <div class="stat-label">Students Placed</div>
          <div class="stat-val" id="pub-stat-placed" style="color:var(--success);">0</div>
          <div style="font-size:0.8rem; color:var(--text-muted);">Unique student job offers</div>
        </div>
        <div class="stat-icon" style="background:var(--success-light); color:var(--success);">
          <svg class="svg-icon" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        </div>
      </div>

      <div class="stat-card">
        <div>
          <div class="stat-label">Overall Placement Rate</div>
          <div class="stat-val" id="pub-stat-rate" style="color:var(--primary);">0%</div>
          <div style="font-size:0.8rem; color:var(--text-muted);">Institutional cohort rate</div>
        </div>
        <div class="stat-icon" style="background:var(--primary-light); color:var(--primary);">
          <svg class="svg-icon" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        </div>
      </div>

      <div class="stat-card">
        <div>
          <div class="stat-label">Active Campus Drives</div>
          <div class="stat-val" id="pub-stat-drives">0</div>
          <div style="font-size:0.8rem; color:var(--text-muted);">Full-time &amp; Internships</div>
        </div>
        <div class="stat-icon" style="background:var(--purple-light); color:var(--purple);">
          <svg class="svg-icon" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
      </div>

      <div class="stat-card">
        <div>
          <div class="stat-label">Recruiting Companies</div>
          <div class="stat-val" id="pub-stat-companies">0</div>
          <div style="font-size:0.8rem; color:var(--text-muted);">Corporate hiring partners</div>
        </div>
        <div class="stat-icon" style="background:var(--accent-cyan-light); color:var(--accent-cyan);">
          <svg class="svg-icon" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="9" y1="22" x2="9" y2="22.01"/><line x1="15" y1="22" x2="15" y2="22.01"/><line x1="8" y1="6" x2="8" y2="6.01"/><line x1="16" y1="6" x2="16" y2="6.01"/><line x1="8" y1="10" x2="8" y2="10.01"/><line x1="16" y1="10" x2="16" y2="10.01"/><line x1="8" y1="14" x2="8" y2="14.01"/><line x1="16" y1="14" x2="16" y2="14.01"/></svg>
        </div>
      </div>
    </div>

    <!-- Chart -->
    <div style="background:var(--bg-surface); padding:1.5rem; border-radius:var(--radius-lg); border:1px solid var(--border-color); margin-bottom:2.5rem; box-shadow:var(--shadow-sm);">
      <h3 style="font-size:1.2rem; font-weight:700; margin-bottom:1rem;">Placement Percentage by Academic Branch</h3>
      <div style="height:320px; position:relative;">
        <canvas id="public-branch-chart"></canvas>
      </div>
    </div>

    <!-- Recruiters -->
    <h3 style="font-size:1.4rem; font-weight:800; margin-bottom:1.25rem;">Key Recruiting Partners</h3>
    <div class="grid grid-3 gap-3" id="public-recruiters-grid"></div>
  </main>

  <footer style="background:var(--bg-surface); border-top:1px solid var(--border-color); padding:2rem 0; text-align:center; font-size:0.85rem; color:var(--text-muted);">
    &copy; <?php echo date('Y'); ?> CareerPulse Platform. Public Placement Statistics Report.
  </footer>

  <script src="assets/js/app.js"></script>
  <script src="assets/js/admin.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      Admin.loadPublicStats();
    });
  </script>
</body>
</html>
