# 📚 COG-TOR Academic Grading Management System

A comprehensive Academic Grading Management System built with Laravel 10, designed for educational institutions to manage grade submissions, approvals, and generate official academic documents (Certificate of Grades & Transcript of Records).

![Status](https://img.shields.io/badge/Status-In%20Development-yellow)
![Laravel](https://img.shields.io/badge/Laravel-10.x-red)
![PHP](https://img.shields.io/badge/PHP-8.4-blue)
![License](https://img.shields.io/badge/License-MIT-green)

---

## 🎯 Project Overview

This system streamlines the academic grading workflow with a multi-tier approval system: **Faculty → Dean → Registrar**, automated GWA computation, and official document generation (COG/TOR).

### Key Features
- ✅ **Four-tier Role System** (Admin → Faculty → Dean → Registrar)
- ✅ **Grade Submission Workflow** (Faculty submits → Dean approves → Registrar finalizes)
- ✅ **Student Enrollment Management** (Dean enrolls students to subjects)
- ✅ **Automated GWA Calculation** (Semester & cumulative)
- ✅ **COG & TOR Generation** (PDF documents with official formatting)
- ✅ **Complete Audit Trail** (Track all grade changes and approvals)
- ✅ **Excel Import/Export** (Batch operations & reports)

---

## 🚀 Current Status: Phase 3 Complete ✅

**Completed:**
- ✅ Phase 1: Foundation Setup & Database Architecture
- ✅ Phase 2: Models & Seeders (11 models + test data)
- ✅ Phase 3: Authentication & Authorization (Role-based dashboards)

**Next Up: Phase 4 - Admin Module**
- 📅 User Management (CRUD)
- 📅 Department/Course/Subject Management
- 📅 School Year & Semester Setup

See [PROGRESS.md](PROGRESS.md) for detailed roadmap and [CHANGELOG.md](CHANGELOG.md) for development history.

---

## 📋 System Workflow

```
┌─────────────────────────────────────────────────────────────┐
│                    ADMIN DASHBOARD                          │
│  • Manage Users (Faculty, Dean, Registrar)                 │
│  • Setup Academic Structure (Courses, Subjects, SY/Sem)    │
│  • Manage Departments                                       │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                   FACULTY DASHBOARD                         │
│  • View Assigned Subjects                                   │
│  • Encode Student Grades                                    │
│  • Submit Grades to Dean                                    │
│  Status: "Pending Dean Approval"                           │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    DEAN DASHBOARD                           │
│  • Manage Faculty & Students                                │
│  • Enroll Students to Subjects                              │
│  • Review Submitted Grades                                  │
│  • Validate Enrollment Status                               │
│  • Approve/Reject Grades (with remarks)                     │
│  • Forward Approved Grades to Registrar                     │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                  REGISTRAR DASHBOARD                        │
│  • Receive Approved Grades                                  │
│  • Store in Official Database                               │
│  • Update Student Academic Records                          │
│  • Generate COG (Certificate of Grades)                     │
│  • Generate TOR (Transcript of Records)                     │
│  • Compute GWA (General Weighted Average)                   │
│  • Print/Download Official Documents                        │
└─────────────────────────────────────────────────────────────┘
```

---

## 🛠️ Tech Stack

### Backend
- **Laravel 10 LTS** - PHP Framework
- **MySQL 8.0** - Database
- **PHP 8.4.11** - Programming Language

### Frontend
- **Blade Templates** - Laravel's templating engine
- **Tailwind CSS** - Utility-first CSS framework
- **Alpine.js** - Minimal JavaScript framework
- **Vite** - Modern build tool

### Key Packages
| Package | Purpose |
|---------|---------|
| Laravel Breeze | Authentication scaffolding |
| Spatie Permission | Role & permission management |
| Maatwebsite Excel | Excel import/export |
| Spatie Activity Log | Audit trail logging |
| Laravel DomPDF | PDF generation (COG/TOR) |
| Laravel Debugbar | Development debugging |

---

## 📦 Installation

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js 18+ & NPM
- MySQL 8.0+
- XAMPP/WAMP/Laragon (or equivalent)

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
   - Update `.env` with your database credentials:
     ```env
     DB_DATABASE=cog_tor_system
     DB_USERNAME=root
     DB_PASSWORD=
     ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed database**
   ```bash
   php artisan db:seed
   ```
   
   **Test Accounts Created:**
   - Admin: `admin@cogtor.test` / `password`
   - Dean: `dean@cogtor.test` / `password`
   - Faculty: `faculty@cogtor.test` / `password`
   - Registrar: `registrar@cogtor.test` / `password`
   - Pending: `pending@cogtor.test` / `password` (cannot login)

8. **Build assets**
   ```bash
   npm run build
   ```

9. **Start development server**
   ```bash
   php artisan serve
   ```

Visit: `http://localhost:8000`

---

## 👥 User Roles

### Admin
- Full system access
- Manage users (Faculty, Dean, Registrar)
- Setup academic structure (Departments, Courses, Subjects)
- Configure school years and semesters
- View all system activities

### Faculty
- View assigned subjects
- Encode student grades
- Submit grades to Dean for approval
- View submission status
- Receive feedback/remarks from Dean

### Dean
- Manage faculty and students
- Enroll students to subjects/sections
- Review submitted grades
- Approve or reject grades (with remarks)
- Forward approved grades to Registrar
- View department reports

### Registrar
- Receive approved grades from Dean
- Finalize and store official grades
- Generate COG (Certificate of Grades)
- Generate TOR (Transcript of Records)
- Compute GWA (General Weighted Average)
- Print/download official documents

---

## 📊 Database Structure

### Core Tables (21 Total)

**Academic Structure:**
- `school_years` - Academic year tracking (2024-2025, etc.)
- `semesters` - Semester management (1st Sem, 2nd Sem, Summer)
- `departments` - Academic departments (CCS, COE, COED)
- `courses` - Degree programs (BSIT, BSCS, etc.)
- `subjects` - Subject catalog with units and year level

**Student Management:**
- `students` - Master student list with course enrollment
- `enrollments` - Student-to-subject enrollment records

**Grade Management:**
- `grades` - Final grades per enrollment
- `grade_submissions` - Workflow tracking (Faculty → Dean → Registrar)

**Document Generation:**
- `cog_records` - Certificate of Grades snapshots
- `tor_records` - Transcript of Records snapshots

**Laravel/Spatie Tables:**
- `users` - Authentication + roles
- `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` (Spatie)
- `activity_log` - Audit trail

---

## 🧮 Grading System

### Philippine Grade Scale (1.0-5.0)
- **1.00** = 97-100% (Excellent)
- **1.25** = 94-96%
- **1.50** = 91-93%
- **1.75** = 88-90%
- **2.00** = 85-87% (Very Good)
- **2.25** = 82-84%
- **2.50** = 79-81%
- **2.75** = 76-78%
- **3.00** = 75% (Passing)
- **5.00** = Below 75% (Failed)

### GWA Computation
**Semester GWA:**
```
GWA = Σ(Grade × Units) / Σ(Units)
```

**Cumulative GWA:**
```
Cumulative GWA = Σ(Semester GWA × Total Semester Units) / Σ(Total Units)
```

---

## 📈 Development Roadmap

| Phase | Status | Description |
|-------|--------|-------------|
| **Phase 1** | ✅ Complete | Foundation + Database Architecture |
| **Phase 2** | ✅ Complete | Models, Seeders, Relationships |
| **Phase 3** | ✅ Complete | Authentication & Authorization |
| **Phase 4** | 📅 Next | Admin Module (User & Academic Setup) |
| **Phase 5** | 📅 Planned | Faculty Module (Grade Encoding) |
| **Phase 6** | 📅 Planned | Dean Module (Grade Review & Approval) |
| **Phase 7** | 📅 Planned | Registrar Module (COG/TOR Generation) |
| **Phase 8** | 📅 Planned | Excel Import/Export |
| **Phase 9** | 📅 Planned | Reporting & Analytics |
| **Phase 10** | 📅 Planned | UI/UX Polish & Testing |

**Overall Progress:** 30%

---

## 🎉 Recent Achievements

### Phase 3 Completed! ✅
- 2 custom middleware (CheckRole, CheckStatus)
- Role-based login redirects
- 4 dashboard controllers with real-time stats
- 4 responsive dashboard views
- Complete route protection
- Tested authentication workflow

**What's Working:**
- ✅ Role-based access control (Admin, Dean, Faculty, Registrar)
- ✅ User status validation (pending/inactive blocking)
- ✅ Dashboard stats and tables
- ✅ Automatic session invalidation for blocked users
- ✅ 403 Forbidden errors for unauthorized access
- ✅ All test accounts functional

---

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/phase-X-description`)
3. Commit your changes (`git commit -m '[PHASE-X] Description'`)
4. Push to the branch (`git push origin feature/phase-X-description`)
5. Open a Pull Request

### Commit Message Format
```
[PHASE-X] Brief description

- Detail 1
- Detail 2
```

---

## 📈 Project Stats

- **Started:** February 15, 2026
- **Current Version:** 1.0.0-alpha
- **Current Phase:** Phase 4 (Admin Module)
- **Database Tables:** 21
- **Models:** 11 (+ User)
- **Middleware:** 2 custom
- **Controllers:** 4 dashboard controllers
- **Views:** 4 dashboard views
- **Test Accounts:** 5
- **Sample Students:** 10
- **Sample Subjects:** 10

---

## 🔒 Security Considerations

- [ ] Change default seeder passwords before production
- [ ] Enable rate limiting on login attempts
- [ ] Implement CSRF protection (enabled by default)
- [ ] Use HTTPS in production
- [ ] Validate all Excel file uploads
- [ ] Environment variable encryption
- [ ] Regular database backups

---

## 📞 Support

For questions or issues:
- **GitHub:** https://github.com/gitpushfrances/cog-tor-system
- **Documentation:** See PROGRESS.md and CHANGELOG.md

---

**Last Updated:** February 15, 2026 - 5:30 PM  
**Maintained By:** Frances Igop
