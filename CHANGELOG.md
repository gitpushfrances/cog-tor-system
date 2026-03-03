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
**Status:** 🔄 In Progress — Steps 1–32 done, continue from Step 33

### What Changed From Original Plan (v1 → v2)
| Area | v1 (Old) | v2 (New) | Why |
|------|----------|----------|-----|
| Student management | Admin owned it | Dean owns it (department-scoped) | Correct role boundary |
| Grade approval | One grade at a time | Bulk per faculty submission | Realistic at scale |
| Dean scope | System-wide | Per department via `department_id` | Multi-department support |
| Faculty rejection | No resubmit flow | Reject → Faculty corrects → Resubmit with remarks | Complete workflow |
| Registrar document flow | Browse student list | Search → Academic Profile | Scales to 500+ students |
| TOR generation | Semester selector (wrong) | Always full record (CHED standard) | Academically correct |
| Grade status ENUM | 3 values | 5 values | Covers full workflow |
| SweetAlert | Missing | All destructive actions | UX safety |

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
- [x] Role helpers: isAdmin(), isFaculty(), isDean(), isRegistrar()
- [x] Status helpers: isActive(), isPending(), isInactive()
- [x] Query scopes for filtering

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
- [x] Dean → `/dean/dashboard`
- [x] Faculty → `/faculty/dashboard`
- [x] Registrar → `/registrar/dashboard`

### 3.3 Route Groups
- [x] Admin, Dean, Faculty, Registrar route groups with middleware

### 3.4 Dashboard Controllers & Views
- [x] AdminController, DeanController, FacultyController, RegistrarController
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
- ✅ Student management removed — now owned by Dean
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
- ✅ Batch grade submission to Dean
- ✅ Resubmit flow: rejection banner + remarks + resubmit modal
- ✅ Grade table locks on submit, unlocks on rejection

---

## PHASE 6: DEAN MODULE ✅ COMPLETED
**Date:** February 21–22, 2026
**Status:** ✅ Complete (100%)

### 6.1 Dashboard
- [x] 4 stat cards — Total Students (department-scoped), Active Enrollments, Pending Grades, Approved Grades ✅
- [x] Pending submissions table — grouped by subject, "Review All" per row ✅
- [x] Navigation updated — Dashboard + Students nav links ✅

### 6.2 Student Management
- [x] Dean/StudentController — department-scoped CRUD ✅
- [x] Dean/ExcelController — import/export scoped to department ✅
- [x] dean/students/index.blade.php ✅
- [x] dean/students/create.blade.php ✅
- [x] dean/students/edit.blade.php ✅

### 6.3 Grade Review & Approval (Bulk)
- [x] review() — loads all submissions for same subject at once ✅
- [x] approve() — bulk updates all grades in subject to approved_by_dean ✅
- [x] reject() — bulk updates all grades to rejected, saves dean_remarks ✅
- [x] dean/review.blade.php — full class table, Approve All + Reject buttons ✅
- [x] Resubmitted submissions flagged with Faculty remarks visible ✅

**Deliverables:**
- ✅ Department-scoped student management with Excel import/export
- ✅ Bulk grade approval/rejection
- ✅ Correct ENUM status values: approved_by_dean, rejected

---

## PHASE 7: REGISTRAR MODULE ✅ COMPLETED
**Date:** February 22, 2026
**Status:** ✅ Complete (100%)

### 7.1 Controllers
- [x] RegistrarController — index (student search + paginated list), finalize ✅
- [x] DocumentController — studentProfile(), cogForm, generateCog, torForm, generateTor, downloadCog, downloadTor ✅

### 7.2 Routes
- [x] registrar.dashboard, registrar.submissions.finalize
- [x] registrar.students.profile ✅
- [x] registrar.students.cog, .cog.generate
- [x] registrar.students.tor, .tor.generate
- [x] registrar.cog.download, registrar.tor.download
- [x] Old registrar.students browse route — REMOVED ✅

### 7.3 Views
- [x] registrar/dashboard.blade.php — two-column redesign: student directory (left, paginated, searchable) + finalization queue (right), Font Awesome icons ✅
- [x] registrar/student-profile.blade.php — Academic Profile: grades grouped by school year → semester, semester GWA, cumulative GWA, Generate COG per semester, Generate TOR at top, Download buttons for existing records ✅
- [x] registrar/pdf/cog.blade.php, registrar/pdf/tor.blade.php

