# CHANGELOG - COG-TOR System

## Project Information
**System Name:** Academic Grading Management System (COG-TOR)  
**Tech Stack:** Laravel 10 LTS + MySQL + Blade + Tailwind CSS  
**PHP Version:** 8.4.11  
**Node Version:** 18.20.8  
**Started:** February 15, 2026  

---

## PHASE 1: FOUNDATION & DATABASE ARCHITECTURE ✅ COMPLETED
**Date:** February 15, 2026  
**Status:** ✅ Complete (100%)

### 1.1 Environment Setup
- [x] Navigated to Desktop directory: `C:\Users\Frances\Desktop`
- [x] Created Laravel 10 project: `cog-tor-system`
- [x] Composer configured with PHP 8.4.11
- [x] Node.js 18.20.8 and NPM 10.8.2 verified
- [x] MySQL 8.0 ready via XAMPP

### 1.2 Core Packages Installed
- [x] **Laravel Breeze v1.26** - Authentication scaffolding (Blade + Tailwind)
- [x] **Spatie Laravel Permission v6.x** - Role and permission management
- [x] **Maatwebsite Excel v3.1+** - Excel import/export functionality
- [x] **Spatie Laravel Activity Log v4.8+** - Audit trail for grade changes
- [x] **Laravel DomPDF v3.x** - PDF generation for COG/TOR documents
- [x] **Laravel Debugbar v3.9** - Development debugging tool

### 1.3 Database Setup
- [x] Created database: `cog_tor_system`
- [x] Configured `.env` file with database credentials
- [x] Created 12 custom migration files
- [x] Ran all migrations successfully (21 tables total)

### 1.4 GitHub Repository
- [x] Initialized Git repository
- [x] Created remote: https://github.com/gitpushfrances/cog-tor-system.git
- [x] Pushed initial commit to main branch

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
- [x] **SchoolYear** model with relationships and helper methods
- [x] **Semester** model with relationships
- [x] **Department** model
- [x] **Course** model
- [x] **Subject** model
- [x] **Student** model
- [x] **Enrollment** model
- [x] **Grade** model
- [x] **GradeSubmission** model
- [x] **CogRecord** model
- [x] **TorRecord** model

### 2.2 User Model Enhanced
- [x] Added Spatie Permission trait (HasRoles)
- [x] Added Activity Log trait (LogsActivity)
- [x] Added complete relationships
- [x] Role helper methods: isAdmin(), isFaculty(), isDean(), isRegistrar()
- [x] Status helper methods: isActive(), isPending(), isInactive()
- [x] Query scopes for filtering

### 2.3 Database Seeders Created
- [x] **RoleSeeder** - 4 roles with 23 permissions
- [x] **UserSeeder** - 5 test accounts
- [x] **AcademicStructureSeeder** - School years, semesters, departments, courses
- [x] **SubjectSeeder** - 10 sample subjects
- [x] **StudentSeeder** - 10 sample students
- [x] **DatabaseSeeder** - Main seeder orchestration

**Deliverables:**
- ✅ 11 Eloquent models with complete relationships
- ✅ Enhanced User model with role/status helpers
- ✅ 5 database seeders with test data
- ✅ 4 working test accounts

---

## PHASE 3: AUTHENTICATION & AUTHORIZATION ✅ COMPLETED
**Date:** February 15, 2026  
**Status:** ✅ Complete (100%)

### 3.1 Middleware Implementation
- [x] **CheckRole** middleware created
  - Accepts multiple roles as parameters
  - Redirects to login if unauthenticated
  - Returns 403 Forbidden if role mismatch
  - Registered as 'role' alias in Kernel

- [x] **CheckStatus** middleware created
  - Blocks pending users with error message
  - Blocks inactive users with error message
  - Automatically logs out blocked users
  - Invalidates session for security
  - Registered as 'status' alias in Kernel

