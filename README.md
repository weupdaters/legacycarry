# Legacy Carry — Society Gate Management & Service Marketplace Backend

[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![Payment Gateway](https://img.shields.io/badge/Payments-Cashfree-green.svg)](https://www.cashfree.com)
[![Notifications](https://img.shields.io/badge/FCM-Firebase-orange.svg)](https://firebase.google.com)

---

## 📌 Project Overview
**Legacy Carry** is an enterprise-grade backend platform built with **Laravel 12**. It integrates two core systems:
1. **Gated Society & Gate Security Management** (similar to MyGate): Digital gate passes, visitor logs, vehicle tracking, society notices, amenity slot bookings, community polls, and helpdesk support tickets.
2. **On-Demand Service Provider Marketplace with Escrow**: Residents hire verified daily-wage laborers/service providers, secure payments via Cashfree escrow, track working hours with timers, and enforce entry/exit verification via guard QR code scanners.

---

## 🗂️ Clean Project Directory Structure

```text
legacy_carry/
├── app/
│   ├── Console/Commands/       # Cron & CLI sync commands (Chat sync, Cashfree payouts)
│   ├── Exports/                # Excel/CSV export classes for admin reports
│   ├── Helpers/                # Custom helper functions (FCM, permissions, auditing)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Web dashboard controllers for platform admins
│   │   │   ├── Api/            # Mobile app RESTful JSON API controllers
│   │   │   └── Member/         # Society member & resident web controllers
│   │   └── Middleware/         # Permission checks, superadmin guard, auth filters
│   ├── Models/                 # Eloquent ORM database entities
│   ├── Providers/              # Application service providers & route bindings
│   ├── Services/               # Business logic services (Cashfree, Firebase, SMS, etc.)
│   └── Traits/                 # Reusable model & query traits
├── bootstrap/                  # Framework bootstrapping & package caching
├── config/                     # Configuration files (app, database, services, cashfree)
├── database/
│   ├── factories/              # Database testing model factories
│   ├── migrations/             # Timestamped schema migrations
│   └── seeders/                # Initial master data seeders
├── docs/                       # Project architecture, API specs & walkthroughs
│   ├── API_DOCUMENTATION_AMENITIES_POLLS.md
│   ├── API_DOCUMENTATION_TICKETS.md
│   ├── PROJECT_WALKTHROUGH.md
│   ├── QA_REPORT.md
│   └── SYSTEM_ARCHITECTURE.md
├── public/                     # Web server DocumentRoot (index.php, assets, error pages)
│   ├── assets/                 # Admin panel styles, scripts, and libraries
│   ├── frontend/               # Public frontend client assets
│   ├── 404.html & 502.html     # Web server direct fallback error pages
│   ├── googlebda8f43ad61625b3.html # Google Search Console verification
│   └── robots.txt              # Search engine crawler configuration
├── resources/
│   ├── css/ & js/              # Frontend build inputs (Vite)
│   └── views/
│       ├── admin/              # Super admin Blade dashboards & views
│       ├── auth/               # Authentication Blade views
│       ├── errors/             # Custom 404/500/502 Blade error templates
│       ├── member/             # Resident & member web portal views
│       └── users/              # Service provider & employee views
├── routes/
│   ├── admin.php               # Super admin web routes
│   ├── api.php                 # Mobile & client RESTful API endpoints
│   ├── console.php             # Artisan scheduled commands
│   └── web.php                 # Public web portal routes
├── storage/                    # Uploads, compiled views, session logs, and cache
└── tests/                      # Feature & unit automated test suites
```

---

## 🚀 Key Modules & End-to-End Workflow

| Stage | Actor | Action | System Response |
| :---: | :---: | :--- | :--- |
| **1** | Resident | Posts a service requirement (`JobPost`) | Job becomes discoverable by local service providers. |
| **2** | Worker | Applies with quote (`JobApplication`) | Resident is alerted of applicant's proposal. |
| **3** | Resident | Hires worker & pays **Advance** | Advance goes into **Admin Escrow** via Cashfree. System generates `Appointment` + `entry_token`. Worker gets FCM push. |
| **4** | Guard | Scans worker's `entry_token` QR at Gate | Checks advance payment. Validates token. Logs `guard_in_time`. Alerts resident: *"Worker has entered"*. |
| **5** | Worker | Executes service & tracks timer | Resident monitors progress. Worker marks job completed. |
| **6** | Resident | Approves job & pays **Final Balance** | Cashfree settles remaining fee to Worker. Generates secure `exit_token`. Worker gets FCM notification. |
| **7** | Guard | Scans worker's `exit_token` QR at Gate | Validates exit clearance. Logs `guard_out_time`. Appointment marked completed. |

---

## 🛠️ Setup & Installation

1. **Clone the repository:**
   ```bash
   git clone <repo_url> legacy_carry
   cd legacy_carry
   ```

2. **Install Composer dependencies:**
   ```bash
   composer install
   ```

3. **Configure Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Update `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, Cashfree API keys, and Firebase service account credentials in `.env`.*

4. **Run Database Migrations:**
   ```bash
   php artisan migrate
   ```

5. **Start Local Development Server:**
   ```bash
   php artisan serve
   ```

---

## 📚 Detailed Documentation
- [System Architecture & Lifecycle Blueprint](docs/SYSTEM_ARCHITECTURE.md)
- [End-to-End Walkthrough & Roles Guide](docs/PROJECT_WALKTHROUGH.md)
- [Amenities & Polls API Documentation](docs/API_DOCUMENTATION_AMENITIES_POLLS.md)
- [Tickets & Helpdesk API Documentation](docs/API_DOCUMENTATION_TICKETS.md)
- [Quality Assurance & Audit Report](docs/QA_REPORT.md)
