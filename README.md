# COG-TOR Academic Grading Management System

A comprehensive Academic Grading Management System built with Laravel 10, designed for educational institutions to manage grade submissions, approvals, and generate official academic documents (Certificate of Grades & Transcript of Records).

![Status](https://img.shields.io/badge/Status-In%20Development-yellow)
![Laravel](https://img.shields.io/badge/Laravel-10.x-red)
![PHP](https://img.shields.io/badge/PHP-8.4-blue)
![License](https://img.shields.io/badge/License-MIT-green)
![Progress](https://img.shields.io/badge/Progress-97%25-brightgreen)
![Phases](https://img.shields.io/badge/Phases-14%20Total-blue)

---

## Project Overview

This system streamlines the academic grading workflow. As of **Phase 13**, the workflow is being migrated from a multi-tier approval chain (Faculty → Head of Department → Registrar) to a **single-actor Registrar workflow**, per an updated client flowchart — the Registrar now encodes, validates, saves, and generates COG/TOR directly, with Faculty and Head of Department being phased out of the grade pipeline (disabled, not deleted, for reversibility). The system also includes automated GWA computation, official document generation (COG/TOR), and a full database backup and restore system.

> **Phase 13 In Progress (~70%) — Registrar-Only Workflow Migration, started July 2, 2026.** Registrar direct-encode bugs fixed, `faculty_id` made nullable, and full Registrar-side Student/Enrollment/Excel management built to absorb HoD's responsibilities. Statically verified (syntax, routing, view-binding all green). **Not yet done:** browser end-to-end test, and disabling Faculty/HoD routes. See CHANGELOG.md for full detail.

### Key Features
- **Four-Role System** — Admin, Faculty, Head of Department, Registrar *(Faculty and HoD's grade-related roles are being phased out per Phase 13 — see note below)*
- **🆕 Registrar Direct Grade Encoding** — Registrar encodes, validates, and saves grades directly, no Faculty/HoD handoff required (Phase 13)
- **🆕 Registrar Student Management** — Full CRUD, institution-wide (not department-scoped) — absorbed from Head of Department (Phase 13)
- **🆕 Registrar Enrollment Management** — Enroll/remove students per active semester, institution-wide — absorbed from Head of Department (Phase 13)
- **🆕 Registrar Excel Import/Export** — Bulk student import/export, unscoped — absorbed from Head of Department (Phase 13)
- **Grade Submission Workflow (legacy, being phased out)** — Faculty submits → HoD bulk approves → Registrar bulk finalizes per subject
- **Department-Scoped HoD (legacy)** — Each HoD manages their department only via `department_id` — student/enrollment/Excel management being absorbed by Registrar; grade review/faculty assignment slated for lockout
- **Rejection & Resubmission Flow (legacy)** — HoD rejects with remarks → Faculty corrects → resubmits with remarks — slated for lockout
- **Automated GWA Calculation** — Semester and cumulative weighted average
- **COG & TOR Generation** — PDF documents, correct semester + school year labels, CHED standard
- **Search-First Document Flow** — Registrar searches student → Academic Profile → generates COG/TOR
- **Bulk Finalization** — Registrar finalizes all grades per subject with preview modal
- **Complete Audit Trail** — Spatie Activity Log tracks all grade changes
- **Excel Import/Export** — Registrar (Phase 13): institution-wide student import/export | HoD (legacy): department-scoped | Faculty: grade template
- **Font Awesome 6.5** — Clean professional icons throughout, zero hardcoded emojis
- **SweetAlert2 Confirmations** — All destructive actions confirmed, including new Registrar Student/Enrollment actions (Phase 13)
- **Backup & Restore** — Admin can create, download, and restore database backups via UI

---

## Current Status

| Phase | Description | Status |
|-------|-------------|--------|
| Phase 1–8 | Foundation through Excel Features | ✅ Complete |
| Phase 9 | System Restructure | ✅ Complete (99% — old E2E test superseded by Phase 13) |
| Phase 10 | Reporting & Analytics | 📅 Planned |
| Phase 11 | UI/UX Polish & Testing | 🔄 40% Done (blocked pending Phase 13) |
| Phase 12 | Backup & Restore | ✅ Complete |
| **Phase 13** | **Registrar-Only Workflow Migration** | 🔄 **~70% In Progress** |
| Phase 14 | Curriculum Feature (renumbered from old Phase 13) | 📅 Planned |

**Overall Progress: ~97%** *(dipped slightly from 99% due to Phase 13 scope insertion — reflects real remaining work, not regression)*

> **Resume point:** Phase 13.8 — Browser end-to-end test for Registrar's new Student/Enrollment/Excel/Encode-Grades flow. See CHANGELOG.md.

---

## System Workflow
ADMIN
Scope: System configuration only. No student/grade access.

Manage Users (Faculty, HoD, Registrar)

Assign department_id when creating Faculty or HoD accounts (REQUIRED)
Tab filter by role — All / Faculty / HoD / Registrar
Department column shows assignment or "Unassigned"
Role badges display formatted labels (Head Of Department, not head_of_department)


Manage Departments, Courses, Subjects
Configure School Years & Semesters (upcoming → active → completed)
Backup & Restore

Create full database backup (zip stored locally)
View backup history with download and delete
Restore database from uploaded .sql file



HEAD OF DEPARTMENT (per department — scoped by department_id)
⚠️ Phase 13: Student/Enrollment/Excel responsibilities being absorbed by Registrar.
Grade review and Faculty Assignment slated for route lockout (Phase 13.6, not yet applied).
Scope: Full academic management, own department only.

Manage Students (CRUD + bulk Excel import/export)

Import template has format-hint notes row — no fake sample data
Birthdate must be YYYY-MM-DD format


Manage Enrollment (enroll/remove students per active semester)
Assign Subjects to Faculty (faculty dropdown scoped to same department)
Review Grade Submissions (bulk — full class table at once)

Approve All → SweetAlert confirm → forwards batch to Registrar
Reject → SweetAlert confirm (requires remarks) → returns to Faculty
Resubmissions show Faculty remarks prominently


Dashboard stats (students, enrollments, pending/approved grades) all scoped to department
Delete Student → SweetAlert confirm modal

FACULTY
⚠️ Phase 13: This entire role is slated for route lockout (Phase 13.6, not yet applied) —
Registrar now encodes grades directly per the client's updated flowchart.
Scope: Assigned subjects only.

Encode Grades (manual or Excel template upload)
Submit Full Class Batch to HoD → SweetAlert confirm
Grade table locks after submission
If rejected: red banner shows HoD remarks → "Update & Resubmit"
Resubmit → SweetAlert confirm (requires remarks explaining corrections)

REGISTRAR
🆕 Phase 13: Now the single actor for the entire grade lifecycle, per client flowchart:
Select Student → Encode/Update Grades → Validate → Save → Auto-Generate COG/TOR →
Verify → Store, Ready for Printing/Release.
Scope: Grade encoding, student/enrollment management, and official document generation — institution-wide.

🆕 Manage Students (CRUD + bulk Excel import/export) — institution-wide, no department scoping

Search + filter by course, year level, status
Import template has format-hint notes row — no fake sample data
Birthdate must be YYYY-MM-DD format


🆕 Manage Enrollment — enroll/remove students per active semester, institution-wide

Dynamic subject dropdown disables subjects the selected student is already enrolled in
Removal blocked if a grade already exists for that enrollment


🆕 Encode Grades Directly — no Faculty/HoD handoff; faculty_id stored as null (schema made nullable in Phase 13); a GradeSubmission record is still created for compatibility with the existing finalize/unfinalize workflow
Finalization Queue tab

Preview modal — see all students + grades before finalizing
Finalize All per subject → SweetAlert confirm → permanently locks


Generate COG / TOR tab

Search student → Academic Profile
Generate COG per semester → SweetAlert confirm → PDF download
Generate TOR (full record, cumulative GWA) → SweetAlert confirm → PDF download
---

## Grade Status Chain
[Faculty encodes]        →  saved                                (legacy path — slated for lockout)
[Faculty submits]        →  pending_head_of_department_review    (locked for Faculty — legacy path)
[HoD approves bulk]      →  approved_by_head_of_department       (legacy path — slated for lockout)
[HoD rejects]            →  rejected              (Faculty can edit & resubmit — legacy path)
[Faculty resubmits]      →  pending_head_of_department_review    (cycle repeats — legacy path)
[Registrar encodes directly] → approved_by_head_of_department    (🆕 Phase 13 — GradeSubmission auto-created, dean_remarks notes "Direct entry by Registrar")
[Registrar finalizes]    →  finalized             (permanently locked)
> These are the **only 5 valid ENUM values** for `grades.status`. The Phase 13 direct-encode path reuses `approved_by_head_of_department` as a workaround to stay compatible with the existing finalize logic — it does not represent an actual HoD approval action.

---

## Tech Stack

### Backend
- **Laravel 10 LTS** — PHP MVC Framework (10.50.2)
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
| Spatie Laravel Backup | v9.x | Database backup & restore |

> **Note (Phase 13):** `doctrine/dbal` is intentionally **not** installed — Laravel 10.x still requires it for `Schema::table()->change()`, but a raw `DB::statement('ALTER TABLE ...')` migration was used instead for the one nullable-column change needed, to avoid adding the dependency.

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
   > Includes the Phase 13 migration `make_faculty_id_nullable_on_grades_table`, which allows `grades.faculty_id` to be `null` for Registrar direct-entry.

7. **Seed database**
```bash
   php artisan db:seed
```

   **Test Accounts:**
   | Role | Email | Password | Notes |
   |------|-------|----------|-------|
   | Admin | admin@cogtor.test | password | Full system config access |
   | Head of Department | hod@cogtor.test | password | Scoped to department_id=1 — grade review/assignment slated for lockout (Phase 13.6) |
   | Faculty | faculty@cogtor.test | password | department_id=1 — slated for lockout (Phase 13.6) |
   | Registrar | registrar@cogtor.test | password | 🆕 Now handles direct grade encoding + student/enrollment/Excel management (Phase 13) |
   | Pending | pending@cogtor.test | password | Blocked by status middleware |

   > **Note:** Seeder uses `updateOrCreate` — safe to re-run. department_id is always applied correctly.

8. **Configure backup (XAMPP only)**

   Open `config/database.php` and add the `dump` key inside the `mysql` connection array:
```php
   'mysql' => [
       // ... existing config ...
       'engine' => null,
       'dump' => [
           'dump_binary_path' => 'C:/xampp/mysql/bin/',
       ],
   ],
```

   > For WAMP: `C:/wamp64/bin/mysql/mysqlX.X.XX/bin/`
   > For Laragon: `C:/laragon/bin/mysql/mysql-X.X.XX-winx64/bin/`

8b. **Enable the Backup Now button (Windows only)**

   The **Backup Now** button in the Admin UI requires a small helper script and Apache running alongside `php artisan serve`. This works around a Windows-specific limitation where PHP's built-in dev server can't spawn the `mysqldump` subprocess the backup needs — see CHANGELOG.md Phase 13.12 for the full explanation.

   One-time setup, per machine:
   1. Copy `public/run-backup.php` to `C:/xampp/htdocs/cog-tor-backup-trigger/run-backup.php` (create the folder if needed)
   2. Copy `storage/backup-project-path.txt.example` to `C:/xampp/htdocs/cog-tor-backup-trigger/backup-project-path.txt`
   3. Open that copied text file and replace the example path with the full path to **your own** copy of this project (e.g. `C:/Users/YourName/Desktop/cog-tor-system`)
   4. Make sure XAMPP's **Apache** module is running (in addition to MySQL) whenever you plan to use the Backup Now button

   If Apache is not running, Backup Now will fail; use `php artisan backup:run` from the terminal as a fallback instead.

9. **Build assets**
```bash
   npm run build
```

10. **Start development server**
```bash
    php artisan serve
```

Visit `http://localhost:8000` — redirects to login automatically.

---

## 🆕 Registrar Student, Enrollment & Excel Management (Phase 13)

As of Phase 13, the Registrar has full institution-wide (not department-scoped) student and enrollment management, absorbed from Head of Department per the client's updated single-actor flowchart.

### Student Management
- `/registrar/students` — search + filter by course, year level, status
- Add / Edit / Delete students (delete blocked if the student has existing enrollments)
- No department scoping — Registrar sees all students across all departments

### Enrollment Management
- `/registrar/enrollments` — shows active semester banner (or a warning if no semester is active)
- Enroll a student into a subject — subject dropdown dynamically disables subjects the student is already enrolled in for the active semester
- Remove enrollment — blocked if a grade already exists for that enrollment

### Excel Import/Export
- Download Template, Import Excel, Export Excel — all reuse the existing `StudentsExport`/`StudentsImport` classes with `null` passed for department scoping (no code duplication needed)

> ⏳ **Not yet browser-tested** — built and statically verified (syntax, routing, view-binding all confirmed), but the actual click-through test (add student → enroll → export → encode grade) has not yet been run. See CHANGELOG.md Phase 13.8.

---

## Backup & Restore

The system includes a full database backup and restore feature accessible via the Admin sidebar under **Backup & Restore**.

### How to Create a Backup
1. Log in as Admin
2. Click **Backup & Restore** in the sidebar
3. Click **Backup Now** and confirm
4. The backup zip will appear in the Backup History table

> ⚠️ **Requires Apache running.** On Windows dev environments, `php artisan serve` cannot reliably spawn the `mysqldump` subprocess the backup needs (a known Windows-specific limitation — see CHANGELOG.md Phase 13.12 for full detail). The **Backup Now** button works by triggering the backup through Apache instead. Before clicking Backup Now, make sure **both Apache and MySQL are running** in your XAMPP Control Panel — the rest of the app can still run normally via `php artisan serve`. If Apache is stopped, Backup Now will fail with a connection error.

### How to Restore from Backup
1. Download the backup zip from Backup History
2. Extract the `.sql` file from inside the zip
3. Go to **Restore Database** section
4. Upload the `.sql` file and click **Restore Now**

> ⚠️ Restore overwrites the entire current database. Only use `.sql` files exported from this system.

### Manual Backup via CLI
```bash
php artisan backup:run
```

Backups are stored at `storage/app/cog-tor-backup/` as timestamped `.zip` files. This CLI command always works regardless of Apache/dev-server setup, and can be used as a fallback if the Backup Now button is unavailable.

### Notification Warning
The message `Sending notification failed` after `backup:run` is **harmless** — it appears because no mail driver is configured. The backup still completes successfully.

---

## Excel Import Format

### Student Import Template
Download from **Registrar → Students → Download Template** (institution-wide, Phase 13) or **HoD → Students → Download Template** (department-scoped, legacy). The template contains a notes row explaining each column's expected format.

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
| Course Code | e.g. BSIT | Must match an active course (Registrar: any department; HoD: own department only) |
| Status | active / inactive / graduated / dropped | |

---

## Database Structure

**Tables: 24 total**

| Group | Tables |
|-------|--------|
| Academic Structure | school_years, semesters, departments, courses, subjects |
| Users | users (includes `department_id` for Faculty and HoD) |
| Students | students, enrollments |
| Grades | grades (5-value ENUM, `faculty_id` nullable as of Phase 13), grade_submissions (faculty_remarks, resubmission_count) |
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
Semester GWA    = Σ(Grade × Units) / Σ(Units)
Cumulative GWA  = Σ(all grades × units) / Σ(all units) — across ALL finalized semesters
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
| grades.faculty_id | nullable as of Phase 13 (Registrar direct-entry) | never the Registrar's own `auth()->id()` |

---

## Development Roadmap

| Phase | Status | Description |
|-------|--------|-------------|
| Phase 1 | ✅ Complete | Foundation + Database Architecture |
| Phase 2 | ✅ Complete | Models, Seeders, Relationships |
| Phase 3 | ✅ Complete | Authentication & Authorization |
| Phase 4 | ✅ Complete | Admin Module |
| Phase 5 | ✅ Complete | Faculty Module *(slated for route lockout — Phase 13.6)* |
| Phase 6 | ✅ Complete | Head of Department Module *(grade review + assignment slated for lockout; student/enrollment/Excel absorbed by Registrar — Phase 13)* |
| Phase 7 | ✅ Complete | Registrar Module *(significantly extended in Phase 13)* |
| Phase 8 | ✅ Complete | Excel Import/Export |
| Phase 9 | ✅ 99% | System Restructure — old E2E test superseded by Phase 13 |
| Phase 10 | 📅 Planned | Reporting & Analytics |
| Phase 11 | 🔄 40% | UI/UX Polish & Testing — blocked pending Phase 13 |
| Phase 12 | ✅ Complete | Backup & Restore |
| **Phase 13** | 🔄 **~70%** | **Registrar-Only Workflow Migration** |
| Phase 14 | 📅 Planned | Curriculum Feature *(renumbered from old Phase 13)* |

---

## Known Issues

| Issue | Status |
|-------|--------|
| 🆕 Browser end-to-end test for Registrar's new Student/Enrollment/Excel/Encode-Grades flow not yet run | Next session — Phase 13.8 |
| 🆕 Faculty and HoD grade-related routes still live, not yet locked out | Planned — Phase 13.6 |
| 🆕 Dual role-check system (Spatie `HasRoles` vs legacy `role` column) not yet audited for lockout safety | Planned — part of Phase 13.6 |
| Old 12-step multi-role end-to-end test is obsolete under the new single-actor flow | New test plan needed post-Phase 13 |
| Admin dashboard still shows student nav links | Cleanup Phase 11 |
| PDF storage not publicly accessible | Fix before production |
| No student portal | Post-Phase 11 |
| Audit log has no UI | Post-Phase 11 |
| Registrar COG/TOR template not yet matched to official form | Blocked — awaiting physical template from Registrar's office |
| Excel Report of Grades import not yet built | Blocked — awaiting format from panel |
| Backup Now button requires Apache running (Windows dev environments) | Documented workaround — see CHANGELOG.md Phase 13.12; expected to work without this workaround on real server deployments |

---

## Security Checklist

- [ ] Change default seeder passwords before production
- [x] Rate limiting on login — 10 attempts/minute
- [x] CSRF protection — enabled by default
- [ ] Use HTTPS in production
- [x] Validate all Excel file uploads
- [ ] Run `php artisan storage:link` before production deploy
- [ ] Review signed URL strategy for PDF access control
- [ ] **NEW (Phase 13):** Audit legacy `role` column / `isFaculty()`-style helper methods to confirm Faculty/HoD route lockout (once applied) can't be bypassed through that path

---

## Project Stats

- **Started:** February 15, 2026
- **Last Updated:** July 2, 2026
- **Version:** 1.0.0-alpha (Phase 13 in progress, Phase 11 in progress)
- **Database Tables:** 24
- **Models:** 11 (+ User)
- **Middleware:** 2 custom (CheckRole, CheckStatus)
- **Controllers:** 20 (+ BackupController) + 3 new Registrar controllers (Phase 13)
- **Routes:** ~131 verified clean (pre-Phase 13) + 13 new Registrar routes (Phase 13) = ~144
- **Views:** 41+ Blade views + 4 new Registrar views (Phase 13)
- **Test Accounts:** 5
- **Total Phases:** 14 *(renumbered — see CHANGELOG.md)*

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

**Last Updated:** July 2, 2026
**Maintained By:** Frances Igop
**Institution:** Eastern Samar State University — Guiuan Campus