### 7.4 Key Notes
- Institution name: `Eastern Samar State University - Guiuan Campus`
- Use `Storage::` facade always — never `\Storage::`
- DomPDF: use `->output()` not `->download()` to avoid .tmp file creation
- TOR: always full cumulative record — no semester selector (CHED standard)
- GWA: Σ(grade × units) / Σ(units) — weighted, not simple average

**Deliverables:**
- ✅ Search-first student flow on Registrar dashboard
- ✅ Academic Profile page with grouped grades, GWA, COG/TOR generation
- ✅ Full COG/TOR PDF generation

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
- [x] app/Http/Controllers/Dean/ExcelController.php — CREATED Phase 9.4 ✅

### 8.2 Dean Excel
- [x] studentTemplate() — CSV template with department-scoped course codes
- [x] importStudents() — validates course belongs to Dean's department
- [x] exportStudents() — scoped to Dean's department_id

### 8.3 Faculty Excel (UNCHANGED)
- [x] downloadTemplate(), uploadGrades()
- [x] 2 faculty Excel routes

**Deliverables:**
- ✅ StudentsExport/Import department-scoped
- ✅ Dean ExcelController built
- ✅ Admin Excel student methods gone

---

## PHASE 9: SYSTEM RESTRUCTURE 🔄 IN PROGRESS
**Date:** March 2026
**Status:** 🔄 In Progress (~91%)

---

### 9.1 — Database Migrations ✅ DONE
- [x] Migration: `add_department_id_to_users_table` — `department_id BIGINT UNSIGNED NULL`, FK → departments.id
- [x] Migration: `add_columns_to_grade_submissions_table` — faculty_remarks (text, nullable), resubmission_count (int, default 0)
- [x] Migration: `update_grade_status_enum` — grades.status locked to 5 values, dean_action ENUM locked to approved_by_dean/rejected
- [x] All 24 migrations green

> ⚠️ LESSON: MySQL ENUM changes = expand first → update data → lock. Can't write a value not in the current ENUM.

---

### 9.2 — Seeder Updates ✅ DONE
- [x] RoleSeeder — firstOrCreate throughout, 3 new Dean permissions (import students, export students, resubmit grades)
- [x] UserSeeder — firstOrCreate, Dean assigned department_id = 1
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

### 9.4 — Dean Module Restructure ✅ DONE
- [x] Dean/StudentController.php — department-scoped CRUD, destroy blocked if enrollments exist
- [x] Dean/ExcelController.php — template, import, export
- [x] Dean student + Excel routes registered
- [x] StudentsExport.php + StudentsImport.php — department_id scoping added
- [x] dean/students/index.blade.php — paginated list, search, course/year/status filters, import/export/add buttons
- [x] dean/students/create.blade.php — creation form, course dropdown filtered to department
- [x] dean/students/edit.blade.php — edit form
- [x] dean/review.blade.php — bulk class table, Approve All + Reject with remarks
- [x] DeanController approve() — bulk, writes approved_by_dean
- [x] DeanController reject() — bulk, writes rejected, saves dean_remarks
- [x] DeanController index() — total_students scoped to department
- [x] Navigation updated — Students nav link added for Dean role

---

### 9.5 — Faculty Module Update ✅ DONE
- [x] GradeController store() — status: saved
- [x] GradeController submit() — status: pending_dean_review
- [x] GradeController resubmit() — validates rejected state, saves faculty_remarks, increments resubmission_count, resets dean_action, sets pending_dean_review
- [x] faculty/grades/index.blade.php — locks on submit, red rejection banner + Dean remarks, resubmit modal with faculty_remarks field
- [x] faculty/subjects.blade.php — status badge per subject card, rejection remarks on card, "Update & Resubmit" button
- [x] Resubmit route registered

---

