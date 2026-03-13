# CHANGELOG - COG-TOR System

## Project Information
**System Name:** Academic Grading Management System (COG-TOR)
**Tech Stack:** Laravel 10 LTS + MySQL + Blade + Tailwind CSS
**PHP Version:** 8.4.11
**Node Version:** 18.20.8
**Started:** February 15, 2026

---

## ⚡ RESUME POINT — READ THIS FIRST

**Current Phase:** Phase 9 — System Restructure
**Status:** 🔄 In Progress — Steps 1–41 done + March 12 session fixes, continue from Step 42

### What Changed From Original Plan (v1 → v2)
| Area | v1 (Old) | v2 (New) | Why |
|------|----------|----------|-----|
| Student management | Admin owned it | Head of Department owns it (department-scoped) | Correct role boundary |
| Grade approval | One grade at a time | Bulk per faculty submission | Realistic at scale |
| Head of Department scope | System-wide | Per department via `department_id` | Multi-department support |
| Faculty rejection | No resubmit flow | Reject → Faculty corrects → Resubmit with remarks | Complete workflow |
| Registrar document flow | Browse student list | Search → Academic Profile | Scales to 500+ students |
| TOR generation | Semester selector (wrong) | Always full record (CHED standard) | Academically correct |
| Grade status ENUM | 3 values | 5 values | Covers full workflow |
| SweetAlert | Missing | All destructive actions | UX safety |
| Registrar finalization | 1-by-1 per grade | Bulk per subject with preview modal | Realistic at scale |

### Phase Renumbering
```
Phase 1–8   Unchanged
Phase 9     NEW — System Restructure (insert here)
Phase 10    Was Phase 9 — Reporting & Analytics
Phase 11    Was Phase 10 — UI/UX & Testing (30% work carries over)
```

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
- [x] Role helpers: isAdmin(), isFaculty(), isHead of Department(), isRegistrar()
- [x] Status helpers: isActive(), isPending(), isInactive()
- [x] Query scopes for filtering
- [x] `department()` belongsTo relationship added ✅ (added March 12 session)
- [x] `department_id` added to `$fillable` ✅ (bug fix — March 12 session)

### 2.3 Database Seeders Created
- [x] RoleSeeder — 4 roles with 23 permissions
- [x] UserSeeder — 5 test accounts
- [x] AcademicStructureSeeder — School years, semesters, departments, courses
- [x] SubjectSeeder — 10 sample subjects
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
- [x] Head of Department → `/head of department/dashboard`
- [x] Faculty → `/faculty/dashboard`
- [x] Registrar → `/registrar/dashboard`

### 3.3 Route Groups
- [x] Admin, Head of Department, Faculty, Registrar route groups with middleware

### 3.4 Dashboard Controllers & Views
- [x] AdminController, Head of DepartmentController, FacultyController, RegistrarController
- [x] All 4 dashboard views

**Deliverables:**
- ✅ 2 custom middleware (CheckRole, CheckStatus)
- ✅ Role-based login redirects
- ✅ 4 dashboard controllers and views

---

## PHASE 4: ADMIN MODULE ✅ COMPLETED
**Date:** February 15–21, 2026
**Status:** ✅ Complete (100%)

> ✅ PHASE 9.3 DONE: Admin StudentController and student Excel routes have been REMOVED.

### 4.1–4.9 Completed Features
- [x] UserController — full CRUD + approve/reject
- [x] DepartmentController — full CRUD
- [x] CourseController — full CRUD
- [x] SubjectController — full CRUD
- [x] SchoolYearController — full CRUD + setActive
- [x] SemesterController — full CRUD + setActive
- [x] ~~StudentController~~ — DELETED in Phase 9.3 ✅
- [x] ~~Admin ExcelController~~ — DELETED in Phase 9.3 ✅
- [x] Dashboard redirect bug fixed
- [x] Back buttons on all views
- [x] Admin student routes removed from web.php ✅

