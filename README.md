# UNESCO-LUMINA-X

> **Universal Scholastic Grant & Education Equity Platform**  
> An end-to-end web platform engineered to automate student scholarship intake, multi-step document verification, administrative appraisal, sanction disbursement calculation, and real-time status reporting.

---

## 🌟 Executive Summary

**UNESCO LUMINA-X** democratizes access to higher education through a secure, transparent grant disbursement protocol for eligible scholars. The system is bifurcated into two primary operational portals:

1. **Scholar / Student Portal** (`Scholarship-Management-System/`)
   - Self-service onboarding and registration
   - Multi-step application wizard (Personal, College, Hostel, Bank, Document Attachments)
   - Real-time application preview and status tracking
   - Automated PDF Acknowledgement generator (FPDF)

2. **Sovereign Administrative Command Center** (`Scholarship-Management-System/gov/`)
   - Centralized administrative dashboard for document inspection
   - Eligibility validation and financial sanction calculations
   - Automated transactional email dispatches via PHPMailer (Gmail SMTP)
   - Status updates, archival, and reporting

---

## 📂 Project Architecture

```text
UNESCO-LUMINA-X/
├── Scholarship-Management-System/   # Core Application Portal (PHP / MySQL)
│   ├── css/                        # Lumina Glassmorphism Design System (lumina-theme.css)
│   ├── js/                         # Lumina HTTP Client & Auth Guard scripts
│   ├── gov/                        # Sovereign Authority Command Center & Admin APIs
│   │   ├── upload/                 # Secure document storage
│   │   ├── phpmailer/              # Transactional email service
│   │   ├── fpdf/                   # Administrative PDF generation engine
│   │   └── data.php                # Master verification & sanction console
│   ├── font/                       # Typography assets
│   ├── connect.php                 # Database connectivity config
│   ├── index.php                   # Public landing portal
│   ├── home.php                    # Scholar dashboard & application status
│   ├── login.php                   # Scholar authentication
│   └── registration.php            # Scholar onboarding
├── workflow/                       # Comprehensive Architecture & System Specifications
│   ├── README.md                   # System workflow index & high-level architecture
│   ├── frontend_workflow.md        # UI/UX design tokens & client lifecycle
│   ├── backend_workflow.md         # PHP processing engine & session pipeline
│   ├── database_workflow.md        # Relational schema, tables & data dictionary
│   ├── end_to_end_workflow.md      # DFDs, sequence diagrams & state machines
│   ├── frontend_data_integration_workflow.md # Frontend-backend data contract
│   └── security_auth_workflow.md   # Authentication & session security matrix
└── README.md                       # Project root documentation
```

---

## 🚀 Getting Started

### Prerequisites
- **Web Server:** Apache (via XAMPP, WampServer, or LAMP stack)
- **PHP:** Version 7.4 or 8.x
- **Database:** MariaDB / MySQL 5.7+
- **Extensions:** `mysqli`, `curl`, `openssl`, `mbstring`

### Database Setup
1. Start MySQL via XAMPP or your database manager.
2. Create a database named `scholarship`:
   ```sql
   CREATE DATABASE scholarship;
   ```
3. Import the database schema referenced in [`workflow/database_workflow.md`](workflow/database_workflow.md).
4. Verify database connection credentials in:
   - `Scholarship-Management-System/connect.php`
   - `Scholarship-Management-System/gov/connect.php`

### Launching the Application
1. Place the project inside your web root (e.g., `htdocs/` for XAMPP).
2. Start Apache and MySQL services.
3. Access the portal in your browser:
   - **Student Portal:** `http://localhost/Scholarship-Management-System/index.php`
   - **Admin Command Center:** `http://localhost/Scholarship-Management-System/gov/login.php`

---

## 🛡️ Security & Architecture Documentation

For complete diagrams, DFDs, ER models, and workflow specifications, explore the [`workflow/`](workflow/) directory:
- [Workflow Architecture Overview](workflow/README.md)
- [Frontend Workflow & UI Design System](workflow/frontend_workflow.md)
- [Backend Architecture & Request Lifecycles](workflow/backend_workflow.md)
- [Database Schema & Data Dictionary](workflow/database_workflow.md)
- [End-to-End System Integration](workflow/end_to_end_workflow.md)
- [Security & Authentication Architecture](workflow/security_auth_workflow.md)

---

## 📄 License
This project is licensed under the academic / open scholastic framework. See repository files for specific terms.