### 3.2 Authentication Controller Modified
- [x] **AuthenticatedSessionController** updated
  - Role-based redirect logic after login
  - Admin → `/admin/dashboard`
  - Dean → `/dean/dashboard`
  - Faculty → `/faculty/dashboard`
  - Registrar → `/registrar/dashboard`

### 3.3 Route Configuration
- [x] Created Admin route group with middleware protection
- [x] Created Dean route group with middleware protection
- [x] Created Faculty route group with middleware protection
- [x] Created Registrar route group with middleware protection

### 3.4 Dashboard Controllers
- [x] **AdminController** - System stats and recent users
- [x] **DeanController** - Enrollment and grade approval stats
- [x] **FacultyController** - Subject and grade stats
- [x] **RegistrarController** - Finalization and document stats

### 3.5 Dashboard Views
- [x] **Admin Dashboard** - Stats cards and recent users table
- [x] **Dean Dashboard** - Pending grade submissions
- [x] **Faculty Dashboard** - Recent grades with status
- [x] **Registrar Dashboard** - Pending finalization queue

### 3.6 Testing & Verification
- [x] Tested all 4 user roles (Admin, Dean, Faculty, Registrar)
- [x] Verified pending user blocking
- [x] Verified role-based access control (403 errors)
- [x] Verified status checking middleware

**Deliverables:**
- ✅ 2 custom middleware (CheckRole, CheckStatus)
- ✅ Role-based login redirects
- ✅ 4 dashboard controllers with real-time stats
- ✅ 4 responsive dashboard views
- ✅ Complete authentication workflow tested

---

## PHASE 4: ADMIN MODULE ✅ COMPLETED
**Date:** February 15–21, 2026  
**Status:** ✅ Complete (100%)

### 4.1 User Management ✅ COMPLETED
- [x] **UserController** created with full CRUD
  - index() - List all users with pagination
  - create() - User creation form
  - store() - Save new user with validation
  - edit() - Edit user form
  - update() - Update user with validation
  - destroy() - Delete user (prevents self-deletion)
  - approve() - Approve pending users
  - reject() - Reject/deactivate users

- [x] **User Views** created
  - index.blade.php - User list with approve/reject/edit/delete actions
  - create.blade.php - User creation form with role and status selection
  - edit.blade.php - User edit form with optional password change

- [x] **User Routes** registered
  - Resource routes: admin.users.*
  - Custom routes: admin.users.approve, admin.users.reject

- [x] **Features Implemented**
  - Form validation (name, email unique, password confirmation)
  - Role assignment (faculty, dean, registrar)
  - Status management (active, pending, inactive)
  - Approval workflow (admin approves, sets approved_by and approved_at)
  - Prevent self-deletion
  - Success/error flash messages
  - Pagination (15 per page)
  - Back button navigation added to all views

### 4.2 Department Management ✅ COMPLETED
- [x] **DepartmentController** created with full CRUD
- [x] **Department Views** created (index, create, edit)
- [x] **Department Routes** registered (admin.departments.*)
- [x] **Features Implemented**
  - Form validation (code unique, name required)
  - Status management (active, inactive)
  - Course count display (withCount relationship)
  - Prevent deletion if courses exist
  - Optional description field
  - Flash messages and pagination (15 per page)
  - Back button navigation added to all views

### 4.3 Course Management ✅ COMPLETED
- [x] **CourseController** created with full CRUD
- [x] **Course Views** created (index, create, edit)
- [x] **Course Routes** registered (admin.courses.*)
- [x] **Features Implemented**
  - Form validation (code unique, department exists, years 1-10)
  - Department relationship (belongsTo)
  - Subject and student count display (withCount)
  - Prevent deletion if subjects or students exist
  - Status management and pagination
  - Back button navigation added to all views

### 4.4 Admin Dashboard Enhancement ✅ COMPLETED
- [x] **Quick Navigation Menu** added
  - User Management, Department, Course links
  - Hover effects and transitions
