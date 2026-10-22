// CareerPulse - Student Portal, Kanban Tracker & Performance Engine

const Student = {
  currentJobs: [],
  currentApplications: [],
  currentAppDetail: null,
  charts: {},

  // ================= 1. Job Portal =================
  async loadJobs() {
    const search = document.getElementById('job-search-input')?.value || '';
    const category = document.getElementById('job-category-filter')?.value || 'All';
    const type = document.getElementById('job-type-filter')?.value || 'All';
    const workplace = document.getElementById('job-workplace-filter')?.value || 'All';
    const sort = document.getElementById('job-sort-filter')?.value || 'newest';

    const params = new URLSearchParams({ search, category, type, workplace, sort });

    try {
      const res = await fetch(`api/portal.php?action=list_jobs&${params}`);
      const data = await res.json();
      if (data.success) {
        this.currentJobs = data.jobs;
        this.renderJobsList();
      }
    } catch (e) {
      console.error('Error loading jobs:', e);
    }
  },

  renderJobsList() {
    const container = document.getElementById('jobs-grid-container');
    if (!container) return;

    if (this.currentJobs.length === 0) {
      container.innerHTML = `
        <div style="grid-column: 1 / -1; text-align:center; padding:3rem; background:var(--bg-surface); border-radius:var(--radius-lg); border:1px solid var(--border-color);">
          <div style="margin-bottom:0.75rem; color:var(--text-muted);">
            <svg class="svg-icon" style="width:40px; height:40px;" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/></svg>
          </div>
          <h3 style="font-size:1.2rem; font-weight:700;">No vacancies match your criteria</h3>
          <p style="color:var(--text-secondary); margin-top:0.3rem;">Try adjusting your keyword search or filter selections.</p>
        </div>
      `;
      return;
    }

    container.innerHTML = this.currentJobs.map(j => {
      // Deadline formatting
      let deadlineBadge = '';
      if (j.is_expired) {
        deadlineBadge = `<span class="badge badge-red">Closed</span>`;
      } else if (j.days_left <= 2) {
        deadlineBadge = `<span class="badge deadline-badge urgent">Closes in ${j.days_left === 0 ? 'Today' : j.days_left + 'd'}</span>`;
      } else if (j.days_left <= 5) {
        deadlineBadge = `<span class="badge deadline-badge soon">${j.days_left} days left</span>`;
      } else {
        deadlineBadge = `<span class="badge" style="background:var(--bg-surface-alt); border:1px solid var(--border-color);">Deadline: ${j.deadline}</span>`;
      }

      // Eligibility pill
      let eligHtml = '';
      if (j.eligibility) {
        if (j.eligibility.is_eligible) {
          eligHtml = `
            <div class="eligibility-pill eligible">
              <svg class="svg-icon" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              <span>Eligible (${j.eligibility.student_cgpa} CGPA, Min: ${j.min_cgpa})</span>
            </div>
          `;
        } else {
          eligHtml = `
            <div class="eligibility-pill ineligible">
              <svg class="svg-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              <span>${j.eligibility.reason}</span>
            </div>
          `;
        }
      }

      // Match Score Meter
      let matchHtml = '';
      if (j.match_score !== null) {
        matchHtml = `
          <div class="match-meter">
            <span style="font-size:0.78rem; font-weight:700; color:var(--text-secondary);">Match: ${j.match_score}%</span>
            <div class="match-bar-bg">
              <div class="match-bar-fill" style="width: ${j.match_score}%;"></div>
            </div>
            <button class="btn btn-sm btn-outline" style="font-size:0.72rem; padding:0.15rem 0.45rem;" onclick="Student.openKeywordMatcher(${j.id})">Analyze</button>
          </div>
        `;
      }

      // Apply button / status
      let actionBtn = '';
      if (j.has_applied) {
        actionBtn = `<button class="btn btn-sm btn-success" disabled>Applied (${j.my_status})</button>`;
      } else if (j.is_expired) {
        actionBtn = `<button class="btn btn-sm btn-secondary" disabled>Closed</button>`;
      } else {
        actionBtn = `<button class="btn btn-sm btn-primary" onclick="Student.openApplyModal(${j.id})">Apply Now</button>`;
      }

      return `
        <div class="job-card">
          <div>
            <div class="job-card-header">
              <img src="${j.logo_url || 'assets/img/logo.svg'}" class="company-avatar" alt="${j.company_name}" onerror="this.src='assets/img/logo.svg'">
              <div style="flex:1;">
                <div class="job-company">${j.company_name}</div>
                <h3 class="job-title">${j.title}</h3>
                <div style="font-size:0.82rem; color:var(--text-muted); margin-top:0.2rem;">${j.location} &bull; ${j.workplace_type}</div>
              </div>
            </div>

            <div class="job-badges">
              <span class="badge badge-blue">${j.category}</span>
              <span class="badge badge-purple">${j.type}</span>
              <span class="badge" style="background:var(--success-light); color:var(--success); border:1px solid var(--success-border); font-weight:700;">${j.salary_range}</span>
              ${deadlineBadge}
            </div>

            ${eligHtml}
            ${matchHtml}

            <p style="font-size:0.85rem; color:var(--text-secondary); line-height:1.45; margin:0.75rem 0; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
              ${j.description}
            </p>
          </div>

          <div style="display:flex; align-items:center; justify-content:space-between; margin-top:1rem; padding-top:0.85rem; border-top:1px solid var(--border-color);">
            <button class="btn btn-sm btn-secondary" onclick="Student.viewJobDetails(${j.id})">Details</button>
            ${actionBtn}
          </div>
        </div>
      `;
    }).join('');
  },

  viewJobDetails(jobId) {
    const job = this.currentJobs.find(j => j.id == jobId);
    if (!job) return;

    const modal = document.getElementById('job-detail-modal');
    if (!modal) return;

    document.getElementById('job-detail-title').textContent = job.title;
    document.getElementById('job-detail-company').textContent = `${job.company_name} • ${job.location}`;
    document.getElementById('job-detail-salary').textContent = job.salary_range;
    document.getElementById('job-detail-deadline').textContent = job.deadline;
    document.getElementById('job-detail-cgpa').textContent = `Min CGPA: ${job.min_cgpa}`;
    document.getElementById('job-detail-branches').textContent = `Branches: ${job.eligible_branches}`;
    document.getElementById('job-detail-desc').innerHTML = job.description.replace(/\n/g, '<br>');
    document.getElementById('job-detail-reqs').innerHTML = job.requirements.replace(/\n/g, '<br>');
    document.getElementById('job-detail-skills').textContent = job.skills_required || 'None listed';

    const applyBtn = document.getElementById('job-detail-apply-btn');
    if (applyBtn) {
      if (job.has_applied) {
        applyBtn.textContent = `Applied (${job.my_status})`;
        applyBtn.disabled = true;
      } else {
        applyBtn.textContent = 'Apply Directly';
        applyBtn.disabled = false;
        applyBtn.onclick = () => {
          App.closeModal('job-detail-modal');
          Student.openApplyModal(job.id);
        };
      }
    }

    App.openModal('job-detail-modal');
  },

  openApplyModal(jobId) {
    if (!App.state.user) {
      App.showToast('Please log in as a student to apply.', 'warning');
      App.openModal('login-modal');
      return;
    }
    const job = this.currentJobs.find(j => j.id == jobId);
    if (!job) return;

    document.getElementById('apply-job-id').value = job.id;
    document.getElementById('apply-modal-title').textContent = `Apply to ${job.company_name}`;
    document.getElementById('apply-modal-subtitle').textContent = job.title;

    const resumeNotice = document.getElementById('apply-resume-notice');
    if (resumeNotice) {
      resumeNotice.textContent = App.state.user.resume_filename 
        ? `Profile CV Attached: ${App.state.user.resume_filename}` 
        : `No CV currently uploaded in profile. You can apply now and upload your CV later from your profile settings.`;
    }

    App.openModal('apply-modal');
  },

  async submitApplication(event) {
    event.preventDefault();
    const form = event.target;
    const jobId = form.job_id.value;
    const coverNote = form.cover_note.value.trim();

    try {
      const res = await fetch('api/portal.php?action=apply', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ job_id: jobId, cover_note: coverNote })
      });
      const data = await res.json();
      if (data.success) {
        App.closeModal('apply-modal');
        App.showToast('Application submitted successfully. Track it on your dashboard.', 'success');
        Student.loadJobs();
        Student.loadTracker();
      } else {
        App.showToast(data.error || 'Failed to submit application', 'danger');
      }
    } catch (e) {
      App.showToast('Network error while applying.', 'danger');
    }
  },

  // ================= 2. Resume Keyword Matcher =================
  openKeywordMatcher(jobId) {
    const job = this.currentJobs.find(j => j.id == jobId);
    if (!job) return;

    document.getElementById('km-job-text').value = `${job.title}\n${job.description}\n${job.requirements}\nSkills: ${job.skills_required}`;
    document.getElementById('km-skills-text').value = App.state.user?.skills || 'React, JavaScript, TypeScript, PHP, MySQL, Python';

    Student.analyzeKeywordMatch();
    App.openModal('keyword-matcher-modal');
  },

  async analyzeKeywordMatch() {
    const jobText = document.getElementById('km-job-text')?.value || '';
    const skillsText = document.getElementById('km-skills-text')?.value || '';

    if (!jobText.trim()) {
      App.showToast('Please provide a job description to analyze.', 'warning');
      return;
    }

    try {
      const res = await fetch('api/resume_match.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          job_description: jobText,
          student_skills: skillsText
        })
      });
      const data = await res.json();
      if (data.success) {
        document.getElementById('km-score-val').textContent = `${data.match_percentage}%`;
        document.getElementById('km-score-bar').style.width = `${data.match_percentage}%`;
        document.getElementById('km-verdict').textContent = data.verdict;

        const matchedContainer = document.getElementById('km-matched-tags');
        matchedContainer.innerHTML = data.matched_keywords.length > 0
          ? data.matched_keywords.map(k => `<span class="badge badge-green">${k}</span>`).join(' ')
          : `<span style="color:var(--text-muted); font-size:0.85rem;">None detected</span>`;

        const missingContainer = document.getElementById('km-missing-tags');
        missingContainer.innerHTML = data.missing_keywords.length > 0
          ? data.missing_keywords.map(k => `<span class="badge badge-yellow">${k}</span>`).join(' ')
          : `<span class="badge badge-green">Zero skill gaps found</span>`;
      }
    } catch (e) {
      console.error(e);
    }
  },

  // ================= 3. Interactive Kanban Board =================
  async loadTracker() {
    try {
      const res = await fetch('api/tracker.php?action=list');
      const data = await res.json();
      if (data.success) {
        this.currentApplications = data.applications;
        this.renderKanban(data.applications);
        this.renderSmartReminders(data.reminders);
        this.renderDashboardMetrics(data.metrics);
      }
    } catch (e) {
      console.error('Error loading tracker:', e);
    }
  },

  renderKanban(apps) {
    const columns = ['Wishlist', 'Applied', 'Screening', 'Interviewing', 'Offered', 'Rejected'];

    columns.forEach(col => {
      const countEl = document.getElementById(`kanban-count-${col.toLowerCase()}`);
      const containerEl = document.getElementById(`kanban-col-${col.toLowerCase()}`);
      if (!containerEl) return;

      const colApps = apps.filter(a => a.status === col);
      if (countEl) countEl.textContent = colApps.length;

      containerEl.innerHTML = colApps.map(a => `
        <div class="kanban-card" draggable="true" ondragstart="Student.handleDragStart(event, ${a.id})" onclick="Student.openAppDetails(${a.id})">
          <div class="kanban-card-company">${a.company_name}</div>
          <div class="kanban-card-title">${a.role_title}</div>
          ${a.salary_range ? `<div style="font-size:0.78rem; color:var(--success); font-weight:600;">${a.salary_range}</div>` : ''}
          ${a.interview_date ? `<div style="font-size:0.75rem; color:var(--primary); font-weight:700; margin-top:0.35rem;">Interview: ${new Date(a.interview_date).toLocaleDateString([], {month:'short', day:'numeric'})}</div>` : ''}
          <div class="kanban-card-meta">
            <span>${a.notes_count || 0} notes</span>
            <span style="color:var(--text-muted); font-size:0.72rem;">Updated ${new Date(a.updated_at).toLocaleDateString()}</span>
          </div>
        </div>
      `).join('');
    });
  },

  handleDragStart(event, appId) {
    event.dataTransfer.setData('text/plain', appId);
    event.target.classList.add('dragging');
    event.dataTransfer.effectAllowed = 'move';
  },

  handleDragOver(event) {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
    event.currentTarget.classList.add('drag-over');
  },

  handleDragLeave(event) {
    event.currentTarget.classList.remove('drag-over');
  },

  async handleDrop(event, targetStatus) {
    event.preventDefault();
    event.currentTarget.classList.remove('drag-over');
    const appId = event.dataTransfer.getData('text/plain');
    if (!appId) return;

    document.querySelectorAll('.kanban-card.dragging').forEach(c => c.classList.remove('dragging'));

    // Optimistic UI update
    const app = this.currentApplications.find(a => a.id == appId);
    if (app && app.status !== targetStatus) {
      app.status = targetStatus;
      this.renderKanban(this.currentApplications);

      try {
        const res = await fetch('api/tracker.php?action=update_status', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            application_id: appId,
            status: targetStatus,
            notes: `Moved card to ${targetStatus} via Kanban board`
          })
        });
        const data = await res.json();
        if (data.success) {
          App.showToast(`Updated to ${targetStatus}`, 'success');
          Student.loadTracker();
        } else {
          App.showToast(data.error || 'Failed to update status', 'danger');
          Student.loadTracker();
        }
      } catch (e) {
        App.showToast('Network error updating status.', 'danger');
        Student.loadTracker();
      }
    }
  },

  // ================= 4. Application Details, Timeline & Notes =================
  async openAppDetails(appId) {
    try {
      const res = await fetch(`api/tracker.php?action=get_details&application_id=${appId}`);
      const data = await res.json();
      if (data.success) {
        this.currentAppDetail = data;
        const app = data.application;

        document.getElementById('app-modal-company').textContent = app.company_name;
        document.getElementById('app-modal-role').textContent = app.role_title;
        document.getElementById('app-modal-status-select').value = app.status;
        document.getElementById('app-modal-app-id').value = app.id;

        // Render timeline
        const timelineContainer = document.getElementById('app-modal-timeline');
        timelineContainer.innerHTML = data.history.map(h => `
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <div style="font-weight:700; font-size:0.9rem; color:var(--text-primary);">
                ${h.old_status ? `${h.old_status} &rarr; ` : ''}<span style="color:var(--primary);">${h.new_status}</span>
              </div>
              <div style="font-size:0.82rem; color:var(--text-secondary); margin-top:0.2rem;">${h.notes || 'Status changed'}</div>
              <div style="font-size:0.72rem; color:var(--text-muted); margin-top:0.25rem;">${new Date(h.created_at).toLocaleString()}</div>
            </div>
          </div>
        `).join('') || '<div style="color:var(--text-muted); font-size:0.85rem;">No history events recorded yet.</div>';

        // Render interview prep notes
        const notesContainer = document.getElementById('app-modal-notes-list');
        notesContainer.innerHTML = data.notes.map(n => `
          <div style="background:var(--bg-surface-alt); padding:0.9rem; border-radius:var(--radius-md); border:1px solid var(--border-color); margin-bottom:0.75rem;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.4rem;">
              <span style="font-weight:700; font-size:0.92rem;">${n.round_name}</span>
              <span class="badge ${n.result === 'Passed' ? 'badge-green' : (n.result === 'Failed' ? 'badge-red' : 'badge-yellow')}">${n.result}</span>
            </div>
            ${n.questions_asked ? `<div style="font-size:0.85rem; margin-top:0.35rem;"><strong>Questions Asked:</strong><br><span style="white-space:pre-wrap; color:var(--text-secondary);">${n.questions_asked}</span></div>` : ''}
            ${n.notes ? `<div style="font-size:0.85rem; margin-top:0.4rem;"><strong>Key Takeaways:</strong><br><span style="white-space:pre-wrap; color:var(--text-secondary);">${n.notes}</span></div>` : ''}
            <div style="font-size:0.72rem; color:var(--text-muted); margin-top:0.4rem;">Recorded on ${n.interview_date || 'N/A'}</div>
          </div>
        `).join('') || '<div style="color:var(--text-muted); font-size:0.85rem; margin-bottom:1rem;">No interview prep notes added yet. Record your questions and lessons learned below!</div>';

        App.openModal('app-details-modal');
      }
    } catch (e) {
      console.error(e);
    }
  },

  async updateAppStatusFromModal() {
    const appId = document.getElementById('app-modal-app-id')?.value;
    const newStatus = document.getElementById('app-modal-status-select')?.value;
    if (!appId || !newStatus) return;

    try {
      const res = await fetch('api/tracker.php?action=update_status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ application_id: appId, status: newStatus })
      });
      const data = await res.json();
      if (data.success) {
        App.showToast(`Status updated to ${newStatus}`, 'success');
        this.openAppDetails(appId);
        this.loadTracker();
      }
    } catch (e) {
      App.showToast('Failed to update status', 'danger');
    }
  },

  async addInterviewNote(event) {
    event.preventDefault();
    const form = event.target;
    const appId = document.getElementById('app-modal-app-id')?.value;
    const roundName = form.round_name.value.trim();
    const interviewDate = form.interview_date.value;
    const questionsAsked = form.questions_asked.value.trim();
    const notes = form.notes.value.trim();
    const result = form.result.value;

    if (!roundName) {
      App.showToast('Please specify the interview round name', 'warning');
      return;
    }

    try {
      const res = await fetch('api/tracker.php?action=add_interview_note', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          application_id: appId,
          round_name: roundName,
          interview_date: interviewDate,
          questions_asked: questionsAsked,
          notes: notes,
          result: result
        })
      });
      const data = await res.json();
      if (data.success) {
        App.showToast('Interview note recorded!', 'success');
        form.reset();
        this.openAppDetails(appId);
      }
    } catch (e) {
      App.showToast('Error saving interview note', 'danger');
    }
  },

  async deleteCurrentApplication() {
    const appId = document.getElementById('app-modal-app-id')?.value;
    if (!appId) return;

    if (!confirm('Are you sure you want to remove this application from your tracker?')) return;

    try {
      const res = await fetch('api/tracker.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ application_id: appId })
      });
      const data = await res.json();
      if (data.success) {
        App.closeModal('app-details-modal');
        App.showToast('Application deleted', 'info');
        this.loadTracker();
      }
    } catch (e) {
      App.showToast('Error deleting application', 'danger');
    }
  },

  async submitExternalApp(event) {
    event.preventDefault();
    const form = event.target;
    const company = form.company_name.value.trim();
    const role = form.role_title.value.trim();
    const status = form.status.value;
    const notes = form.notes.value.trim();

    if (!company || !role) {
      App.showToast('Please specify Company and Role Title.', 'warning');
      return;
    }

    try {
      const res = await fetch('api/tracker.php?action=add_external', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ company_name: company, role_title: role, status, notes })
      });
      const data = await res.json();
      if (data.success) {
        App.closeModal('add-external-modal');
        form.reset();
        App.showToast('Off-campus application tracked!', 'success');
        this.loadTracker();
      }
    } catch (e) {
      App.showToast('Error tracking application.', 'danger');
    }
  },

  // ================= 5. Smart Reminders & Dashboard Charts =================
  renderSmartReminders(reminders) {
    const box = document.getElementById('student-reminders-box');
    if (!box) return;

    if (!reminders || reminders.length === 0) {
      box.style.display = 'none';
      return;
    }

    box.style.display = 'flex';
    box.innerHTML = `
      <div style="display:flex; align-items:center; color:var(--warning);">
        <svg class="svg-icon" style="width:24px; height:24px;" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
      <div style="flex:1;">
        <strong style="font-size:0.92rem; color:#92400E;">Application Follow-Up &amp; Schedule Alerts (${reminders.length}):</strong>
        <div style="font-size:0.85rem; margin-top:0.2rem; color:var(--text-secondary);">
          ${reminders.map(r => `&bull; ${r.message}`).join('<br>')}
        </div>
      </div>
    `;
  },

  renderDashboardMetrics(metrics) {
    if (!metrics) return;
    const totalEl = document.getElementById('stat-student-total');
    const interviewEl = document.getElementById('stat-student-interview');
    const offerEl = document.getElementById('stat-student-offers');
    const rateEl = document.getElementById('stat-student-rate');

    if (totalEl) totalEl.textContent = metrics.total;
    if (interviewEl) interviewEl.textContent = metrics.interviewing;
    if (offerEl) offerEl.textContent = metrics.offered;
    if (rateEl) rateEl.textContent = `${metrics.offer_rate}%`;

    this.renderCharts(metrics);
  },

  async loadDashboard() {
    await this.loadTracker();
  },

  renderCharts(metrics) {
    // 1. Doughnut Chart: Status Breakdown
    const donutCtx = document.getElementById('student-status-chart')?.getContext('2d');
    if (donutCtx) {
      if (this.charts.statusChart) this.charts.statusChart.destroy();
      this.charts.statusChart = new Chart(donutCtx, {
        type: 'doughnut',
        data: {
          labels: ['Wishlist', 'Applied', 'Screening', 'Interviewing', 'Offered', 'Rejected'],
          datasets: [{
            data: [
              metrics.wishlist || 0,
              metrics.applied || 0,
              metrics.screening || 0,
              metrics.interviewing || 0,
              metrics.offered || 0,
              metrics.rejected || 0
            ],
            backgroundColor: ['#94A3B8', '#3B82F6', '#F59E0B', '#8B5CF6', '#10B981', '#EF4444'],
            borderWidth: 2,
            borderColor: 'transparent'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
          },
          cutout: '68%'
        }
      });
    }

    // 2. Bar Chart: Conversion Funnel
    const barCtx = document.getElementById('student-funnel-chart')?.getContext('2d');
    if (barCtx) {
      if (this.charts.funnelChart) this.charts.funnelChart.destroy();
      this.charts.funnelChart = new Chart(barCtx, {
        type: 'bar',
        data: {
          labels: ['Applied', 'Screening', 'Interviewing', 'Offers'],
          datasets: [{
            label: 'Candidate Velocity',
            data: [
              metrics.applied + metrics.screening + metrics.interviewing + metrics.offered,
              metrics.screening + metrics.interviewing + metrics.offered,
              metrics.interviewing + metrics.offered,
              metrics.offered
            ],
            backgroundColor: ['#3B82F6', '#F59E0B', '#8B5CF6', '#10B981'],
            borderRadius: 6
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
    }
  },

  // ================= 6. Calendar View =================
  loadCalendar() {
    const list = document.getElementById('calendar-events-list');
    if (!list) return;

    const events = [];
    this.currentApplications.forEach(a => {
      if (a.interview_date) {
        events.push({
          type: 'interview',
          title: `Interview: ${a.company_name} (${a.role_title})`,
          date: new Date(a.interview_date),
          raw: a.interview_date
        });
      }
      if (a.deadline) {
        events.push({
          type: 'deadline',
          title: `Deadline: ${a.company_name} (${a.role_title})`,
          date: new Date(a.deadline),
          raw: a.deadline
        });
      }
    });

    events.sort((a, b) => a.date - b.date);

    if (events.length === 0) {
      list.innerHTML = `<div style="text-align:center; padding:2.5rem; color:var(--text-muted);">No upcoming interviews or application deadlines scheduled.</div>`;
      return;
    }

    list.innerHTML = events.map(ev => {
      const iconSvg = ev.type === 'interview'
        ? `<svg class="svg-icon" style="width:24px; height:24px; color:var(--purple);" viewBox="0 0 24 24"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="22"/></svg>`
        : `<svg class="svg-icon" style="width:24px; height:24px; color:var(--warning);" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`;

      return `
        <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
          <div style="display:flex; align-items:center; gap:1rem;">
            <div>${iconSvg}</div>
            <div>
              <div style="font-weight:700; font-size:0.95rem;">${ev.title}</div>
              <div style="font-size:0.82rem; color:var(--text-secondary); margin-top:0.2rem;">${ev.date.toLocaleString([], { dateStyle: 'full', timeStyle: ev.type === 'interview' ? 'short' : undefined })}</div>
            </div>
          </div>
          <span class="badge ${ev.type === 'interview' ? 'badge-purple' : 'badge-yellow'}">${ev.type.toUpperCase()}</span>
        </div>
      `;
    }).join('');
  },

  exportCSV() {
    window.location.href = 'api/tracker.php?action=export_csv';
  },

  exportICS() {
    window.location.href = 'api/tracker.php?action=export_ics';
    App.showToast('Schedule downloaded (.ics). Import into Google Calendar or Outlook!', 'success');
  }
};

window.Student = Student;
