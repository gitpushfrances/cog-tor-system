# CHANGELOG - COG-TOR System

## Project Information
**System Name:** Academic Grading Management System (COG-TOR)
**Tech Stack:** Laravel 10 LTS + MySQL + Blade + Tailwind CSS
**PHP Version:** 8.4.11
**Node Version:** 18.20.8
**Started:** February 15, 2026

---

## ⚡ RESUME POINT — READ THIS FIRST

**Current Phase:** Phase 13 — Registrar-Only Workflow Migration
**Status:** 🔄 In Progress (~85%) — Controllers, routes, and views built and statically verified. Subject `semester` data bug found and fixed (root cause + patch + seeder). Faculty/HoD roles locked out at route level (Phase 13.6 done), test accounts removed. Browser end-to-end test NOT yet fully run.

### 🔔 Open Enhancement Request (not yet scheduled)
> **Add a dedicated COG/TOR Records tab/section** so generated documents are tracked and retrievable as a proper history/log, rather than only accessible at the moment of generation. See "COG/TOR Records Tracking" note under Phase 13.10 below and in Next Steps.

### What Changed From Original Plan (v1 → v2 → v3)
| Area | v1 (Old) | v2 (Phase 9) | v3 (Phase 13 — current) | Why |
|------|----------|----------|----------|-----|
| Student management | Admin owned it | Head of Department owns it (department-scoped) | **Registrar owns it (institution-wide, no department scoping)** | Client flowchart: single-actor Registrar workflow |
| Grade approval | One grade at a time | Bulk per faculty submission | **Registrar encodes directly, no handoff** | Client flowchart: Registrar does everything |
| Head of Department scope | System-wide | Per department via `department_id` | Still per-department, but grade review/assignment slated for lockout | Multi-department support (v2) → obsolete under v3 flow |
| Faculty rejection | No resubmit flow | Reject → Faculty corrects → Resubmit with remarks | Still live, slated for lockout | Complete workflow (v2) → obsolete under v3 flow |
| Registrar document flow | Browse student list | Search → Academic Profile | Unchanged | Scales to 500+ students |
| TOR generation | Semester selector (wrong) | Always full record (CHED standard) | Unchanged | Academically correct |
| Grade status ENUM | 3 values | 5 values | Unchanged, reused with a workaround (see 13.1) | Covers full workflow |
| SweetAlert | Missing | All destructive actions | Unchanged | UX safety |
| Registrar finalization | 1-by-1 per grade | Bulk per subject with preview modal | Unchanged | Realistic at scale |
| Enrollment management | — | HoD owns it (department-scoped) | **Registrar owns it (institution-wide)** | Follows same single-actor logic as student mgmt |
| Excel import/export (students) | — | HoD owns it, department-scoped | **Registrar owns it, unscoped (`null` = no department filter)** | `StudentsExport`/`StudentsImport` already supported `null` param — reused as-is |
| Registrar `faculty_id` handling | — | — | **Nullable — Registrar direct-entry sets `null` instead of own ID** | Schema/semantics fix — column meant for Faculty, not Registrar |
| Subject `semester` field | — | Free-text, form used abbreviations (`1st`,`2nd`) | **Fixed to store full string (`1st Semester`) matching `semesters.semester_name`** | Registrar's Encode Grades compares against the full string — mismatch caused silent empty results |

### Phase Renumbering
Phase 1–12  Unchanged
Phase 13    NEW — Registrar-Only Workflow Migration (insert here)
Phase 14    Was Phase 13 — Curriculum Feature (renumbered)
---

## PHASE 1: FOUNDATION & DATABASE ARCHITECTURE ✅ COMPLETED
**Date:** February 15, 2026
**Status:** ✅ Complete (100%)

### 1.1 Environment Setup
- [x] Navigated to Desktop: `C:\Users\Frances\Desktop`
- [x] Created Laravel 10 project: `cog-tor-system`
- [x] Composer configured with PHP 8.4.11
- [x] Node.js 18.20.8 and NPM 10.8.2 verified
- [x] MySQL 8.0 ready via XAMPP

### 1.2 Core Packages Installed
- [x] Laravel Breeze v1.26 — Auth scaffolding (Blade + Tailwind)
- [x] Spatie Laravel Permission v6.x — Role and permission management
- [x] Maatwebsite Excel v3.1+ — Excel import/export
- [x] Spatie Laravel Activity Log v4.8+ — Audit trail
- [x] Laravel DomPDF v3.x — PDF generation
- [x] Laravel Debugbar v3.9 — Dev debugging

### 1.3 Database Setup
- [x] Created database: `cog_tor_system`
- [x] Configured `.env` with database credentials
- [x] Created 12 custom migration files
- [x] Ran all migrations — 21 tables total

### 1.4 GitHub Repository
- [x] Initialized Git repository
- [x] Remote: https://github.com/gitpushfrances/cog-tor-system.git
- [x] Initial commit pushed to main

**Deliverables:**
- ✅ Working Laravel 10 installation
- ✅ 21 database tables (9 default + 12 custom)
- ✅ All packages installed and configured
- ✅ Code pushed to GitHub

---

## PHASE 2: MODELS & SEEDERS ✅ COMPLETED
**Date:** February 15, 2026
**Status:** ✅ Complete (100%)

### 2.1 Eloquent Models Created
- [x] SchoolYear, Semester, Department, Course, Subject
- [x] Student, Enrollment, Grade, GradeSubmission
- [x] CogRecord, TorRecord

### 2.2 User Model Enhanced
- [x] Spatie HasRoles trait added
- [x] Activity Log trait added
- [x] Role helpers: isAdmin(), isFaculty(), isHeadOfDepartment(), isRegistrar()
- [x] Status helpers: isActive(), isPending(), isInactive()
- [x] Query scopes for filtering
- [x] `department()` belongsTo relationship added ✅ (March 12 session)
- [x] `department_id` added to `$fillable` ✅ (bug fix — March 12 session)

### 2.3 Database Seeders Created
- [x] RoleSeeder — 4 roles with 23 permissions
- [x] UserSeeder — 5 test accounts, all using updateOrCreate ✅ (fixed March 23 session)
- [x] AcademicStructureSeeder — School years, semesters, departments, courses
- [x] SubjectSeeder — 10 sample subjects ✅ (fixed July 2 session — see Phase 13.9)
- [x] StudentSeeder — 10 sample students
- [x] DatabaseSeeder — Main orchestration

**Deliverables:**
- ✅ 11 Eloquent models with complete relationships
- ✅ Enhanced User model
- ✅ 5 seeders with test data

---

## PHASE 3: AUTHENTICATION & AUTHORIZATION ✅ COMPLETED
**Date:** February 15, 2026
**Status:** ✅ Complete (100%)

### 3.1 Middleware
- [x] CheckRole — multi-role support, 403 on mismatch
- [x] CheckStatus — blocks pending/inactive, invalidates session

### 3.2 Login Redirects
- [x] Admin → `/admin/dashboard`
- [x] Head of Department → `/hod/dashboard`
- [x] Faculty → `/faculty/dashboard`
- [x] Registrar → `/registrar/dashboard`

### 3.3 Route Groups
- [x] Admin, Head of Department, Faculty, Registrar route groups with middleware

### 3.4 Dashboard Controllers & Views
- [x] AdminController, HeadOfDepartmentController, FacultyController, RegistrarController
- [x] All 4 dashboard views

**Deliverables:**
- ✅ 2 custom middleware (CheckRole, CheckStatus)
- ✅ Role-based login redirects
- ✅ 4 dashboard controllers and views

---

## PHASE 4: ADMIN MODULE ✅ COMPLETED
**Date:** February 15–21, 2026
**Status:** ✅ Complete (100%)

### 4.1–4.9 Completed Features
- [x] UserController — full CRUD + approve/reject
- [x] DepartmentController — full CRUD
- [x] CourseController — full CRUD
- [x] SubjectController — full CRUD ⚠️ (semester field bug fixed July 2 session — see Phase 13.9)
- [x] SchoolYearController — full CRUD + setActive
- [x] SemesterController — full CRUD + setActive
- [x] ~~StudentController~~ — DELETED in Phase 9.3 ✅
- [x] ~~Admin ExcelController~~ — DELETED in Phase 9.3 ✅
- [x] Dashboard redirect bug fixed
- [x] Back buttons on all views
- [x] Admin student routes removed from web.php ✅
- [x] Admin dashboard stat card emojis → Font Awesome icons ✅ (March 23 session)
- [x] Recent Users role badge — raw `head_of_department` → formatted `Head Of Department` ✅ (March 23 session)

**Deliverables:**
- ✅ Full Admin CRUD for academic structure only
- ✅ Student management removed — now owned by Head of Department
- ✅ Routes cleaned — no admin.students.* routes remain
- ✅ Clean Font Awesome icons on stat cards

> ⚠️ NOTE: Admin dashboard view still has leftover student nav links — cleanup deferred to Phase 11.
> ⚠️ **Phase 13 confirmation:** Client confirmed Admin role stays fully active (Registrar Head will hold Admin access, or both roles merge into one login) — no lockout planned for Admin. Only Faculty/HoD are being locked out (Phase 13.6).

---

## PHASE 5: FACULTY MODULE ✅ COMPLETED
**Date:** February 21, 2026
**Status:** ✅ Complete (100%)

### 5.1 Controllers
- [x] FacultyController — dashboard and subjects list
- [x] GradeController — index, store, edit, update, submit, resubmit ✅

### 5.2 Views
- [x] faculty/dashboard.blade.php — emoji icons → Font Awesome ✅ (March 23 session)
- [x] faculty/subjects.blade.php — status badges, rejection remarks, resubmit button ✅
- [x] faculty/grades/index.blade.php — locks on submit, rejection banner, resubmit modal, SweetAlert2 on submit + resubmit ✅ — emoji status badges → Font Awesome ✅ (March 23 session)
- [x] faculty/grades/edit.blade.php

### 5.3 Routes
- [x] faculty.dashboard, faculty.subjects
- [x] faculty.subjects.grades (GET/POST)
- [x] faculty.subjects.grades.edit, .update
- [x] faculty.subjects.grades.submit
- [x] faculty.subjects.grades.resubmit ✅

**Deliverables:**
- ✅ Grade encoding with PH scale conversion
- ✅ Batch grade submission to Head of Department
- ✅ Resubmit flow: rejection banner + remarks + resubmit modal
- ✅ Grade table locks on submit, unlocks on rejection
- ✅ SweetAlert2 on Submit and Resubmit (with remarks validation)

> ⚠️ **Phase 13 note:** Per client's updated flowchart, this entire module is slated to be **disabled at the route level** (not deleted) once Phase 13 is complete — Registrar now encodes grades directly. See Phase 13.6.

---

## PHASE 6: HEAD OF DEPARTMENT MODULE ✅ COMPLETED
**Date:** February 21–22, 2026
**Status:** ✅ Complete (100%)

### 6.1 Dashboard
- [x] 4 stat cards — Total Students, Active Enrollments, Pending Grades, Approved Grades (all department-scoped) ✅
- [x] Pending submissions table — grouped by subject, "Review All" per row ✅
- [x] Navigation updated — Dashboard + Students + Enrollment + Faculty Assignment + Grade Submissions ✅

### 6.2 Student Management
- [x] HeadOfDepartment/StudentController — department-scoped CRUD ✅
- [x] HeadOfDepartment/ExcelController — template, import, export ✅
- [x] head_of_department/students/index.blade.php — Delete button now has SweetAlert2 confirm ✅ (March 23 session)
- [x] head_of_department/students/create.blade.php ✅
- [x] head_of_department/students/edit.blade.php ✅
- [x] Download Template / Import Excel / Export Excel — emoji → Font Awesome ✅ (March 23 session)