- [x] **Dashboard Stats** - Total Users, Active, Pending, Total Students
- [x] **Recent Users Table** - Name, Email, Role, Status (latest 5)
- [x] **Dashboard redirect fix** - Generic `/dashboard` route now redirects based on user role
  - Admin → `/admin/dashboard`
  - Dean → `/dean/dashboard`
  - Faculty → `/faculty/dashboard`
  - Registrar → `/registrar/dashboard`

### 4.5 Subject Management ✅ COMPLETED
**Date:** February 21, 2026

- [x] **SubjectController** created with full CRUD
  - index() - List subjects with course and department info
  - create() - Subject creation form with course dropdown
  - store() - Save with validation
  - edit() - Edit form
  - update() - Update with validation
  - destroy() - Delete (prevents deletion if enrollments exist)

- [x] **Subject Views** created
  - index.blade.php - Subject list with code, name, course, units, year, semester, status
  - create.blade.php - Subject creation form
  - edit.blade.php - Subject edit form

- [x] **Subject Routes** registered (admin.subjects.*)

- [x] **Features Implemented**
  - Form validation (code unique, units 1-10, year level 1-5)
  - Semester options (1st, 2nd, Summer)
  - Course relationship with department display
  - Prevent deletion if enrollments exist
  - Back button navigation on all views

### 4.6 School Year Management ✅ COMPLETED
**Date:** February 21, 2026

- [x] **SchoolYearController** created with full CRUD + setActive
  - index() - List school years with semester count
  - create/store/edit/update/destroy - Full CRUD
  - setActive() - Sets selected school year as active, deactivates others

- [x] **School Year Views** created (index, create, edit)

- [x] **School Year Routes** registered
  - Resource routes: admin.school-years.*
  - Custom route: admin.school-years.set-active

- [x] **Features Implemented**
  - Only one active school year at a time (auto-deactivates others)
  - Prevent deletion if semesters exist
  - Set Active button on index
  - Back button navigation on all views

### 4.7 Semester Management ✅ COMPLETED
**Date:** February 21, 2026

- [x] **SemesterController** created with full CRUD + setActive
  - index() - List semesters with school year info
  - create/store/edit/update/destroy - Full CRUD
  - setActive() - Sets selected semester as active, deactivates others

- [x] **Semester Views** created (index, create, edit)

- [x] **Semester Routes** registered
  - Resource routes: admin.semesters.*
  - Custom route: admin.semesters.set-active

- [x] **Features Implemented**
  - Linked to school year (belongsTo)
  - Only one active semester at a time
  - Prevent deletion if enrollments exist
  - Set Active button on index
  - Back button navigation on all views

### 4.8 Student Management ✅ COMPLETED
**Date:** February 21, 2026

- [x] **StudentController** created with full CRUD
  - index() - List students with course info
  - create/store/edit/update/destroy - Full CRUD

- [x] **Student Views** created (index, create, edit)

- [x] **Student Routes** registered (admin.students.*)

- [x] **Features Implemented**
  - Student ID unique validation
  - Email unique validation
  - Course assignment (belongsTo)
  - Year level (1-5)
  - Status: active, inactive, graduated (color-coded badges)
  - Prevent deletion if enrollments exist
  - Back button navigation on all views

### 4.9 Bug Fixes & UX Improvements ✅ COMPLETED
**Date:** February 21, 2026

- [x] **Dashboard redirect bug fixed**
  - Generic `/dashboard` route was showing "You're logged in!" to admin
  - Fixed by adding role-based redirect logic to the route
- [x] **Back buttons added** to all admin views (create, edit, index)
  - create/edit pages: "← Back" using `url()->previous()`
  - index pages: "← Back to Dashboard" linking to admin.dashboard
- [x] **Route cache cleared** after all route changes
- [x] **54 admin routes** confirmed registered and working

