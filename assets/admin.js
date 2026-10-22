// CareerPulse - Admin Analytics, Moderation & Moderation Controller

const Admin = {
  stats: null,
  charts: {},

  async loadDashboard() {
    try {
      const res = await fetch('api/admin.php?action=stats');
      const data = await res.json();
      if (data.success) {
        this.stats = data;
        this.renderMetrics(data.metrics);
        this.renderBranchChart(data.branch_distribution);
        this.renderRecruitersTable(data.top_recruiters);
        this.renderDrivesBreakdown(data.drives_breakdown);
        this.renderRecentLogs(data.recent_logs);
      }
    } catch (e) {
      console.error('Error loading admin dashboard:', e);
    }
  },

  renderMetrics(m) {
    if (!m) return;
    document.getElementById('stat-admin-students').textContent = m.total_students;
    document.getElementById('stat-admin-companies').textContent = m.total_companies;
    document.getElementById('stat-admin-jobs').textContent = m.total_jobs;
    document.getElementById('stat-admin-pending').textContent = m.pending_jobs;
    document.getElementById('stat-admin-applications').textContent = m.total_applications;
    document.getElementById('stat-admin-offers').textContent = m.total_offers;
    document.getElementById('stat-admin-placement-rate').textContent = `${m.overall_placement_rate}%`;

    const pendingAlert = document.getElementById('admin-pending-alert');
    if (pendingAlert) {
      if (m.pending_jobs > 0) {
        pendingAlert.style.display = 'flex';
        document.getElementById('admin-pending-count-text').textContent = m.pending_jobs;
      } else {
        pendingAlert.style.display = 'none';
      }
    }
  },

  renderBranchChart(branchStats) {
    const ctx = document.getElementById('admin-branch-chart')?.getContext('2d');
    if (!ctx || !branchStats) return;

    if (this.charts.branchChart) this.charts.branchChart.destroy();

    const labels = branchStats.map(b => b.department);
    const rates = branchStats.map(b => b.placement_rate);
    const totals = branchStats.map(b => b.total_students);

    this.charts.branchChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Placement Percentage (%)',
            data: rates,
            backgroundColor: '#2563EB',
            borderRadius: 6,
            yAxisID: 'y'
          },
          {
            label: 'Total Registered Students',
            data: totals,
            backgroundColor: '#94A3B8',
            borderRadius: 6,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: { callback: v => v + '%' },
            title: { display: true, text: 'Placement Rate (%)' }
          },
          y1: {
            position: 'right',
            beginAtZero: true,
            grid: { drawOnChartArea: false },
            title: { display: true, text: 'Headcount' }
          },
          x: { grid: { display: false } }
        }
      }
    });
  },

  renderRecruitersTable(recruiters) {
    const tbody = document.getElementById('admin-recruiters-tbody');
    if (!tbody || !recruiters) return;

    tbody.innerHTML = recruiters.map(r => `
      <tr>
        <td>
          <div style="display:flex; align-items:center; gap:0.75rem;">
            <img src="${r.logo_url || 'assets/img/logo.svg'}" class="company-avatar" style="width:36px; height:36px;" onerror="this.src='assets/img/logo.svg'">
            <div>
              <div style="font-weight:700;">${r.company_name}</div>
              <div style="font-size:0.75rem; color:var(--text-muted);">${r.industry}</div>
            </div>
          </div>
        </td>
        <td><strong>${r.total_jobs}</strong> drives</td>
        <td>${r.total_applications} candidates</td>
        <td><span class="badge badge-green">${r.offers_made} Hires</span></td>
      </tr>
    `).join('');
  },

  renderDrivesBreakdown(drives) {
    const tbody = document.getElementById('admin-drives-tbody');
    if (!tbody || !drives) return;

    tbody.innerHTML = drives.map(d => `
      <tr>
        <td>
          <div style="font-weight:700;">${d.title}</div>
          <div style="font-size:0.78rem; color:var(--text-muted);">${d.company_name} • ${d.type}</div>
        </td>
        <td>${d.deadline}</td>
        <td><strong>${d.application_count}</strong> applications</td>
        <td><span class="badge badge-green">${d.status}</span></td>
      </tr>
    `).join('');
  },

  renderRecentLogs(logs) {
    const container = document.getElementById('admin-recent-logs');
    if (!container || !logs) return;

    container.innerHTML = logs.map(l => `
      <div style="padding:0.65rem 0; border-bottom:1px solid var(--border-color); font-size:0.85rem; display:flex; align-items:center; justify-content:space-between;">
        <div>
          <span class="badge badge-blue" style="font-size:0.7rem; font-weight:700;">${l.action}</span>
          <span style="margin-left:0.5rem; color:var(--text-secondary);">${l.details || ''}</span>
        </div>
        <span style="font-size:0.72rem; color:var(--text-muted);">${new Date(l.created_at).toLocaleTimeString()}</span>
      </div>
    `).join('');
  },

  // ================= Moderation Queue =================
  async loadModeration() {
    try {
      const res = await fetch('api/admin.php?action=pending_jobs');
      const data = await res.json();
      if (data.success) {
        this.renderPendingTable(data.pending_jobs);
      }
    } catch (e) {
      console.error(e);
    }
  },

  renderPendingTable(jobs) {
    const tbody = document.getElementById('admin-pending-tbody');
    if (!tbody) return;

    if (jobs.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:2.5rem; color:var(--text-muted);"><div style="display:inline-flex; align-items:center; gap:0.5rem; justify-content:center;">${App.getSvgIcon('check-circle', 'svg-icon text-success')} All caught up. No pending drive submissions requiring review.</div></td></tr>`;
      return;
    }

    tbody.innerHTML = jobs.map(j => `
      <tr>
        <td>
          <div style="font-weight:700;">${j.title}</div>
          <div style="font-size:0.78rem; color:var(--text-muted);">${j.category} • ${j.type}</div>
        </td>
        <td>
          <strong>${j.company_name}</strong>
          <div style="font-size:0.75rem; color:var(--text-muted);">${j.company_email}</div>
        </td>
        <td>${j.salary_range}</td>
        <td>${j.min_cgpa} CGPA</td>
        <td>${j.deadline}</td>
        <td>
          <div class="flex gap-2">
            <button class="btn btn-sm btn-success" onclick="Admin.approveJob(${j.id})">Approve</button>
            <button class="btn btn-sm btn-danger" onclick="Admin.rejectJob(${j.id})">Reject</button>
          </div>
        </td>
      </tr>
    `).join('');
  },

  async approveJob(jobId) {
    try {
      const res = await fetch('api/admin.php?action=approve_job', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ job_id: jobId })
      });
      const data = await res.json();
      if (data.success) {
        App.showToast(data.message, 'success');
        this.loadModeration();
        this.loadDashboard();
      } else {
        App.showToast(data.error || 'Failed to approve', 'danger');
      }
    } catch (e) {
      App.showToast('Error approving drive.', 'danger');
    }
  },

  async rejectJob(jobId) {
    const reason = prompt('Please enter the reason for rejecting this job posting:', 'Incomplete requirements or duplicate drive');
    if (!reason) return;

    try {
      const res = await fetch('api/admin.php?action=reject_job', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ job_id: jobId, reason: reason })
      });
      const data = await res.json();
      if (data.success) {
        App.showToast(data.message, 'info');
        this.loadModeration();
        this.loadDashboard();
      }
    } catch (e) {
      App.showToast('Error rejecting drive.', 'danger');
    }
  },

  // ================= User Account Management =================
  async loadUsers() {
    const role = document.getElementById('admin-user-role-filter')?.value || 'All';
    const search = document.getElementById('admin-user-search')?.value || '';

    const params = new URLSearchParams({ role, search });
    try {
      const res = await fetch(`api/admin.php?action=users_list&${params}`);
      const data = await res.json();
      if (data.success) {
        this.renderUsersTable(data.users);
      }
    } catch (e) {
      console.error(e);
    }
  },

  renderUsersTable(users) {
    const tbody = document.getElementById('admin-users-tbody');
    if (!tbody) return;

    if (users.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:2rem; color:var(--text-muted);">No accounts found matching filter.</td></tr>`;
      return;
    }

    tbody.innerHTML = users.map(u => `
      <tr>
        <td>
          <div style="font-weight:700;">${u.name}</div>
          <div style="font-size:0.75rem; color:var(--text-muted);">${u.email}</div>
        </td>
        <td>
          <span class="user-role-badge role-${u.role}">${u.role}</span>
        </td>
        <td>
          ${u.role === 'student' ? `${u.department} (CGPA: ${u.cgpa})` : (u.company_name || 'N/A')}
        </td>
        <td>
          <span class="badge ${u.status === 'active' ? 'badge-green' : 'badge-red'}">${u.status}</span>
        </td>
        <td>${new Date(u.created_at).toLocaleDateString()}</td>
        <td>
          <button class="btn btn-sm ${u.status === 'active' ? 'btn-danger' : 'btn-success'}" onclick="Admin.toggleUserStatus(${u.id}, '${u.status === 'active' ? 'suspended' : 'active'}')">
            ${u.status === 'active' ? 'Suspend' : 'Reactivate'}
          </button>
        </td>
      </tr>
    `).join('');
  },

  async toggleUserStatus(userId, newStatus) {
    try {
      const res = await fetch('api/admin.php?action=toggle_user_status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userId, status: newStatus })
      });
      const data = await res.json();
      if (data.success) {
        App.showToast(data.message, 'success');
        this.loadUsers();
      }
    } catch (e) {
      App.showToast('Error updating account status', 'danger');
    }
  },

  // ================= Audit Activity Logs =================
  async loadLogs() {
    try {
      const res = await fetch('api/admin.php?action=activity_logs');
      const data = await res.json();
      if (data.success) {
        const tbody = document.getElementById('admin-logs-tbody');
        if (!tbody) return;

        tbody.innerHTML = data.logs.map(l => `
          <tr>
            <td><span class="badge badge-blue">${l.action}</span></td>
            <td><strong>${l.user_name || 'System'}</strong> <span style="font-size:0.75rem; color:var(--text-muted);">(${l.user_role || 'guest'})</span></td>
            <td>${l.details || ''}</td>
            <td><code style="font-size:0.8rem;">${l.ip_address}</code></td>
            <td>${new Date(l.created_at).toLocaleString()}</td>
          </tr>
        `).join('');
      }
    } catch (e) {
      console.error(e);
    }
  },

  // ================= Public Stats Page =================
  async loadPublicStats() {
    try {
      const res = await fetch('api/admin.php?action=public_stats');
      const data = await res.json();
      if (data.success) {
        document.getElementById('pub-stat-placed').textContent = data.metrics.placed_students;
        document.getElementById('pub-stat-rate').textContent = `${data.metrics.overall_placement_rate}%`;
        document.getElementById('pub-stat-drives').textContent = data.metrics.total_jobs;
        document.getElementById('pub-stat-companies').textContent = data.metrics.total_companies;

        this.renderPublicBranchChart(data.branch_distribution);
        this.renderPublicRecruiters(data.top_recruiters);
      }
    } catch (e) {
      console.error(e);
    }
  },

  renderPublicBranchChart(branchStats) {
    const ctx = document.getElementById('public-branch-chart')?.getContext('2d');
    if (!ctx || !branchStats) return;

    if (this.charts.pubChart) this.charts.pubChart.destroy();
    this.charts.pubChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: branchStats.map(b => b.department),
        datasets: [{
          label: 'Placement Percentage (%)',
          data: branchStats.map(b => b.placement_rate),
          backgroundColor: '#2563EB',
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } },
          x: { grid: { display: false } }
        }
      }
    });
  },

  renderPublicRecruiters(recruiters) {
    const container = document.getElementById('public-recruiters-grid');
    if (!container || !recruiters) return;

    container.innerHTML = recruiters.map(r => `
      <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:1.25rem; display:flex; align-items:center; gap:1rem; box-shadow:var(--shadow-sm);">
        <img src="${r.logo_url || 'assets/img/logo.svg'}" class="company-avatar" style="width:48px; height:48px;" onerror="this.src='assets/img/logo.svg'">
        <div>
          <h4 style="font-size:1.05rem; font-weight:700;">${r.company_name}</h4>
          <div style="font-size:0.8rem; color:var(--text-secondary);">${r.industry}</div>
          <div style="font-size:0.8rem; color:var(--primary); font-weight:600; margin-top:0.25rem;">${r.offers_made} Offers Extended</div>
        </div>
      </div>
    `).join('');
  }
};

window.Admin = Admin;
