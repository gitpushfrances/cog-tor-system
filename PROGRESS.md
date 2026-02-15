# 📋 PROGRESS - COG-TOR System Development Roadmap

**Project:** Academic Grading Management System (COG-TOR)  
**Started:** February 15, 2026  
**Current Phase:** Phase 2  
**Overall Completion:** 10%

---

## 🎯 Phase Overview

| Phase | Name | Status | Completion | Duration |
|-------|------|--------|------------|----------|
| **Phase 1** | Foundation & Database | ✅ Complete | 100% | 2 hours |
| **Phase 2** | Models & Seeders | 📅 Current | 0% | ~2 hours |
| **Phase 3** | Authentication & Roles | 📅 Next | 0% | ~2 hours |
| **Phase 4** | Admin Module | 📅 Planned | 0% | ~4 hours |
| **Phase 5** | Faculty Module | 📅 Planned | 0% | ~4 hours |
| **Phase 6** | Dean Module | 📅 Planned | 0% | ~4 hours |
| **Phase 7** | Registrar Module | 📅 Planned | 0% | ~5 hours |
| **Phase 8** | Excel Features | 📅 Planned | 0% | ~3 hours |
| **Phase 9** | Reporting & Analytics | 📅 Planned | 0% | ~3 hours |
| **Phase 10** | Polish & Testing | 📅 Planned | 0% | ~3 hours |

---

## ✅ PHASE 1: FOUNDATION & DATABASE (COMPLETE)

**Duration:** 2 hours  
**Status:** ✅ 100% Complete  
**Date Completed:** February 15, 2026

### 1.1 Environment Setup
- [x] Navigate to Desktop directory
- [x] Create Laravel 10 project
- [x] Configure Composer for PHP 8.4
- [x] Verify Node.js 18 and NPM

### 1.2 Package Installation
- [x] Laravel Breeze (Authentication)
- [x] Spatie Permission (Roles)
- [x] Maatwebsite Excel (Import/Export)
- [x] Spatie Activity Log (Audit Trail)
- [x] Laravel DomPDF (PDF Generation)
- [x] Laravel Debugbar (Development)

### 1.3 Database Architecture
- [x] Create database: `cog_tor_system`
- [x] Configure `.env` file
- [x] Create 12 migration files:
  - school_years
  - semesters
  - departments
  - courses
  - subjects
  - students
  - enrollments
  - grades
  - grade_submissions
  - cog_records
  - tor_records
  - add_role_and_status_to_users
- [x] Run initial migrations (Laravel + Spatie tables)

### 1.4 Frontend Setup
- [x] Install NPM packages
- [x] Configure Tailwind CSS
- [x] Build assets with Vite
- [x] Test development server

**Deliverables:**
- ✅ Working Laravel 10 installation
- ✅ 21 database tables (9 default + 12 custom)
- ✅ All packages installed and configured
- ✅ Frontend assets compiled

---

## 📅 PHASE 2: MODELS & SEEDERS (CURRENT)

**Duration:** ~2 hours  
**Status:** 📅 In Progress (0%)  
**Started:** February 15, 2026

### 2.1 Create Eloquent Models
- [ ] SchoolYear model
  - Relationships: hasMany(Semester)
  - Methods: isActive(), isCurrent()
- [ ] Semester model
  - Relationships: belongsTo(SchoolYear), hasMany(Enrollment)
  - Methods: isActive(), getFullName()
- [ ] Department model
  - Relationships: hasMany(Course)
- [ ] Course model
  - Relationships: belongsTo(Department), hasMany(Subject), hasMany(Student)
- [ ] Subject model
  - Relationships: belongsTo(Course), hasMany(Enrollment)
- [ ] Student model
  - Relationships: belongsTo(Course), hasMany(Enrollment), hasMany(Grade)
  - Methods: getFullName(), isActive()
- [ ] Enrollment model
  - Relationships: belongsTo(Student), belongsTo(Subject), belongsTo(Semester), belongsTo(User as enrolledBy), hasOne(Grade)
- [ ] Grade model
  - Relationships: belongsTo(Enrollment), belongsTo(User as faculty), hasOne(GradeSubmission)
  - Methods: isPending(), isApproved(), isFinalized()
- [ ] GradeSubmission model
  - Relationships: belongsTo(Grade), belongsTo(User as submittedBy), belongsTo(User as reviewedBy), belongsTo(User as finalizedBy)
- [ ] CogRecord model
  - Relationships: belongsTo(Student), belongsTo(Semester), belongsTo(User as generatedBy)
