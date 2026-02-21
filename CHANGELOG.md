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

## PHASE 5: FACULTY MODULE 📅 PLANNED
**Status:** 📅 Not Started (0%)

### Planned Tasks:
- [ ] View assigned subjects
- [ ] Student enrollment list per subject
- [ ] Grade encoding interface
- [ ] Grade submission workflow
- [ ] View submission status and history
- [ ] Receive Dean feedback/remarks
- [ ] Grade editing (before submission)

---

## PHASE 6: DEAN MODULE 📅 PLANNED
**Status:** 📅 Not Started (0%)

### Planned Tasks:
- [ ] Student enrollment management
- [ ] Assign students to subjects/sections
- [ ] View submitted grades from faculty
- [ ] Approve grades (with validation)
- [ ] Reject grades (with remarks/feedback)
- [ ] Forward approved grades to Registrar
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

---

## PROGRESS SUMMARY

| Phase | Status | Completion | Duration |
|-------|--------|------------|----------|
| Phase 1: Foundation & Database | ✅ Complete | 100% | 2 hours |
| Phase 2: Models & Seeders | ✅ Complete | 100% | 2 hours |
| Phase 3: Auth & Authorization | ✅ Complete | 100% | 2 hours |
| Phase 4: Admin Module | ✅ Complete | 100% | ~6 hours |
| Phase 5: Faculty Module | 📅 Planned | 0% | ~4 hours |
| Phase 6: Dean Module | 📅 Planned | 0% | ~4 hours |
| Phase 7: Registrar Module | 📅 Planned | 0% | ~5 hours |
| Phase 8: Excel Features | 📅 Planned | 0% | ~3 hours |
| Phase 9: Reporting & Analytics | 📅 Planned | 0% | ~3 hours |
| Phase 10: UI/UX & Testing | 📅 Planned | 0% | ~3 hours |

**Overall Project Completion:** ~45%

---

## NEXT STEPS

### Next Up — Phase 5: Faculty Module
1. View assigned subjects per faculty
2. Student enrollment list per subject
3. Grade encoding interface
4. Grade submission workflow to Dean
5. View submission status (Pending / Approved / Rejected)
6. Receive and display Dean remarks on rejection

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

---

**Last Updated:** February 21, 2026  
**Phase 4 Completed:** ✅ Admin Module — 100%  
**Next Milestone:** Phase 5 - Faculty Module (Grade Encoding)  
**Target Completion:** March 2026