**Deliverables:**
- ✅ Full Admin CRUD for academic structure only
- ✅ Student management removed — now owned by Head of Department
- ✅ Routes cleaned — no admin.students.* routes remain

> ⚠️ NOTE: Admin dashboard view still has leftover student nav links — cleanup deferred to Phase 11.

---

## PHASE 5: FACULTY MODULE ✅ COMPLETED
**Date:** February 21, 2026
**Status:** ✅ Complete (100%)

### 5.1 Controllers
- [x] FacultyController — dashboard and subjects list
- [x] GradeController — index, store, edit, update, submit, resubmit ✅ (resubmit added Phase 9)

### 5.2 Views
- [x] faculty/dashboard.blade.php
- [x] faculty/subjects.blade.php — updated Phase 9: status badges, rejection remarks, resubmit button ✅
- [x] faculty/grades/index.blade.php — updated Phase 9: locks on submit, rejection banner, resubmit modal ✅
- [x] faculty/grades/edit.blade.php

### 5.3 Routes
- [x] faculty.dashboard, faculty.subjects
- [x] faculty.subjects.grades (GET/POST)
- [x] faculty.subjects.grades.edit, .update
- [x] faculty.subjects.grades.submit
- [x] faculty.subjects.grades.resubmit ✅ (added Phase 9)

**Deliverables:**
- ✅ Grade encoding with PH scale conversion
- ✅ Batch grade submission to Head of Department
- ✅ Resubmit flow: rejection banner + remarks + resubmit modal
- ✅ Grade table locks on submit, unlocks on rejection

---

## PHASE 6: HEAD OF DEPARTMENT MODULE ✅ COMPLETED
**Date:** February 21–22, 2026
**Status:** ✅ Complete (100%)

### 6.1 Dashboard
- [x] 4 stat cards — Total Students (department-scoped), Active Enrollments, Pending Grades, Approved Grades ✅
- [x] All 4 stats now fully department-scoped ✅ (bug fix — March 12 session)
- [x] Pending submissions table — grouped by subject, "Review All" per row ✅
- [x] Navigation updated — Dashboard + Students + Enrollment + Faculty Assignment + Grade Submissions ✅

### 6.2 Student Management
- [x] Head of Department/StudentController — department-scoped CRUD ✅
- [x] Head of Department/ExcelController — import/export scoped to department ✅
- [x] head of department/students/index.blade.php ✅
- [x] head of department/students/create.blade.php ✅
- [x] head of department/students/edit.blade.php ✅

### 6.3 Grade Review & Approval (Bulk)
- [x] review() — loads all submissions for same subject at once ✅
- [x] approve() — bulk updates all grades in subject to approved_by_head of department ✅
- [x] reject() — bulk updates all grades to rejected, saves head of department_remarks ✅
- [x] head of department/review.blade.php — full class table, Approve All + Reject buttons ✅
- [x] Resubmitted submissions flagged with Faculty remarks visible ✅

### 6.4 Enrollment Management ✅
- [x] Head of Department/EnrollmentController — index, store, destroy
- [x] Enroll students into subjects for active semester only
- [x] Remove enrollment blocked if grade exists
- [x] head of department/enrollments/index.blade.php
- [x] Routes: head of department.enrollments.index, head of department.enrollments.store, head of department.enrollments.destroy

### 6.5 Faculty Assignment ✅
- [x] Head of Department/SubjectAssignmentController — index, update
- [x] Assign/unassign faculty to subjects scoped to Head of Department's department
- [x] Subjects grouped by course in table view
- [x] head of department/assignments/index.blade.php
- [x] Routes: head of department.assignments.index, head of department.assignments.update
- [x] Faculty dropdown scoped to department_id ✅

**Deliverables:**
- ✅ Department-scoped student management with Excel import/export
- ✅ Bulk grade approval/rejection
- ✅ Correct ENUM status values: approved_by_head of department, rejected
- ✅ Enrollment management module
- ✅ Faculty assignment module with department scoping
- ✅ All dashboard stats fully department-scoped (fixed March 12 session)