### 6.3 Grade Review & Approval (Bulk)
- [x] review() — loads all submissions for same subject at once ✅
- [x] approve() — bulk updates all grades to approved_by_head_of_department ✅
- [x] reject() — bulk updates all grades to rejected, saves remarks ✅
- [x] head_of_department/review.blade.php — SweetAlert2 on Approve All + Reject (with remarks validation) ✅

### 6.4 Enrollment Management ✅
- [x] HeadOfDepartment/EnrollmentController — index, store, destroy
- [x] Enroll students into subjects for active semester only
- [x] Remove enrollment blocked if grade exists

### 6.5 Faculty Assignment ✅
- [x] HeadOfDepartment/SubjectAssignmentController — index, update
- [x] Faculty dropdown scoped to department_id ✅

**Deliverables:**
- ✅ Department-scoped student management with Excel import/export
- ✅ Bulk grade approval/rejection with SweetAlert2
- ✅ Enrollment management module
- ✅ Faculty assignment module with department scoping
- ✅ SweetAlert2 on all destructive actions (March 23 session)
- ✅ Clean Font Awesome icons throughout (March 23 session)

> ⚠️ **Phase 13 note:** Grade review/approval (6.3) and Faculty Assignment (6.5) are slated to be **disabled at the route level** — made obsolete by the client's single-actor flowchart. Student management (6.2) and Enrollment management (6.4) responsibilities are being **absorbed by Registrar** (institution-wide, unscoped) rather than deleted from HoD's side outright — HoD's versions stay dormant, reversible. See Phase 13.6.

---

## PHASE 7: REGISTRAR MODULE ✅ COMPLETED
**Date:** February 22, 2026
**Status:** ✅ Complete (100%) — extended in Phase 13

### 7.1 Controllers
- [x] RegistrarController — index, finalize, finalizeSubject, encodeGradesForm, encodeGrades ✅
- [x] DocumentController — studentProfile(), generateCog, generateTor, downloadCog, downloadTor ✅

### 7.2 Routes
- [x] registrar.dashboard, registrar.submissions.finalize
- [x] registrar.submissions.finalize-subject ✅
- [x] registrar.students.profile ✅
- [x] registrar.students.cog, .cog.generate
- [x] registrar.students.tor, .tor.generate
- [x] registrar.cog.download, registrar.tor.download

### 7.3 Views
- [x] registrar/dashboard.blade.php — 2 tabs, bulk finalize, preview modal, SweetAlert2 confirm ✅
- [x] registrar/student-profile.blade.php — SweetAlert2 on Generate TOR + Generate COG ✅ (confirmed March 23 session)
- [x] registrar/pdf/cog.blade.php, registrar/pdf/tor.blade.php
- [x] registrar/encode-grades.blade.php — 3-step wizard (academic context → student → grade table) ✅ (Phase 13)

### 7.4 Key Notes
- Institution name: `Eastern Samar State University - Guiuan Campus`
- Use `Storage::` facade always — never `\Storage::`
- DomPDF: use `->output()` not `->download()`
- TOR: always full cumulative record — no semester selector (CHED standard)
- TOR label: `semester_name — SY year_code`
- GWA: Σ(grade × units) / Σ(units) — weighted

**Deliverables:**
- ✅ Search-first student flow
- ✅ Academic Profile with grouped grades, GWA, COG/TOR generation
- ✅ Bulk finalize per subject with preview modal
- ✅ SweetAlert2 on Finalize All, Generate TOR, Generate COG

> 🔄 **Phase 13 extension:** Registrar module significantly expanded — direct grade encoding bug fixes, Student Management, Enrollment Management, and Excel Import/Export added. See Phase 13 below for full detail.
> 🔔 **Open enhancement request:** No dedicated tab/section currently exists to browse previously generated COG/TOR records as a list/history — generation is a one-off action tied to a student's profile page. See Phase 13.10.

---

## PHASE 8: EXCEL FEATURES ✅ COMPLETED
**Date:** February 26, 2026
**Status:** ✅ Complete (100%)

### 8.1 Files
- [x] app/Exports/StudentsExport.php — department_id scoped, **supports `null` for unscoped export** ✅
- [x] app/Imports/StudentsImport.php — department_id scoped, `date_format:Y-m-d` strict validation, **supports `null` for unscoped import** ✅ (fixed March 23 session)
- [x] app/Exports/GradeTemplateExport.php — unchanged ✅
- [x] app/Imports/GradesImport.php — unchanged ✅
- [x] app/Http/Controllers/HeadOfDepartment/ExcelController.php — template notes row, single course code ✅ (fixed March 23 session)

### 8.2 Head of Department Excel Fixes (March 23 session)
- [x] studentTemplate() — fake sample row with `2024-00001` removed
- [x] studentTemplate() — replaced with format-hint notes row (explains each column)
- [x] studentTemplate() — course code uses `->value('code')` — single code, not all joined
- [x] StudentsImport rules() — `nullable|date` → `nullable|date_format:Y-m-d`

**Deliverables:**
- ✅ Template no longer misleads users with fake sample data
- ✅ Birthdate validation strictly enforces YYYY-MM-DD

> 🔄 **Phase 13 note:** `StudentsExport(null)` / `StudentsImport(null)` — the existing `null` support for department_id — was reused as-is by Registrar's new ExcelController with zero modification needed. Confirmed via `class_exists()` check in Phase 13.7.

---

## PHASE 9: SYSTEM RESTRUCTURE ✅ COMPLETED
**Date:** March 2026
**Status:** ✅ Complete (100%)

### 9.1–9.9 — Previously Completed
(See prior sessions — all database migrations, seeder updates, module restructures, bug fixes done)

### 9.10 — SweetAlert2 ✅ COMPLETE (March 23 session)
- [x] @stack('scripts') added to layouts/app.blade.php ✅
- [x] HoD: Approve All — SweetAlert2 confirm ✅
- [x] HoD: Reject submission — SweetAlert2 confirm + remarks validation ✅
- [x] HoD: Delete Student — SweetAlert2 confirm ✅
- [x] Faculty: Submit to HoD — SweetAlert2 confirm ✅
- [x] Faculty: Resubmit to HoD — SweetAlert2 confirm + remarks validation ✅
- [x] Registrar: Finalize All — SweetAlert2 confirm ✅
- [x] Registrar: Generate TOR — SweetAlert2 confirm ✅
- [x] Registrar: Generate COG — SweetAlert2 confirm ✅ (bonus)

### 9.11 — Verification & End-to-End Test ⏳ SUPERSEDED
- [ ] ~~php artisan route:list — full clean verification~~ — superseded by Phase 13.7 verification pass
- [ ] ~~php artisan migrate:status — all green~~ — pending
- [ ] ~~Full 12-step end-to-end test~~ — the 12-step multi-role flow (Faculty → HoD → Registrar) this test was designed for is being replaced by the Phase 13 single-actor flow. A new E2E test plan (Admin + Registrar checklists) is now provided alongside this session's work — see Phase 13.8.

### 9.12 — Excel Import Fixes ✅ DONE (March 23 session)
- [x] Template fake sample row → notes row
- [x] Single course code in template (not all joined)
- [x] Strict date_format:Y-m-d validation

### 9.13 — Emoji → Font Awesome Cleanup ✅ DONE (March 23 session)
- [x] 26 blade files scanned
- [x] All hardcoded emojis replaced with Font Awesome 6.5 icons
- [x] Affected: admin/dashboard, faculty/dashboard, faculty/grades/index, faculty/subjects, head_of_department/dashboard, head_of_department/review, head_of_department/students/index
- [x] guest.blade.php ✦ SVG art — intentionally preserved
- [x] Role badge formatting: raw DB value → ucwords(str_replace('_', ' ', ...))

### 9.14 — UserSeeder Bug Fix ✅ DONE (March 23 session)
- [x] Root cause: `firstOrCreate` never updates existing records
- [x] All 5 users → `updateOrCreate`
- [x] Faculty user now gets `department_id` assigned
- [x] Verified: HoD department_id correctly saves on re-seed

**Phase 9 Final Deliverables — ALL COMPLETE:**
- ✅ All database migrations green
- ✅ Department scoping working correctly
- ✅ Admin student management removed
- ✅ HoD full module — students, enrollment, faculty assignment, grade review
- ✅ Faculty resubmit flow with remarks
- ✅ Registrar bulk finalize + COG/TOR generation
- ✅ Excel import/export with fixed template and strict validation
- ✅ SweetAlert2 on ALL 8 destructive actions
- ✅ Font Awesome icons throughout — zero hardcoded emojis
- ✅ UserSeeder using updateOrCreate — safe to re-seed
- [~] End-to-end test — superseded, see 9.11 note above

---

## PHASE 10: REPORTING & ANALYTICS 📅 PLANNED
**Date:** TBD
**Status:** 📅 Not Started (0%)

### Planned Tasks
- [ ] Department performance reports — average GWA per department per semester
- [ ] Per-subject grade distribution
- [ ] Faculty submission tracking — on-time vs late
- [ ] School year/semester summary reports
- [ ] Export reports to Excel/PDF

---

## PHASE 11: UI/UX & TESTING 🔄 IN PROGRESS
**Date:** February 26, 2026 (started)
**Status:** 🔄 In Progress (~40%)

### 11.1 Login Page ✅ DONE
- [x] Split-screen layout, custom SVG illustration, Playfair Display + DM Sans fonts

### 11.2 All Role Dashboards ✅ DONE
- [x] Admin, HoD, Faculty, Registrar dashboards redesigned
- [x] Font Awesome icons, role badge formatting, stat cards

### 11.4 Brand Identity & Color System Overhaul 🔄 IN PROGRESS (July 14 session)
**Trigger:** University-branded rebrand requested — replace generic navy/gold theme with official ESSU green + gold palette, apply the real university seal in place of the placeholder graduation-cap icon.

