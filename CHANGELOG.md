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

## PHASE 3: AUTHENTICATION & AUTHORIZATION 📅 NEXT
**Status:** 📅 Ready to Start (0%)

### Planned Tasks:
- [ ] Create CheckRole middleware
- [ ] Create CheckStatus middleware
- [ ] Modify authentication controllers
- [ ] Setup role-based routes
- [ ] Create dashboard controllers
- [ ] Create basic dashboard views
- [ ] Test authentication workflow

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

---

## PROGRESS SUMMARY

| Phase | Status | Completion | Duration |
|-------|--------|------------|----------|
| Phase 1: Foundation & Database | ✅ Complete | 100% | 2 hours |
| Phase 2: Models & Seeders | ✅ Complete | 100% | 2 hours |
| Phase 3: Auth & Authorization | 📅 Next | 0% | ~2 hours |
| Phase 4: Admin Module | 📅 Planned | 0% | ~4 hours |
| Phase 5: Faculty Module | 📅 Planned | 0% | ~4 hours |
| Phase 6: Dean Module | 📅 Planned | 0% | ~4 hours |
| Phase 7: Registrar Module | 📅 Planned | 0% | ~5 hours |
| Phase 8: Excel Features | 📅 Planned | 0% | ~3 hours |
| Phase 9: Reporting & Analytics | 📅 Planned | 0% | ~3 hours |
| Phase 10: UI/UX & Testing | 📅 Planned | 0% | ~3 hours |

**Overall Project Completion:** 20%

---

## NEXT STEPS

### Immediate Actions (Phase 3):
1. Create CheckRole and CheckStatus middleware
2. Modify AuthenticatedSessionController for role-based redirects
3. Setup route groups for each user role
4. Create dashboard controllers (Admin, Faculty, Dean, Registrar)
5. Create basic dashboard Blade views
6. Test login with seeded accounts
7. Verify role-based access control

---

## LESSONS LEARNED

### Phase 2 Insights:
1. **Model relationships are crucial** - Defined all relationships upfront prevents refactoring
2. **Helper methods improve code readability** - `isActive()` is cleaner than checking status directly
3. **Scopes simplify queries** - `User::faculty()->active()` is very readable
4. **Seeder order matters** - Must seed in dependency order (roles → users → departments → courses)
5. **Type casting is important** - Proper date and decimal casting prevents bugs
6. **Soft deletes are valuable** - Easy data recovery without complex restore procedures
7. **Activity logging on User model** - Automatic audit trail for user actions

---

**Last Updated:** February 15, 2026 - 4:00 PM  
**Next Milestone:** Complete Phase 3 - Authentication & Authorization  
**Target Completion:** February 2026