---

## PHASE 7: REGISTRAR MODULE ✅ COMPLETED
**Date:** February 22, 2026
**Status:** ✅ Complete (100%)

### 7.1 Controllers
- [x] RegistrarController — index (student search + paginated list), finalize, finalizeSubject ✅
- [x] DocumentController — studentProfile(), cogForm, generateCog, torForm, generateTor, downloadCog, downloadTor ✅

### 7.2 Routes
- [x] registrar.dashboard, registrar.submissions.finalize
- [x] registrar.submissions.finalize-subject ✅ (added March 12 session — bulk finalize)
- [x] registrar.students.profile ✅
- [x] registrar.students.cog, .cog.generate
- [x] registrar.students.tor, .tor.generate
- [x] registrar.cog.download, registrar.tor.download
- [x] Old registrar.students browse route — REMOVED ✅

### 7.3 Views
- [x] registrar/dashboard.blade.php — full rewrite: 2 tabs (Finalization Queue + Generate COG/TOR), bulk finalize per subject, preview modal, SweetAlert2 confirm ✅ (March 12 session)
- [x] registrar/student-profile.blade.php — duplicate Download TOR button removed ✅ (March 12 session)
- [x] registrar/pdf/cog.blade.php, registrar/pdf/tor.blade.php

### 7.4 Key Notes
- Institution name: `Eastern Samar State University - Guiuan Campus`
- Use `Storage::` facade always — never `\Storage::`
- DomPDF: use `->output()` not `->download()` to avoid .tmp file creation
- TOR: always full cumulative record — no semester selector (CHED standard)
- TOR semester label: `semester_name — SY year_code` (e.g. "2nd Semester — SY 2025-2026")
- GWA: Σ(grade × units) / Σ(units) — weighted, not simple average

**Deliverables:**
- ✅ Search-first student flow on Registrar dashboard
- ✅ Academic Profile page with grouped grades, GWA, COG/TOR generation
- ✅ Full COG/TOR PDF generation
- ✅ Bulk finalize per subject (not 1-by-1)
- ✅ Preview modal shows all students + grades before finalizing
- ✅ SweetAlert2 confirm on Finalize All
- ✅ TOR now shows correct semester name + school year (fixed March 12 session)

---

## PHASE 8: EXCEL FEATURES ✅ COMPLETED
**Date:** February 26, 2026
**Status:** ✅ Complete (100%)

### 8.1 Files — Current State
- [x] app/Exports/StudentsExport.php — updated with department_id scoping ✅
- [x] app/Imports/StudentsImport.php — updated with department_id scoping ✅
- [x] app/Exports/GradeTemplateExport.php — unchanged ✅
- [x] app/Imports/GradesImport.php — unchanged ✅
- [x] ~~app/Http/Controllers/Admin/ExcelController.php~~ — DELETED Phase 9.3 ✅
- [x] app/Http/Controllers/Faculty/ExcelController.php — unchanged ✅
- [x] app/Http/Controllers/Head of Department/ExcelController.php — CREATED Phase 9.4 ✅

### 8.2 Head of Department Excel
- [x] studentTemplate() — CSV template with department-scoped course codes
- [x] importStudents() — validates course belongs to Head of Department's department
- [x] exportStudents() — scoped to Head of Department's department_id

### 8.3 Faculty Excel (UNCHANGED)
- [x] downloadTemplate(), uploadGrades()
- [x] 2 faculty Excel routes

**Deliverables:**
- ✅ StudentsExport/Import department-scoped
- ✅ Head of Department ExcelController built
- ✅ Admin Excel student methods gone

---

## PHASE 9: SYSTEM RESTRUCTURE 🔄 IN PROGRESS
**Date:** March 2026
**Status:** 🔄 In Progress (~98%)

---