### 9.6 — Registrar Module Rework ✅ DONE
- [x] RegistrarController index() — student search by name/student_number, all students by default, filtered on search, paginated (15/page)
- [x] DocumentController studentProfile() — finalized enrollments grouped by school year → semester, semester GWA + cumulative GWA, existing COG/TOR record checks
- [x] registrar/dashboard.blade.php — two-column layout: student directory (left, paginated, searchable) + finalization queue (right), Font Awesome icons, no emojis
- [x] registrar/student-profile.blade.php — student header, cumulative GWA, TOR button at top, grades by school year → semester, Generate COG per semester, Download buttons for existing records
- [x] Old registrar.students browse route removed
- [x] Font Awesome 6.5 added to app.blade.php via CDN

---

### 9.7 — SweetAlert2 Installation ⏳ PENDING
- [ ] Add SweetAlert2 CDN to resources/views/layouts/app.blade.php
- [ ] Replace confirm() calls with Swal.fire() on:
  - [ ] Dean: Approve All
  - [ ] Dean: Reject submission
  - [ ] Faculty: Submit to Dean
  - [ ] Faculty: Resubmit to Dean
  - [ ] Registrar: Finalize grades
  - [ ] Registrar: Generate TOR

---

### 9.8 — Verification & End-to-End Test ⏳ PENDING
- [ ] php artisan route:list — full clean verification
- [ ] php artisan migrate:status — all green
- [ ] Full 12-step end-to-end test:
  1. Admin creates Dean account with department_id assigned
  2. Dean logs in — sees only their department's students
  3. Dean imports students via Excel
  4. Dean enrolls students into subject
  5. Dean assigns subject to Faculty
  6. Faculty encodes grades, submits batch
  7. Dean reviews full class table, approves in bulk
  8. Registrar sees finalization queue, finalizes batch
  9. Registrar searches student, views Academic Profile
  10. Registrar generates COG — PDF downloads correctly
  11. Registrar generates TOR — PDF downloads correctly
  12. Faculty resubmit: Dean rejects → Faculty sees remarks → Faculty corrects → Resubmits → Dean sees resubmission flag

**Phase 9 Deliverables:**
- [x] 3 new migrations run successfully
- [x] Dean has department_id in seeder
- [x] Admin student controller and routes deleted
- [x] Dean/StudentController with department scoping
- [x] Dean/ExcelController with import/export
- [x] Dean student views (index, create, edit)
- [x] Dean bulk approval UI (Approve All + Reject with remarks)
- [x] Faculty resubmit flow with remarks (GradeController + views)
- [x] Registrar search-first + Academic Profile
- [x] Registrar dashboard full redesign (two-column, FA icons, paginated)
- [x] Font Awesome 6.5 added to app.blade.php
- [ ] SweetAlert2 on all destructive actions
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
- [x] Dean dashboard — 4 stat cards, pending submissions table, Students nav link
- [x] Faculty dashboard — stats, quick subject links, status badges, rejection flow
- [x] Registrar dashboard — two-column redesign, student directory + finalization queue, Font Awesome