- [ ] TorRecord model
  - Relationships: belongsTo(Student), belongsTo(User as generatedBy)

### 2.2 Modify User Model
- [ ] Add role helper methods:
  - `isAdmin()`
  - `isFaculty()`
  - `isDean()`
  - `isRegistrar()`
- [ ] Add status helper methods:
  - `isActive()`
  - `isPending()`
  - `isInactive()`
- [ ] Add relationships:
  - `hasMany(Enrollment, 'enrolled_by')`
  - `hasMany(Grade, 'faculty_id')`
  - `hasMany(GradeSubmission, 'submitted_by')`
  - `hasMany(CogRecord, 'generated_by')`
  - `hasMany(TorRecord, 'generated_by')`

### 2.3 Create Database Seeders
- [ ] RoleSeeder
  - Create 4 roles: admin, faculty, dean, registrar
  - Define permissions per role
- [ ] UserSeeder
  - Create test accounts:
    - Admin: admin@cogtor.test / password
    - Faculty: faculty@cogtor.test / password
    - Dean: dean@cogtor.test / password
    - Registrar: registrar@cogtor.test / password
- [ ] AcademicStructureSeeder
  - 1 School Year: 2024-2025
  - 2 Semesters: 1st Semester, 2nd Semester
  - 3 Departments: CCS, COE, COED
  - 5 Courses: BSIT, BSCS, BSCpE, BSEE, BSED
- [ ] SubjectSeeder
  - Create 10 sample subjects across different courses
- [ ] StudentSeeder
  - Create 20 sample students across different courses

### 2.4 Run Migrations & Seeders
- [ ] Run all custom migrations: `php artisan migrate`
- [ ] Verify all tables created successfully
- [ ] Run seeders: `php artisan db:seed`
- [ ] Verify test accounts login successfully

**Commands to Run:**
```bash
# Copy migration files to database/migrations/
# Then run:
php artisan migrate

# Create models
php artisan make:model SchoolYear
php artisan make:model Semester
php artisan make:model Department
php artisan make:model Course
php artisan make:model Subject
php artisan make:model Student
php artisan make:model Enrollment
php artisan make:model Grade
php artisan make:model GradeSubmission
php artisan make:model CogRecord
php artisan make:model TorRecord

# Create seeders
php artisan make:seeder RoleSeeder
php artisan make:seeder UserSeeder
php artisan make:seeder AcademicStructureSeeder
php artisan make:seeder SubjectSeeder
php artisan make:seeder StudentSeeder

# Run seeders
php artisan db:seed
```

**Deliverables:**
- ✅ 11 Eloquent models with relationships
- ✅ Modified User model with role helpers
- ✅ 5 seeders with test data
- ✅ 4 working test accounts
- ✅ Sample academic data

---

## 📅 PHASE 3: AUTHENTICATION & AUTHORIZATION

**Duration:** ~2 hours  
**Status:** 📅 Planned (0%)  
**Estimated Start:** After Phase 2

### 3.1 Middleware Creation
- [ ] Create `CheckRole` middleware
  - Verify user has required role
  - Abort with 403 if unauthorized
- [ ] Create `CheckStatus` middleware
  - Block pending/inactive users
  - Display appropriate error message
- [ ] Register middleware in `Kernel.php`

### 3.2 Authentication Controllers
- [ ] Modify RegisteredUserController
  - Set default role to 'faculty'
  - Set default status to 'pending'
  - Prevent auto-login until approved
- [ ] Modify AuthenticatedSessionController
  - Check user status before login
  - Implement role-based redirects:
    - Admin → `/admin/dashboard`
    - Faculty → `/faculty/dashboard`
    - Dean → `/dean/dashboard`
    - Registrar → `/registrar/dashboard`
- [ ] Modify RedirectIfAuthenticated
  - Redirect logged-in users to their dashboard

### 3.3 Route Protection
- [ ] Create Admin route group (`/admin/*`)
  - Middleware: `auth`, `status`, `role:admin`
- [ ] Create Faculty route group (`/faculty/*`)
  - Middleware: `auth`, `status`, `role:faculty`
- [ ] Create Dean route group (`/dean/*`)
  - Middleware: `auth`, `status`, `role:dean`
- [ ] Create Registrar route group (`/registrar/*`)
  - Middleware: `auth`, `status`, `role:registrar`

### 3.4 Dashboard Controllers
- [ ] AdminController
  - Show system stats (users, students, subjects)
- [ ] FacultyController
  - Show assigned subjects
  - Show grade submission status
- [ ] DeanController
  - Show pending grade approvals
  - Show enrolled students count