**Deliverables:**
- ✅ User Management System (CRUD + Approval)
- ✅ Department Management System (CRUD)
- ✅ Course Management System (CRUD)
- ✅ Subject Management System (CRUD)
- ✅ School Year Management System (CRUD + Set Active)
- ✅ Semester Management System (CRUD + Set Active)
- ✅ Student Management System (CRUD)
- ✅ Dashboard role-based redirect
- ✅ Back button navigation on all pages
- ✅ 54 routes registered and verified

---

## PHASE 5: FACULTY MODULE ✅ COMPLETED
**Date:** February 21, 2026
**Status:** ✅ Complete (100%)

### 5.1 Faculty Controllers Created
- [x] **FacultyController** - Dashboard and subjects list
  - index() - Faculty dashboard with stats (assigned subjects, total students, encoded/pending grades)
  - subjects() - List of subjects assigned to logged-in faculty

- [x] **GradeController** - Full grade encoding and submission workflow
  - index() - Grade encoding table showing all enrolled students per subject
  - store() - Save/update grades for all students in bulk
  - edit() - Edit a single student's grade
  - update() - Update a single student's grade
  - submit() - Submit all grades for a subject to the Dean (creates GradeSubmission records)

### 5.2 Faculty Views Created
- [x] `resources/views/faculty/dashboard.blade.php` - Stats cards and quick links
- [x] `resources/views/faculty/subjects.blade.php` - Assigned subjects list with "Manage Grades" button
- [x] `resources/views/faculty/grades/index.blade.php` - Grade encoding table (percentage input → auto grade)
- [x] `resources/views/faculty/grades/edit.blade.php` - Single grade edit form

### 5.3 Faculty Routes Added
- [x] `faculty.dashboard` - GET /faculty/dashboard
- [x] `faculty.subjects` - GET /faculty/subjects
- [x] `faculty.subjects.grades` - GET /faculty/subjects/{subject}/grades
- [x] `faculty.subjects.grades.store` - POST /faculty/subjects/{subject}/grades
- [x] `faculty.subjects.grades.edit` - GET /faculty/subjects/{subject}/grades/{grade}/edit
- [x] `faculty.subjects.grades.update` - PUT /faculty/subjects/{subject}/grades/{grade}
- [x] `faculty.subjects.grades.submit` - POST /faculty/subjects/{subject}/submit

### 5.4 Migration Added
- [x] Added `faculty_id` column to `subjects` table via new migration

### 5.5 Test Data Seeded
- [x] **TestEnrollmentSeeder** created
  - Assigned faculty ID 3 (Juan Dela Cruz | faculty@cogtor.test) to Subject 1
  - Enrolled all 10 students into Subject 1 with active semester
  - Used `insertOrIgnore` to prevent duplicate enrollment errors

### 5.6 Bug Fixes
- [x] **Faculty routes duplication fixed** — routes were accidentally nested during editing, cleaned up in VS Code
- [x] **Route cache cleared** after all changes

### 5.7 Verified Working
- [x] Faculty can log in and see dashboard
- [x] Faculty can view assigned subjects
- [x] Faculty can encode grades (percentage → auto-converted to Philippine grade scale)
- [x] Faculty can submit grades to Dean (creates GradeSubmission records with submitted_at timestamp)
- [x] 10 grades and 10 GradeSubmission records confirmed in database after testing

**Deliverables:**
- ✅ FacultyController and GradeController
- ✅ 4 faculty views (dashboard, subjects, grades/index, grades/edit)
- ✅ 7 faculty routes registered
- ✅ Grade encoding and submission workflow tested end-to-end
- ✅ TestEnrollmentSeeder for development testing

---

## PHASE 6: DEAN MODULE 🔄 IN PROGRESS
**Date:** February 21, 2026
**Status:** 🔄 In Progress (30%)

### 6.1 Dean Dashboard ✅ COMPLETED
- [x] Dashboard showing 4 stat cards: Total Students, Active Enrollments, Pending Grades, Approved Grades
- [x] Pending Grade Submissions table showing student name, subject, submitted by, and date
- [x] 10 pending submissions displaying correctly after faculty grade submission

