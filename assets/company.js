// CareerPulse - Company & Recruiter Management Engine

const Company = {
  jobs: [],
  selectedJobId: null,
  applicants: [],
  chart: null,

  async loadDashboard() {
    try {
      const res = await fetch('api/portal.php?action=company_jobs');
      const data = await res.json();
      if (data.success) {
        this.jobs = data.jobs;
        this.renderStats(data.analytics);
        this.renderDrivesTable(data.jobs);
        this.renderHiringChart(data.analytics);
      }
    } catch (e) {
      console.error('Error loading company dashboard:', e);
    }
  },

  renderStats(analytics) {
    if (!analytics) return;
    document.getElementById('stat-comp-vacancies').textContent = analytics.total_vacancies || 0;
    document.getElementById('stat-comp-applicants').textContent = analytics.total_applicants || 0;
    document.getElementById('stat-comp-interview').textContent = analytics.interviewing || 0;
    document.getElementById('stat-comp-offers').textContent = analytics.hires_offered || 0;
  },

  renderDrivesTable(jobs) {
    const tbody = document.getElementById('company-drives-tbody');
    if (!tbody) return;

    if (jobs.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--text-muted);">No vacancies posted yet. Click "+ Post Vacancy" to publish your first hiring drive!</td></tr>`;
      return;
    }

    tbody.innerHTML = jobs.map(j => {
      let statusBadge = j.status === 'approved' 
        ? `<span class="badge badge-green">Live</span>` 
        : (j.status === 'pending' ? `<span class="badge badge-yellow">Pending Review</span>` : `<span class="badge badge-red">${j.status}</span>`);

      return `
        <tr>
          <td>
            <div style="font-weight:700; color:var(--text-primary);">${j.title}</div>
            <div style="font-size:0.78rem; color:var(--text-muted);">${j.category} &bull; ${j.type}</div>
          </td>
          <td>${j.location}</td>
          <td><span style="font-weight:600; color:var(--success);">${j.salary_range}</span></td>
          <td>${j.min_cgpa} CGPA</td>
          <td>${j.deadline}</td>
          <td>${statusBadge}</td>
          <td>
            <div class="flex gap-2">
              <button class="btn btn-sm btn-primary" onclick="Company.viewDriveApplicants(${j.id})">
                Applicants (${j.total_applicants})
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  },

  renderHiringChart(analytics) {
    const ctx = document.getElementById('company-funnel-chart')?.getContext('2d');
    if (!ctx || !analytics) return;

    if (this.chart) this.chart.destroy();
    this.chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Total Applicants', 'In Interview Loop', 'Offers Extended'],
        datasets: [{
          label: 'Hiring Pipeline',
          data: [analytics.total_applicants || 0, analytics.interviewing || 0, analytics.hires_offered || 0],
          backgroundColor: ['#3B82F6', '#8B5CF6', '#10B981'],
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true, ticks: { precision: 0 } },
          x: { grid: { display: false } }
        },
        plugins: {
          legend: { display: false }
        }
      }
    });
  },

  async handlePostJob(event) {
    event.preventDefault();
    const form = event.target;
    const payload = {
      title: form.title.value.trim(),
      category: form.category.value,
      type: form.type.value,
      workplace_type: form.workplace_type.value,
      location: form.location.value.trim(),
      salary_range: form.salary_range.value.trim(),
      min_cgpa: parseFloat(form.min_cgpa.value),
      eligible_branches: form.eligible_branches.value.trim(),
      eligible_years: form.eligible_years.value.trim(),
      deadline: form.deadline.value,
      description: form.description.value.trim(),
      requirements: form.requirements.value.trim(),
      skills_required: form.skills_required.value.trim()
    };

    if (!payload.title || !payload.description || !payload.requirements) {
      App.showToast('Please fill in Title, Description, and Requirements.', 'warning');
      return;
    }

    try {
      const res = await fetch('api/portal.php?action=create_job', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        App.closeModal('post-job-modal');
        form.reset();
        App.showToast(data.message, 'success');
        Company.loadDashboard();
      } else {
        App.showToast(data.error || 'Failed to post vacancy', 'danger');
      }
    } catch (e) {
      App.showToast('Error communicating with server.', 'danger');
    }
  },

  async loadDrives() {
    await this.loadDashboard();
  },

  async loadApplicants(jobId = null) {
    if (!jobId && this.jobs.length > 0) {
      jobId = this.jobs[0].id;
    }
    if (jobId) {
      this.viewDriveApplicants(jobId);
    }
  },

  async viewDriveApplicants(jobId) {
    this.selectedJobId = jobId;
    App.switchTab('applicants');

    // Populate drives dropdown
    const select = document.getElementById('applicant-drive-select');
    if (select) {
      select.innerHTML = this.jobs.map(j => `
        <option value="${j.id}" ${j.id == jobId ? 'selected' : ''}>${j.title} (${j.total_applicants} applicants)</option>
      `).join('');
    }

    try {
      const res = await fetch(`api/portal.php?action=job_applicants&job_id=${jobId}`);
      const data = await res.json();
      if (data.success) {
        this.applicants = data.applicants;
        this.renderApplicantsList(data.job, data.applicants);
      }
    } catch (e) {
      console.error(e);
    }
  },

  renderApplicantsList(job, applicants) {
    const container = document.getElementById('applicants-list-container');
    if (!container) return;

    if (applicants.length === 0) {
      container.innerHTML = `
        <div style="text-align:center; padding:3.5rem; background:var(--bg-surface); border-radius:var(--radius-lg); border:1px solid var(--border-color);">
          <div style="margin-bottom:0.75rem; color:var(--text-muted);">
            <svg class="svg-icon" style="width:40px; height:40px;" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <h3 style="font-size:1.2rem; font-weight:700;">No applications received yet for ${job.title}</h3>
          <p style="color:var(--text-secondary); margin-top:0.3rem;">Eligible student applications will appear here in real-time as they apply.</p>
        </div>
      `;
      return;
    }

    container.innerHTML = applicants.map(a => {
      const statusColors = {
        Applied: 'badge-blue',
        Screening: 'badge-yellow',
        Interviewing: 'badge-purple',
        Offered: 'badge-green',
        Rejected: 'badge-red'
      };

      const avatarHtml = a.avatar_filename
        ? `<img src="assets/uploads/avatars/${a.avatar_filename}" class="user-avatar" style="width:48px; height:48px; object-fit:cover;">`
        : `<div class="user-avatar" style="width:48px; height:48px; font-size:1.1rem;">${a.student_name.charAt(0)}</div>`;

      return `
        <div class="job-card" style="margin-bottom:1.15rem;">
          <div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
            <div style="display:flex; align-items:center; gap:0.9rem;">
              ${avatarHtml}
              <div>
                <h3 style="font-size:1.1rem; font-weight:700;">${a.student_name}</h3>
                <div style="font-size:0.85rem; color:var(--text-secondary);">${a.department} &bull; CGPA: <strong>${a.cgpa}</strong> &bull; Class of ${a.graduation_year}</div>
                <div style="font-size:0.8rem; color:var(--text-muted); margin-top:0.2rem;">${a.student_email} ${a.phone ? '&bull; ' + a.phone : ''}</div>
              </div>
            </div>

            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0.4rem;">
              <span class="badge ${statusColors[a.status] || 'badge-blue'}" style="font-size:0.85rem; padding:0.35rem 0.85rem;">Status: ${a.status}</span>
              <span class="badge badge-green" style="font-size:0.78rem;">Match: ${a.match_percentage}%</span>
            </div>
          </div>

          <div style="margin:1rem 0; padding:0.85rem; background:var(--bg-surface-alt); border-radius:var(--radius-md); font-size:0.85rem;">
            <strong>Skills:</strong> <span style="color:var(--text-secondary);">${a.skills || 'No skills listed'}</span>
            ${a.cover_note ? `<div style="margin-top:0.5rem;"><strong>Cover Note:</strong> <em>"${a.cover_note}"</em></div>` : ''}
            ${a.interview_date ? `<div style="margin-top:0.5rem; color:var(--primary); font-weight:600;">Interview Scheduled: ${new Date(a.interview_date).toLocaleString()}</div>` : ''}
            ${a.salary_offered ? `<div style="margin-top:0.35rem; color:var(--success); font-weight:600;">Offered Compensation: ${a.salary_offered}</div>` : ''}
          </div>

          <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem; border-top:1px solid var(--border-color); padding-top:0.85rem;">
            <div class="flex items-center gap-2">
              ${a.resume_filename ? `
                <a href="assets/uploads/resumes/${a.resume_filename}" target="_blank" class="btn btn-sm btn-outline">
                  View CV / Resume
                </a>
              ` : `<span style="font-size:0.82rem; color:var(--text-muted);">No CV uploaded</span>`}
              ${a.github_url ? `<a href="${a.github_url}" target="_blank" class="btn btn-sm btn-secondary">GitHub</a>` : ''}
              ${a.linkedin_url ? `<a href="${a.linkedin_url}" target="_blank" class="btn btn-sm btn-secondary">LinkedIn</a>` : ''}
            </div>

            <button class="btn btn-sm btn-primary" onclick="Company.openStatusModal(${a.id}, '${a.student_name}', '${a.status}')">
              Update Candidate Status
            </button>
          </div>
        </div>
      `;
    }).join('');
  },

  openStatusModal(appId, studentName, currentStatus) {
    document.getElementById('status-modal-app-id').value = appId;
    document.getElementById('status-modal-student-name').textContent = studentName;
    document.getElementById('status-modal-select').value = currentStatus;
    App.openModal('update-applicant-modal');
  },

  async submitStatusUpdate(event) {
    event.preventDefault();
    const form = event.target;
    const appId = form.application_id.value;
    const status = form.status.value;
    const remarks = form.remarks.value.trim();
    const interviewDate = form.interview_date.value;
    const salaryOffered = form.salary_offered.value.trim();

    try {
      const res = await fetch('api/portal.php?action=update_applicant_status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          application_id: appId,
          status: status,
          remarks: remarks,
          interview_date: interviewDate,
          salary_offered: salaryOffered
        })
      });
      const data = await res.json();
      if (data.success) {
        App.closeModal('update-applicant-modal');
        App.showToast(data.message, 'success');
        if (Company.selectedJobId) {
          Company.viewDriveApplicants(Company.selectedJobId);
        }
      } else {
        App.showToast(data.error || 'Failed to update candidate', 'danger');
      }
    } catch (e) {
      App.showToast('Network error updating candidate', 'danger');
    }
  }
};

window.Company = Company;