- [ ] RegistrarController
  - Show finalized grades count
  - Show recent COG/TOR generations

### 3.5 Basic Dashboard Views
- [ ] Create `admin/dashboard.blade.php`
- [ ] Create `faculty/dashboard.blade.php`
- [ ] Create `dean/dashboard.blade.php`
- [ ] Create `registrar/dashboard.blade.php`

### 3.6 Testing
- [ ] Test Admin login and redirect
- [ ] Test Faculty login and redirect
- [ ] Test Dean login and redirect
- [ ] Test Registrar login and redirect
- [ ] Test pending user login block
- [ ] Test unauthorized role access (403)

**Deliverables:**
- ✅ 2 custom middleware (CheckRole, CheckStatus)
- ✅ Modified authentication controllers
- ✅ 4 protected route groups
- ✅ 4 dashboard controllers
- ✅ 4 basic dashboard views
- ✅ Role-based authentication working

---

## 📅 PHASE 4: ADMIN MODULE

**Duration:** ~4 hours  
**Status:** 📅 Planned (0%)

### 4.1 User Management
- [ ] User CRUD interface
  - List all users (Faculty, Dean, Registrar)
  - Create new user
  - Edit user details
  - Deactivate/activate user
  - Delete user (soft delete)
- [ ] Approve pending faculty registrations
  - List pending teachers
  - Approve button (sets status to 'active')
  - Reject button (sets status to 'inactive')
  - Email notification on approval

### 4.2 Academic Structure Management
- [ ] School Year CRUD
  - Create new school year
  - Set active school year
  - Mark completed school years
- [ ] Semester Management
  - Create semesters under school year
  - Set active semester
  - Configure semester dates
- [ ] Department CRUD
  - Add/edit/delete departments
  - Manage department status
- [ ] Course CRUD
  - Add courses under departments
  - Configure course duration (years)
  - Manage course status

### 4.3 Subject Catalog
- [ ] Subject CRUD
  - Create subjects under courses
  - Set subject code, name, units
  - Define year level (1, 2, 3, 4)
  - Import subjects from Excel
  - Export subject catalog to Excel

### 4.4 System Settings
- [ ] Configure school information (name, address)
- [ ] Upload school logo
- [ ] Set grading scale (1.0-5.0)
- [ ] Configure GWA computation rules

**Deliverables:**
- ✅ Complete user management interface
- ✅ Academic structure setup
- ✅ Subject catalog management
- ✅ Excel import/export for subjects
- ✅ System settings configuration

---

## 📅 PHASE 5: FACULTY MODULE

**Duration:** ~4 hours  
**Status:** 📅 Planned (0%)

### 5.1 View Assigned Subjects
- [ ] Dashboard shows subjects assigned by Dean
- [ ] Display subject details (code, name, units, semester)
- [ ] Show enrolled students per subject
- [ ] Filter by semester

### 5.2 Grade Encoding Interface
- [ ] Select subject to encode grades
- [ ] View enrolled students list
- [ ] Enter grades per student
  - Input: Percentage (0-100)
  - Auto-convert to Philippine scale (1.0-5.0)
- [ ] Save as draft (not submitted)
- [ ] Real-time validation (grade range 0-100)