### 6.2 Bug Fixes ✅ COMPLETED
- [x] **`subject_code` column name bug fixed**
  - Dean dashboard view was calling `->subject->subject_code`
  - Subject model column is actually named `code` not `subject_code`
  - Fixed to `->subject->code` in `dean/dashboard.blade.php`
- [x] **`pendingReview()` scope verified working** — returns correct count of 10
- [x] **Dean user confirmed** — dean@cogtor.test exists and can log in

### 6.3 Grade Review & Approval ⏸️ NEXT SESSION
**Status:** ⏸️ Paused — to be completed next session

**Planned — DeanController methods to add:**
- [ ] review() - Show single submission with full grade details for Dean to inspect
- [ ] approve() - Set dean_action = approved, fill reviewed_at and reviewed_by, update grade status
- [ ] reject() - Set dean_action = rejected, save dean_remarks, update grade status, notify faculty

**Planned — Routes to add inside Dean route group:**
- [ ] `dean.submissions.review` - GET /dean/submissions/{submission}/review
- [ ] `dean.submissions.approve` - POST /dean/submissions/{submission}/approve
- [ ] `dean.submissions.reject` - POST /dean/submissions/{submission}/reject

**Planned — Views to create:**
- [ ] `resources/views/dean/review.blade.php` - Full grade review page with approve/reject form
- [ ] Update `dean/dashboard.blade.php` to add "Review" action button per submission row

**Remaining Dean Module tasks (Phase 6 full scope):**
- [ ] Student enrollment management (add/remove students from subjects)
- [ ] Assign subjects to faculty
- [ ] Department performance reports

---

## PHASE 7: REGISTRAR MODULE 📅 PLANNED
**Status:** 📅 Not Started (0%)

### Planned Tasks:
- [ ] Receive approved grades from Dean
- [ ] Finalize and store official grades
- [ ] Generate COG (Certificate of Grades)
- [ ] Generate TOR (Transcript of Records)
- [ ] Compute semester GWA
- [ ] Compute cumulative GWA
- [ ] Print/download official documents
- [ ] Archive management

---

## TECHNICAL NOTES

### Key Relationships Implemented
**SchoolYear → Semester → Enrollment → Grade → GradeSubmission**
- Complete workflow chain established
- Foreign key constraints properly configured
- Soft deletes on all main models

**Department → Course → Subject → Enrollment**
- Academic structure hierarchy working
- Cascade prevention on deletions
- withCount eager loading for performance

### Helper Methods Implemented
- **Grade Conversion:** `Grade::convertToGrade()` - Percentage to Philippine scale
- **Full Name Display:** `Student::getFullName()` - Proper name formatting
- **Status Checks:** `isActive()`, `isPending()`, `isFinalized()` across models
- **Scopes:** Query scopes for common filters

### Middleware Features
- **CheckRole:** Role-based access control with multiple role support
- **CheckStatus:** Account status validation (pending/inactive blocking)
- **Session Security:** Automatic logout and session invalidation

### Form Validation Patterns
- **Unique validation:** Email, course code, department code, subject code, student ID
- **Relationship validation:** Foreign key checks (exists:table,id)
- **Conditional validation:** Optional password on edit
- **Custom validation:** Prevent self-deletion, prevent deletion with dependencies

### Known Column Name Notes
- Subject code column is `code` (NOT `subject_code`) — use `->subject->code` in views
- Grade table columns: `id, enrollment_id, faculty_id, grade, percentage, status, remarks`

---

## PROGRESS SUMMARY