### 9.1 — Database Migrations ✅ DONE
- [x] Migration: `add_department_id_to_users_table` — `department_id BIGINT UNSIGNED NULL`, FK → departments.id
- [x] Migration: `add_columns_to_grade_submissions_table` — faculty_remarks (text, nullable), resubmission_count (int, default 0)
- [x] Migration: `update_grade_status_enum` — grades.status locked to 5 values, head of department_action ENUM locked to approved_by_head of department/rejected
- [x] All 24 migrations green

> ⚠️ LESSON: MySQL ENUM changes = expand first → update data → lock. Can't write a value not in the current ENUM.

---

### 9.2 — Seeder Updates ✅ DONE
- [x] RoleSeeder — firstOrCreate throughout, 3 new Head of Department permissions (import students, export students, resubmit grades)
- [x] UserSeeder — firstOrCreate, Head of Department assigned department_id = 1
- [x] Verified via tinker

---

### 9.3 — Admin Module Cleanup ✅ DONE
- [x] DELETED Admin/StudentController.php
- [x] DELETED Admin/ExcelController.php
- [x] REMOVED admin.students.* + Excel routes from web.php
- [x] web.php brace bug fixed — all role groups top-level
- [x] Verified: no admin.students.* in route:list

> ⚠️ NOTE: Admin dashboard view still has leftover student nav links — cleanup pending Phase 11.

---

### 9.4 — Head of Department Module Restructure ✅ DONE
- [x] Head of Department/StudentController.php — department-scoped CRUD, destroy blocked if enrollments exist
- [x] Head of Department/ExcelController.php — template, import, export
- [x] Head of Department student + Excel routes registered
- [x] StudentsExport.php + StudentsImport.php — department_id scoping added
- [x] head of department/students/index.blade.php — paginated list, search, course/year/status filters, import/export/add buttons
- [x] head of department/students/create.blade.php — creation form, course dropdown filtered to department
- [x] head of department/students/edit.blade.php — edit form
- [x] head of department/review.blade.php — bulk class table, Approve All + Reject with remarks
- [x] Head of DepartmentController approve() — bulk, writes approved_by_head of department
- [x] Head of DepartmentController reject() — bulk, writes rejected, saves head of department_remarks
- [x] Head of DepartmentController index() — all 4 stats department-scoped ✅ (fixed March 12 session)
- [x] Navigation updated — full Head of Department sidebar built

---

### 9.5 — Faculty Module Update ✅ DONE
- [x] GradeController store() — status: saved
- [x] GradeController submit() — status: pending_head of department_review
- [x] GradeController resubmit() — validates rejected state, saves faculty_remarks, increments resubmission_count, resets head of department_action, sets pending_head of department_review
- [x] faculty/grades/index.blade.php — locks on submit, red rejection banner + Head of Department remarks, resubmit modal with faculty_remarks field
- [x] faculty/subjects.blade.php — status badge per subject card, rejection remarks on card, "Update & Resubmit" button
- [x] Resubmit route registered

---

### 9.6 — Registrar Module Rework ✅ DONE
- [x] RegistrarController index() — student search by name/student_number, all students by default, filtered on search, paginated (15/page)
- [x] RegistrarController finalizeSubject() — bulk finalize all grades for a subject at once ✅ (added March 12 session)
- [x] DocumentController studentProfile() — finalized enrollments grouped by school year → semester, semester GWA + cumulative GWA, existing COG/TOR record checks
- [x] DocumentController generateTor() — semester label fixed to `semester_name — SY year_code`, schoolYear eager loaded ✅ (fixed March 12 session)
- [x] registrar/dashboard.blade.php — full rewrite: 2 tabs (Finalization Queue + Generate COG/TOR), bulk finalize per subject row, preview modal with student grades, SweetAlert2 confirm ✅ (March 12 session)
- [x] registrar/student-profile.blade.php — duplicate Download TOR button removed ✅ (March 12 session)
- [x] routes/web.php — `registrar.submissions.finalize-subject` route added ✅ (March 12 session)

---