### 11.3 Remaining Tasks (post-Phase 9)
- [ ] SweetAlert2 on all 6 destructive actions (Phase 9.7)
- [ ] End-to-end workflow test (Phase 9.8)
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
pending_dean_review — Submitted, awaiting Dean action
approved_by_dean    — Dean approved, in Registrar queue
rejected            — Dean rejected, Faculty can resubmit
finalized           — Registrar locked, permanent
```
**Critical:** These are the ONLY valid values. MySQL throws truncation error on anything else.

### Dean Scoping Pattern
```php
Student::whereHas('course', function($q) {
    $q->where('department_id', auth()->user()->department_id);
})
```
Active as of Phase 9.1. Requires `department_id` on users table.

### GWA Formula
```
Semester GWA   = Σ(grade × units) / Σ(units)
Cumulative GWA = Σ(all grades × units) / Σ(all units) — all finalized semesters
```
TOR always uses full cumulative. No partial semester selection.

### Column Name Quick Reference
| Table | Correct | Wrong |
|-------|---------|-------|
| subjects | `code` | `subject_code` |
| students | `student_number` | `student_id` |
| grade_submissions.dean_action | `approved_by_dean` | `approved` |
| grades.status | `pending_dean_review` | `pending` |
| Storage | `Storage::` | `\Storage::` |

### Storage Notes
- COG: `storage/app/cog/{document_number}.pdf`
- TOR: `storage/app/tor/{document_number}.pdf`
- Use `Storage::` facade with `use Illuminate\Support\Facades\Storage`
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
23. MySQL ENUM changes: expand first → update data → lock. Can't write a value not in the current ENUM
24. Migrations that ran as empty stubs need manual DB::statement fix — rollback only goes back one batch
25. Git Bash swallows tinker output — use print_r() wrapper
26. Backslashes in tinker --execute fail on Git Bash — use DB:: facade instead of App\Models\
27. Role route groups must be top-level in web.php — nesting inside middleware('auth') causes unmatched brace errors
28. Permission::firstOrCreate prevents crash when re-seeding existing permissions
29. Emojis render as broken diamonds on some Windows setups — use Font Awesome icons instead
30. `cat > file << 'BLADE'` is the safest way to overwrite a view in Git Bash when manual edits don't save
31. GradeSubmission is 1:1 with Grade — group by subject_id at controller level to achieve bulk review without migration changes
32. Always check $students->isNotEmpty() not just $students — use collection methods, not truthiness check on paginators

---

## PROGRESS SUMMARY

| Phase | Status | Completion | Notes |
|-------|--------|------------|-------|
| Phase 1: Foundation | ✅ Complete | 100% | — |
| Phase 2: Models & Seeders | ✅ Complete | 100% | Seeders updated in Phase 9 |
| Phase 3: Auth & Authorization | ✅ Complete | 100% | — |
| Phase 4: Admin Module | ✅ Complete | 100% | Student mgmt removed in Phase 9.3 |
| Phase 5: Faculty Module | ✅ Complete | 100% | Resubmit flow complete in Phase 9.5 |
| Phase 6: Dean Module | ✅ Complete | 100% | Bulk approval + student mgmt complete |
| Phase 7: Registrar Module | ✅ Complete | 100% | Search-first + Academic Profile complete |
| Phase 8: Excel Features | ✅ Complete | 100% | Export/Import updated, Dean Excel built |
| **Phase 9: System Restructure** | 🔄 In Progress | 91% | **SweetAlert + E2E test remaining** |
| Phase 10: Reporting & Analytics | 📅 Planned | 0% | After Phase 9 |
| Phase 11: UI/UX & Testing | 🔄 In Progress | 30% | Resume after Phase 9 |

**Overall Project Completion: ~93%**

---

## NEXT STEPS — RESUME HERE

### ▶️ CONTINUE PHASE 9 FROM STEP 33:
```
✅ Step 1–16:  Migrations, seeders, admin cleanup, Dean controllers + routes
✅ Step 17:    dean/students/index.blade.php
✅ Step 18:    dean/students/create.blade.php
✅ Step 19:    dean/students/edit.blade.php
✅ Step 20:    dean/review.blade.php (bulk approve/reject)
✅ Step 21:    DeanController approve() — bulk, approved_by_dean
✅ Step 22:    DeanController reject() — bulk, rejected
✅ Step 23:    DeanController index() — department-scoped student count
✅ Step 24:    GradeController store() — status: saved
✅ Step 25:    GradeController submit() — status: pending_dean_review
✅ Step 26:    GradeController resubmit() method added
✅ Step 27:    Faculty grade views (lock, rejection remarks, resubmit button)
✅ Step 28:    RegistrarController index() — search logic + paginated student list
✅ Step 29:    DocumentController studentProfile() method
✅ Step 30:    registrar/student-profile.blade.php
✅ Step 31:    Registrar dashboard full redesign (two-column, FA icons)
✅ Step 32:    Font Awesome 6.5 CDN added to app.blade.php

▶️ Step 33: Add SweetAlert2 CDN to app.blade.php
▶️ Step 34: Replace confirm() with Swal.fire() on all 6 points:
            - Dean: Approve All
            - Dean: Reject submission
            - Faculty: Submit to Dean
            - Faculty: Resubmit to Dean
            - Registrar: Finalize grades
            - Registrar: Generate TOR
▶️ Step 35: php artisan route:clear && php artisan cache:clear
▶️ Step 36: php artisan route:list — full verification
▶️ Step 37: End-to-end test all 12 workflow steps
```

---

**Last Updated:** March 3, 2026
**Phase 9 Status:** 🔄 In Progress — Resume from Step 33 (SweetAlert2)
**Target:** Phase 9 complete → continue Phase 11 UI/UX → Phase 10 Reporting
