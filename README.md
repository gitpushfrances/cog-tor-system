# COG-TOR Academic Grading Management System

A comprehensive Academic Grading Management System built with Laravel 10, designed for educational institutions to manage grade submissions, approvals, and generate official academic documents (Certificate of Grades & Transcript of Records).

![Status](https://img.shields.io/badge/Status-In%20Development-yellow)
![Laravel](https://img.shields.io/badge/Laravel-10.x-red)
![PHP](https://img.shields.io/badge/PHP-8.4-blue)
![License](https://img.shields.io/badge/License-MIT-green)
![Progress](https://img.shields.io/badge/Progress-98%25-brightgreen)
![Phases](https://img.shields.io/badge/Phases-11%20Total-blue)

---

## Project Overview

This system streamlines the academic grading workflow with a multi-tier approval system: **Faculty → Head of Department → Registrar**, automated GWA computation, and official document generation (COG/TOR).

> **v2 Restructure — Phase 9 at 98%:** All major restructure work is done. Only 5 remaining SweetAlert actions and end-to-end test remain. See CHANGELOG.md for exact resume point (Step 44).

### Key Features
- **Four-Role System** — Admin, Faculty, Head of Department, Registrar
- **Grade Submission Workflow** — Faculty submits → Head of Department bulk approves → Registrar bulk finalizes per subject
- **Department-Scoped Head of Department** — Each Head of Department manages their department only via `department_id`
- **Student Management under Head of Department** — CRUD + bulk import/export per department
- **Enrollment Management under Head of Department** — Enroll/remove students per active semester
- **Faculty Assignment under Head of Department** — Assign subjects to faculty scoped by department
- **Rejection & Resubmission Flow** — Head of Department rejects with remarks → Faculty corrects → resubmits with remarks
- **Automated GWA Calculation** — Semester and cumulative weighted average
- **COG & TOR Generation** — PDF documents with official formatting, correct semester + school year labels
- **Search-First Document Flow** — Registrar searches student → Academic Profile → generates COG/TOR
- **Bulk Finalization** — Registrar finalizes all grades per subject at once with preview modal
- **Complete Audit Trail** — Spatie Activity Log tracks all grade changes
- **Excel Import/Export** — Head of Department: bulk student import/export | Faculty: grade template
- **Font Awesome 6.5** — Clean icon set throughout, no emoji rendering issues
- **SweetAlert2 Confirmations** — Registrar finalize confirmed; remaining 5 actions pending (Step 44)

---

## Current Status

| Phase | Description | Status |
|-------|-------------|--------|
| Phase 1–8 | Foundation through Excel Features | ✅ Complete |
| **Phase 9** | **System Restructure — YOU ARE HERE (98%)** | 🔄 In Progress |
| Phase 10 | Reporting & Analytics | 📅 Planned |
| Phase 11 | UI/UX Polish & Testing | 🔄 30% Done |

**Overall Progress: ~98%**

> **Resume point:** Step 44 — SweetAlert2 on remaining 5 actions. See CHANGELOG.md for exact checklist.

---

## System Workflow

```
ADMIN
  Scope: System configuration only. No student/grade access.
  - Manage Users (Faculty, Head of Department, Registrar)
    - Assign department_id when creating Faculty or Head of Department accounts (REQUIRED)
    - Tab filter by role (All / Faculty / Head of Department / Registrar)
    - Department column shows assignment or "Unassigned"
  - Manage Departments, Courses, Subjects
  - Configure School Years & Semesters
    - Status flow: upcoming → active → completed
    - Confirmation modal on activation

HEAD OF DEPARTMENT (per department — scoped by department_id)
  Scope: Full academic management, own department only.
  - Manage Students (CRUD + bulk Excel import/export)
  - Manage Enrollment (enroll/remove students per active semester)
  - Assign Subjects to Faculty (faculty scoped to same department)
  - Review Grade Submissions (bulk — full class table at once)
    - Approve All → forwards batch to Registrar
    - Reject → returns to Faculty with remarks
    - Resubmissions show Faculty remarks prominently
  - Dashboard stats (students, enrollments, pending/approved grades) all scoped to department

FACULTY
  Scope: Assigned subjects only.
  - Encode Grades (manual or Excel template upload)
  - Submit Full Class Batch to Head of Department
  - Grade table locks after submission
  - If rejected: red banner shows Head of Department's remarks, "Update & Resubmit" unlocks grades
  - Add Faculty remarks when resubmitting

REGISTRAR
  Scope: Official records and document generation only.
  - Finalization Queue tab — subjects grouped with student count
    - Preview modal — see all students + grades before finalizing
    - Finalize All per subject — SweetAlert2 confirm before locking permanently
  - Generate COG / TOR tab — search students, click → Academic Profile
    - Generate COG per semester → PDF download
    - Generate TOR (full record, cumulative GWA, correct semester + school year label)
```

---

## Grade Status Chain

```
[Faculty encodes]      →  saved               (draft, editable)
[Faculty submits]      →  pending_head of department_review  (locked for Faculty)
[Head of Department approves bulk]   →  approved_by_head of department     (forwarded to Registrar)
[Head of Department rejects]         →  rejected             (Faculty can edit & resubmit)
[Faculty resubmits]    →  pending_head of department_review  (cycle repeats until approved)
[Registrar finalizes]  →  finalized            (permanently locked)
```

> These are the **only 5 valid ENUM values** for `grades.status`. No others accepted by MySQL.

---

## School Year & Semester Status Chain

```
upcoming  →  active  →  completed
```

> `inactive` is **NOT** a valid ENUM value. Always use `upcoming` for new/future and `completed` for past.

---

## Tech Stack

### Backend
- **Laravel 10 LTS** — PHP MVC Framework
- **MySQL 8.0** — Database (via XAMPP)
- **PHP 8.4.11** — Runtime

### Frontend
- **Blade Templates** — Laravel templating
- **Tailwind CSS** — Utility-first CSS (CDN — use inline styles for critical colors)
- **Vite** — Asset bundler
- **Font Awesome 6.5** — Icons (via CDN in app.blade.php)
- **Google Fonts** — Playfair Display + DM Sans
- **SweetAlert2** — Confirmation dialogs (CDN in app.blade.php)

### Key Packages
| Package | Version | Purpose |
|---------|---------|---------|
| Laravel Breeze | v1.26 | Auth scaffolding |
| Spatie Permission | v6.x | Roles & permissions |
| Maatwebsite Excel | v3.1+ | Excel import/export |
| Spatie Activity Log | v4.8+ | Audit trail |
| Laravel DomPDF | v3.x | COG/TOR PDF generation |
| Laravel Debugbar | v3.9 | Dev debugging |

---

## Installation

### Prerequisites
- PHP 8.1+
- Composer
- Node.js 18+ & NPM
- MySQL 8.0+
- XAMPP/WAMP/Laragon

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/gitpushfrances/cog-tor-system.git
   cd cog-tor-system
   ```

2. **Install PHP dependencies**
   ```bash
   composer install --ignore-platform-req=php
   ```

3. **Install NPM dependencies**
   ```bash
   npm install
   ```

4. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   - Create database: `cog_tor_system`
   - Update `.env`:
     ```env
     DB_DATABASE=cog_tor_system
     DB_USERNAME=root
     DB_PASSWORD=
     ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed database**
   ```bash
   php artisan db:seed
   ```

   **Test Accounts:**
   | Role | Email | Password | Notes |
   |------|-------|----------|-------|
   | Admin | admin@cogtor.test | password | Full system config access |
   | Head of Department | head of department@cogtor.test | password | Scoped to College of Computer Studies (department_id=1) |
   | Faculty | faculty@cogtor.test | password | Assigned to Subject 1, department_id=1 |
   | Registrar | registrar@cogtor.test | password | Document generation |
   | Pending | pending@cogtor.test | password | Blocked by status middleware |

8. **Build assets**
   ```bash
   npm run build
   ```

9. **Start development server**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` — redirects to login automatically.

---

## User Roles

### Admin — System Configurator Only
Does NOT manage students, grades, or enrollments.

- Manage user accounts — create, edit, approve, reject, deactivate
- **Assign `department_id` when creating Faculty or Head of Department accounts** — required for department scoping
- Tab filter: All / Faculty / Head of Department / Registrar with live counts
- Department column shows code + name or "Unassigned"
- Manage Departments, Courses, Subjects
- Configure School Years and Semesters (upcoming → active → completed)

### Faculty — Grade Encoder
Sees only their assigned subjects for the active semester.

- Encode grades manually (percentage → PH grade scale auto-conversion)
- Download Excel grade template per subject, fill offline, upload back
- Submit full class batch to Head of Department (grade table locks after submit)
- If Head of Department rejects: rejection remarks shown prominently → "Update & Resubmit" unlocks grades → add Faculty remarks → resubmit

### Head of Department — Department Academic Manager
Scoped to their assigned department only.

- **Manage Students** (CRUD, department-scoped, Excel import/export)
- **Manage Enrollment** — enroll students per active semester only
- **Faculty Assignment** — assign subjects to faculty scoped by department
- **Review grade submissions in bulk** — full class table, Approve All or Reject with remarks
- **Dashboard stats** — all 4 cards (students, enrollments, pending, approved) scoped to department

### Registrar — Official Records & Documents
Does NOT manage people or grades directly.

- **Finalization Queue tab**
  - Subjects grouped with count of pending students
  - Preview button → modal showing all students + grades for that subject
  - Finalize All → SweetAlert2 confirm → permanently locks all grades in subject
- **Generate COG / TOR tab**
  - Search student by name or number
  - Academic Profile → grades grouped by school year → semester
  - Generate COG per semester (semester GWA, PDF download)
  - Generate TOR (full record, cumulative GWA, label: "Semester Name — SY YYYY-YYYY")

---

## Database Structure

**Tables: 24 total**

| Group | Tables |
|-------|--------|
| Academic Structure | school_years, semesters, departments, courses, subjects |
| Users | users (includes `department_id` for Faculty and Head of Department) |
| Students | students, enrollments |
| Grades | grades (5-value ENUM), grade_submissions (faculty_remarks, resubmission_count) |
| Documents | cog_records, tor_records |
| Laravel/Spatie | permissions, roles, model_has_permissions, model_has_roles, role_has_permissions, activity_log |

---

## Grading System

### Philippine Grade Scale (1.0–5.0)
| Grade | Percentage Range |
|-------|-----------------|
| 1.00 | 97–100% |
| 1.25 | 94–96% |
| 1.50 | 91–93% |
| 1.75 | 88–90% |
| 2.00 | 85–87% |
| 2.25 | 82–84% |
| 2.50 | 79–81% |
| 2.75 | 76–78% |
| 3.00 | 75% |
| 5.00 | Below 75% (Failed) |

### GWA Formula
```
Semester GWA    = Σ(Grade × Units) / Σ(Units)
Cumulative GWA  = Σ(all grades × units) / Σ(all units) — across ALL finalized semesters
```

---

## Column Name Quick Reference
| Table | Correct | Wrong |
|-------|---------|-------|
| subjects | `code` | `subject_code` |
| students | `student_number` | `student_id` |
| school_years | `year_code` | `year_start`, `year_end` |
| semesters | `semester_name`, `semester_order` | `name` |
| grades.status | `saved`, `pending_head of department_review`, `approved_by_head of department`, `rejected`, `finalized` | `pending`, `approved` |
| school_years.status | `upcoming`, `active`, `completed` | `inactive` |
| grade_submissions.head of department_action | `approved_by_head of department`, `rejected` | `approved` |
| Storage facade | `Storage::` (with import) | `\Storage::` |

---

## Development Roadmap

| Phase | Status | Description |
|-------|--------|-------------|
| Phase 1 | ✅ Complete | Foundation + Database Architecture |
| Phase 2 | ✅ Complete | Models, Seeders, Relationships |
| Phase 3 | ✅ Complete | Authentication & Authorization |
| Phase 4 | ✅ Complete | Admin Module |
| Phase 5 | ✅ Complete | Faculty Module (Grade Encoding + Resubmit Flow) |
| Phase 6 | ✅ Complete | Head of Department Module (Bulk Approval + Student + Enrollment + Faculty Assignment) |
| Phase 7 | ✅ Complete | Registrar Module (Bulk Finalize + Preview Modal + TOR Fix) |
| Phase 8 | ✅ Complete | Excel Import/Export |
| **Phase 9** | 🔄 **98%** | System Restructure — 5 SweetAlert actions + E2E test remaining |
| Phase 10 | 📅 Planned | Reporting & Analytics |
| Phase 11 | 🔄 30% | UI/UX Polish & Testing |

---

## Known Issues

| Issue | Status |
|-------|--------|
| SweetAlert2 pending on 5 actions (Head of Department approve/reject, Faculty submit/resubmit, Registrar generate TOR) | Step 44 |
| End-to-end 12-step test not yet run | Step 47 |
| Admin dashboard still shows student nav links | Cleanup Phase 11 |
| PDF storage not publicly accessible | Fix before production |
| No student portal | Post-Phase 11 |
| Audit log has no UI | Post-Phase 11 |

---

## Security Checklist

- [ ] Change default seeder passwords before production
- [ ] Enable rate limiting on login attempts
- [x] CSRF protection — enabled by default
- [ ] Use HTTPS in production
- [x] Validate all Excel file uploads
- [ ] Run `php artisan storage:link` before production deploy
- [ ] Review signed URL strategy for PDF access control

---

## Project Stats

- **Started:** February 15, 2026
- **Last Updated:** March 12, 2026
- **Version:** 1.0.0-alpha (Phase 9 at 98%)
- **Database Tables:** 24
- **Models:** 11 (+ User)
- **Middleware:** 2 custom (CheckRole, CheckStatus)
- **Controllers:** 19
- **Routes:** ~126 verified clean
- **Views:** 40+ Blade views
- **Test Accounts:** 5
- **Total Phases:** 11

---

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/phase-X-description`
3. Commit: `git commit -m '[PHASE-X] Description'`
4. Push: `git push origin feature/phase-X-description`
5. Open a Pull Request

---

## Support

- **GitHub:** https://github.com/gitpushfrances/cog-tor-system
- **Full dev history:** See CHANGELOG.md

---

**Last Updated:** March 12, 2026
**Maintained By:** Frances Igop
**Institution:** Eastern Samar State University — Guiuan Campus
