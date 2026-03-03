# COG-TOR Academic Grading Management System

A comprehensive Academic Grading Management System built with Laravel 10, designed for educational institutions to manage grade submissions, approvals, and generate official academic documents (Certificate of Grades & Transcript of Records).

![Status](https://img.shields.io/badge/Status-In%20Development-yellow)
![Laravel](https://img.shields.io/badge/Laravel-10.x-red)
![PHP](https://img.shields.io/badge/PHP-8.4-blue)
![License](https://img.shields.io/badge/License-MIT-green)
![Progress](https://img.shields.io/badge/Progress-93%25-brightgreen)
![Phases](https://img.shields.io/badge/Phases-11%20Total-blue)

---

## Project Overview

This system streamlines the academic grading workflow with a multi-tier approval system: **Faculty → Dean → Registrar**, automated GWA computation, and official document generation (COG/TOR).

> **v2 Restructure — Phase 9 at 91%:** All major restructure work is done. Only SweetAlert2 integration and end-to-end test remain. See CHANGELOG.md Phase 9 for exact resume point (Step 33).

### Key Features
- **Four-Role System** — Admin, Faculty, Dean, Registrar
- **Grade Submission Workflow** — Faculty submits → Dean bulk approves → Registrar finalizes
- **Department-Scoped Dean** — Each Dean manages their department only via `department_id`
- **Student Management under Dean** — CRUD + bulk import/export per department
- **Rejection & Resubmission Flow** — Dean rejects with remarks → Faculty corrects → resubmits with remarks
- **Automated GWA Calculation** — Semester and cumulative weighted average
- **COG & TOR Generation** — PDF documents with official formatting
- **Search-First Document Flow** — Registrar searches student → Academic Profile → generates COG/TOR
- **Complete Audit Trail** — Spatie Activity Log tracks all grade changes
- **Excel Import/Export** — Dean: bulk student import/export | Faculty: grade template
- **Font Awesome 6.5** — Clean icon set throughout, no emoji rendering issues
- **SweetAlert2 Confirmations** — Pending (Phase 9.7, Step 33)
- **Reporting & Analytics** — Planned Phase 10

---

## Current Status

| Phase | Description | Status |
|-------|-------------|--------|
| Phase 1–8 | Foundation through Excel Features | Complete |
| **Phase 9** | **System Restructure — YOU ARE HERE (91%)** | In Progress |
| Phase 10 | Reporting & Analytics | Planned |
| Phase 11 | UI/UX Polish & Testing | 30% Done |

**Overall Progress: ~93%**

> **Resume point:** Step 33 — add SweetAlert2. See CHANGELOG.md for exact checklist.

---

## System Workflow

```
ADMIN
  Scope: System configuration only. No student/grade access.
  - Manage Users (assign department_id when creating Dean accounts)
  - Manage Departments, Courses, Subjects
  - Configure School Years & Semesters (set active)

DEAN (per department — scoped by department_id)
  Scope: Full academic management, own department only.
  - Manage Students (CRUD + bulk Excel import/export)
  - Manage Enrollment (enroll students into subjects)
  - Assign Subjects to Faculty
  - Review Grade Submissions (bulk — full class table at once)
    - Approve All → forwards batch to Registrar
    - Reject → returns to Faculty with remarks
    - Resubmissions show Faculty remarks prominently

FACULTY
  Scope: Assigned subjects only.
  - Encode Grades (manual or Excel template upload)
  - Submit Full Class Batch to Dean
  - Grade table locks after submission
  - If rejected: red banner shows Dean's remarks, "Update & Resubmit" unlocks grades
  - Add Faculty remarks when resubmitting

REGISTRAR
  Scope: Official records and document generation only.
  - Finalize Dean-approved batches (locks permanently)
  - Search student by name or student number
  - Academic Profile per student:
    - All finalized grades grouped by School Year → Semester
    - Generate COG per semester → PDF download
    - Generate TOR at top → full record, cumulative GWA
    - Download buttons for previously generated documents
```

---

## Grade Status Chain

```
[Faculty encodes]      →  saved               (draft, editable)
[Faculty submits]      →  pending_dean_review  (locked for Faculty)
[Dean approves bulk]   →  approved_by_dean     (forwarded to Registrar)
[Dean rejects]         →  rejected             (Faculty can edit & resubmit)
[Faculty resubmits]    →  pending_dean_review  (cycle repeats until approved)
[Registrar finalizes]  →  finalized            (permanently locked)
```

> These are the **only 5 valid ENUM values** for `grades.status`. No others accepted by MySQL.

---

## Tech Stack

### Backend
- **Laravel 10 LTS** — PHP MVC Framework
- **MySQL 8.0** — Database (via XAMPP)
- **PHP 8.4.11** — Runtime

### Frontend
- **Blade Templates** — Laravel templating
- **Tailwind CSS** — Utility-first CSS
- **Alpine.js** — Minimal JS interactions
- **Vite** — Asset bundler
- **Font Awesome 6.5** — Icons (via CDN in app.blade.php)
- **Google Fonts** — Playfair Display + DM Sans
- **SweetAlert2** — Confirmation dialogs (pending Phase 9.7)

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
   | Dean | dean@cogtor.test | password | Scoped to College of Computer Studies |
   | Faculty | faculty@cogtor.test | password | Assigned to Subject 1 |
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

- Manage user accounts (Faculty, Dean, Registrar) — create, edit, approve, reject, deactivate
- **Assign `department_id` when creating Dean accounts** — required for department scoping to work
- Manage Departments, Courses, Subjects
- Configure School Years and Semesters (set active)

### Faculty — Grade Encoder
Sees only their assigned subjects for the active semester.

- Encode grades manually (percentage → auto PH grade scale conversion)
- Download pre-filled Excel grade template per subject, fill offline, upload back
- Submit full class batch to Dean
- Grade table **locks** after submission
- If Dean rejects: rejection remarks shown prominently on subjects page
- "Update & Resubmit" unlocks grades → make corrections → add Faculty remarks → resubmit

### Dean — Department Academic Manager
Scoped to their assigned department only. Cannot access other departments.

- **Manage Students** (CRUD — department-scoped)
  - Bulk import via Excel template (validates course belongs to department)
  - Export student roster to Excel
- Manage student enrollment into subjects
- Assign subjects to Faculty
- **Review grade submissions in bulk** — full class table visible at once
  - Approve All → forwards entire batch to Registrar
  - Reject with remarks → returns to Faculty
  - Resubmissions flagged with Faculty remarks visible

### Registrar — Official Records & Documents
Does NOT manage people.

- Finalize Dean-approved grade batches (permanently locks)
- **Search student** by name or student number (paginated directory — all students shown by default)
- **Academic Profile** per student — all finalized grades by school year/semester
- Generate **COG** per semester (semester GWA, immediate PDF download)
- Generate **TOR** (full record always — cumulative GWA across all finalized semesters)
- Re-download previously generated COG/TOR from Academic Profile

---

## Database Structure

**Tables: 24 total (all migrations ran)**

| Group | Tables |
|-------|--------|
| Academic Structure | school_years, semesters, departments, courses, subjects |
| Users | users (includes department_id) |
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
TOR always uses full cumulative GWA. No partial semester selection on TOR.

---

## Excel Features

### Dean — Student Import/Export
- **Download Template** — CSV with correct headers and 1 sample row (department-scoped course codes)
- **Import Students** — Upload CSV/Excel to bulk-create (validates: student_number unique, email unique, course within Dean's department, year level 1–5)
- **Export Students** — Styled `.xlsx` roster scoped to Dean's department

### Faculty — Grade Template
- **Download Grade Template** — Pre-filled `.xlsx` per subject (student info + percentage column)
- **Upload Grades** — Auto-saves grades, converts percentage → PH grade scale

---

## Development Roadmap

| Phase | Status | Description |
|-------|--------|-------------|
| Phase 1 | Complete | Foundation + Database Architecture |
| Phase 2 | Complete | Models, Seeders, Relationships |
| Phase 3 | Complete | Authentication & Authorization |
| Phase 4 | Complete | Admin Module (student mgmt removed in Phase 9) |
| Phase 5 | Complete | Faculty Module (Grade Encoding + Resubmit Flow) |
| Phase 6 | Complete | Dean Module (Bulk Approval + Student Management) |
| Phase 7 | Complete | Registrar Module (Search-First + Academic Profile) |
| Phase 8 | Complete | Excel Import/Export (Dean + Faculty) |
| **Phase 9** | **91%** | **System Restructure — SweetAlert + E2E test remaining** |
| Phase 10 | Planned | Reporting & Analytics |
| Phase 11 | 30% Done | UI/UX Polish & Testing |

---

## Known Issues

| Issue | Status |
|-------|--------|
| SweetAlert2 not yet integrated on destructive actions | Pending Phase 9.7 (Step 33) |
| End-to-end 12-step test not yet run | Pending Phase 9.8 (Step 37) |
| Admin dashboard still shows student nav links | Cleanup deferred to Phase 11 |
| PDF storage not publicly accessible | Fix before production deploy |
| No student portal | Planned post-Phase 11 |
| Audit log has no UI | Planned post-Phase 11 |

---

## Column Name Quick Reference
Common sources of bugs — check before writing any query or view.

| Table | Correct | Wrong |
|-------|---------|-------|
| subjects | `code` | `subject_code` |
| students | `student_number` | `student_id` |
| grades.status | `saved`, `pending_dean_review`, `approved_by_dean`, `rejected`, `finalized` | `pending`, `approved` |
| grade_submissions.dean_action | `approved_by_dean`, `rejected` | `approved` |
| Storage facade | `Storage::` (with import) | `\Storage::` |

---

## Security Checklist

- [ ] Change default seeder passwords before production
- [ ] Enable rate limiting on login attempts
- [x] CSRF protection — enabled by default
- [ ] Use HTTPS in production
- [x] Validate all Excel file uploads
- [ ] Run `php artisan storage:link` before production deploy
- [ ] Review signed URL strategy for PDF access control
- [ ] Environment variable encryption

---

## Project Stats

- **Started:** February 15, 2026
- **Last Updated:** March 3, 2026
- **Version:** 1.0.0-alpha (Phase 9 at 91%)
- **Database Tables:** 24 (all migrations ran)
- **Models:** 11 (+ User)
- **Middleware:** 2 custom (CheckRole, CheckStatus)
- **Controllers:** 17
- **Routes:** 111 verified clean
- **Views:** 35+ Blade views
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

**Last Updated:** March 3, 2026
**Maintained By:** Frances Igop
**Institution:** Eastern Samar State University — Guiuan Campus
