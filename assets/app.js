// CareerPulse - Core Application Engine & State Manager
// Handles Theme, Navigation, Modals, Toasts, and Notifications

const App = {
  state: {
    user: null,
    theme: localStorage.getItem('cp_theme') || 'light',
    currentTab: 'home',
    notifications: [],
    unreadNotifs: 0
  },

  init() {
    this.applyTheme(this.state.theme);
    this.bindEvents();
    this.checkAuthStatus();
  },

  applyTheme(theme) {
    this.state.theme = theme;
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('cp_theme', theme);
    const icon = document.getElementById('theme-icon');
    if (icon) {
      icon.innerHTML = theme === 'dark' 
        ? `<svg class="svg-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>` 
        : `<svg class="svg-icon" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>`;
    }
  },

  toggleTheme() {
    const next = this.state.theme === 'dark' ? 'light' : 'dark';
    this.applyTheme(next);
  },

  showToast(message, type = 'info', duration = 4000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icons = {
      success: `<svg class="svg-icon" style="color:var(--success);" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>`,
      danger: `<svg class="svg-icon" style="color:var(--danger);" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
      warning: `<svg class="svg-icon" style="color:var(--warning);" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
      info: `<svg class="svg-icon" style="color:var(--primary);" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`
    };

    toast.innerHTML = `
      <span style="display:inline-flex; align-items:center;">${icons[type] || icons.info}</span>
      <div style="flex:1; font-size:0.9rem; font-weight:500;">${message}</div>
      <button style="background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:1.1rem; line-height:1;" onclick="this.parentElement.remove()">&times;</button>
    `;

    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(10px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, duration);
  },

  openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.add('show');
      document.body.style.overflow = 'hidden';
    }
  },

  closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove('show');
      document.body.style.overflow = '';
    }
  },

  closeAllModals() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('show'));
    document.body.style.overflow = '';
  },

  bindEvents() {
    // Theme toggle
    const themeBtn = document.getElementById('theme-toggle-btn');
    if (themeBtn) {
      themeBtn.addEventListener('click', () => this.toggleTheme());
    }

    // Modal background close
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('modal-overlay')) {
        this.closeAllModals();
      }
      // Notification dropdown outside click
      const notifPanel = document.getElementById('notif-panel');
      const notifBtn = document.getElementById('notif-bell-btn');
      if (notifPanel && notifPanel.classList.contains('show')) {
        if (!notifPanel.contains(e.target) && !notifBtn.contains(e.target)) {
          notifPanel.classList.remove('show');
        }
      }
    });

    // ESC key closes modals
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') this.closeAllModals();
    });

    // Notification bell click
    const notifBtn = document.getElementById('notif-bell-btn');
    if (notifBtn) {
      notifBtn.addEventListener('click', () => {
        const panel = document.getElementById('notif-panel');
        if (panel) {
          panel.classList.toggle('show');
          if (panel.classList.contains('show')) {
            this.loadNotifications();
          }
        }
      });
    }
  },

  async checkAuthStatus() {
    try {
      const res = await fetch('api/auth.php?action=status');
      const data = await res.json();
      this.state.user = data.user;
      this.renderNav();
      
      if (this.state.user) {
        this.loadNotifications();
        // Route to their primary role tab
        if (this.state.user.role === 'student') {
          this.switchTab('student-dashboard');
        } else if (this.state.user.role === 'company') {
          this.switchTab('company-dashboard');
        } else if (this.state.user.role === 'admin') {
          this.switchTab('admin-dashboard');
        }
      } else {
        this.switchTab('home');
      }
    } catch (e) {
      console.error('Auth check error:', e);
      this.renderNav();
      this.switchTab('home');
    }
  },

  renderNav() {
    const navLinks = document.getElementById('nav-links');
    const navActions = document.getElementById('nav-actions');
    const user = this.state.user;

    if (!navLinks || !navActions) return;

    const bellSvg = `<svg class="svg-icon" viewBox="0 0 24 24"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>`;

    if (!user) {
      navLinks.innerHTML = `
        <li><a class="nav-link" onclick="App.switchTab('home')">Home</a></li>
        <li><a class="nav-link" onclick="App.switchTab('portal')">Browse Drives</a></li>
        <li><a class="nav-link" onclick="App.switchTab('stats')">Placement Statistics</a></li>
      `;
      navActions.innerHTML = `
        <button class="btn btn-secondary btn-sm" onclick="App.openModal('login-modal')">Log In</button>
        <button class="btn btn-primary btn-sm" onclick="App.openModal('register-modal')">Get Started</button>
      `;
    } else if (user.role === 'student') {
      const avatarHtml = user.avatar_url
        ? `<img src="${user.avatar_url}" class="user-avatar" style="object-fit:cover;" alt="Avatar">`
        : `<div class="user-avatar">${user.name.charAt(0).toUpperCase()}</div>`;

      navLinks.innerHTML = `
        <li><a class="nav-link" id="nav-student-dashboard" onclick="App.switchTab('student-dashboard')">Dashboard</a></li>
        <li><a class="nav-link" id="nav-portal" onclick="App.switchTab('portal')">Job Portal</a></li>
        <li><a class="nav-link" id="nav-tracker" onclick="App.switchTab('tracker')">Applications Tracker</a></li>
        <li><a class="nav-link" id="nav-calendar" onclick="App.switchTab('calendar')">Schedule</a></li>
        <li><a class="nav-link" id="nav-stats" onclick="App.switchTab('stats')">Campus Statistics</a></li>
      `;
      navActions.innerHTML = `
        <div class="notif-dropdown-wrapper">
          <button class="btn-icon theme-toggle-btn" id="notif-bell-btn" title="Notifications">
            ${bellSvg}
            <span class="notif-badge" id="notif-badge" style="display:none;">0</span>
          </button>
          <div class="notif-panel" id="notif-panel">
            <div class="notif-header">
              <span style="font-weight:700; font-size:0.9rem;">Notifications</span>
              <button class="btn btn-sm btn-outline" style="font-size:0.75rem;" onclick="App.markAllNotifsRead()">Mark all read</button>
            </div>
            <div class="notif-list" id="notif-list">
              <div style="padding:1.5rem; text-align:center; color:var(--text-muted); font-size:0.85rem;">No notifications right now.</div>
            </div>
          </div>
        </div>
        <div class="user-pill" onclick="App.openModal('profile-modal')" title="Edit Profile &amp; CV">
          ${avatarHtml}
          <span style="font-weight:600; font-size:0.88rem;">${user.name.split(' ')[0]}</span>
          <span class="user-role-badge role-student">Student</span>
        </div>
        <button class="btn btn-secondary btn-sm" onclick="Auth.logout()">Logout</button>
      `;
    } else if (user.role === 'company') {
      const avatarHtml = user.avatar_url
        ? `<img src="${user.avatar_url}" class="user-avatar" style="object-fit:cover;" alt="Logo">`
        : `<div class="user-avatar" style="background:var(--purple);">${user.company_name ? user.company_name.charAt(0).toUpperCase() : 'C'}</div>`;

      navLinks.innerHTML = `
        <li><a class="nav-link" id="nav-company-dashboard" onclick="App.switchTab('company-dashboard')">Dashboard</a></li>
        <li><a class="nav-link" id="nav-manage-drives" onclick="App.switchTab('manage-drives')">Manage Vacancies</a></li>
        <li><a class="nav-link" id="nav-applicants" onclick="App.switchTab('applicants')">Applicants Review</a></li>
        <li><a class="nav-link" id="nav-stats" onclick="App.switchTab('stats')">Campus Statistics</a></li>
      `;
      navActions.innerHTML = `
        <button class="btn btn-primary btn-sm" onclick="App.openModal('post-job-modal')">+ Post Vacancy</button>
        <div class="notif-dropdown-wrapper">
          <button class="btn-icon theme-toggle-btn" id="notif-bell-btn" title="Notifications">
            ${bellSvg}
            <span class="notif-badge" id="notif-badge" style="display:none;">0</span>
          </button>
          <div class="notif-panel" id="notif-panel">
            <div class="notif-header">
              <span style="font-weight:700; font-size:0.9rem;">Notifications</span>
              <button class="btn btn-sm btn-outline" style="font-size:0.75rem;" onclick="App.markAllNotifsRead()">Mark all read</button>
            </div>
            <div class="notif-list" id="notif-list">
              <div style="padding:1.5rem; text-align:center; color:var(--text-muted); font-size:0.85rem;">No notifications right now.</div>
            </div>
          </div>
        </div>
        <div class="user-pill" onclick="App.openModal('profile-modal')" title="Edit Company Profile">
          ${avatarHtml}
          <span style="font-weight:600; font-size:0.88rem;">${user.company_name || user.name}</span>
          <span class="user-role-badge role-company">Recruiter</span>
        </div>
        <button class="btn btn-secondary btn-sm" onclick="Auth.logout()">Logout</button>
      `;
    } else if (user.role === 'admin') {
      navLinks.innerHTML = `
        <li><a class="nav-link" id="nav-admin-dashboard" onclick="App.switchTab('admin-dashboard')">Console</a></li>
        <li><a class="nav-link" id="nav-admin-moderation" onclick="App.switchTab('admin-moderation')">Moderation Queue</a></li>
        <li><a class="nav-link" id="nav-admin-users" onclick="App.switchTab('admin-users')">User Accounts</a></li>
        <li><a class="nav-link" id="nav-admin-logs" onclick="App.switchTab('admin-logs')">Audit Trail</a></li>
        <li><a class="nav-link" id="nav-stats" onclick="App.switchTab('stats')">Placement Statistics</a></li>
      `;
      navActions.innerHTML = `
        <div class="user-pill" onclick="App.openModal('profile-modal')">
          <div class="user-avatar" style="background:var(--warning); color:#78350F;">A</div>
          <span style="font-weight:600; font-size:0.88rem;">Administrator</span>
          <span class="user-role-badge role-admin">Dean</span>
        </div>
        <button class="btn btn-secondary btn-sm" onclick="Auth.logout()">Logout</button>
      `;
    }

    // Re-bind bell
    const notifBtn = document.getElementById('notif-bell-btn');
    if (notifBtn) {
      notifBtn.onclick = (e) => {
        e.stopPropagation();
        const panel = document.getElementById('notif-panel');
        if (panel) {
          panel.classList.toggle('show');
          if (panel.classList.contains('show')) App.loadNotifications();
        }
      };
    }
  },

  switchTab(tabId) {
    this.state.currentTab = tabId;
    document.querySelectorAll('.app-section').forEach(sec => sec.style.display = 'none');
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));

    const activeSec = document.getElementById(`section-${tabId}`);
    if (activeSec) {
      activeSec.style.display = 'block';
    }

    const activeLink = document.getElementById(`nav-${tabId}`);
    if (activeLink) {
      activeLink.classList.add('active');
    }

    // Tab-specific lifecycle hooks
    if (tabId === 'portal') {
      Student.loadJobs();
    } else if (tabId === 'student-dashboard') {
      Student.loadDashboard();
    } else if (tabId === 'tracker') {
      Student.loadTracker();
    } else if (tabId === 'calendar') {
      Student.loadCalendar();
    } else if (tabId === 'company-dashboard') {
      Company.loadDashboard();
    } else if (tabId === 'manage-drives') {
      Company.loadDrives();
    } else if (tabId === 'applicants') {
      Company.loadApplicants();
    } else if (tabId === 'admin-dashboard') {
      Admin.loadDashboard();
    } else if (tabId === 'admin-moderation') {
      Admin.loadModeration();
    } else if (tabId === 'admin-users') {
      Admin.loadUsers();
    } else if (tabId === 'admin-logs') {
      Admin.loadLogs();
    } else if (tabId === 'stats') {
      Admin.loadPublicStats();
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
  },

  async loadNotifications() {
    if (!this.state.user) return;
    try {
      const res = await fetch('api/notifications.php?action=list');
      const data = await res.json();
      if (data.success) {
        this.state.notifications = data.notifications;
        this.state.unreadNotifs = data.unread_count;
        this.renderNotifications();
      }
    } catch (e) {
      console.error('Error fetching notifications:', e);
    }
  },

  renderNotifications() {
    const badge = document.getElementById('notif-badge');
    const list = document.getElementById('notif-list');
    if (badge) {
      if (this.state.unreadNotifs > 0) {
        badge.textContent = this.state.unreadNotifs;
        badge.style.display = 'flex';
      } else {
        badge.style.display = 'none';
      }
    }

    if (!list) return;

    if (this.state.notifications.length === 0) {
      list.innerHTML = `<div style="padding:1.5rem; text-align:center; color:var(--text-muted); font-size:0.85rem;">No notifications right now.</div>`;
      return;
    }

    list.innerHTML = this.state.notifications.map(n => `
      <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}" onclick="App.handleNotifClick(${n.id}, '${n.link || ''}')">
        <div class="notif-title">${n.title}</div>
        <div class="notif-msg">${n.message}</div>
        <div class="notif-time">${new Date(n.created_at).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' })}</div>
      </div>
    `).join('');
  },

  async handleNotifClick(id, link) {
    try {
      await fetch('api/notifications.php?action=mark_read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      this.loadNotifications();
    } catch (e) {}

    const panel = document.getElementById('notif-panel');
    if (panel) panel.classList.remove('show');

    if (link) {
      const cleanTab = link.replace('#', '');
      if (cleanTab) this.switchTab(cleanTab);
    }
  },

  async markAllNotifsRead() {
    try {
      await fetch('api/notifications.php?action=mark_all_read', { method: 'POST' });
      this.loadNotifications();
      App.showToast('All notifications marked as read', 'success');
    } catch (e) {}
  },

  // 1-Click Demo Login Helper
  quickLogin(email, password) {
    Auth.submitLogin(email, password);
  }
};

window.App = App;
document.addEventListener('DOMContentLoaded', () => App.init());