### 9.7 — Admin User Management Overhaul ✅ DONE
- [x] UserController — full replacement with role tabs, search, department_id saving
- [x] admin/users/index.blade.php — tabs, search, department column, status badges
- [x] admin/users/create.blade.php — department dropdown JS toggle for Faculty/Head of Department
- [x] admin/users/edit.blade.php — same toggle, optional password
- [x] User model — `department()` belongsTo added + `department_id` added to `$fillable` ✅ (critical bug fix — March 12 session)
- [x] SubjectAssignmentController — faculty dropdown scoped by department_id

---

### 9.8 — Academic Calendar Bug Fixes ✅ DONE
- [x] SchoolYearController — explicit field list, `inactive` → `completed` everywhere
- [x] SemesterController — semester_order/semester_name, `inactive` → `completed`
- [x] All views updated — year_code, semester_name, status badges
- [x] Existing DB records patched via tinker
- [x] Subject index faculty HTML escape bug fixed

---

### 9.9 — GradeSubmission Model Fix ✅ DONE (March 12 session)
- [x] `scopeApproved()` — fixed `'approved'` → `'approved_by_head of department'`
- [x] `isApproved()` — fixed `'approved'` → `'approved_by_head of department'`
- [x] Root cause: ENUM value mismatch caused Registrar pending queue to always show 0

---

### 9.10 — SweetAlert2 ✅ PARTIALLY DONE
- [x] SweetAlert2 CDN confirmed in app.blade.php
- [x] Registrar: Finalize All — SweetAlert2 confirm modal implemented ✅ (March 12 session)
- [ ] Head of Department: Approve All
- [ ] Head of Department: Reject submission
- [ ] Faculty: Submit to Head of Department
- [ ] Faculty: Resubmit to Head of Department
- [ ] Registrar: Generate TOR

---

### 9.11 — Verification & End-to-End Test ⏳ PENDING
- [ ] php artisan route:list — full clean verification
- [ ] php artisan migrate:status — all green
- [ ] Full 12-step end-to-end test

**Phase 9 Deliverables:**
- [x] 3 new migrations run successfully
- [x] Head of Department has department_id in seeder
- [x] Admin student controller and routes deleted
- [x] Head of Department/StudentController with department scoping
- [x] Head of Department/ExcelController with import/export
- [x] Head of Department student views (index, create, edit)
- [x] Head of Department bulk approval UI (Approve All + Reject with remarks)
- [x] Faculty resubmit flow with remarks (GradeController + views)
- [x] Registrar search-first + Academic Profile
- [x] Registrar dashboard full rewrite — 2 tabs, bulk finalize, preview modal ✅
- [x] Registrar bulk finalizeSubject() + route ✅
- [x] Font Awesome 6.5 added to app.blade.php
- [x] Admin User Management overhaul — tabs, search, department column
- [x] User.$fillable — department_id added (critical bug fix) ✅
- [x] GradeSubmission scopeApproved/isApproved fixed ✅
- [x] Head of Department dashboard all stats department-scoped ✅
- [x] TOR semester label fixed (semester_name + school year) ✅
- [x] Head of Department Enrollment module built
- [x] Head of Department Faculty Assignment module built
- [x] School year ENUM and column name bugs fixed
- [x] Semester ENUM and column name bugs fixed
- [x] Subject index faculty column HTML escape bug fixed
- [x] Registrar Finalize All — SweetAlert2 confirm ✅
- [ ] SweetAlert2 on remaining 5 destructive actions
- [ ] Full end-to-end test passing

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
**Status:** 🔄 In Progress (~30%)

### 11.1 Login Page — Complete Redesign ✅ DONE
- [x] Root route `/` redirects to login or role dashboard
- [x] Split-screen layout (dark navy + cream)
- [x] Custom SVG document illustration
- [x] Playfair Display + DM Sans fonts
- [x] Font Awesome icons + password toggle
- [x] All Breeze auth logic preserved

