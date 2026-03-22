# COG-TOR Academic Grading Management System

A comprehensive Academic Grading Management System built with Laravel 10, designed for educational institutions to manage grade submissions, approvals, and generate official academic documents (Certificate of Grades & Transcript of Records).

![Status](https://img.shields.io/badge/Status-In%20Development-yellow)
![Laravel](https://img.shields.io/badge/Laravel-10.x-red)
![PHP](https://img.shields.io/badge/PHP-8.4-blue)
![License](https://img.shields.io/badge/License-MIT-green)
![Progress](https://img.shields.io/badge/Progress-99%25-brightgreen)
![Phases](https://img.shields.io/badge/Phases-11%20Total-blue)

---

## Project Overview

This system streamlines the academic grading workflow with a multi-tier approval system: **Faculty → Head of Department → Registrar**, automated GWA computation, and official document generation (COG/TOR).

> **Phase 9 Complete — Phase 11 in progress:** All restructure, SweetAlert2, emoji cleanup, and seeder fixes are done. Next step is the 12-step end-to-end test then Phase 11 UI/UX polish.

### Key Features
- **Four-Role System** — Admin, Faculty, Head of Department, Registrar
- **Grade Submission Workflow** — Faculty submits → HoD bulk approves → Registrar bulk finalizes per subject
- **Department-Scoped HoD** — Each HoD manages their department only via `department_id`
- **Student Management under HoD** — CRUD + bulk import/export per department
- **Enrollment Management under HoD** — Enroll/remove students per active semester
- **Faculty Assignment under HoD** — Assign subjects to faculty scoped by department
- **Rejection & Resubmission Flow** — HoD rejects with remarks → Faculty corrects → resubmits with remarks
- **Automated GWA Calculation** — Semester and cumulative weighted average
- **COG & TOR Generation** — PDF documents, correct semester + school year labels, CHED standard
- **Search-First Document Flow** — Registrar searches student → Academic Profile → generates COG/TOR
- **Bulk Finalization** — Registrar finalizes all grades per subject with preview modal
- **Complete Audit Trail** — Spatie Activity Log tracks all grade changes
- **Excel Import/Export** — HoD: bulk student import/export with format-hint template | Faculty: grade template
- **Font Awesome 6.5** — Clean professional icons throughout, zero hardcoded emojis
- **SweetAlert2 Confirmations** — All 8 destructive actions confirmed

---

## Current Status

| Phase | Description | Status |
|-------|-------------|--------|
| Phase 1–8 | Foundation through Excel Features | ✅ Complete |
| **Phase 9** | **System Restructure** | ✅ Complete (99% — E2E test pending) |
| Phase 10 | Reporting & Analytics | 📅 Planned |
| Phase 11 | UI/UX Polish & Testing | 🔄 40% Done |

**Overall Progress: ~99%**

> **Resume point:** Phase 9.11 — 12-step end-to-end test. See CHANGELOG.md.

---

## System Workflow

```
ADMIN
  Scope: System configuration only. No student/grade access.
  - Manage Users (Faculty, HoD, Registrar)
    - Assign department_id when creating Faculty or HoD accounts (REQUIRED)
    - Tab filter by role — All / Faculty / HoD / Registrar
    - Department column shows assignment or "Unassigned"
    - Role badges display formatted labels (Head Of Department, not head_of_department)
  - Manage Departments, Courses, Subjects
  - Configure School Years & Semesters (upcoming → active → completed)

HEAD OF DEPARTMENT (per department — scoped by department_id)
  Scope: Full academic management, own department only.
  - Manage Students (CRUD + bulk Excel import/export)
    - Import template has format-hint notes row — no fake sample data
    - Birthdate must be YYYY-MM-DD format
  - Manage Enrollment (enroll/remove students per active semester)
  - Assign Subjects to Faculty (faculty dropdown scoped to same department)
  - Review Grade Submissions (bulk — full class table at once)
    - Approve All → SweetAlert confirm → forwards batch to Registrar
    - Reject → SweetAlert confirm (requires remarks) → returns to Faculty
    - Resubmissions show Faculty remarks prominently
  - Dashboard stats (students, enrollments, pending/approved grades) all scoped to department
  - Delete Student → SweetAlert confirm modal

FACULTY
  Scope: Assigned subjects only.
  - Encode Grades (manual or Excel template upload)
  - Submit Full Class Batch to HoD → SweetAlert confirm
  - Grade table locks after submission
  - If rejected: red banner shows HoD remarks → "Update & Resubmit"
  - Resubmit → SweetAlert confirm (requires remarks explaining corrections)

REGISTRAR
  Scope: Official records and document generation only.
  - Finalization Queue tab
    - Preview modal — see all students + grades before finalizing
    - Finalize All per subject → SweetAlert confirm → permanently locks
  - Generate COG / TOR tab
    - Search student → Academic Profile
    - Generate COG per semester → SweetAlert confirm → PDF download
    - Generate TOR (full record, cumulative GWA) → SweetAlert confirm → PDF download
```

---

## Grade Status Chain

```
[Faculty encodes]        →  saved
[Faculty submits]        →  pending_head_of_department_review  (locked for Faculty)
[HoD approves bulk]      →  approved_by_head_of_department     (forwarded to Registrar)
[HoD rejects]            →  rejected             (Faculty can edit & resubmit)
[Faculty resubmits]      →  pending_head_of_department_review  (cycle repeats)
[Registrar finalizes]    →  finalized            (permanently locked)
```

> These are the **only 5 valid ENUM values** for `grades.status`.

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
- **Font Awesome 6.5** — Icons (CDN in app.blade.php)
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
   | Head of Department | hod@cogtor.test | password | Scoped to department_id=1 |
   | Faculty | faculty@cogtor.test | password | department_id=1 |
   | Registrar | registrar@cogtor.test | password | Document generation |
   | Pending | pending@cogtor.test | password | Blocked by status middleware |

   > **Note:** Seeder uses `updateOrCreate` — safe to re-run. department_id is always applied correctly.

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

## Excel Import Format

### Student Import Template
Download from HoD → Students → Download Template. The template contains a notes row explaining each column's expected format.

| Column | Format | Notes |
|--------|--------|-------|
| Student Number | YYYY-NNNNN | Must be unique, e.g. 2024-00011 |
| First Name | Text | Required |
| Middle Name | Text | Optional |
| Last Name | Text | Required |
| Suffix | Text | Optional |
| Birth Date | YYYY-MM-DD | Strictly enforced, e.g. 2003-01-15 |
| Gender | Male or Female | Case-sensitive |
| Email | valid email | Must be unique |
| Phone | 09XXXXXXXXX | Optional |
| Address | Text | Optional |
| Year Level | 1–5 | Max 5 |
| Course Code | e.g. BSIT | Must match active course in your department |
| Status | active / inactive / graduated / dropped | |

---

## Database Structure

**Tables: 24 total**

| Group | Tables |
|-------|--------|
| Academic Structure | school_years, semesters, departments, courses, subjects |
| Users | users (includes `department_id` for Faculty and HoD) |
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
| grades.status | `saved`, `pending_head_of_department_review`, `approved_by_head_of_department`, `rejected`, `finalized` | `pending`, `approved` |
| school_years.status | `upcoming`, `active`, `completed` | `inactive` |
| grade_submissions.hod_action | `approved_by_head_of_department`, `rejected` | `approved` |
| Storage facade | `Storage::` (with import) | `\Storage::` |

---

## Development Roadmap

| Phase | Status | Description |
|-------|--------|-------------|
| Phase 1 | ✅ Complete | Foundation + Database Architecture |
| Phase 2 | ✅ Complete | Models, Seeders, Relationships |
| Phase 3 | ✅ Complete | Authentication & Authorization |
| Phase 4 | ✅ Complete | Admin Module |
| Phase 5 | ✅ Complete | Faculty Module |
| Phase 6 | ✅ Complete | Head of Department Module |
| Phase 7 | ✅ Complete | Registrar Module |
| Phase 8 | ✅ Complete | Excel Import/Export |
| **Phase 9** | ✅ **99%** | System Restructure — E2E test pending |
| Phase 10 | 📅 Planned | Reporting & Analytics |
| Phase 11 | 🔄 40% | UI/UX Polish & Testing |

---

## Known Issues

| Issue | Status |
|-------|--------|
| End-to-end 12-step test not yet run | Next session — Step 9.11 |
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
- **Last Updated:** March 23, 2026
- **Version:** 1.0.0-alpha (Phase 9 complete, Phase 11 in progress)
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

**Last Updated:** March 23, 2026
**Maintained By:** Frances Igop
**Institution:** Eastern Samar State University — Guiuan Campus
