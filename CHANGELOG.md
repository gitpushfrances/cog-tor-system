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
  - hasMany(Semester)
  - isActive(), isCurrent()
  - Scopes: active(), current()

- [x] **Semester** model with relationships
  - belongsTo(SchoolYear)
  - hasMany(Enrollment), hasMany(CogRecord)
  - isActive(), getFullName()

- [x] **Department** model
  - hasMany(Course)
  - isActive()

- [x] **Course** model
  - belongsTo(Department)
  - hasMany(Subject), hasMany(Student)
  - isActive(), getFullName()

- [x] **Subject** model
  - belongsTo(Course)
  - hasMany(Enrollment)
  - isActive(), getFullName()
  - Scope: byYearLevel()

- [x] **Student** model
  - belongsTo(Course)
  - hasMany(Enrollment), hasMany(Grade), hasMany(CogRecord), hasMany(TorRecord)
  - getFullName(), isActive(), isGraduated()
  - Scopes: active(), byCourse(), byYearLevel()

- [x] **Enrollment** model
  - belongsTo(Student, Subject, Semester, User as enrolledBy)
  - hasOne(Grade)
  - isEnrolled(), isCompleted(), hasGrade()
  - Scopes: enrolled(), bySemester(), byStudent()

- [x] **Grade** model
  - belongsTo(Enrollment, User as faculty)
  - hasOne(GradeSubmission)
  - isPending(), isApprovedByDean(), isFinalized(), isPassing()
  - Static method: convertToGrade() - Philippine scale conversion
  - Scopes: pending(), approvedByDean(), finalized(), passing(), failing()

- [x] **GradeSubmission** model
  - belongsTo(Grade, User as submittedBy/reviewedBy/finalizedBy)
  - isApproved(), isRejected(), isPendingReview(), isReviewed(), isFinalized()
  - Scopes: pendingReview(), approved(), rejected(), finalized()

- [x] **CogRecord** model
  - belongsTo(Student, Semester, User as generatedBy)
  - getDocumentTitle(), hasFile()
  - Scopes: byStudent(), bySemester(), recent()

- [x] **TorRecord** model
  - belongsTo(Student, User as generatedBy)
  - getDocumentTitle(), isComplete(), hasFile()
  - Scopes: byStudent(), complete(), partial(), recent()

### 2.2 User Model Enhanced
- [x] Added Spatie Permission trait (HasRoles)
- [x] Added Activity Log trait (LogsActivity)
- [x] Added relationships:
  - enrollmentsCreated, gradesCreated
  - submissionsCreated, submissionsReviewed, submissionsFinalized
  - cogRecordsGenerated, torRecordsGenerated
  - approvedBy, approvedUsers
- [x] Role helper methods: isAdmin(), isFaculty(), isDean(), isRegistrar()
- [x] Status helper methods: isActive(), isPending(), isInactive()
- [x] Scopes: active(), pending(), byRole(), admins(), faculty(), deans(), registrars()

### 2.3 Database Seeders Created
- [x] **RoleSeeder**
  - Created 4 roles: admin, faculty, dean, registrar
  - Created 23 permissions
  - Assigned permissions to each role

- [x] **UserSeeder**
  - Admin: admin@cogtor.test / password
  - Dean: dean@cogtor.test / password (approved by Admin)
  - Faculty: faculty@cogtor.test / password (approved by Dean)
  - Registrar: registrar@cogtor.test / password (approved by Admin)
  - Pending Faculty: pending@cogtor.test / password (status: pending)

- [x] **AcademicStructureSeeder**
  - 2 School Years (2024-2025 active, 2025-2026 upcoming)
  - 3 Semesters (1st Sem completed, 2nd Sem active, Summer upcoming)
  - 3 Departments (CCS, COE, COED)
  - 5 Courses (BSIT, BSCS, BSCpE, BSEE, BSED)

- [x] **SubjectSeeder**
  - 5 BSIT subjects (IT 101-301)
  - 5 BSCS subjects (CS 101-401)
  - Total: 10 subjects

