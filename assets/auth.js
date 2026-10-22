// CareerPulse - Authentication & Profile Manager
const Auth = {
  async submitLogin(email, password) {
    try {
      const res = await fetch('api/auth.php?action=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });
      const data = await res.json();

      if (data.success) {
        App.state.user = data.user;
        App.closeAllModals();
        App.renderNav();
        App.showToast(`Welcome back, ${data.user.name}!`, 'success');

        if (data.user.role === 'student') {
          App.switchTab('student-dashboard');
        } else if (data.user.role === 'company') {
          App.switchTab('company-dashboard');
        } else if (data.user.role === 'admin') {
          App.switchTab('admin-dashboard');
        }
      } else {
        App.showToast(data.error || 'Login failed. Please check credentials.', 'danger');
      }
    } catch (e) {
      console.error(e);
      App.showToast('Server connection error. Please verify WAMP is running.', 'danger');
    }
  },

  async handleLoginForm(event) {
    event.preventDefault();
    const form = event.target;
    const email = form.email.value.trim();
    const password = form.password.value;
    if (!email || !password) {
      App.showToast('Please enter both email and password.', 'warning');
      return;
    }
    await this.submitLogin(email, password);
  },

  async handleRegisterForm(event) {
    event.preventDefault();
    const form = event.target;
    const role = form.role.value;
    const name = form.name.value.trim();
    const email = form.email.value.trim();
    const password = form.password.value;

    if (!name || !email || !password) {
      App.showToast('Please fill in all required fields.', 'warning');
      return;
    }

    const payload = { role, name, email, password };

    if (role === 'student') {
      payload.department = form.department ? form.department.value : 'Computer Science';
      payload.cgpa = form.cgpa ? parseFloat(form.cgpa.value) : 8.0;
      payload.graduation_year = form.graduation_year ? parseInt(form.graduation_year.value) : 2026;
      payload.skills = form.skills ? form.skills.value.trim() : '';
      payload.phone = form.phone ? form.phone.value.trim() : '';
    } else if (role === 'company') {
      payload.company_name = form.company_name ? form.company_name.value.trim() : name;
      payload.website = form.website ? form.website.value.trim() : '';
      payload.industry = form.industry ? form.industry.value : 'Technology';
      payload.location = form.location ? form.location.value.trim() : 'Bengaluru, India';
      payload.description = form.description ? form.description.value.trim() : '';
    }

    try {
      const res = await fetch('api/auth.php?action=register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();

      if (data.success) {
        App.state.user = data.user;
        App.closeAllModals();
        App.renderNav();
        App.showToast(`Account created successfully! Welcome to CareerPulse, ${data.user.name}.`, 'success');

        if (role === 'student') {
          App.switchTab('portal');
        } else {
          App.switchTab('company-dashboard');
        }
      } else {
        App.showToast(data.error || 'Registration failed.', 'danger');
      }
    } catch (e) {
      console.error(e);
      App.showToast('Server connection error during registration.', 'danger');
    }
  },

  async handleProfileUpdate(event) {
    event.preventDefault();
    const form = event.target;
    const user = App.state.user;
    if (!user) return;

    const payload = {
      name: form.name.value.trim()
    };

    if (user.role === 'student') {
      payload.phone = form.phone.value.trim();
      payload.department = form.department.value;
      payload.cgpa = parseFloat(form.cgpa.value);
      payload.graduation_year = parseInt(form.graduation_year.value);
      payload.skills = form.skills.value.trim();
      payload.bio = form.bio.value.trim();
      payload.github_url = form.github_url.value.trim();
      payload.linkedin_url = form.linkedin_url.value.trim();
    } else if (user.role === 'company') {
      payload.company_name = form.company_name.value.trim();
      payload.website = form.website.value.trim();
      payload.industry = form.industry.value;
      payload.location = form.location.value.trim();
      payload.description = form.description.value.trim();
    }

    try {
      const res = await fetch('api/auth.php?action=update_profile', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        App.state.user = data.user;
        App.closeModal('profile-modal');
        App.renderNav();
        App.showToast('Profile updated successfully!', 'success');
        if (user.role === 'student') {
          Student.loadDashboard();
        }
      } else {
        App.showToast(data.error || 'Failed to update profile', 'danger');
      }
    } catch (e) {
      App.showToast('Error updating profile.', 'danger');
    }
  },

  async handleResumeUpload(event) {
    if (event) event.preventDefault();
    const fileInput = document.getElementById('resume-file-input');
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
      App.showToast('Please select a CV/Resume file (PDF, DOCX).', 'warning');
      return;
    }

    const formData = new FormData();
    formData.append('resume', fileInput.files[0]);

    try {
      const res = await fetch('api/auth.php?action=upload_resume', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data.success) {
        App.showToast('Resume uploaded and attached to your profile.', 'success');
        if (App.state.user) {
          App.state.user.resume_filename = data.filename;
        }
        const display = document.getElementById('current-resume-display');
        if (display) {
          display.innerHTML = `
            <div class="cv-status-card">
              <div class="flex items-center gap-2">
                <svg class="svg-icon" style="color:var(--primary);" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <div>
                  <div style="font-weight:600; font-size:0.88rem;">${data.filename}</div>
                  <div style="font-size:0.75rem; color:var(--text-muted);">Current Active Resume</div>
                </div>
              </div>
              <a href="assets/uploads/resumes/${data.filename}" target="_blank" class="btn btn-sm btn-outline">View File</a>
            </div>
          `;
        }
      } else {
        App.showToast(data.error || 'Resume upload failed', 'danger');
      }
    } catch (e) {
      App.showToast('Error uploading resume.', 'danger');
    }
  },

  async handleAvatarUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('avatar', file);

    try {
      const res = await fetch('api/auth.php?action=upload_avatar', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data.success) {
        App.showToast('Profile photo updated successfully.', 'success');
        App.state.user = data.user;
        const preview = document.getElementById('profile-avatar-preview');
        if (preview) {
          preview.src = data.avatar_url;
        }
        App.renderNav();
      } else {
        App.showToast(data.error || 'Failed to upload photo', 'danger');
      }
    } catch (e) {
      App.showToast('Error uploading profile picture.', 'danger');
    }
  },

  async logout() {
    try {
      await fetch('api/auth.php?action=logout');
      App.state.user = null;
      App.renderNav();
      App.switchTab('home');
      App.showToast('Logged out successfully', 'info');
    } catch (e) {
      console.error(e);
    }
  },

  toggleRoleFields(role) {
    const studentFields = document.getElementById('reg-student-fields');
    const companyFields = document.getElementById('reg-company-fields');
    if (studentFields && companyFields) {
      if (role === 'student') {
        studentFields.style.display = 'block';
        companyFields.style.display = 'none';
      } else {
        studentFields.style.display = 'none';
        companyFields.style.display = 'block';
      }
    }
  }
};

window.Auth = Auth;