### 11.2 All Role Dashboards — Overhaul ✅ DONE
- [x] Admin dashboard — 6 stat cards, full nav grid
- [x] Head of Department dashboard — 4 stat cards, pending submissions table, full sidebar nav
- [x] Faculty dashboard — stats, quick subject links, status badges, rejection flow
- [x] Registrar dashboard — 2-tab redesign, finalization queue + student directory, preview modal

### 11.3 Remaining Tasks (post-Phase 9)
- [ ] SweetAlert2 on remaining 5 destructive actions (Phase 9.10)
- [ ] End-to-end workflow test (Phase 9.11)
- [ ] Excel import/export end-to-end testing
- [ ] Mobile responsiveness review across all role views
- [ ] Error handling — empty states, 404 pages, form error UX
- [ ] Loading states for PDF generation
- [ ] UI consistency pass across all role views
- [ ] Remove student nav links from Admin dashboard view (leftover Phase 9.3)

---

## TECHNICAL NOTES

### Grade Status ENUM — v2 Values ✅ Active
```
saved               — Faculty encoded, not yet submitted
pending_head of department_review — Submitted, awaiting Head of Department action
approved_by_head of department    — Head of Department approved, in Registrar queue
rejected            — Head of Department rejected, Faculty can resubmit
finalized           — Registrar locked, permanent
```
**Critical:** These are the ONLY valid values. MySQL throws truncation error on anything else.

### School Year Status ENUM ✅
```
upcoming   — Not yet active (default)
active     — Current school year (only 1 at a time)
completed  — Past school year
```
**Critical:** `inactive` is NOT a valid value.

### Semester Status ENUM ✅
```
upcoming   — Not yet active (default)
active     — Current semester (only 1 at a time)
completed  — Past semester
```
**Critical:** `inactive` is NOT valid.

### Head of Department Scoping Pattern
```php
Student::whereHas('course', function($q) {
    $q->where('department_id', auth()->user()->department_id);
})
```

### GWA Formula
```
Semester GWA   = Σ(grade × units) / Σ(units)
Cumulative GWA = Σ(all grades × units) / Σ(all units) — all finalized semesters
```

### TOR Semester Label Format
```
{semester_name} — SY {year_code}
e.g. "2nd Semester — SY 2025-2026"
```

### Column Name Quick Reference
| Table | Correct | Wrong |
|-------|---------|-------|
| subjects | `code` | `subject_code` |
| students | `student_number` | `student_id` |
| grade_submissions.head of department_action | `approved_by_head of department` | `approved` |
| grades.status | `pending_head of department_review` | `pending` |
| school_years | `year_code` | `year_start`, `year_end` |
| semesters | `semester_name`, `semester_order` | `name` |
| Storage | `Storage::` | `\Storage::` |

### Storage Notes
- COG: `storage/app/cog/{document_number}.pdf`
- TOR: `storage/app/tor/{document_number}.pdf`
- Use `->output()` not `->download()` on DomPDF

### Windows / Git Bash Notes
- Interactive artisan commands: prefix with `winpty`
- Permission errors on bootstrap/cache: `chmod -R 777 bootstrap/cache storage`
- Complex tinker chains fail on Git Bash — use simple single expressions
- `php artisan tinker --execute` with backslashes fails — use DB:: facade directly

---

## LESSONS LEARNED

### Phase 4:
1. Resource controllers handle 7 CRUD actions automatically
2. withCount() prevents N+1 for relationship counts
3. Check for dependencies before deletion
4. Flash messages on every action
5. Unique validation with ignore on update
6. Generic /dashboard must redirect by role
7. winpty prefix for interactive artisan on Windows

### Phase 5:
8. TestEnrollmentSeeder faster than clicking through admin for dev
9. Always verify routes with route:list after editing
10. Duplicate route blocks fail silently

### Phase 6:
11. Always verify column names against migration — subject_code vs code
12. Check laravel.log before assuming code is broken
13. Always cat controller file to confirm methods were saved
14. Grade ENUM is strict — check SHOW COLUMNS via tinker before assuming values
15. bootstrap/cache permissions: chmod -R 777

