# 📚 COG-TOR Academic Grading Management System

A comprehensive Academic Grading Management System built with Laravel 10, designed for educational institutions to manage grade submissions, approvals, and generate official academic documents (Certificate of Grades & Transcript of Records).

![Status](https://img.shields.io/badge/Status-In%20Development-yellow)
![Laravel](https://img.shields.io/badge/Laravel-10.x-red)
![PHP](https://img.shields.io/badge/PHP-8.4-blue)
![License](https://img.shields.io/badge/License-MIT-green)
![Progress](https://img.shields.io/badge/Progress-85%25-brightgreen)

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
- ✅ **Excel Import/Export** (Bulk student import, grade template download/upload)
- ✅ **Professional Login UI** (Split-screen design with custom SVG illustration)
- 📅 **Reporting & Analytics** (Planned — Phase 9)

---

## 🚀 Current Status: Phase 8 Complete ✅ | Phase 10 In Progress 🔄

**Completed:**
- ✅ Phase 1: Foundation Setup & Database Architecture
- ✅ Phase 2: Models & Seeders (11 models + test data)
- ✅ Phase 3: Authentication & Authorization (Role-based dashboards)
- ✅ Phase 4: Admin Module (Full CRUD — Users, Departments, Courses, Subjects, School Years, Semesters, Students)
- ✅ Phase 5: Faculty Module (Grade encoding & submission workflow)
- ✅ Phase 6: Dean Module (Grade review, approve & reject workflow)
- ✅ Phase 7: Registrar Module (Grade finalization, COG & TOR PDF generation)
- ✅ Phase 8: Excel Features (Bulk student import, grade template export/upload)
- 🔄 Phase 10: UI/UX & Testing (~30% — Login redesign + all dashboards done)

**Next Up: Phase 10 completion → Phase 9 Reporting**
- 🔄 End-to-end workflow testing
- 🔄 UI consistency pass across all modules
- 📅 Phase 9: Department reports, grade distribution, faculty tracking

See [CHANGELOG.md](CHANGELOG.md) for full development history.

---

## 📋 System Workflow

```
┌─────────────────────────────────────────────────────────────┐
│                    ADMIN DASHBOARD                          │
│  • Manage Users (Faculty, Dean, Registrar)                 │
│  • Setup Academic Structure (Courses, Subjects, SY/Sem)    │
│  • Manage Departments & Students                            │
│  • Bulk Student Import via Excel                            │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                   FACULTY DASHBOARD                         │
│  • View Assigned Subjects                                   │
│  • Encode Student Grades (manual or Excel upload)           │
│  • Download Grade Template (pre-filled with enrolled        │
│    students), fill offline, upload back                     │
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
- **MySQL 8.0** - Database (via XAMPP / MariaDB 10.4.32)
- **PHP 8.4.11** - Programming Language

### Frontend
- **Blade Templates** - Laravel's templating engine
- **Tailwind CSS** - Utility-first CSS framework
- **Alpine.js** - Minimal JavaScript framework
- **Vite** - Modern build tool
- **Google Fonts** — Playfair Display + DM Sans (login UI)
- **Font Awesome 6.5.1** — Icon library (login UI)

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
   - Pending: `pending@cogtor.test` / `password` (cannot login — blocked by status middleware)

8. **Build assets**
   ```bash
   npm run build
   ```

9. **Start development server**
   ```bash
   php artisan serve
   ```

Visit: `http://localhost:8000` — automatically redirects to login page.

---

## 👥 User Roles

### Admin
- Full system access
- Manage users (Faculty, Dean, Registrar) — create, edit, approve, reject, delete
- Setup academic structure (Departments, Courses, Subjects)
- Configure school years and semesters (set active)
- Manage student master list
- Bulk import students via Excel template
- Export all students to Excel

### Faculty
- View assigned subjects
- Encode student grades (manual entry or Excel upload)
- Download pre-filled grade template per subject
- Submit grades to Dean for approval
- View submission status and Dean rejection remarks

### Dean
- Manage faculty and students
- Enroll students to subjects/sections
- Review submitted grades
- Approve or reject grades (with remarks on rejection)
- Forward approved grades to Registrar

### Registrar
- Receive approved grades from Dean
- Finalize and store official grades
- Generate COG (Certificate of Grades) per semester
- Generate TOR (Transcript of Records) full academic history
- Compute GWA (General Weighted Average) automatically
- Print/download official PDF documents

---

## 📊 Database Structure

### Core Tables (22 Total)

**Academic Structure:**
- `school_years` - Academic year tracking (2024-2025, etc.)
- `semesters` - Semester management (1st Sem, 2nd Sem, Summer)
- `departments` - Academic departments (CCS, COE, COED)
- `courses` - Degree programs (BSIT, BSCS, etc.)
- `subjects` - Subject catalog with units, year level, and faculty assignment

**Student Management:**
- `students` - Master student list with course enrollment
- `enrollments` - Student-to-subject enrollment records

**Grade Management:**
- `grades` - Final grades per enrollment (status: pending → approved_by_dean → finalized)
- `grade_submissions` - Workflow tracking (Faculty → Dean → Registrar)

**Document Generation:**
- `cog_records` - Certificate of Grades snapshots with PDF path
- `tor_records` - Transcript of Records snapshots with PDF path

**Laravel/Spatie Tables:**
- `users` - Authentication + roles
- `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` (Spatie)
- `activity_log` - Audit trail (Spatie Activity Log)

---

## 🧮 Grading System

### Philippine Grade Scale (1.0–5.0)
| Grade | Percentage Range | Description |
|-------|-----------------|-------------|
| 1.00 | 97–100% | Excellent |
| 1.25 | 94–96% | — |
| 1.50 | 91–93% | — |
| 1.75 | 88–90% | — |
| 2.00 | 85–87% | Very Good |
| 2.25 | 82–84% | — |
| 2.50 | 79–81% | — |
| 2.75 | 76–78% | — |
| 3.00 | 75% | Passing |
| 5.00 | Below 75% | Failed |

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

## 📁 Excel Features

### Admin — Student Import/Export
- **Download Template** — CSV with correct headers and 1 sample row
- **Export Students** — Styled `.xlsx` with all current student records
- **Import Students** — Upload filled CSV/Excel to bulk-create students with validation

### Faculty — Grade Template
- **Download Grade Template** — Pre-filled `.xlsx` per subject with enrolled students (Columns A–E: student info, Column F: percentage to fill)
- **Upload Grades** — Upload filled template to auto-save grades to database (converts percentage → Philippine grade scale)

---

## 📈 Development Roadmap

| Phase | Status | Description |
|-------|--------|-------------|
| **Phase 1** | ✅ Complete | Foundation + Database Architecture |
| **Phase 2** | ✅ Complete | Models, Seeders, Relationships |
| **Phase 3** | ✅ Complete | Authentication & Authorization |
| **Phase 4** | ✅ Complete | Admin Module (Full CRUD + Student Management) |
| **Phase 5** | ✅ Complete | Faculty Module (Grade Encoding + Excel Upload) |
| **Phase 6** | ✅ Complete | Dean Module (Grade Review & Approval) |
| **Phase 7** | ✅ Complete | Registrar Module (COG/TOR PDF Generation) |
| **Phase 8** | ✅ Complete | Excel Import/Export (Admin + Faculty) |
| **Phase 9** | 📅 Planned | Reporting & Analytics |
| **Phase 10** | 🔄 In Progress | UI/UX Polish & Testing (~30%) |

**Overall Progress: ~85%**

---

## 🎉 Recent Achievements

### Phase 8 Completed! ✅ (February 26, 2026)
- 6 new files: 2 Exports, 2 Imports, 2 Excel Controllers
- 5 new routes registered and verified
- Bulk student import with validation
- Faculty grade template — download pre-filled, upload grades back
- Route ordering conflict fixed (static before wildcard)
- `student_id` vs `student_number` bug resolved

### Phase 10 UI/UX — Login & Dashboards Done ✅ (February 26, 2026)
- Root `/` route now redirects to login (no more Laravel welcome page)
- Login page fully redesigned — split-screen layout, custom SVG document illustration, Playfair Display typography, Font Awesome icons, password toggle, card with layered shadow and gold accent stripe
- All 4 role dashboards rewritten with full navigation coverage and improved stats

**What's Working Right Now:**
- ✅ Professional login page — production-grade design
- ✅ Role-based access control — all 4 roles with proper middleware
- ✅ Complete grade workflow — Faculty encodes → Dean approves → Registrar finalizes → PDF downloads
- ✅ COG & TOR PDF generation — Eastern Samar State University - Guiuan Campus header
- ✅ Excel bulk student import/export
- ✅ Excel grade template for faculty
- ✅ All dashboards with full navigation and stats

---

## ⚠️ Known Issues

| Issue | Severity | Status |
|-------|----------|--------|
| `dean_action` vs `approved_by_dean` status sync | Medium | 📅 To Fix |
| No notification system (Faculty not alerted on rejection) | Medium | 📅 To Fix |
| No student portal (students cannot view own grades) | Low | 📅 Planned |
| Audit log installed but no UI to view it | Low | 📅 Planned |
| No document request workflow | Low | 📅 Planned |
| PDF storage not publicly accessible (needs `storage:link`) | High for Production | 📅 To Fix before deploy |

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
- **Last Updated:** February 26, 2026
- **Current Version:** 1.0.0-alpha
- **Current Phase:** Phase 10 (UI/UX & Testing) + Phase 9 up next
- **Database Tables:** 22
- **Models:** 11 (+ User)
- **Middleware:** 2 custom (CheckRole, CheckStatus)
- **Controllers:** 15+ across all 4 role modules
- **Routes:** 105+ registered
- **Views:** 30+ Blade views
- **Test Accounts:** 5
- **Sample Students:** 10
- **Sample Subjects:** 10

---

## 🔒 Security Considerations

- [ ] Change default seeder passwords before production
- [ ] Enable rate limiting on login attempts
- [ ] Implement CSRF protection (enabled by default ✅)
- [ ] Use HTTPS in production
- [ ] Validate all Excel file uploads (✅ done in import classes)
- [ ] Environment variable encryption
- [ ] Regular database backups
- [ ] Run `php artisan storage:link` and review PDF access control before production deploy

---

## 📞 Support

For questions or issues:
- **GitHub:** https://github.com/gitpushfrances/cog-tor-system
- **Documentation:** See CHANGELOG.md for full development history

---

**Last Updated:** February 26, 2026  
**Maintained By:** Frances Igop  
**Institution:** Eastern Samar State University — Guiuan Campus