### 5.3 Grade Submission
- [ ] Review all grades before submission
- [ ] Add general remarks (optional)
- [ ] Submit all grades to Dean
- [ ] View submission status:
  - Draft
  - Pending Dean Approval
  - Approved
  - Rejected (with Dean's remarks)

### 5.4 Grade Revision
- [ ] View rejected grades with Dean's feedback
- [ ] Edit and re-submit grades
- [ ] Track submission history

**Deliverables:**
- ✅ Subject assignment display
- ✅ Grade encoding interface
- ✅ Grade submission workflow
- ✅ Status tracking system
- ✅ Grade revision capability

---

## 📅 PHASE 6: DEAN MODULE

**Duration:** ~4 hours  
**Status:** 📅 Planned (0%)

### 6.1 Manage Faculty
- [ ] View all faculty under department
- [ ] Assign subjects to faculty
- [ ] Remove subject assignments
- [ ] View faculty workload

### 6.2 Student Management
- [ ] View all students in department
- [ ] Student CRUD operations
- [ ] Import students from Excel
- [ ] Export student list to Excel

### 6.3 Enrollment Management
- [ ] Enroll students to subjects
  - Select student
  - Select subject
  - Select semester
  - Validate: No duplicate enrollments
- [ ] Bulk enrollment (import from Excel)
- [ ] View enrollment status per student
- [ ] Drop/withdraw student from subject

### 6.4 Grade Review & Approval
- [ ] View pending grade submissions
- [ ] Display Faculty name, Subject, Date submitted
- [ ] Review grades per student
- [ ] Verify student enrollment status
- [ ] Approve grades:
  - Button: "Approve & Forward to Registrar"
  - Status changes to "Approved by Dean"
- [ ] Reject grades:
  - Button: "Reject & Return to Faculty"
  - Require remarks/feedback
  - Notify faculty via email
- [ ] Track approval history

### 6.5 Department Reports
- [ ] View all enrolled students
- [ ] View all submitted grades
- [ ] Filter by semester, subject, faculty
- [ ] Export reports to Excel

**Deliverables:**
- ✅ Faculty management interface
- ✅ Student enrollment system
- ✅ Grade review dashboard
- ✅ Approve/reject workflow
- ✅ Department reporting

---

## 📅 PHASE 7: REGISTRAR MODULE

**Duration:** ~5 hours  
**Status:** 📅 Planned (0%)

### 7.1 Receive Approved Grades
- [ ] View grades approved by Dean
- [ ] Display pending finalization queue
- [ ] Verify grade data completeness
- [ ] Finalize grades (store in official record)

### 7.2 Student Academic Records
- [ ] View complete grade history per student
- [ ] Display all semesters and subjects
- [ ] Show grade status (Finalized, Pending)
- [ ] Compute semester GWA
- [ ] Compute cumulative GWA

### 7.3 COG Generation (Certificate of Grades)
- [ ] Select student
- [ ] Select semester
- [ ] Retrieve finalized grades for that semester
- [ ] Auto-compute semester GWA
- [ ] Generate PDF with official format:
  - School logo and header
  - Student information
  - List of subjects with grades
  - Semester GWA
  - Date of issue
  - Registrar's signature
- [ ] Save PDF to storage
- [ ] Generate unique document number
- [ ] Store generation record in database
- [ ] Download/print COG

### 7.4 TOR Generation (Transcript of Records)
- [ ] Select student
- [ ] Retrieve ALL finalized grades across all semesters
- [ ] Auto-compute cumulative GWA
- [ ] Generate PDF with official format:
  - School logo and header
  - Student information
  - Complete academic record:
    - Grouped by school year and semester
    - All subjects with grades
    - Semester GWA per term
    - Cumulative GWA
  - Date of issue
  - Registrar's signature
- [ ] Save PDF to storage
- [ ] Generate unique document number
- [ ] Store generation record in database
- [ ] Download/print TOR

### 7.5 Document Management
- [ ] View all generated COG/TOR documents
- [ ] Filter by student, date, document type
- [ ] Re-generate/reprint documents
- [ ] Track document generation history
- [ ] Export document list to Excel

**Deliverables:**
- ✅ Grade finalization system
- ✅ COG generation with PDF
- ✅ TOR generation with PDF
- ✅ GWA computation (semester & cumulative)
- ✅ Document management interface

---

## 📅 PHASE 8: EXCEL IMPORT/EXPORT

**Duration:** ~3 hours  
**Status:** 📅 Planned (0%)

### 8.1 Excel Import Features
- [ ] Import students (bulk upload)
  - Template: student_number, first_name, last_name, course, year_level, email
  - Validation: Duplicate student numbers
  - Preview before import
  - Rollback on errors
- [ ] Import subjects (bulk upload)
  - Template: code, name, course, units, year_level
  - Validation: Duplicate subject codes
- [ ] Import enrollments (bulk upload)
  - Template: student_number, subject_code, semester
  - Validation: Student/subject exists
- [ ] Import grades (bulk upload)
  - Template: student_number, subject_code, grade
  - Validation: Enrollment exists

### 8.2 Excel Export Features
- [ ] Export student list
  - Columns: student_number, name, course, year_level, status
  - Filter by course, year_level
- [ ] Export subject catalog
  - Columns: code, name, course, units, year_level
- [ ] Export enrollment report
  - Columns: student_number, name, subject, semester, status
- [ ] Export grade report
  - Columns: student_number, name, subject, grade, status, faculty
- [ ] Export COG/TOR generation log
  - Columns: document_number, student, type, date_generated

### 8.3 Excel Templates
- [ ] Create downloadable Excel templates
- [ ] Include sample data in templates
- [ ] Add data validation rules
- [ ] Provide import instructions

**Deliverables:**
- ✅ 4 import features (students, subjects, enrollments, grades)
- ✅ 5 export features (various reports)
- ✅ Excel templates with validation
- ✅ Error handling and rollback

---

## 📅 PHASE 9: REPORTING & ANALYTICS

**Duration:** ~3 hours  
**Status:** 📅 Planned (0%)

### 9.1 Faculty Reports
- [ ] My subjects dashboard
- [ ] Grade submission summary
- [ ] Approval status tracking
- [ ] Rejection history with remarks

### 9.2 Dean Reports
- [ ] Department overview dashboard
- [ ] Faculty workload report
- [ ] Student enrollment statistics
- [ ] Grade approval tracking
- [ ] Pending submissions alert
- [ ] Monthly/semester reports

### 9.3 Registrar Reports
- [ ] System-wide statistics
- [ ] Graduation readiness report (students with completed grades)
- [ ] COG/TOR generation summary
- [ ] GWA distribution analysis
- [ ] Dean's list (honor students)

### 9.4 Activity Logs
- [ ] Grade change audit trail
  - Who changed what grade
  - Old value → New value
  - Timestamp
  - Reason/remarks
- [ ] User activity log
  - Login history
  - Critical actions (approve/reject, finalize)
- [ ] Export logs to Excel

### 9.5 Charts & Visualizations
- [ ] Grade distribution chart (bar graph)
- [ ] Enrollment trends (line graph)
- [ ] COG/TOR generation trends
- [ ] Department comparison

**Deliverables:**
- ✅ Role-specific dashboards
- ✅ Comprehensive reports
- ✅ Activity audit trail
- ✅ Charts and visualizations

---

## 📅 PHASE 10: UI/UX POLISH & TESTING

**Duration:** ~3 hours  
**Status:** 📅 Planned (0%)

### 10.1 UI/UX Improvements
- [ ] Consistent color scheme (Tailwind)
- [ ] Responsive design (mobile-friendly)
- [ ] Loading states (spinners, skeleton screens)
- [ ] Toast notifications (success/error messages)
- [ ] Confirmation modals (delete, approve, reject)
- [ ] Breadcrumb navigation
- [ ] Help tooltips
- [ ] Keyboard shortcuts guide

### 10.2 Form Validation
- [ ] Client-side validation (JavaScript)
- [ ] Server-side validation (Laravel)
- [ ] Display error messages clearly
- [ ] Highlight invalid fields

### 10.3 Email Notifications
- [ ] Faculty: Grade approval/rejection
- [ ] Dean: New grade submissions
- [ ] Registrar: Grades ready for finalization
- [ ] Admin: New faculty registrations

### 10.4 Testing
- [ ] Feature testing (grade workflow)
- [ ] Permission testing (unauthorized access)
- [ ] Database constraint testing
- [ ] Excel import/export testing
- [ ] PDF generation testing
- [ ] Manual testing of all workflows

### 10.5 Documentation
- [ ] User manual (PDF)
- [ ] Admin installation guide
- [ ] Deployment checklist
- [ ] Troubleshooting guide
- [ ] API documentation (if applicable)

### 10.6 Performance Optimization
- [ ] Database query optimization (N+1 prevention)
- [ ] Eager loading for relationships
- [ ] Index frequently queried columns
- [ ] Cache static data (departments, courses)

**Deliverables:**
- ✅ Polished UI/UX
- ✅ Comprehensive testing
- ✅ Email notifications
- ✅ Complete documentation
- ✅ Performance optimizations

---

## 🎯 Success Criteria

### Phase Completion Checklist
Each phase is considered complete when:
- [ ] All tasks in the phase are checked off
- [ ] Code is tested manually
- [ ] No critical bugs
- [ ] Committed to Git with proper message format
- [ ] CHANGELOG.md updated
- [ ] README.md updated (if needed)

### Project Completion Criteria
- [ ] All 10 phases complete
- [ ] All user roles can perform their tasks
- [ ] Grade workflow works end-to-end (Faculty → Dean → Registrar)
- [ ] COG and TOR can be generated successfully
- [ ] Excel import/export working
- [ ] Comprehensive testing passed
- [ ] Documentation complete
- [ ] Deployed to production (optional)

---

## 📊 Progress Tracking

**Current Status:**
- **Completed Phases:** 1 / 10
- **Overall Progress:** 10%
- **Estimated Time Remaining:** ~30 hours
- **Target Completion:** March 2026

**Next Steps:**
1. Copy migration files to `database/migrations/`
2. Run `php artisan migrate`
3. Create Eloquent models
4. Create database seeders
5. Run `php artisan db:seed`
6. Test login with seeded accounts

---

**Last Updated:** February 15, 2026  
**Next Milestone:** Complete Phase 2 - Models & Seeders
