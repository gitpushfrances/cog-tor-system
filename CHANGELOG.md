# CHANGELOG - COG-TOR System

## Project Information
**System Name:** Academic Grading Management System (COG-TOR)
**Tech Stack:** Laravel 10 LTS + MySQL + Blade + Tailwind CSS
**PHP Version:** 8.4.11
**Node Version:** 18.20.8
**Started:** February 15, 2026

---

## ⚡ RESUME POINT — READ THIS FIRST

**Current Phase:** Phase 11 — UI/UX & Testing
**Status:** 🔄 In Progress — Phase 9 complete. Resume with E2E test (Step 9.11) then Phase 11.

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
- [x] Role helpers: isAdmin(), isFaculty(), isHeadOfDepartment(), isRegistrar()
- [x] Status helpers: isActive(), isPending(), isInactive()
- [x] Query scopes for filtering
- [x] `department()` belongsTo relationship added ✅ (March 12 session)
- [x] `department_id` added to `$fillable` ✅ (bug fix — March 12 session)

### 2.3 Database Seeders Created
- [x] RoleSeeder — 4 roles with 23 permissions
- [x] UserSeeder — 5 test accounts, all using updateOrCreate ✅ (fixed March 23 session)
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
- [x] SubjectController — full CRUD
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

---

## PHASE 7: REGISTRAR MODULE ✅ COMPLETED
**Date:** February 22, 2026
**Status:** ✅ Complete (100%)

### 7.1 Controllers
- [x] RegistrarController — index, finalize, finalizeSubject ✅
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

---

## PHASE 8: EXCEL FEATURES ✅ COMPLETED
**Date:** February 26, 2026
**Status:** ✅ Complete (100%)

### 8.1 Files
- [x] app/Exports/StudentsExport.php — department_id scoped ✅
- [x] app/Imports/StudentsImport.php — department_id scoped, `date_format:Y-m-d` strict validation ✅ (fixed March 23 session)
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

### 9.11 — Verification & End-to-End Test ⏳ NEXT SESSION
- [ ] php artisan route:list — full clean verification
- [ ] php artisan migrate:status — all green
- [ ] Full 12-step end-to-end test

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
- [ ] End-to-end test — next session

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

### 11.3 Remaining Tasks
- [ ] End-to-end workflow test (Phase 9.11) — DO THIS FIRST
- [ ] Mobile responsiveness review across all role views
- [ ] Error handling — empty states, 404 pages, form error UX
- [ ] Loading states for PDF generation
- [ ] UI consistency pass across all role views
- [ ] Remove leftover student nav links from Admin dashboard (Phase 9.3 leftover)

---

## TECHNICAL NOTES

### Grade Status ENUM — v2 Values ✅ Active
```
saved                        — Faculty encoded, not yet submitted
pending_head_of_department_review  — Submitted, awaiting HoD action
approved_by_head_of_department     — HoD approved, in Registrar queue
rejected                     — HoD rejected, Faculty can resubmit
finalized                    — Registrar locked, permanent
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
| grade_submissions.hod_action | `approved_by_head_of_department` | `approved` |
| grades.status | `pending_head_of_department_review` | `pending` |
| school_years | `year_code` | `year_start`, `year_end` |
| semesters | `semester_name`, `semester_order` | `name` |
| Storage facade | `Storage::` (with import) | `\Storage::` |

### Storage Notes
- COG: `storage/app/cog/{document_number}.pdf`
- TOR: `storage/app/tor/{document_number}.pdf`
- Use `->output()` not `->download()` on DomPDF

### Windows / Git Bash Notes
- Interactive artisan commands: prefix with `winpty`
- Permission errors on bootstrap/cache: `chmod -R 777 bootstrap/cache storage`
- Complex tinker chains fail on Git Bash — use simple single expressions
- `php artisan tinker --execute` with backslashes fails — use DB:: facade directly
- sed commands mangle unicode emojis — use line-number targeting `sed -i 'Ns/.*/replacement/'`

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

---

## PROGRESS SUMMARY

| Phase | Status | Completion | Notes |
|-------|--------|------------|-------|
| Phase 1: Foundation | ✅ Complete | 100% | — |
| Phase 2: Models & Seeders | ✅ Complete | 100% | updateOrCreate in seeder |
| Phase 3: Auth & Authorization | ✅ Complete | 100% | — |
| Phase 4: Admin Module | ✅ Complete | 100% | Font Awesome icons, role badge formatting |
| Phase 5: Faculty Module | ✅ Complete | 100% | SweetAlert2 + Font Awesome |
| Phase 6: HoD Module | ✅ Complete | 100% | SweetAlert2 + Font Awesome + delete confirm |
| Phase 7: Registrar Module | ✅ Complete | 100% | SweetAlert2 on all doc actions |
| Phase 8: Excel Features | ✅ Complete | 100% | Fixed template + strict date validation |
| **Phase 9: System Restructure** | ✅ Complete | 99% | **E2E test next session** |
| Phase 10: Reporting & Analytics | 📅 Planned | 0% | After Phase 11 |
| Phase 11: UI/UX & Testing | 🔄 In Progress | 40% | E2E + mobile + error handling |

**Overall Project Completion: ~99%**

---

## NEXT STEPS — RESUME HERE

```
▶️ Priority 1: Phase 9.11 — End-to-end test (12 steps)
   1.  Admin: verify active school year + active semester exist
   2.  Admin: verify department, course, subject exist
   3.  Admin: verify HoD + Faculty accounts have correct department_id
   4.  HoD: assign subject to faculty
   5.  HoD: enroll students into subject
   6.  Faculty: encode grades
   7.  Faculty: submit to HoD (confirm SweetAlert fires)
   8.  HoD: review and approve all (confirm SweetAlert fires)
   9.  Registrar: verify subject in finalization queue
   10. Registrar: finalize subject (preview modal + SweetAlert confirm)
   11. Registrar: generate COG
   12. Registrar: generate TOR

▶️ Priority 2: Phase 11 remaining
   - Mobile responsiveness
   - Empty states, 404, form error UX
   - Loading states for PDF generation
   - UI consistency pass
   - Remove leftover student nav links from Admin dashboard
```

---

**Last Updated:** March 23, 2026
**Phase 9 Status:** ✅ Complete
**Current Focus:** Phase 9.11 E2E test → Phase 11 UI/UX