| Phase | Status | Completion | Duration |
|-------|--------|------------|----------|
| Phase 1: Foundation & Database | ✅ Complete | 100% | 2 hours |
| Phase 2: Models & Seeders | ✅ Complete | 100% | 2 hours |
| Phase 3: Auth & Authorization | ✅ Complete | 100% | 2 hours |
| Phase 4: Admin Module | ✅ Complete | 100% | ~6 hours |
| Phase 5: Faculty Module | ✅ Complete | 100% | ~4 hours |
| Phase 6: Dean Module | 🔄 In Progress | 30% | ongoing |
| Phase 7: Registrar Module | 📅 Planned | 0% | ~5 hours |
| Phase 8: Excel Features | 📅 Planned | 0% | ~3 hours |
| Phase 9: Reporting & Analytics | 📅 Planned | 0% | ~3 hours |
| Phase 10: UI/UX & Testing | 📅 Planned | 0% | ~3 hours |

**Overall Project Completion:** ~55%

---

## NEXT STEPS — RESUME HERE NEXT SESSION

### ⏸️ Pick up at: Phase 6 — Dean Grade Review & Approval

**Step 1:** Add 3 methods to `app/Http/Controllers/Dean/DeanController.php`
- review(GradeSubmission $submission)
- approve(Request $request, GradeSubmission $submission)
- reject(Request $request, GradeSubmission $submission)

**Step 2:** Add 3 routes to the Dean route group in `routes/web.php`
```php
Route::get('/submissions/{submission}/review', [DeanController::class, 'review'])->name('submissions.review');
Route::post('/submissions/{submission}/approve', [DeanController::class, 'approve'])->name('submissions.approve');
Route::post('/submissions/{submission}/reject', [DeanController::class, 'reject'])->name('submissions.reject');
```

**Step 3:** Create `resources/views/dean/review.blade.php`
- Show student name, subject, grade, percentage, remarks
- Approve button (POST to approve route)
- Reject form with dean_remarks textarea (POST to reject route)

**Step 4:** Update `resources/views/dean/dashboard.blade.php`
- Add "Review" button/link per row in the pending submissions table

**Step 5:** Test full Dean approval flow end-to-end

---

## LESSONS LEARNED

### Phase 4 Insights:
1. **Resource controllers save time** - Laravel's resource routes handle 7 CRUD actions automatically
2. **withCount() is powerful** - Display relationship counts without N+1 queries
3. **Prevent orphaned records** - Check for dependencies before deletion
4. **Flash messages improve UX** - User feedback on every action is essential
5. **Form validation centralizes logic** - Validation rules in controller keep views clean
6. **Pagination improves performance** - 15 items per page prevents memory issues
7. **Relationship eager loading** - with() prevents N+1 query problems
8. **Unique validation with ignore** - Allow updating without triggering unique constraint on own record
9. **Role-based dashboard redirect** - Generic /dashboard route must redirect by role to avoid wrong view
10. **Back buttons matter** - Navigation links improve UX significantly on admin panels
11. **winpty prefix needed in Git Bash** - Required for interactive Laravel artisan commands on Windows

### Phase 5 Insights:
12. **TestEnrollmentSeeder is faster than UI** - Seeding test data directly is quicker than clicking through admin panel during development
13. **Routes can silently revert** - Always verify routes with `route:list` after editing, especially after session breaks
14. **--execute flag limitations on Windows** - Complex chained tinker commands fail on Git Bash; use simple single-expression commands instead
15. **Duplicate route blocks break silently** - Nested route groups don't throw errors but cause unpredictable behavior

### Phase 6 Insights (so far):
16. **Always verify column names against the model** - `subject_code` vs `code` caused the Dean dashboard to show nothing despite correct data
17. **Check the log before assuming code is broken** - `storage/logs/laravel.log` shows the real error, not just the browser message

---

**Last Updated:** February 21, 2026
**Phase 5 Completed:** ✅ Faculty Module — 100%
**Phase 6 Status:** 🔄 In Progress — Dean dashboard working, review/approve/reject to be built next session
**Next Milestone:** Complete Phase 6 Dean Module (Grade Review & Approval)
**Target Completion:** March 2026