### Phase 7:
16. Split GET/POST for document generation — GET confirm view + POST for actual generation
17. Always use Storage:: facade with proper import
18. GWA is weighted — not a simple average
19. DomPDF: use output() not download()

### Phase 8:
20. Route order: static routes before wildcard routes
21. Check composer.json before installing packages
22. student_id vs student_number is a recurring trap

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
41. `$fillable` missing `department_id` causes silent data loss on User create/update — always verify fillable matches all columns being written
42. `scopeApproved()` ENUM mismatch (`'approved'` vs `'approved_by_head of department'`) causes entire Registrar queue to return empty silently
43. Arrow functions (`fn()`) inside Blade `@json()` inside HTML attributes cause PHP ParseError — use regular `function()` inside `@php` block and `json_encode()` instead
44. `cat` command output lowercases JS — JavaScript is case-sensitive, `getElementById` ≠ `getelementbyid`
45. Semesters use `semester_name` not `name` — always eager load `semester.schoolYear` for TOR generation

---

## PROGRESS SUMMARY

| Phase | Status | Completion | Notes |
|-------|--------|------------|-------|
| Phase 1: Foundation | ✅ Complete | 100% | — |
| Phase 2: Models & Seeders | ✅ Complete | 100% | department_id added to fillable |
| Phase 3: Auth & Authorization | ✅ Complete | 100% | — |
| Phase 4: Admin Module | ✅ Complete | 100% | Student mgmt removed in Phase 9.3 |
| Phase 5: Faculty Module | ✅ Complete | 100% | Resubmit flow complete |
| Phase 6: Head of Department Module | ✅ Complete | 100% | All stats department-scoped (fixed) |
| Phase 7: Registrar Module | ✅ Complete | 100% | Bulk finalize + preview modal + TOR fix |
| Phase 8: Excel Features | ✅ Complete | 100% | — |
| **Phase 9: System Restructure** | 🔄 In Progress | 98% | **5 SweetAlert actions + E2E test remaining** |
| Phase 10: Reporting & Analytics | 📅 Planned | 0% | After Phase 9 |
| Phase 11: UI/UX & Testing | 🔄 In Progress | 30% | Resume after Phase 9 |

**Overall Project Completion: ~98%**

---

## NEXT STEPS — RESUME HERE

```
✅ Steps 1–41:  All prior work complete
✅ Step 42:     SweetAlert2 CDN confirmed in app.blade.php
✅ Step 42b:    Registrar Finalize All — SweetAlert2 confirm implemented
✅ Step 43a:    GradeSubmission scopeApproved fixed (approved → approved_by_head of department)
✅ Step 43b:    User.$fillable — department_id added
✅ Step 43c:    Head of Department dashboard stats — all 4 department-scoped
✅ Step 43d:    Registrar dashboard — 2 tabs, bulk finalize, preview modal
✅ Step 43e:    TOR semester label fixed (semester_name + SY year_code)
✅ Step 43f:    Duplicate Download TOR button removed from student-profile

▶️ Step 44: Add SweetAlert2 Swal.fire() to remaining 5 destructive actions:
            - Head of Department: Approve All (head of department/review.blade.php)
            - Head of Department: Reject submission (head of department/review.blade.php)
            - Faculty: Submit to Head of Department (faculty/grades/index.blade.php)
            - Faculty: Resubmit to Head of Department (faculty/grades/index.blade.php)
            - Registrar: Generate TOR (registrar/student-profile.blade.php)
▶️ Step 45: php artisan route:clear && php artisan cache:clear
▶️ Step 46: php artisan route:list — full verification
▶️ Step 47: End-to-end test all 12 workflow steps
```

---

**Last Updated:** March 12, 2026
**Phase 9 Status:** 🔄 In Progress — Resume from Step 44 (remaining SweetAlert actions)
**Target:** Phase 9 complete → continue Phase 11 UI/UX → Phase 10 Reporting
