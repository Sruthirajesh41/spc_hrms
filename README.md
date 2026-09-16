# SPC Universal — HR Management Module

A working Laravel 12 build of the HR Management Module proposal: real database, real
forms, real role-based access — not a layout stub. Seeded with a genuine 10-person
org (HR, Sales, Operations) and a full history of attendance, leave, payroll,
appraisals, recruitment, PF/gratuity and incentive records, so every screen shows
data that actually adds up.

## What's here

- **4 roles**, matching Section 4 of the proposal: Employee, Reporting Manager, HR Admin, Super Admin.
- **10 modules**: Attendance, Leave, Payroll, Recruitment & Onboarding, Employee Records,
  Performance Appraisal, PF & Gratuity, Commission & Incentive, Reports & Dashboards,
  System & Access — each with working forms and tables backed by the database, not
  placeholder screens.
- **A demo sign-in screen** (`/login`) listing all 10 seeded people. No password —
  pick anyone to see the app exactly as they would. A "Signed in as" switcher in the
  top-right lets you jump between people without losing your place on the page.
- **Real role-based access**: a module only appears in the sidebar / dashboard for
  roles the proposal lists against it, and hitting a module URL directly for a role
  that shouldn't have it returns a 403. Managers only see their own direct reports'
  approvals; HR Admin and Super Admin see the whole org.
- **Live KPIs and dashboards** — every number on the dashboard and module screens is
  computed from the database on each request, not hard-coded.

## Try it as...

- **Arjun Nair** (Super Admin) — org-wide view, System & Access, audit log.
- **Priya Menon** (HR Admin) — payroll runs, recruitment, employee records, incentive rules.
- **Rahul Varma** or **Sandra Thomas** (Reporting Manager) — team attendance/leave approvals,
  appraisal reviews for their direct reports.
- **Kiran Pillai**, **Anjali Krishnan**, etc. (Employee) — self-service leave, attendance,
  payslips, self-assessment.

## Structure

```
app/Http/Controllers/          One controller per module (Attendance, Leave, Payroll,
                                Recruitment, EmployeeRecords, Appraisal, PfGratuity,
                                Incentive, Reports, System) + Dashboard + Auth.
app/Http/Middleware/           EnsureUserSelected — demo "auth", redirects to /login.
app/Models/                    26 Eloquent models, one per table.
config/hr_modules.php          Roles, modules, feature lists, nav — single source of truth
                                for what's visible to whom. Live KPI values are computed
                                in DashboardController, not stored here.
database/migrations/           One migration creating all 26 tables (dependency-ordered,
                                with foreign keys) — mirrors the schema in the original
                                spc_hr SQL dump.
database/seeders/               DatabaseSeeder loads seed_data.sql — the real sample
                                dataset (10 users/employees + attendance, leave, payroll,
                                appraisal, recruitment, PF/gratuity and incentive history),
                                re-ordered so parent rows insert before dependents.
routes/web.php                 /login, /, /modules/{key} + one or two POST actions per module.
resources/views/layouts/app.blade.php   Shell, design tokens, sidebar + topbar wiring.
resources/views/modules/*.blade.php     One real screen per module — forms, tables,
                                pipelines, tabs — all bound to live data.
```

## Run it locally

This was written by hand (no `vendor/` included, since this environment can't reach
Packagist). On your machine, with **PHP 8.2+** and **Composer 2.x** installed:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Then open `http://localhost:8000` — you'll land on the sign-in screen. Pick anyone
from the list.

Built on **Laravel 12** (currently supported with security fixes until Feb 2027). If
`composer install` ever refuses a framework version citing "affected by security
advisories," it means the pinned version has since gone end-of-life — bump
`laravel/framework` in `composer.json` to whatever the current supported major is
rather than suppressing the check.

The dataset was validated independently against real SQLite (schema + all 26 tables'
data, foreign keys enforced) before being wired into the Laravel migration/seeder, so
`migrate --seed` should load cleanly.

## Design notes

- Sidebar in dark ink (`#1C2430`); each role gets its own accent color (Employee teal,
  Manager ochre, HR Admin plum, Super Admin brick) that tints the nav, KPI numbers,
  and module badges.
- Fraunces (serif) for headings, Inter for body/UI/data — loaded via Google Fonts CDN,
  no build step required.
- KPI strip and module list use hairline dividers rather than card-and-shadow
  treatment, closer to an operations ledger than a generic SaaS template.
- Payslips open as a clean, print-ready page (`window.print()` → save as PDF) rather
  than requiring a PDF library.

## What's intentionally simple (demo scope)

- **Auth is a picker, not a login form.** Swap in real password auth + Laravel's
  session guard against the existing `users` table when this goes further than a demo.
- **Payroll runs are read-only** in this build (the two seeded runs are shown as
  processed history); there's no "run payroll now" action yet.
- **Salary structure edits** create a new dated row rather than a full versioned
  history UI — the data model supports it, the screen doesn't expose it yet.
