# 🚀 CareerPulse — Next-Gen Job & Internship Acceleration Platform

**CareerPulse** is a modern, responsive web application engineered for campus placement drives, off-campus internship tracking, corporate recruiting, and university placement administration.

Optimized specifically for **WAMP Server (PHP 8.x + MySQL 8.x PDO)**, the platform connects **Students**, **Companies**, and **University Administrators** into a unified, high-velocity recruitment pipeline.

---

## 🌐 Live Local Access (WAMP Server)

With WAMP Server running, open your web browser:
👉 **[http://localhost/careerpulse/](http://localhost/careerpulse/)**

Or visit the standalone public placement report:
👉 **[http://localhost/careerpulse/stats.php](http://localhost/careerpulse/stats.php)**

> **Active Directory:** `C:\Users\dell\.gemini\antigravity\scratch\careerpulse`  
> **WAMP Web Root Junction:** `C:\wamp64\www\careerpulse`  
> *(Linked via Windows directory junction `mklink /J` — edits in either location sync automatically)*

---

## ⚡ 1-Click Demo Login Credentials

The top bar of the application features a **1-Click Demo Switcher** for instant testing without manual typing:

| Role | Account Name | Email | Password | Key Showcase |
|---|---|---|---|---|
| 🎓 **Student** | Alex Rivera (CS, 8.75 CGPA) | `alex@university.edu` | `student123` | Active Kanban board, Stripe Offer, Google Interview, Reminders |
| 🎓 **Student** | Priya Sharma (IT, 7.40 CGPA) | `priya@university.edu` | `student123` | Eligibility checks (displays CGPA delta warnings) |
| 🏢 **Recruiter** | Stripe Campus Team | `recruiter@stripe.com` | `company123` | Vacancy pipeline, candidate shortlisting, candidate status updates |
| 🏢 **Recruiter** | Google University Hiring | `careers@google.com` | `company123` | Cloud & ML drives, interview loop management |
| 🛡️ **Administrator** | Dr. Arthur Vance (Dean) | `admin@careerpulse.com` | `admin123` | Drive moderation queue, branch placement charts, audit logs |

---

## 🌟 Wow Factors & Standout Features

### 1. 📋 Drag-and-Drop Kanban Board (Plain JavaScript + HTML5 Drag API)
- 6 organized recruitment columns: `Wishlist`, `Applied`, `Screening / OA`, `Interviewing`, `Offered`, and `Rejected`.
- Dragging cards between columns triggers optimistic UI state and saves changes asynchronously to MySQL via `api/tracker.php?action=update_status`.
- Clicking any card opens a rich modal featuring the **Status History Timeline** and **Interview Prep Notes**.

### 2. 🎯 Real-Time Eligibility Checker
- Compares student profile metrics (CGPA, Academic Branch, Graduation Year) against vacancy criteria.
- Dynamic color-coded badges:
  - `✅ You're Eligible (8.75 CGPA, Min: 7.50)`
  - `⚠️ Needs 8.0 CGPA, you have 7.4`

### 3. 🔍 Intelligent ATS Resume Keyword Matcher
- Paste any job description to compare its requirements against profile skills.
- Returns a dynamic **Match Percentage (%)**, highlights **Found Matching Keywords** in green, detects **Skill Gaps** in amber, and provides actionable resume tips.

### 4. ⏰ Deadline Countdowns with Adaptive Urgency
- Real-time countdowns on active drives:
  - **Urgent (≤ 2 days):** Pulsating red alert badge (`⏰ Closes in 2d`).
  - **Soon (≤ 5 days):** Amber warning badge (`⏳ 5 days left`).
  - **Normal:** Standard slate badge with scheduled closing date.

### 5. 💡 Automated Smart Reminders
- Generates proactive notifications:
  - *"It has been 7 days since your last status update with Microsoft. Consider sending a polite follow-up inquiry."*
  - *"Upcoming interview with Google on Oct 23 at 02:00 PM!"*

### 6. 📅 Interactive Calendar & .ICS Export
- Visual chronological timeline of interviews and deadlines.
- **Export to .ICS**: Generates an iCalendar file compatible with Google Calendar, Apple Calendar, and Outlook.
- **Export to CSV**: 1-click tabular export of all tracked applications.

### 7. 🛡️ Admin Moderation Queue & Platform Analytics
- Company drive submissions require admin approval before going live to students.
- Interactive **Chart.js** visualizations:
  - Branch-wise placement percentages (Computer Science, IT, Electronics).
  - Hiring stage velocity funnel.
  - Top corporate recruiters ranked by hires and applicant volume.
  - Live system audit trail recording IP addresses, user actions, and timestamps.

---

## 🏗️ Technical Architecture & REST-Style Endpoints

```
Browser (Vanilla HTML5 + Modern CSS3 + Modular ES6 JavaScript + Chart.js)
        │
        │  fetch() / JSON REST requests with session cookies
        ▼
PHP Backend (WAMP / Apache / PHP 8.x PDO)
  ├── api/auth.php           Session login, registration, role checks, profile update, resume upload
  ├── api/tracker.php        Student personal Kanban CRUD, timeline, prep notes, CSV & .ICS exports
  ├── api/portal.php         Campus drives listing, search/filter, eligibility, direct apply, recruiter tools
  ├── api/admin.php          Platform analytics, drive moderation, account management, audit logs
  ├── api/notifications.php  In-app bell notification dispatcher and status toggles
  └── api/resume_match.php   ATS keyword extraction and gap analysis
        │
        │  PDO (Prepared Statements with SQL Injection Defense)
        ▼
MySQL 8.x Database (`careerpulse_db`)
  ├── users                  User accounts, password hashes (bcrypt), roles (student/company/admin)
  ├── student_profiles       CGPA, branch, graduation year, skills, resume filename, bio
  ├── company_profiles       Company name, industry, headquarters, website, verification flag
  ├── jobs                   Vacancies, requirements, deadlines, criteria, moderation status
  ├── applications           Internal & external applications with status, cover notes, salary
  ├── application_history    Status change history and timeline logs
  ├── interview_notes        Rounds cleared, questions asked, difficulty, takeaways
  ├── notifications          In-app alerts with read/unread tracking
  └── activity_logs          System-wide audit trail with client IP addresses
```

---

## 🛠️ Re-seeding or Resetting the Database

The database is configured to auto-initialize on first run. If you ever want to re-seed fresh demo data from the command line:

```powershell
# Run using WAMP PHP
& "C:\wamp64\bin\php\php8.2.29\php.exe" "C:\Users\dell\.gemini\antigravity\scratch\careerpulse\setup_db.php"
```