- [x] University seal logo processed — background removed via border-connected flood fill (preserves internal white ring/circle seal details, unlike a naive white color-key), feathered edge, cropped to content. Generated at 32/64/128/256/512px + full-res transparent PNG.
- [x] Logo assets placed at `public/images/logo/` — `essu-seal.png` (primary, sidebar + login), `essu-seal-full.png` (login backdrop watermark), `essu-horizontal.png` (seal + wordmark lockup, reserved for future COG/TOR PDF header — not yet wired in)
- [x] Color palette defined — primary greens (`#4b6043` darkest → `#95bb72` soft) from the university's palette reference; gold (`#c9a84c` family) retained as accent since it's already in the seal artwork and was already used app-wide; status badges (Approved/Rejected/Pending) intentionally left on standard Tailwind colors to avoid visual collision with brand green
- [x] `resources/views/layouts/guest.blade.php` (login page) — navy hex/rgba swapped to green via `sed`; seal image replaces the generic graduation-cap icon badge; full-size seal added as a low-opacity (`0.08`) watermark on the left panel; "Excellence · Integrity · Accountability" slogan added under the brand name
- [x] `resources/views/layouts/partials/sidebar.blade.php` — tan/khaki neutrals swapped to green via `sed`; active/hover nav states changed gold → green (gold kept only on the logo tile background and user-avatar-initial circle, both intentional accents); sidebar logo swapped from Font Awesome icon to the real seal image on a white backing tile (needed for the seal's fine detail to stay legible on the green sidebar)
- [x] `resources/views/layouts/app.blade.php` — page content background changed from neutral gray (`#f1f5f9`) to green-tinted off-white (`#eef4e7`)
- [ ] Registrar/Admin content-page buttons and badges (e.g. gold "Search" button on Encode Grades, similar controls elsewhere) — not yet converted, pending per-page pass
- [ ] Additional login page imagery (user has more assets planned) — explicitly deferred, general rebrand pass prioritized first per client direction

### 11.3 Remaining Tasks
- [ ] End-to-end workflow test — **blocked pending Phase 13 completion** (old 12-step test is obsolete under the new single-actor flow); Admin + Registrar manual test checklists now available (see Phase 13.8)
- [ ] Mobile responsiveness review across all role views
- [ ] Error handling — empty states, 404 pages, form error UX
- [ ] Loading states for PDF generation
- [ ] UI consistency pass across all role views — 🔄 **started July 14 session** (login page + sidebar rebranded to green; other role/content pages still pending, see 11.4)
- [ ] Remove leftover student nav links from Admin dashboard (Phase 9.3 leftover)
- [ ] **NEW:** Remove/hide Faculty and HoD sidebar nav sections once Phase 13.6 lockout is applied
- [ ] **NEW:** Build a COG/TOR Records tab/section (see Phase 13.10)

---

## PHASE 12: BACKUP & RESTORE ✅ COMPLETED
**Date:** April 1, 2026
**Status:** ✅ Complete (100%)

### 12.1 Package Installation
- [x] Installed `spatie/laravel-backup` via Composer
- [x] Published config: `php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"`

### 12.2 Configuration
- [x] Detected XAMPP stack — mysqldump at `C:/xampp/mysql/bin/`
- [x] Set `dump_binary_path` in `config/database.php` under MySQL connection (spatie v3 location — not backup.php)
- [x] Disabled notification channels (`'via' => []`) — no mail configured
- [x] Kept `notifiable` class reference intact to prevent TypeError
- [x] Backup name set to `cog-tor-backup`
- [x] Backup destination: `storage/app/cog-tor-backup/` (local disk)
- [x] Verified: `php artisan backup:run` succeeds — 1.62 MB zip created

### 12.3 BackupController
- [x] Created `app/Http/Controllers/Admin/BackupController.php`
- [x] `index()` — lists all backup zips with size and created date
- [x] `run()` — triggers `Artisan::call('backup:run')` via POST
- [x] `download($filename)` — streams zip file to browser
- [x] `delete($filename)` — deletes zip from storage
- [x] `restore(Request $request)` — accepts .sql upload, runs `DB::unprepared()`

### 12.4 Routes (5 registered)
- [x] `GET  /backup` → `admin.backup.index`
- [x] `POST /backup/run` → `admin.backup.run`
- [x] `GET  /backup/download/{filename}` → `admin.backup.download`
- [x] `DELETE /backup/delete/{filename}` → `admin.backup.delete`
- [x] `POST /backup/restore` → `admin.backup.restore`

### 12.5 Admin Backup Page
- [x] Created `resources/views/admin/backup/index.blade.php`
- [x] Section 1: Create Backup — "Backup Now" button with confirm dialog
- [x] Section 2: Backup History — table with Filename, Size, Created, Download, Delete
- [x] Section 3: Restore Database — file upload (.sql), warning message, "Restore Now" button
- [x] Flash messages for success and error states

### 12.6 Sidebar Navigation
- [x] Added "Backup & Restore" link with `fa-database` icon to `resources/views/layouts/partials/sidebar.blade.php`
- [x] Placed between Users and Academic section
- [x] Active state: `request()->routeIs('admin.backup.*')`
- [x] Fixed: duplicate nav entry removed, broken `{` in Blade class expression corrected

### 12.7 Known Behaviors
- "Sending notification failed" on `backup:run` — harmless, no mail configured, backup still completes
- Restore accepts `.sql` and `.txt` MIME types — upload the `.sql` extracted from the backup zip
- Backup zip contains the full database dump — extract the `.sql` file inside before restoring

**Phase 12 Final Deliverables:**
- ✅ `spatie/laravel-backup` installed and configured for XAMPP
- ✅ Manual backup via Admin UI
- ✅ Backup history with download and delete
- ✅ Database restore via SQL file upload
- ✅ Sidebar nav link wired correctly

> 🔒 **Security note (Phase 13 session):** confirmed `admin/backup/*` routes (index, delete, download, restore, run) were verified via `php artisan route:list --path=backup -v` to correctly carry `Authenticate`, `CheckStatus`, `CheckRole:admin` — no changes needed, but this check was the starting point of the Phase 13 session.

---

## PHASE 13: REGISTRAR-ONLY WORKFLOW MIGRATION 🔄 IN PROGRESS
**Date:** July 2, 2026
**Status:** 🔄 In Progress (~75%)

### 13.0 Trigger — Client Requirement Change
Client provided an updated process flowchart specifying a **single-actor workflow**: Registrar now handles the entire grade lifecycle — encode, validate, save, auto-generate COG/TOR, and verify — with no handoff to Faculty or Head of Department. This supersedes the Phase 6/9 multi-role approval chain (Faculty submits → HoD reviews → Registrar finalizes) for the encoding side of the system.

**Flowchart steps (source of truth for this phase):**
ogin (Registrar only) → Select Student Record → Encode/Update Grades
→ Validate Grades (loop back to Edit if incomplete)
→ Save to Database → Auto-Generate COG/TOR → Format Documents
→ Registrar Verification (loop back to Revise if not approved)
→ Store COG/TOR → Ready for Printing/Release
**Strategy adopted:** *Disable, don't delete.* Faculty and HoD grade-related code stays in the codebase, dormant, in case of a future reversal — only route-level access will be cut off, not the underlying controllers/models/views/data. This is safer than deletion because `grades` and `grade_submissions` tables have Faculty/HoD baked into their column semantics (`faculty_id`, `submitted_by`/`reviewed_by`/`finalized_by`), so ripping out the code risks orphaning existing data references.

### 13.1 Registrar Direct-Encode Bug Fixes ✅ DONE
`RegistrarController::encodeGrades()` had three bugs from being adapted off the old multi-role flow without a matching data-layer update:
- [x] `faculty_id => auth()->id()` — was storing the **Registrar's** user ID in a column semantically meant for Faculty. Fixed → now stores `null`.
- [x] Missing `GradeSubmission` record on direct Registrar entry — broke downstream stats and the unfinalize workflow, which depend on a `GradeSubmission` existing. Fixed → `GradeSubmission::updateOrCreate()` added, stamping the Registrar into `submitted_by` / `reviewed_by` / `finalized_by`, with `dean_action => 'approved_by_head_of_department'` and a `dean_remarks` note ("Direct entry by Registrar") to make the workaround traceable in the data itself.
- [x] `remarks => null` — was silently wiping any existing remarks on every save. Fixed → remarks field left untouched.
- [x] Applied via `/tmp/patch_encode.php` string-replace script — confirmed `PATCHED OK`, grep-verified all three fixes landed at expected lines (171, 179, 189).

### 13.2 `faculty_id` NOT NULL Schema Blocker ✅ DONE
Setting `faculty_id => null` (13.1) violated the `grades` table schema — `faculty_id` was defined via `foreignId()->constrained('users')` with no `->nullable()`, confirmed via:
```php
$table->foreignId('faculty_id')->constrained('users')->comment('Faculty who encoded the grade');
```
- [x] Confirmed via `SHOW COLUMNS FROM grades` — `bigint(20) unsigned`, `Null: NO`, `Key: MUL` (FK index).
- [x] **No `doctrine/dbal` installed**, and project is on **Laravel 10.50.2** — this Laravel version still requires that package for the standard `Schema::table()->change()` migration approach (only dropped in Laravel 11+). Rather than add a new dependency for one column, used a raw MySQL statement instead — smaller footprint, matches "not over-engineered" direction.
- [x] New migration `make_faculty_id_nullable_on_grades_table` — `DB::statement('ALTER TABLE grades MODIFY faculty_id BIGINT UNSIGNED NULL')`, with a `down()` that reverses it (documented caveat: rollback will fail if any row has `faculty_id = NULL` at that point).
- [x] Migrated successfully — reconfirmed via `SHOW COLUMNS`: `Null` flipped from `NO` → `YES`, type/unsigned/FK index (`Key: MUL`) all unchanged.

### 13.3 Full Codebase Mapping ✅ DONE
Before making further changes, pulled and reviewed the entire Registrar/Faculty/HoD stack to avoid guessing:
- [x] All 13 (pre-Phase 13) `registrar/*` routes confirmed clean — `Authenticate` → `CheckStatus` → `CheckRole:registrar` on every route.
- [x] Discovered **dual role-check system**: Spatie's `HasRoles` (`hasRole()`, pivot tables) used in `routes/web.php` and middleware, alongside a **legacy `role` column + `isFaculty()`-style helpers** on the `User` model that check the raw column directly. Both are seeded in parallel by `UserSeeder`. Flagged as a risk for any future "disable" work — a check using only one system could let a user through.
- [x] Confirmed Faculty's entire grade workflow lives self-contained inside `Route::middleware(['auth','status','role:faculty'])->prefix('faculty')->...` — nothing outside that block references `Faculty\GradeController`, `Faculty\FacultyController`, or `Faculty\ExcelController`. Safe to disable at the route level without entangling other code.
- [x] Confirmed HoD's route group similarly self-contained — `grep -rn "HeadOfDepartment\\\\" app/Http/Controllers/ app/Providers/` returned no external references.
- [x] Identified HoD's route group mixes two concerns: **grade review** (`submissions.review/approve/reject` — obsolete under new flow) and **student/enrollment/subject-assignment management** (still needed, but ownership moves to Registrar per client instruction "Registrar takes over").
- [x] Confirmed via grep that `Faculty\GradeController` (`app/Http/Controllers/Faculty/GradeController.php:43`) still has a live `Grade::updateOrCreate()` write path — directly contradicts the client's "only Registrar encodes grades" requirement until Phase 13.6 (disable pass) is applied.
- [x] Confirmed `enrollments.enrolled_by` column comment says "Dean" — another stale-role artifact from the original 3-role design, functionally harmless (plain FK, no DB-level role enforcement) but noted for awareness.

### 13.4 Scope Decision — HoD Absorption ✅ CONFIRMED WITH CLIENT/DEV
- [x] Confirmed: Registrar takes over **all** of HoD's student/enrollment/Excel responsibilities (not just grade review) — full alignment to flowchart.
- [x] Key architectural implication flagged and accepted: HoD's student/enrollment/Excel methods are all scoped by `auth()->user()->department_id` (e.g. `Student::whereHas('course', fn($q) => $q->where('department_id', $departmentId))`). Registrar is institution-wide. Absorbing these features means **dropping department scoping entirely**, not a like-for-like copy — Registrar will see every student across every department. This is a simplification that fits the new single-actor model.
- [x] **Client clarified Admin role scope (July 2 session):** raised the open question of whether Admin needed to be reactivated separately from Registrar. Client confirmed **Admin role stays fully active** — the Registrar Head will also hold Admin access (or both responsibilities merge into one account). No architectural change required; this was a scope clarification, not a redesign. Admin was never part of the "cut roles" discussion in the first place — only Faculty/HoD were ever slated for lockout.

### 13.5 Registrar New Capabilities — Built ✅ DONE

**New Controllers** (`app/Http/Controllers/Registrar/`):
- [x] `StudentController.php` — `index` (search + course/year_level/status filters, paginated, unscoped), `create`, `store`, `edit`, `update`, `destroy` (blocked if student has existing enrollments)
- [x] `EnrollmentController.php` — `index` (active-semester-scoped, includes `enrolledMap` for JS-side already-enrolled dropdown filtering), `store` (with duplicate-enrollment guard via `firstOrCreate` + try/catch on `QueryException`), `destroy` (blocked if a grade already exists for that enrollment)
- [x] `ExcelController.php` — `studentTemplate` (CSV download with format-hint notes row, mirrors HoD's Phase 8 fix), `exportStudents` (reuses `StudentsExport(null)` — no department filter), `importStudents` (reuses `StudentsImport(null)`, surfaces per-row failures/errors back to the student index via session flash)

**New Views** (`resources/views/registrar/`):
- [x] `students/index.blade.php` — table with search/filter form, Download Template / Import Excel / Export Excel actions, SweetAlert2 delete confirm
- [x] `students/create.blade.php`, `students/edit.blade.php` — full student form (name, course, birth date, gender, contact, address, type, year level, status)
- [x] `enrollments/index.blade.php` — active semester banner, enroll form with dynamic JS (subject dropdown disables already-enrolled subjects per selected student), enrollments table with SweetAlert2 remove confirm
- [x] `encode-grades.blade.php` — 3-step wizard: Step 1 (School Year → Semester → Department → Course), Step 2 (Select Student), Step 3 (grade table per subject, Save & Finalize)

**New Routes** (inserted into existing `registrar` route group in `routes/web.php`, verified no naming collisions with `head_of_department.*` equivalents):
- [x] `registrar.students.index/create/store/edit/update/destroy` (6 routes)
- [x] `registrar.enrollments.index/store/destroy` (3 routes)
- [x] `registrar.excel.student-template/export-students/import-students` (3 routes)
- [x] Total Registrar routes: **13 (original) + 13 (new) = 25**, all confirmed carrying `Authenticate`, `CheckStatus`, `CheckRole:registrar`

**Sidebar** (`resources/views/layouts/partials/sidebar.blade.php`):
- [x] Registrar block reorganized into `Academic` (Students, Enrollment) and `Grades` (Encode Grades) section labels — matches HoD's existing sidebar grouping convention for visual consistency
- [x] Icons: `fa-user-graduate` (Students), `fa-clipboard-list` (Enrollment) — same icons HoD's own sidebar already uses for equivalent links

### 13.6 Faculty/HoD Route Lockout ✅ DONE (July 10 session)
**Client decision:** full removal was ruled out (too risky — `grades`/`grade_submissions` FK semantics), route-level lockout only, with test accounts hard-deleted.

- [x] `routes/web.php` — `role:faculty` → `role:faculty_disabled`, `role:head_of_department` → `role:head_of_department_disabled` on both route groups. Neither string is a seeded Spatie role, so `hasAnyRole()` always returns `false` → clean 403 via `CheckRole` middleware (verified: no exception path, confirmed via `cat -n app/Http/Middleware/CheckRole.php`).
- [x] Dead login-redirect branches removed from `routes/web.php` (`/dashboard` closure) and `AuthenticatedSessionController::store()` — both previously had `elseif` branches for `head_of_department`/`faculty` that are now unreachable.
- [x] `User.php`'s legacy `isFaculty()`/`isHeadOfDepartment()` helpers left untouched as inert dead code — confirmed via grep no view/route calls them directly, no bypass path exists.
- [x] Sidebar nav (`sidebar.blade.php`) — both `@if(hasRole('head_of_department'))` and `@if(hasRole('faculty'))` blocks removed entirely.
- [x] Admin User Management (`UserController.php` + `admin/users/index.blade.php`) — Faculty/HoD tabs, role-count queries, role dropdown options (`create()`/`edit()`), and `department_id` validation/assignment logic all removed. Only `all`/`registrar` tabs remain.
- [x] **Test accounts hard-deleted** (not just left dormant) — `faculty@cogtor.test` (id 3) and `hod@cogtor.test` (id 2) removed from `users` table. Required nulling `approved_by` first on both rows (found via `users_approved_by_foreign` FK violation — `faculty@cogtor.test.approved_by` pointed at the HoD's own id).
- [x] `UserSeeder.php` — HoD and Faculty `updateOrCreate` blocks removed entirely, along with their `$this->command->info()` lines. `pending@cogtor.test` intentionally left as-is (still seeds with legacy `role => 'faculty'` string but is functionally inert now that the role is locked out — flagged, not yet cleaned up).
- [x] Verified: `php -l` clean on all 4 touched PHP files, `php artisan view:cache` compiles clean, `php artisan route:list --path=admin/users -v` shows 9 routes all `CheckRole:admin`.
- [x] **Admin role confirmed untouched** — no changes to `role:admin` middleware or Admin routes.

**Still open:** `pending@cogtor.test` seeder entry uses the now-locked-out `faculty` role — low priority, revisit if it causes confusion during testing.

### 13.7 Static Verification Pass ✅ DONE
Full non-browser verification completed before UI testing, catching and fixing multiple real issues along the way:

**Issues found and fixed during this pass:**
- [x] **Controllers initially not created at all** — an earlier "paste the code" step was silently skipped; `ls -la` on `app/Http/Controllers/Registrar/` showed only pre-existing `DocumentController.php`/`RegistrarController.php`. Recreated all 3 via heredoc (`cat > file << 'EOF'`), one at a time to catch failures immediately.
- [x] **`enrollments/index.blade.php` was empty (0 bytes)** and **`enrollments/create.blade.php` contained the wrong content** (the *students create* form had been pasted into the enrollments folder by mistake, under the wrong filename). Fixed via `mv` to relocate the misplaced file to `students/create.blade.php` (its correct location/name), then repopulated `enrollments/index.blade.php` with the correct content via heredoc.
- [x] **Routes never inserted into `routes/web.php`** on the first attempt — confirmed via `grep -n "registrar.students\|registrar.enrollments\|registrar.excel" routes/web.php` returning zero matches. Fixed by pasting the full route block into the existing `registrar` group.
- [x] **False alarm — `scopeActive()` initially thought missing** on `Student`/`Subject`/`Semester`/`Course` models due to a bad grep pattern (`"function active"` instead of Laravel's actual `scopeActive()` convention). Re-verified via `grep -n "scopeActive"` — confirmed present on all 4 models. No code change needed.
- [x] **`stdout is not a tty` / empty redirected file** — `php artisan route:list > file.txt` produced a genuinely empty file in this Git Bash/MINGW environment (confirmed via `wc -l` = 0), rather than the message being cosmetic noise as assumed earlier in the session. Verification approach switched to `--path=` filtered `route:list -v` (which reliably prints to terminal) instead of redirecting to a file — added to Windows/Git Bash Notes below.

**Final verification checklist — all green:**
- [x] `php -l` syntax check — `StudentController.php`, `EnrollmentController.php`, `ExcelController.php`, `routes/web.php` — no errors
- [x] `php artisan route:list --path=registrar -v` — 25 routes, correct middleware stack on every route
- [x] `php artisan route:list --path=head-of-department -v` — 19 routes, confirmed fully untouched, no naming collisions with new Registrar routes
- [x] `php artisan view:cache` — Blade templates compile with no syntax errors
- [x] `view()->exists()` checks — `registrar.students.index/create/edit`, `registrar.enrollments.index` — all confirmed present
- [x] `Enrollment` model relationship check — `student()`, `subject()`, `semester()`, `grade()` all present, matches what `EnrollmentController` calls
- [x] `StudentsExport`/`StudentsImport` class existence confirmed via `class_exists()` — reusable as-is, no modification needed
- [x] `Student`, `Enrollment`, `Course`, `Subject`, `Semester` model existence confirmed
- [x] Full cache clear (`route`, `view`, `config`, `cache`, `optimize`) run before handoff to browser testing

### 13.8 Browser End-to-End Test 🔄 IN PROGRESS
Manual test checklists (Admin + Registrar) now written and ready to run — see the top of this session's notes / shared separately with the dev. Partial testing already surfaced and fixed a real bug (see 13.9). Full pass not yet completed.

**Admin checklist highlights:** login/dashboard access, Subjects CRUD (including the semester field, post-13.9 fix), School Years/Semesters, Departments/Courses, Users, Backup & Restore.

**Registrar checklist highlights:** login/dashboard/sidebar, Students CRUD (unscoped), Enrollment (active semester banner, dynamic subject dropdown), Excel template/export, Encode Grades 3-step wizard — **including the regression test for BSIT + 1st Semester**, which was broken and is now fixed (13.9).

- [ ] Full pass completion and sign-off — pending

### 13.9 Subject `semester` Data & Form Bug — Root Cause Found and Fixed ✅ DONE
**How it surfaced:** While testing Encode Grades as Registrar, selecting **BSIT + 1st Semester** returned "No subjects found for the selected course and semester. Make sure subjects are configured by the Admin." — while BSCS + 2nd Semester worked fine. Initially suspected a permissions issue; investigation showed it was a data/form bug, unrelated to any role or access control.

**Root cause (two layers):**
1. **Data layer:** The original `SubjectSeeder.php` never set a `semester` value on any of its 10 `Subject::create()` calls — all 10 seeded subjects (`IT 101`–`IT 301`, `CS 101`–`CS 401`) had a blank `semester` column. The 5 subjects that *did* work (`CS101`–`CS105`, no space in code) were created separately, later, with the correct value — unrelated to the seeder.
2. **Code layer (the real bug):** `RegistrarController::encodeGradesForm()` filters subjects via `Subject::where('semester', $selectedSemesterModel->semester_name)`, comparing against the **full string** stored in `semesters.semester_name` (e.g. `"1st Semester"`). But the Admin **Subject create/edit forms** (`resources/views/admin/subjects/create.blade.php`, `edit.blade.php`) had `<option value="1st">`, `<option value="2nd">` — **abbreviated** values — with matching validation (`'semester' => 'required|in:1st,2nd,Summer'`) in `SubjectController.php`. So *any* subject saved or edited through the Admin UI for 1st or 2nd semester would silently fail to appear in Encode Grades, since `"1st"` never equals `"1st Semester"`. Only `"Summer"` happened to match both ways by coincidence. Confirmed live when editing `IT 101`/`IT 102` through Admin (to assign a faculty member) also — unintentionally — set their `semester` to the abbreviated `"1st"`, actively re-breaking those two rows during this same session.

**Fix applied — all three layers corrected together:**
- [x] `resources/views/admin/subjects/create.blade.php` — `<option value="1st">` / `"2nd"` → `<option value="1st Semester">` / `"2nd Semester"` (Summer unchanged)
- [x] `resources/views/admin/subjects/edit.blade.php` — same fix, preserving the `old('semester', $subject->semester)` fallback pattern
- [x] `app/Http/Controllers/Admin/SubjectController.php` — both `store()` and `update()` validation rules changed from `'required|in:1st,2nd,Summer'` to `'required|in:1st Semester,2nd Semester,Summer'`
- [x] Verified via `php -l` (controller) and `php artisan view:cache` (both views) — all clean, no syntax errors
- [x] **Manual data patch** — snapshotted the 10 affected rows before changing anything (rollback reference), then ran a `DB::table('subjects')->where('code', ...)->update(['semester' => ...])` pass fixing all 10, including correcting `IT 101`/`IT 102` from the incomplete `"1st"` back to the full `"1st Semester"`. Verified after via the same snapshot query — all 10 confirmed correct.
- [x] **`database/seeders/SubjectSeeder.php` corrected** — added the missing `semester` key to all 10 `Subject::create()` calls, and switched `Subject::create()` → `Subject::updateOrCreate(['code' => ...], [...])` (per Lesson #46) so that re-running the seeder on a database that already has these rows **corrects** them instead of throwing a unique-constraint error on `code` or silently skipping. Verified via `php -l` and a live `php artisan db:seed --class=SubjectSeeder` run — reported "created/updated successfully," and a follow-up query confirmed all 10 subjects now hold the correct full-string semester values.

**⚠️ Provisional/placeholder data — flagged for revisit:** The specific semester assigned to each of the 10 subjects (`IT 101`→`1st Semester`, `IT 102`→`2nd Semester`, etc.) follows the common odd/even course-numbering convention (odd = 1st Semester, even = 2nd Semester) as a **temporary placeholder only**. This was **not** verified against an official curriculum or prospectus — none was available at the time of this fix, and the client confirmed proceeding with the common-convention placeholder for now. **This needs to be revisited and corrected against the real curriculum once available**, since BSIT and BSCS numbering doesn't necessarily follow the same term-placement convention across institutions.

**Why this matters going forward:** Without the form/validation fix, this bug would have recurred every time anyone edited a subject through the Admin UI, even after the manual data patch — the seeder fix alone wouldn't have prevented it either, since the seeder only runs once (or on `migrate:fresh --seed`), not on every edit. Fixing all three layers together (form + validation + seeder) closes the loop.

### 13.11 COG/TOR Duplicate "Current" Record Bug 🔄 IN PROGRESS
**How it surfaced:** Sofia (test student) ended up with more than one `cog_records` row marked `is_current = true` for the same semester — `/registrar/documents` showed multiple "Current" COGs instead of one, with older generations never getting superseded.

**Root cause:** `DocumentController::generateCog()` (and `generateTor()`) previously ran the "mark old records not-current" update and the "create new record" insert as two sequential, non-atomic statements with no logging — no way to confirm supersede actually ran, and no protection if the request died mid-way.

**Fix applied — COG:**
- [x] Wrapped the supersede + create logic in `DB::transaction()`, added `Log::info('COG supersede check', ['student_id' => ..., 'rows_superseded' => ...])` so the operation is atomic and verifiable in `storage/logs/laravel.log`.
- [x] **Regression introduced by the fix, then caught and fixed:** `$documentNumber` was declared *inside* the transaction closure but referenced two lines *after* it (building `$path` and the `Content-Disposition` header) — PHP does not leak closure-local variables to the parent scope. This crashed the request (`Undefined variable $documentNumber`), which caused Laravel to roll back the whole transaction — meaning every prior "did the supersede fix work?" test was inconclusive not because the fix was broken, but because the crash masked it before it could be verified.
- [x] Corrected by pulling `$documentNumber = $cog->document_number;` from the returned model **after** the closure closes, instead of relying on the closure to leak the variable.
- [x] Verified via `php -l app/Http/Controllers/Registrar/DocumentController.php` — no syntax errors.

**Fix applied — TOR (same pattern, applied second):**
- [x] `generateTor()` had the identical non-atomic, unlogged supersede pattern and was patched to match COG: wrapped in `DB::transaction()`, added `Log::info('TOR supersede check', ['student_id' => ..., 'rows_superseded' => ...])`, and `$documentNumber = $tor->document_number;` placed correctly *after* the closure this time (applying the lesson from the COG regression instead of repeating it).
- [ ] `php -l` confirmation on the TOR patch — **pending, not yet pasted back**
- [ ] Live retest — generate a new COG *and* TOR for Sofia (semester with the pre-existing duplicate), confirm both `storage/logs/laravel.log` entries appear ("COG supersede check" / "TOR supersede check"), and confirm `/registrar/documents` shows at most one "Current" record per type — **pending**

**Known false alarms ruled out during this debugging pass (documented so they aren't re-investigated):**
- IDE red squiggles under `\DB` / `\Log` in the controller — cosmetic, caused by missing `laravel-ide-helper` stub files, not a real error.
- "Unexpected endif" / SonarLint accessibility warnings reported against files under `storage/framework/views/*` — these are compiled Blade cache artifacts with hash filenames, not the actual `.blade.php` source; safe to ignore and worth excluding from the editor's watcher (`storage/framework/views/**`) to stop recurring false positives.

**⚠️ Still not addressed — existing bad data:** Sofia's pre-existing duplicate "Current" COG rows are **not** retroactively fixed by this code change — the fix only prevents *new* duplicates going forward. A cleanup pass (collapse existing duplicates to one current record per student+semester/type) and a DB-level uniqueness constraint migration are both still outstanding.

### 13.12 Backup & Restore — "Backup Now" Button Completely Non-Functional ✅ FIXED
**How it surfaced:** Client bug report — clicking **Backup Now** on `/admin/backup` either errored or reported "success" with no zip ever appearing in Backup History, while `php artisan backup:run` from the terminal always worked correctly.

**Root cause chain (three separate bugs stacked on top of each other):**

1. **`config/backup.php` `notifiable` key commented out** — `\Spatie\Backup\Notifications\Notifiable::class` was commented out (a leftover from an earlier "keep the class to prevent TypeError" note, misapplied). With the key absent, `app(null)` was resolved instead of a proper Notifiable, throwing `EventHandler::determineNotifiable(): Return value must be of type Notifiable, Application returned`. Fixed by uncommenting the key.
2. **Mail notification channel still active with no mail server available** — once notifiable resolved, Spatie tried to actually send a `BackupWasSuccessfulNotification` via `mail`, hitting `.env`'s configured `MAIL_HOST=mailpit` (a Docker/Sail-style mail catcher not present in this XAMPP/Windows setup) → connection failure. Fixed by setting every notification class's channel array to `[]` in `config/backup.php`, disabling notification delivery entirely rather than trying to stand up a working mail transport for a dev-only feature.
3. **The real blocker — Windows/`php artisan serve` cannot spawn a subprocess that opens its own TCP socket.** Even with the config fixed, every backup triggered via the browser failed with `mysqldump: Got error: 2004: "Can't create TCP/IP socket (10106)"`, while the identical command succeeded every time from the terminal. Extensive isolation testing (see below) confirmed this is specific to `php artisan serve` itself — PHP's built-in dev server holds an active listening TCP socket in-process, and any child process it spawns (directly or via an intermediate process) appears to inherit a corrupted/blocked socket layer on this Windows machine. This is a known rough edge of PHP's built-in server on Windows, not a bug in the application, Spatie, or Laravel.

**Isolation steps taken to rule out every other explanation** (documented so this reasoning isn't repeated from scratch in future sessions):
- `SystemRoot`, `DB_HOST`, PHP binary/version — confirmed identical between CLI and web contexts
- Restarting `php artisan serve` fresh — no change
- Running the dev server from native `cmd.exe` instead of Git Bash — no change (ruled out shell-wrapping theories)
- Antivirus/Windows Defender real-time protection disabled — no change
- Spawning the exact command Spatie builds, via reflection into `MySql::getProcess()`, both `exec()` and raw `Symfony\Process` (array form, no shell) — both still hit `10106` when run from inside `php artisan serve`'s process tree
- Spawning a **fresh, separate `php.exe` process** to run `backup:run` (`Process` with `PHP_BINARY` + `artisan` path) — still hit `10106`, since it remained a descendant of the `serve` process tree
- The exact same command, run via XAMPP's Apache (a completely different server model with no persistent listening socket in the request-handling process) — **succeeded immediately**, both as a raw standalone `proc_open()` test script and via `mysqladmin ping`

**Fix applied — route the real backup execution through Apache instead of in-process:**
- [x] `config/backup.php` — `notifiable` uncommented; all 6 notification classes (`BackupHasFailedNotification`, `UnhealthyBackupWasFoundNotification`, `CleanupHasFailedNotification`, `BackupWasSuccessfulNotification`, `HealthyBackupWasFoundNotification`, `CleanupWasSuccessfulNotification`) changed from `['mail']` to `[]`
- [x] Created `public/run-backup.php` — ships with the project (versioned in Git), a minimal standalone script (no Laravel bootstrap, avoids any XAMPP-bundled-PHP-vs-app's-PHP version mismatch) that uses `proc_open()` to run `backup:run` against the correct PHP binary, with the project directory as its working directory, returning JSON with exit code/stdout/stderr
- [x] **Machine-specific path handling made portable** — rather than hardcoding a developer's absolute project path (which would break for every other machine/client that clones the repo), `run-backup.php` reads the actual project path from `storage/backup-project-path.txt`, a plain-text, one-line, git-ignored file. A committed `storage/backup-project-path.txt.example` ships instead, showing the expected format — each developer/client copies it and edits one line to match where they placed the project on their own machine
- [x] Deployed a copy of `run-backup.php` (and its accompanying config text file) to `C:/xampp/htdocs/cog-tor-backup-trigger/` so Apache can serve it directly at `http://localhost/cog-tor-backup-trigger/run-backup.php`
- [x] `BackupController::run()` rewritten — instead of calling `Artisan::call('backup:run')` in-process, it now makes an internal `Http::get()` call to that Apache-served path, which Apache runs using its own bundled PHP (subprocess-creation-only — Laravel itself never runs there), triggering the real backup via the project's actual PHP binary outside the `serve` process tree entirely
- [x] Verified end-to-end via `storage/logs/laravel.log`: `http_status: 200`, `success: true`, `exit_code: 0`, real backup output (zip created, correct file size), and confirmed the new zip appears in Backup History on `/admin/backup`

**New operational dependency:** Apache (and MySQL) must be running in XAMPP Control Panel whenever **Backup Now** is clicked, even though the rest of the app runs via `php artisan serve`. Backup Now will fail (connection refused) if Apache is stopped.

**Setup required per machine (documented in README):** Copy `public/run-backup.php` to `C:/xampp/htdocs/cog-tor-backup-trigger/`, copy `storage/backup-project-path.txt.example` to `storage/backup-project-path.txt` (also copied alongside the script in htdocs) and `.env`-style edit the one path line to match the local project location.

**Still open / lower priority:**
- [ ] The Apache-served copy of `run-backup.php` is currently a public, unauthenticated endpoint on `localhost` — acceptable for local dev only; would need a shared-secret check (e.g. a key compared against `.env`) before any real deployment
- [ ] Consider whether this workaround is still needed once the app moves to a real deployment target (Linux server, proper hosting) — the root `php artisan serve` limitation is Windows-dev-environment-specific and very likely won't exist in production
- [x] Additional per-machine setup gaps found and fixed on a second developer machine — see Phase 13.15
- [ ] **Under consideration:** exporting backups directly as a standalone `.json` or `.sql` file (instead of only inside a `.zip`) for faster backup/restore — a plain `.sql` dump is already what's inside the zip today (see 12.7), so this would mainly mean exposing/downloading it directly rather than requiring the user to extract it from the zip first; a `.json` export would need a defined schema/structure decision before implementation. Not yet scoped or scheduled — flagged here for a future session.

**SweetAlert2 + loading spinner (unrelated, added same session):**
- [x] `resources/views/admin/backup/index.blade.php` — replaced plain `confirm()` on **Backup Now** with a SweetAlert2 confirm dialog followed by a `Swal.showLoading()` spinner modal that stays open until the full-page POST reloads with the result
- [x] Hit Lesson #44 twice in the same file — `document.getElementById`/`addEventListener` and later the entire `Swal.fire`/`showCancelButton`/`isConfirmed`/`showLoading` block got lowercased on the way into the file via a `cat`/pipe write; both instances required a full block rewrite done directly in the code editor, not via terminal pipe

### 13.15 Backup Now — Additional Per-Machine Root Causes Found on Second Machine Setup ✅ FIXED
**How it surfaced:** Setting up the Phase 13.12 fix fresh on a second developer machine (RANEA GRACE's) surfaced three additional issues that hadn't appeared on the original machine — all specific to per-machine environment differences, not the application code.

1. **Hardcoded `--host=localhost` silently overriding `DB_HOST`** — `config/database.php`'s `mysql.dump` array had `'add_extra_option' => '--host=localhost'` left over from initial XAMPP setup. This CLI flag is appended directly to every `mysqldump` command Spatie builds, so it silently overrode `DB_HOST=127.0.0.1` in `.env` no matter how many times `.env` was edited or `config:clear`/`config:cache` was run. Fixed by changing the value to `'--host=127.0.0.1'`.
2. **A single active project vhost became the default handler for ALL of port 80** — adding a `<VirtualHost *:80>` block for the project's `.test` domain (per Phase 13.12/Lesson #79) meant Apache treated it as the default vhost for any unmatched request, including plain `localhost` — which broke the `http://localhost/cog-tor-backup-trigger/run-backup.php` trigger URL with a 404 (specifically Laravel's own styled 404 page, since the request was being routed into the project's app instead of `htdocs`). Fixed by adding an explicit `<VirtualHost *:80>` block for `ServerName localhost` pointing at `C:/xampp/htdocs`, listed **first** in `httpd-vhosts.conf`.
3. **Apache could not read most of the project's files when the project lives inside a Windows user profile folder** (`C:\Users\<name>\Desktop\...`) — once DB dumping worked, the resulting backup zip was only ~12 KB instead of ~5.7 MB, because Spatie's file-backup step silently found almost nothing to zip. Confirmed via the same command zipping ~1779 files correctly when run from the terminal under the interactive user's own permissions. Fixed by granting **Read & execute / List folder contents / Read** NTFS permissions on the project folder.
4. **`run-backup.php` was calling `backup:run` with `--only-db`**, producing database-only backups rather than the full project+database backup the button is expected to produce. Fixed by removing the `--only-db` flag.

**Confirmed fixed via:** `curl http://localhost/cog-tor-backup-trigger/run-backup.php` returning `{"success":true,...}` standalone, then a full "Backup Now" click from the browser producing a ~5.7 MB zip (matching file count of a terminal-run `php artisan backup:run`) in Backup History.

**Note for future machine setups:** these three issues don't throw obvious "misconfigured" errors — they surface as different, seemingly-unrelated symptoms (wrong host error → 404 → undersized zip) at each step. Check all three explicitly on any new machine, not just Phase 13.12's original fix.

### 13.13 Masterlist Import — Strict Subject Validation & Categorized Report Modal ✅ DONE
**Trigger:** Manual testing of the Masterlist Import feature (bulk grade import via Excel) surfaced a silent-failure risk — a subject code typed under the wrong semester column, or a typo'd code, was being auto-created as a phantom/duplicate `Subject` row instead of being rejected.

**Fixes applied to `app/Imports/MasterlistImport.php`:**
- [x] Subject lookup made strict — `Subject::where([...])->first()` against `course_id` + `code` + `year_level` + `semester`, no more auto-create on miss. A column that doesn't resolve is skipped with a specific error naming the code, course, year, and semester checked.
- [x] New row-level guard — a row is skipped entirely if the sheet's declared Year Level doesn't match the student's own `year_level` on record, checked before any subject columns are processed for that row.
- [x] Results split into three buckets — `successes[]`, `warnings[]` (name mismatch, still imported), `errors[]` (skipped, with reason) — instead of one flat error list.
- [x] **Blank/lookup-sheet guard fix:** the template workbook ships with hidden lookup sheets (`CourseLookup`, `SubjectLookup`) backing the dropdown validation on the data-entry sheet. `Maatwebsite\Excel`'s `ToCollection` runs `collection()` once per sheet, so these were each triggering a false "Course Code not selected" error. Root cause traced via `listWorksheetNames()` on the actual template file. Fixed by checking whether the expected header cell (course code) is empty while the sheet still has other content — if so, treat it as a non-data lookup sheet and skip silently, rather than guessing from "is the whole sheet blank."

**New UI — categorized SweetAlert2 import report** (`resources/views/registrar/students/index.blade.php`):
- [x] Session-flashed `import_report` (imported count + successes/warnings/errors arrays) rendered as a single SweetAlert2 modal on page load via a small `window.__importReport` data-island script + logic added to the existing `@push('scripts')` block — no new dependency, reuses SweetAlert2 already used elsewhere in the app.
- [x] Iterated twice on visual design per feedback: v1 was flat color-coded lists; v2 added stat cards with counts; **final version** uses elevated white cards (border + shadow) with icon/count/subtitle per category, a dynamic header/icon based on outcome (Import Completed / Completed with Warnings / Import Failed / Nothing to Import), independently-scrollable sections per category (capped height, so one long list can't push others off-screen), and a scoped `<style>` block injected into the Swal `html` string (not Tailwind utility classes — dynamically-injected classes don't compile via the Tailwind CDN, see Lesson #40) so markup stays semantic instead of using inline styles on every element.
- [x] Confirmed root-cause of a specific test-sheet failure during this work: sheet used `CS 101`/`CS 102`/`CS 301`, which are real subjects but belong to a *different course* (`course_id => 2`, not BSIT) and different year levels — not a bug, correct rejection behavior. Documented as a data/curriculum-entry issue, not a code issue.

### 13.14 Enrollment Management — Bug Fixes & New Filters ✅ DONE
**Trigger:** Manual testing of `registrar.enrollments.store` showed brand-new enrollments being incorrectly reported as "already enrolled."

**Root cause — `Registrar/EnrollmentController.php`:**
- [x] `store()` used `[$enrollment, $created] = Enrollment::firstOrCreate(...)` — **`firstOrCreate()` does not return a `[model, wasCreated]` tuple in Laravel**, it returns only the model. The destructuring assignment silently mis-assigned `$created`, which evaluated falsy regardless of whether a new row was actually inserted — meaning the row *was* being created correctly, but the response always claimed a duplicate. Fixed by replacing with an explicit `->where([...])->exists()` check before an explicit `Enrollment::create(...)` call — no tuple assumptions.
- [x] Success message upgraded to name the actual student and subject (`"{$student->getFullName()} has been enrolled in {$subject->code} — {$subject->name} for {$activeSemester->semester_name}."`) instead of a generic "Student enrolled successfully." — closes a UX gap where the registrar couldn't tell who/what an enrollment confirmation was actually for.
- [x] `store()` now calls `->withInput(['student_id' => $student->id])` on both the success and duplicate-error redirect paths, and `students/index`-style `old('student_id')` selection was added to the Student `<select>` in `enrollments/index.blade.php` — keeps the selected student in the dropdown after a redirect instead of resetting the form, letting a registrar enroll one student into multiple subjects back-to-back. The page's existing cascading JS (`if (studentSelect.value) studentSelect.dispatchEvent(new Event('change'))`) already handled re-triggering the Subject dropdown repopulation on load — no JS changes needed for this part.

**New filters added to `EnrollmentController::index()` and `enrollments/index.blade.php`:**
- [x] `date_filter` query param — `all` / `today` / `week` / `month` / `custom`, filtering on `enrollment_date`. Custom reveals two `<input type="date">` fields (`date_from`/`date_to`) with an explicit Apply button, since a date range can't sensibly auto-submit on every keystroke like the preset dropdown does.
- [x] `group_by` query param — `none` / `subject` / `department` / `year_level`, using `Collection::groupBy()` on eager-loaded `student.course.department` / `subject` / `student.year_level`. Grouped view intentionally drops pagination (loads the full filtered set) since group-then-paginate doesn't compose cleanly — documented as a deliberate simplification, not a bug.
- [x] Added an "Enrolled On" column to the enrollments table, showing `enrollment_date` regardless of filter state.
- [x] Group section headers styled as a distinct amber banner (`#fef3e2` background, `#c9a84c` accent matching the existing Enroll button color) with an icon and pill count badge — first version blended into the table background and was hard to spot while scrolling; fixed after visual feedback.

### 13.10 🔔 Open Enhancement Request — COG/TOR Records Tracking Tab ⏳ NOT SCHEDULED
Flagged during the July 2 session, not yet built:
- [ ] No dedicated tab/section currently exists to view a **history/log of all previously generated COG and TOR documents**. Right now, COG/TOR generation is a one-off action reachable only from a student's Academic Profile page (`registrar.students.cog` / `.tor`) — there's no way to browse "all COGs generated this semester" or "all TORs generated for BSIT students" as a list.
- [ ] Proposed: a new Registrar-side tab (e.g. `registrar.documents.index` or similar) listing all `cog_records` / `tor_records` rows — student name, document number, date generated, semester/school year, with a re-download link — so generated documents are tracked as a proper record, not just a transient PDF download.
- [ ] Not yet scoped in detail (exact columns, filters, whether Admin should also see this) — needs a short discussion before implementation. Placing this here as a reminder so it isn't lost before the next phase of work.

### 13.16 Masterlist Relocation & Encode Grades History UX Refinement ✅ DONE (July 22 session)

**Trigger:** Download Masterlist / Import Masterlist buttons were sitting on Student Management (`registrar/students`), but the masterlist is grade data scoped to a School Year/Semester/Course, not student bio-data — logically misplaced next to CRUD actions for student records. Client flagged this as inconsistent.

**Masterlist relocation:**
- [x] `ExcelController::importMasterlist()` — redirect target changed from `registrar.students.index` → `registrar.encode-grades`. `import_report` flash payload (imported/successes/warnings/errors) unchanged.
- [x] `registrar/students/index.blade.php` — Download Masterlist / Import Masterlist buttons removed from header; `import_report` script block and its SweetAlert2 rendering logic removed from `@push('scripts')`. Delete-confirm SweetAlert2 logic (unrelated) left untouched.
- [x] `registrar/encode-grades.blade.php` — same two buttons added to header, restyled to match this page's existing gold-accent inline-style system (not copied verbatim from the Students page's Tailwind classes). `import_report` script block and its full SweetAlert2 rendering logic moved over verbatim into a new `@push('scripts')` block (this page previously had none).
- [x] No route, controller-signature, `MasterlistExport`, or `MasterlistImport` changes needed — confirmed both classes take no request-scoped constructor params (Course/School Year/Year Level are read from the sheet's own header cells at import time), so this was a pure move, not a rewire.
- [x] **Regression caught before browser testing:** first pass of the edit left a stray `</x-app-layout>` closing tag mid-file (from an earlier find-and-replace inserting content in the wrong spot) — `php -l` and `php artisan view:cache` both passed clean despite this, since neither validates Blade component nesting. Caught by inspecting the full file directly; fixed by removing the duplicate tag. **Lesson:** Blade component-tag mismatches inside a page will not surface via `php -l` or `view:cache` — always visually confirm structure (or load the page in-browser) after edits that move content across `<x-slot>`/component boundaries, don't rely on static checks alone.

**Encode Grades — student history modal, view vs. edit split:**
- [x] `registrar/encode-grades.blade.php` — student list's "History" column renamed to "Action"; single eye-icon button replaced with two buttons: eye (`viewHistory(id, false)`, read-only) and pencil (`viewHistory(id, true)`, editable).
- [x] `viewHistory(id, editable)` now takes a second param, stored in module-level `historyEditable`, read by the two render functions below.
- [x] History modal's grade table — Action column (Edit/Encode buttons + status badge) now only renders when `historyEditable` is `true`; column widths on the remaining columns widen slightly to fill the space when it's hidden, rather than leaving a gap.
- [x] `openGradeEditModal()` / `submitGradeEditModal()` / `closeGradeEditModal()` unchanged — they're only reachable from buttons that now only exist in edit mode, so no additional guarding needed.
- [x] `/registrar/students/{id}/grade-history` endpoint unchanged — same response payload powers both modes; only client-side rendering branches on `historyEditable`.

**Phase 13 Deliverables So Far:**
- ✅ Registrar direct-encode bugs fixed (`faculty_id`, missing `GradeSubmission`, remarks wipe)
- ✅ `faculty_id` schema made nullable via dependency-free raw migration
- ✅ Full Registrar Student/Enrollment/Excel management built — controllers, views, routes, sidebar
- ✅ Static verification pass complete — all syntax, routing, view-binding, and model-dependency checks green
- ✅ Admin role scope clarified with client — stays active, no lockout
- ✅ Subject `semester` bug found and fixed at all three layers (form, validation, seeder) + data patched
- ✅ Admin + Registrar manual E2E test checklists written
- 🔄 Browser E2E test — in progress, partial pass done (surfaced 13.9), full pass pending
- ✅ Faculty/HoD route lockout — done (route-level `_disabled` role strings, test accounts hard-deleted, dual role-check audited)
- ⏳ Dual role-check system audit (Spatie vs legacy `role` column) — pending, relevant to lockout work
- 🔔 COG/TOR Records tracking tab — flagged, not yet scheduled
- 🔄 COG/TOR duplicate "current" record bug — fixed for COG (verified), TOR patch applied but not yet `php -l`-confirmed or live-retested; existing duplicate data + DB uniqueness constraint still outstanding
- ✅ Backup & Restore "Backup Now" button — fixed (config notifiable/notification-channel bugs + `php artisan serve` Windows socket-inheritance limitation worked around via Apache-triggered execution); SweetAlert2 confirm + loading spinner added; additional per-machine root causes (hardcoded DB host flag, vhost default-handler conflict, NTFS folder permissions, `--only-db` flag) found and fixed on a second machine — see Phase 13.15
- ✅ Masterlist Import — strict subject validation (no more phantom auto-created subjects), row-level year-level mismatch check, categorized SweetAlert2 import report (successes/warnings/errors), lookup-sheet false-error fix
- ✅ Enrollment Management — fixed `firstOrCreate` tuple-destructuring bug causing false "already enrolled" errors on new enrollments, added named success messages, added student-dropdown persistence across submissions, added date-range + group-by filters

---

## PHASE 14: CURRICULUM FEATURE 📅 PLANNED
**Date:** TBD
**Status:** 📅 Not Started (0%)

*(Renumbered from Phase 13 — see Phase Renumbering note at top of file)*

### Planned Tasks
- [ ] Curricula table + curriculum_subjects table
- [ ] Admin UI to build/manage curriculum per course per year
- [ ] Link enrollments/grades to curriculum subjects
- [ ] Update COG generation to pull from curriculum
- [ ] Follow CHED/SUC standard (same pattern as SAIS, AISIS)
- [ ] **NEW:** Once real curriculum data exists, revisit and correct the provisional `semester` placeholder values set in Phase 13.9 for `IT 101`–`IT 301` and `CS 101`–`CS 401`

---

## TECHNICAL NOTES

### Grade Status ENUM — v2 Values ✅ Active
saved                        — Faculty encoded, not yet submitted
pending_head_of_department_review  — Submitted, awaiting HoD action
approved_by_head_of_department     — HoD approved, in Registrar queue
rejected                     — HoD rejected, Faculty can resubmit
finalized                    — Registrar locked, permanent
**Critical:** These are the ONLY valid values. MySQL throws truncation error on anything else.

> **Phase 13 note:** Registrar's direct-encode path (`encodeGrades()`) now also writes a `GradeSubmission` with `dean_action => 'approved_by_head_of_department'` to stay compatible with this ENUM chain, even though no actual Dean/HoD action occurred — this is an intentional workaround, not a new valid semantic meaning for that value. See Phase 13.1.

### School Year Status ENUM ✅
upcoming   — Not yet active (default)
active     — Current school year (only 1 at a time)
completed  — Past school year
**Critical:** `inactive` is NOT a valid value.

### Semester Status ENUM ✅
upcoming   — Not yet active (default)
active     — Current semester (only 1 at a time)
completed  — Past semester
**Critical:** `inactive` is NOT valid.

### Subject `semester` Field — Free Text, Must Match `semesters.semester_name` Exactly
```php
// Correct values as of Phase 13.9 fix:
'semester' => '1st Semester'   // NOT '1st'
'semester' => '2nd Semester'   // NOT '2nd'
'semester' => 'Summer'         // unchanged, already matched
```
**Critical:** `subjects.semester` is a plain string column, NOT a foreign key to `semesters`. `RegistrarController::encodeGradesForm()` does an exact string match against `semesters.semester_name`. Any mismatch (abbreviation, typo, casing, trailing whitespace) causes subjects to silently disappear from Encode Grades with no error — just an empty "no subjects found" state. See Phase 13.9 for the full incident writeup.

### Head of Department Scoping Pattern
```php
Student::whereHas('course', function($q) {
    $q->where('department_id', auth()->user()->department_id);
})
```
> **Phase 13 note:** Registrar's equivalent methods intentionally **omit** this scoping — Registrar is institution-wide, not department-scoped. Do not copy this pattern into `Registrar\StudentController` or `Registrar\EnrollmentController`.

### `grades.faculty_id` Column — Nullable as of Phase 13
```php
// Migration (2026_02_15_144012_create_grades_table.php) — original definition:
$table->foreignId('faculty_id')->constrained('users')->comment('Faculty who encoded the grade');

// Phase 13 migration (make_faculty_id_nullable_on_grades_table) applied on top:
// DB::statement('ALTER TABLE grades MODIFY faculty_id BIGINT UNSIGNED NULL');
```
**Critical:** When Registrar encodes a grade directly, `faculty_id` must be set to `null` — never to the Registrar's own `auth()->id()`. The column is semantically reserved for Faculty.

### GWA Formula
Semester GWA   = Σ(grade × units) / Σ(units)
Cumulative GWA = Σ(all grades × units) / Σ(all units) — all finalized semesters

### TOR Semester Label Format
{semester_name} — SY {year_code}
e.g. "2nd Semester — SY 2025-2026"
### Column Name Quick Reference
| Table | Correct | Wrong |
|-------|---------|-------|
| subjects | `code` | `subject_code` |
| students | `student_number` | `student_id` |
| grade_submissions.hod_action | `approved_by_head_of_department` | `approved` |
| grades.status | `pending_head_of_department_review` | `pending` |
| school_years | `year_code` | `year_start`, `year_end` |
| semesters | `semester_name`, `semester_order` | `name` |
| Storage facade | `Storage::` (with import) | `\Storage::` |
| grades.faculty_id | nullable as of Phase 13 (Registrar direct-entry) | never the Registrar's own `auth()->id()` |
| subjects.semester | full string: `1st Semester`, `2nd Semester`, `Summer` (Phase 13.9 fix) | abbreviated: `1st`, `2nd` |

### Storage Notes
- COG: `storage/app/cog/{document_number}.pdf`
- TOR: `storage/app/tor/{document_number}.pdf`
- Backups: `storage/app/cog-tor-backup/*.zip`
- Use `->output()` not `->download()` on DomPDF

### Windows / Git Bash Notes
- Interactive artisan commands: prefix with `winpty`
- Permission errors on bootstrap/cache: `chmod -R 777 bootstrap/cache storage`
- Complex tinker chains fail on Git Bash — use simple single expressions
- `php artisan tinker --execute` with backslashes fails — use DB:: facade directly
- sed commands mangle unicode emojis — use line-number targeting `sed -i 'Ns/.*/replacement/'`
- `!` in bash strings triggers history expansion — use base64_decode() approach for PHP file writes
- Git Bash blocks pipe `|` to PHP in some contexts — use PHP file approach instead
- **NEW (Phase 13):** `php artisan route:list > file.txt` can produce a genuinely empty file in Git Bash/MINGW (`stdout is not a tty`) — confirmed via `wc -l` returning 0. Use `--path=` filtered `route:list -v` printed directly to terminal instead of redirecting to a file for grep-based checks.
- **NEW (Phase 13):** heredoc file-creation commands (`cat > file << 'EOF' ... EOF`) can be silently interrupted by `Ctrl+C` mid-paste, especially after a long preceding command — always verify the target file was actually created (`ls -la`, `php -l`) immediately after, don't assume success.
- **NEW (Phase 13):** doctrine/dbal is still required in Laravel 10.x (not just <10) for `Schema::table()->change()` — only Laravel 11+ drops this requirement. For single-column nullability changes on MySQL, a raw `DB::statement('ALTER TABLE ... MODIFY ...')` inside a migration avoids adding the dependency.
- **NEW (Phase 13):** `php artisan tinker --execute` still fails on `App\Models\X` namespace backslashes on Git Bash even when quoted — use `DB::table(...)` instead of Eloquent model class references inside `--execute` strings.
- **NEW (Phase 13):** `SESSION_DRIVER=file` (the default) means there is no `sessions` database table — attempting `DB::table('sessions')` will throw "table doesn't exist." This is expected, not a bug; only relevant if `SESSION_DRIVER=database` is explicitly configured.

---

## LESSONS LEARNED

### Phase 4–8: (unchanged — see prior entries 1–22)

### Phase 9:
23. MySQL ENUM changes: expand first → update data → lock
24. Migrations that ran as empty stubs need manual DB::statement fix
25. Git Bash swallows tinker output — use print_r() wrapper
26. Backslashes in tinker --execute fail on Git Bash — use DB:: facade
27. Role route groups must be top-level in web.php
28. Permission::firstOrCreate prevents crash when re-seeding
29. Emojis render as broken diamonds on Windows — use Font Awesome
30. `cat > file << 'BLADE'` is the safest way to overwrite a view in Git Bash
31. GradeSubmission is 1:1 with Grade — group by subject_id at controller level
32. Always check $students->isNotEmpty() not just $students
33. `{{ $var ?? '<span>fallback</span>' }}` renders HTML as escaped text — use `@if`
34. school_years ENUM is `upcoming/active/completed` — `inactive` causes truncation error
35. semesters ENUM is same — `inactive` not valid
36. `year_start`/`year_end` columns don't exist — actual column is `year_code`
37. `name` column doesn't exist on semesters — use `semester_name` and `semester_order`
38. Intelephense P1013 `withQueryString()` warning is a false positive
39. `$request->all()` on update passes `_token` and `_method` — always use explicit field list
40. Tailwind CDN doesn't compile dynamic classes — use inline styles for critical colors
41. `$fillable` missing `department_id` causes silent data loss on User create/update
42. `scopeApproved()` ENUM mismatch causes entire Registrar queue to return empty silently
43. Arrow functions inside Blade @json() inside HTML attributes cause PHP ParseError
44. `cat` command output lowercases JS — JavaScript is case-sensitive
45. Semesters use `semester_name` not `name` — always eager load `semester.schoolYear` for TOR
46. `firstOrCreate` never updates existing records — use `updateOrCreate` in seeders
47. `department_id` null on HoD causes `whereHas` scoping to silently return zero results
48. Excel template with hardcoded student numbers matching seeder causes unique validation failures
49. `nullable|date` accepts any PHP-parseable format silently — use `nullable|date_format:Y-m-d`
50. sed on Git Bash mangles multi-byte unicode — use line-number targeting for emoji replacement
51. `@push('scripts')` has no effect without `@stack('scripts')` in the layout
52. implode(' / ', $courseCodes) in template puts all codes in one cell — use `->value('code')`

### Phase 12:
53. spatie/laravel-backup v3+ moved `dump_binary_path` from `config/backup.php` to `config/database.php` under the MySQL connection block
54. XAMPP mysqldump is at `C:/xampp/mysql/bin/` — not in system PATH by default
55. Commenting out `notifiable` key in backup.php causes TypeError — keep the class, disable channels via `'via' => []`
56. `!` inside bash strings (even escaped) triggers history expansion in Git Bash — always write PHP files via base64_decode() for complex content
57. The real sidebar file is `resources/views/layouts/partials/sidebar.blade.php` — not `navigation.blade.php`
58. "Sending notification failed" on backup:run is harmless when no mail driver is configured

### Phase 13:
59. `foreignId()->constrained()` with no `->nullable()` chained is NOT NULL by default — verify with `SHOW COLUMNS FROM table` before assuming a column accepts null
60. Laravel 10.x still requires `doctrine/dbal` for `Schema::table()->change()` — only Laravel 11+ drops it. Use raw `DB::statement('ALTER TABLE ... MODIFY ...')` to avoid the dependency for simple nullability changes
61. Two parallel role-check systems can coexist silently (Spatie `HasRoles` pivot tables + a legacy `role` column with helper methods like `isFaculty()`) — always grep for both before assuming a role check is fully covered by one system
62. Route groups scoped per-role with their own `prefix()`/`name()` are naturally collision-safe even if two roles have identically-named sub-resources (e.g. both HoD and Registrar can have `students.index` — they resolve to different fully-qualified route names)
63. Department-scoped query patterns (`whereHas('course', fn($q) => $q->where('department_id', ...))`) must NOT be copy-pasted into institution-wide controllers — always confirm scope requirements before reusing a "similar" controller as a template
64. `php artisan route:list > file.txt` can produce a genuinely empty file in Git Bash/MINGW despite the command appearing to succeed — verify redirected output with `wc -l`, or avoid redirection and use `--path=` filtering printed directly to terminal instead
65. Heredoc commands (`cat > file << 'EOF'`) run immediately after an unrelated `Ctrl+C` can silently fail to execute — the terminal returns to a fresh prompt looking exactly like success; always verify file creation explicitly (`ls -la` + `php -l`), never assume from a clean-looking prompt alone
66. When multiple files are pasted back-to-back in one batch, content can end up saved under the wrong filename/path (e.g. a "students create" form saved as `enrollments/create.blade.php`) — verify actual file contents with `head`/`cat`, not just that a file with the expected name exists
67. Reusable Export/Import classes that already accept a nullable scoping parameter (e.g. `new StudentsExport(null)`) can often be reused as-is across roles with different scoping needs — check constructor signatures before assuming a new class needs to be written
68. A `<select>` dropdown's `value="..."` attribute and a foreign/related table's comparison field must use **identical strings**, not just visually-similar labels — `<option value="1st">1st Semester</option>` looks correct in the UI but silently breaks any backend query comparing against the full `"1st Semester"` string
69. Editing one field in an Admin form (e.g. assigning a faculty member to a subject) can silently corrupt an unrelated field if that form submits ALL fields on every save, including ones the user didn't intend to touch — worth checking whether "quick edit" actions should use a scoped update instead of the full form submit
70. When a bug is traced to a data problem, always check whether the **seeder** that originally created the data has the same root-cause bug — patching only the live database data leaves a fresh `migrate:fresh --seed` (or a client's first setup) reproducing the exact same issue
71. `SESSION_DRIVER=file` (Laravel's default) means there's no `sessions` table in the database — querying `DB::table('sessions')` will throw "table doesn't exist," which is expected behavior, not a bug, when using the file driver
72. Wrapping existing sequential code in `DB::transaction(function () use (...) { ... })` requires re-tracing every variable the old code produced — anything declared inside the closure is scoped to it and will NOT be visible on lines after the closure closes; pull needed values off the closure's return value instead
80. A helper script's own directory (`dirname(__DIR__)`, `__DIR__`) only reflects the *actual* project structure if the script stays inside the project — copying a "portable" script to a different physical location (e.g. XAMPP's `htdocs`) breaks any path logic based on the script's own location; for scripts that must be deployed outside the project, read the real project path from a small external config file instead of computing it from `__DIR__`
81. Machine-specific paths (absolute install locations, developer usernames in file paths) should never be hardcoded directly into a script meant to ship with a repo — use a git-ignored config file with a committed `.example` template instead, so each machine/client sets their own value without risking silently inheriting another developer's path
82. When hard-deleting a seeded test account, check every FK column pointing at `users.id` first — not just the obvious ones — a seeder can wire one test account's `approved_by` to another test account's id, causing a delete order dependency that isn't visible from the schema alone
83. Laravel's `firstOrCreate()` returns a single model, not a `[model, wasCreated]` tuple — destructuring it (`[$a, $b] = firstOrCreate(...)`) silently mis-assigns both variables without throwing, so a "successful create" branch can look identical to a "duplicate found" branch at runtime. Use an explicit `->exists()` check before `create()` instead when the created/not-created distinction matters.
84. A spreadsheet template can validly contain more than one sheet (e.g. hidden lookup sheets backing dropdown data validation) — `ToCollection`'s `collection()` runs once per sheet automatically, so a "was this sheet actually filled in for import" check needs to test for the *expected data* being present, not just "is every cell blank," or lookup/reference sheets get misread as failed submissions.
85. Dynamically-generated modal/report HTML (e.g. built as a JS string for SweetAlert2) can use a scoped `<style>` block injected inline with the markup for real CSS classes — this avoids both the Tailwind-CDN dynamic-class limitation (Lesson #40) and the maintainability cost of inline `style="..."` on every element.
86. `->withInput([...])` on a redirect only repopulates fields a view explicitly checks for via `old('field')` — adding the controller-side call alone does nothing without a matching `old('field') == ... ? 'selected' : ''` (or similar) check in the Blade template.
87. Grouping a result set (`Collection::groupBy()`) and paginating it don't compose — grouping requires the full filtered set in memory. When a UI offers both, treat them as mutually exclusive display modes rather than trying to paginate within groups.
88. `!important` inside a bash/sed command run interactively in Git Bash triggers history expansion (same root cause as Lesson #56's `!` issue) and silently aborts the *entire* multi-flag `sed` command, not just the affected `-e` clause — run `set +H` once per session before any sed/bash one-liner containing `!important` or other `!`-prefixed content
73. A crash immediately after a transaction closure causes Laravel to roll the whole transaction back — this can make a genuinely-fixed bug look untested/unconfirmed in logs, because the crash prevents the request from ever completing far enough to prove the earlier fix worked
74. Editor diagnostics reported against files under `storage/framework/views/*` (or any compiled/generated directory) are not your source code — compiled Blade cache files use hash filenames and regenerate on every `view:clear`; exclude these paths from the editor's file watcher rather than debugging them directly
75. Commenting out a package config key "to be safe" can be worse than deleting it — an absent `notifiable` config value silently resolves to the service container itself via `app(null)`, producing a confusing TypeError far from the actual missing setting
76. `php artisan serve` on Windows can prevent child processes (including ones spawned by packages like Spatie Backup, via `mysqldump`) from successfully opening their own TCP sockets (`WSAEPROVIDERFAILEDINIT` / error 10106) — this is tied to the dev server holding its own listening socket in-process, not to the command being run, the shell, antivirus, or Windows networking in general; the same command succeeds identically via CLI or through Apache
77. When ruling out a suspected cause, prefer a test that actually exercises the failing behavior (e.g. a real network connection attempt) over one that merely proves related capability (`mysqldump --version` proves the binary runs, but never opens a socket — it cannot reproduce a connection-layer bug)
78. XAMPP's bundled Apache PHP version can differ from a project's actual required PHP version (e.g. XAMPP's 8.2 vs. project's required 8.4) — running a Laravel app through Apache may require pointing Apache at the correct PHP install (FastCGI/PHP-FPM) rather than assuming XAMPP's bundled PHP is sufficient; a standalone PHP script with no Composer/Laravel bootstrap is a fast way to test Apache-level behavior without hitting this mismatch
79. Defining a single active `<VirtualHost *:80>` in `httpd-vhosts.conf` makes it the default handler for ALL requests on that port, including plain `localhost` — add an explicit `ServerName localhost` vhost pointing at the normal `htdocs` root to avoid unrelated requests being silently captured by a project-specific vhost
89. A CLI flag hardcoded into a package's config (e.g. `mysqldump`'s `--host=...` via Spatie's `add_extra_option`) takes precedence over the same setting configured through `.env`/`DB_HOST` — always grep the whole `config/` directory for the literal value you're debugging, not just the obvious connection array, when a `.env` change appears to have no effect
90. NTFS folder permissions can allow a process to serve a web request from a folder while still blocking that same process from recursively reading the folder's contents — a "the site loads fine" result doesn't rule out a permissions problem for background file-processing tasks run by the same server
91. When a backup script accepts a flag like `--only-db`, a small output size is by design, not a bug — always check the actual command and flags being run before assuming a small output file indicates failure
92. The same underlying fix (e.g. an Apache-triggered backup workaround) can require different follow-up fixes on different machines — a hardcoded DB host flag, vhost ordering, and folder permissions can each independently block the fix, and each fails with a different, seemingly unrelated symptom

---

## PROGRESS SUMMARY

| Phase | Status | Completion | Notes |
|-------|--------|------------|-------|
| Phase 1: Foundation | ✅ Complete | 100% | — |
| Phase 2: Models & Seeders | ✅ Complete | 100% | updateOrCreate in seeder; SubjectSeeder fixed Phase 13.9 |
| Phase 3: Auth & Authorization | ✅ Complete | 100% | — |
| Phase 4: Admin Module | ✅ Complete | 100% | Font Awesome icons, role badge formatting; Subject form semester bug fixed Phase 13.9 |
| Phase 5: Faculty Module | ✅ Complete | 100% | Slated for route lockout in Phase 13.6 |
| Phase 6: HoD Module | ✅ Complete | 100% | Grade review + assignments slated for lockout; student/enrollment mgmt being absorbed by Registrar |
| Phase 7: Registrar Module | ✅ Complete | 100% | Significantly extended in Phase 13; COG/TOR records tab flagged as open request (13.10) |
| Phase 8: Excel Features | ✅ Complete | 100% | `null`-scoping reused by Registrar in Phase 13 |
| Phase 9: System Restructure | ✅ Complete | 99% | Old E2E test superseded by Phase 13 flow change |
| Phase 10: Reporting & Analytics | 📅 Planned | 0% | After Phase 11 |
| Phase 11: UI/UX & Testing | 🔄 In Progress | 40% | Blocked pending Phase 13 completion |
| Phase 12: Backup & Restore | ✅ Complete | 100% | spatie/laravel-backup, Admin UI |
| **Phase 13: Registrar-Only Workflow Migration** | 🔄 **In Progress** | **~88%** | **Registrar module built + statically verified. Admin role scope confirmed. Subject semester bug fixed. Faculty/HoD lockout done. Masterlist import validation + report UI done. Enrollment bug fixes + filters done. Browser E2E pending.** |
| Phase 14: Curriculum Feature | 📅 Planned | 0% | Renumbered from old Phase 13; will also correct provisional semester placeholders from 13.9 |

**Overall Project Completion: ~97%** *(dipped slightly from 99% due to Phase 13 scope insertion — reflects real remaining work, not regression)*

---

## NEXT STEPS — RESUME HERE
▶️ Priority 0: Phase 13.11 — Finish COG/TOR Duplicate-Record Fix Verification
Paste back `php -l` result for the TOR patch, then live-retest both COG and TOR generation for Sofia (semester with the pre-existing duplicate) — confirm both log lines appear and `/registrar/documents` shows one "Current" per type. Only after that: write the cleanup script for Sofia's existing duplicate COG rows, then apply the DB-level uniqueness constraint migration.

✅ Phase 13.13 — Masterlist Import Validation & Report UI — DONE (see above)
✅ Phase 13.14 — Enrollment Bug Fixes & Filters — DONE (see above)

▶️ Priority 1: Phase 13.8 — Complete Browser End-to-End Test
Use the Admin + Registrar manual test checklists (July 2 session) to run a full pass:

Admin: Subjects CRUD, School Years/Semesters, Departments/Courses, Users, Backup & Restore
Registrar: Students CRUD, Enrollment, Excel template/export, Encode Grades wizard
— including the BSIT + 1st Semester regression test (confirms Phase 13.9 fix holds)
Note exact step + error message for anything that fails

✅ Phase 13.6 — Faculty/HoD Route Lockout — DONE (July 10 session, see above)

▶️ Priority 3: 🔔 COG/TOR Records Tracking Tab (Phase 13.10 — new, flagged this session)

Scope a new Registrar (and possibly Admin) tab listing all generated cog_records/tor_records
Define columns/filters needed (student, date, semester, school year, re-download link)
Not yet built — discuss scope before implementation

▶️ Priority 4: Phase 11 remaining (post-13)

New E2E test plan reflecting single-actor flow (replaces old 12-step multi-role test)
Mobile responsiveness
Empty states, 404, form error UX
Loading states for PDF generation
UI consistency pass
Remove leftover student nav links from Admin dashboard
Remove/hide Faculty + HoD sidebar sections once 13.6 lockout applied

▶️ Priority 5: Phase 10 — Reporting & Analytics (unchanged, still planned)
▶️ Priority 6: Phase 14 — Curriculum Feature (renumbered, still planned)

Curricula table + curriculum_subjects table
Admin UI to build/manage curriculum per course per year
Link enrollments/grades to curriculum subjects
Update COG generation to pull from curriculum
Follow CHED/SUC standard (same pattern as SAIS, AISIS)
Revisit and correct provisional subject semester placeholders from Phase 13.9 against real curriculum


---

**Last Updated:** July 22, 2026
**Phase 13 Status:** 🔄 In Progress (~89%)
**Current Focus:** Phase 13.16 Masterlist relocation + Encode Grades history UX (done) → Phase 11.4 Brand Identity/UI Rebrand (in progress) → Phase 13.8 Full Browser E2E Test → 13.10 COG/TOR Records Tab → Phase 10 Reporting → Phase 14 Curriculum