- [x] **StudentSeeder**
  - 5 BSIT students (3rd year)
  - 5 BSCS students (4th year)
  - Total: 10 students with complete details

- [x] **DatabaseSeeder** (main seeder)
  - Calls all seeders in correct order
  - Displays summary after seeding

### 2.4 Seeding Results
- [x] All seeders ran successfully
- [x] Total execution time: ~1.5 seconds
- [x] No errors or warnings
- [x] Database populated with test data

**Deliverables:**
- ✅ 11 Eloquent models with complete relationships
- ✅ Enhanced User model with role/status helpers
- ✅ 5 database seeders with test data
- ✅ 4 working test accounts
- ✅ Sample academic data (departments, courses, subjects, students)

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
  - Added role-based redirect logic after login
  - Admin → `/admin/dashboard`
  - Dean → `/dean/dashboard`
  - Faculty → `/faculty/dashboard`
  - Registrar → `/registrar/dashboard`
  - Fallback to default dashboard

### 3.3 Route Configuration
- [x] Created Admin route group
  - Prefix: `/admin`
  - Middleware: `auth`, `status`, `role:admin`
  - Route: `admin.dashboard`

- [x] Created Dean route group
  - Prefix: `/dean`
  - Middleware: `auth`, `status`, `role:dean`
  - Route: `dean.dashboard`

- [x] Created Faculty route group
  - Prefix: `/faculty`
  - Middleware: `auth`, `status`, `role:faculty`
  - Route: `faculty.dashboard`

- [x] Created Registrar route group
  - Prefix: `/registrar`
  - Middleware: `auth`, `status`, `role:registrar`
  - Route: `registrar.dashboard`

### 3.4 Dashboard Controllers
- [x] **AdminController** created
  - Dashboard with system stats (users, students, subjects, departments)
  - Recent users table with role and status display
  - Located: `app/Http/Controllers/Admin/AdminController.php`

- [x] **DeanController** created
  - Dashboard with enrollment and grade approval stats
  - Pending grade submissions table
  - Located: `app/Http/Controllers/Dean/DeanController.php`

- [x] **FacultyController** created
  - Dashboard with subject and grade stats
  - Recent grades submitted table with status
  - Located: `app/Http/Controllers/Faculty/FacultyController.php`

- [x] **RegistrarController** created
  - Dashboard with finalization and document stats
  - Pending grade finalization table
  - Located: `app/Http/Controllers/Registrar/RegistrarController.php`

### 3.5 Dashboard Views
- [x] **Admin Dashboard** (`resources/views/admin/dashboard.blade.php`)
  - 4 stat cards (Total Users, Active Users, Pending Users, Total Students)
  - Recent users table with name, email, role, and status
  - Responsive grid layout with Tailwind CSS

- [x] **Dean Dashboard** (`resources/views/dean/dashboard.blade.php`)
  - 4 stat cards (Students, Enrollments, Pending Grades, Approved Grades)
  - Pending grade submissions table
  - Shows student, subject, faculty, and date

- [x] **Faculty Dashboard** (`resources/views/faculty/dashboard.blade.php`)
  - 4 stat cards (Assigned Subjects, Total Grades, Pending, Approved)
  - Recent grades table with student, subject, grade, and status
  - Color-coded status badges

- [x] **Registrar Dashboard** (`resources/views/registrar/dashboard.blade.php`)
  - 4 stat cards (Pending Finalization, Finalized, COG, TOR)
  - Pending finalization table
  - Shows approved grades awaiting finalization

### 3.6 Testing & Verification
- [x] Tested Admin login and dashboard access
- [x] Tested Dean login and dashboard access
- [x] Tested Faculty login and dashboard access
- [x] Tested Registrar login and dashboard access
- [x] Tested Pending user blocking (account pending approval message)
- [x] Verified role-based access control (403 errors for unauthorized access)
- [x] Verified status checking (pending/inactive users blocked)
- [x] All test accounts working correctly

**Deliverables:**
- ✅ 2 custom middleware (CheckRole, CheckStatus)
- ✅ Role-based login redirects
- ✅ 4 dashboard controllers with stats and data queries
- ✅ 4 responsive dashboard views
- ✅ Complete route protection with middleware
- ✅ Tested authentication workflow
- ✅ User status validation working

---

## PHASE 4: ADMIN MODULE 📅 NEXT
**Status:** 📅 Ready to Start (0%)

### Planned Tasks:
- [ ] User Management (CRUD for Faculty, Dean, Registrar)
- [ ] User approval/rejection system
- [ ] Department Management (CRUD)
- [ ] Course Management (CRUD)
- [ ] Subject Management (CRUD)
- [ ] School Year & Semester Management
- [ ] System settings and configuration

---

## TECHNICAL NOTES

### Key Relationships Implemented
**SchoolYear → Semester → Enrollment → Grade → GradeSubmission**
- Complete workflow chain established
- Foreign key constraints properly configured
- Soft deletes on all main models

**User Roles and Permissions**
- Admin: Full system access (manage users, academic structure)
- Faculty: Grade encoding and submission
- Dean: Student enrollment, grade approval
- Registrar: Grade finalization, COG/TOR generation

### Helper Methods Implemented
- **Grade Conversion:** `Grade::convertToGrade()` - Percentage to Philippine scale (1.0-5.0)
- **Full Name Display:** `Student::getFullName()` - Proper name formatting with suffix
- **Status Checks:** `isActive()`, `isPending()`, `isFinalized()` across multiple models
- **Scopes:** Query scopes for common filters (active, pending, by role, by semester)

### Model Features
- **Soft Deletes:** All main models support soft deletion
- **Timestamps:** Automatic created_at and updated_at tracking
- **Type Casting:** Proper date and decimal casting
- **Activity Logging:** User model configured for audit trail
- **Fillable Mass Assignment:** Protected against mass assignment vulnerabilities

### Middleware Features
- **CheckRole:** Role-based access control with multiple role support
- **CheckStatus:** Account status validation (pending/inactive blocking)
- **Session Security:** Automatic logout and session invalidation for blocked users

---

## PROGRESS SUMMARY

| Phase | Status | Completion | Duration |
|-------|--------|------------|----------|
| Phase 1: Foundation & Database | ✅ Complete | 100% | 2 hours |
| Phase 2: Models & Seeders | ✅ Complete | 100% | 2 hours |
| Phase 3: Auth & Authorization | ✅ Complete | 100% | 2 hours |
| Phase 4: Admin Module | 📅 Next | 0% | ~4 hours |
| Phase 5: Faculty Module | 📅 Planned | 0% | ~4 hours |
| Phase 6: Dean Module | 📅 Planned | 0% | ~4 hours |
| Phase 7: Registrar Module | 📅 Planned | 0% | ~5 hours |
| Phase 8: Excel Features | 📅 Planned | 0% | ~3 hours |
| Phase 9: Reporting & Analytics | 📅 Planned | 0% | ~3 hours |
| Phase 10: UI/UX & Testing | 📅 Planned | 0% | ~3 hours |

**Overall Project Completion:** 30%

---

## NEXT STEPS

### Immediate Actions (Phase 4):
1. Create User Management CRUD (list, create, edit, delete)
2. Implement user approval/rejection workflow
3. Create Department Management CRUD
4. Create Course Management CRUD
5. Create Subject Management CRUD
6. Create School Year/Semester Management
7. Add form validation and error handling

---

## LESSONS LEARNED

### Phase 3 Insights:
1. **Middleware order matters** - `auth` before `status` before `role`
2. **Role-based redirects improve UX** - Users land on their relevant dashboard immediately
3. **Status checking is critical** - Prevents pending/inactive users from accessing system
4. **Query scopes are powerful** - `User::active()->count()` is clean and reusable
5. **Blade layouts are efficient** - `<x-app-layout>` provides consistent UI across dashboards
6. **Testing each role individually** - Caught edge cases early in development
7. **Session invalidation for security** - Blocked users fully logged out, not just redirected

---

**Last Updated:** February 15, 2026 - 5:30 PM  
**Next Milestone:** Complete Phase 4 - Admin Module  
**Target Completion:** February 2026
