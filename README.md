<div align="center">

# 🎓 Student Platform

**A multi-portal learning management platform for schools, teachers, and students.**

Built with Laravel, Filament, and Livewire.

[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-5-FDAE4B?style=for-the-badge&logo=laravel&logoColor=111827)](https://filamentphp.com)
[![Livewire](https://img.shields.io/badge/Livewire-4-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![PHP](https://img.shields.io/badge/PHP-%5E8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-0f766e?style=for-the-badge)](composer.json)

</div>

---

## Overview

**Student Platform** is a school-focused LMS that brings administration, teaching, and learning into role-specific portals. It supports managing schools and classes, delivering lessons, running quizzes and assignments, grading submissions, and communicating with students through queued, school-configured email notifications.

## ✨ Features

| Area | Highlights |
|---|---|
| **Portals & access** | Role-based experiences for platform/school administration, teachers, and students (Filament panels + permissions). |
| **School management** | Schools, learning classes, enrollments, teachers, students, grades, and school branding/profile assets. |
| **Learning content** | Lessons with attachments, assignments with submissions/attachments, and quizzes with questions, options, media, and attempts. |
| **Assessment** | Quiz attempts/answers, assignment submissions, scoring, and grading workflows. |
| **Communication** | In-app notifications plus queued email notifications for class activities; teacher email composer with optional per-student score details and parent/guardian copies. |
| **Email delivery** | Per-school SMTP settings; notifications are queued and sent through the owning school’s mail configuration. |
| **Utilities** | Quiz import template download, quiz question media routes, profile photos/logos, calendar notes, and a public landing page. |

## 🧰 Tech Stack

- **Backend:** PHP 8.3+, Laravel 13
- **Admin/UI:** Filament 5, Livewire 4, Flux 2
- **Frontend tooling:** Vite 8, Tailwind CSS 4
- **Auth & permissions:** Laravel Fortify, Spatie Laravel Permission
- **Queue/mail:** Database-backed queues and mail (configurable per school for activity notifications)
- **Testing/quality:** Pest 4, PHPUnit-style Laravel testing, Pint, Larastan/PHPStan

## 📋 Requirements

- PHP `^8.3`
- Composer
- Node.js + npm
- A supported database (default local setup uses SQLite; MySQL/PostgreSQL also work via `.env`)
- A running queue worker for email/queued notifications

## 🚀 Quick Start

```bash
# 1) Install dependencies
composer install
npm install

# 2) Configure environment
cp .env.example .env
php artisan key:generate

# 3) Prepare database
php artisan migrate --seed

# 4) Start the app (separate terminals)
php artisan serve
php artisan queue:work
npm run dev
```

> [!TIP]
> For an all-in-one local dev workflow, you can also use:
> ```bash
> composer dev
> ```
> This runs the server, queue listener, and Vite together via `concurrently`.

## ⚙️ Configuration

Key environment settings live in `.env` (see [`.env.example`](.env.example)):

```dotenv
APP_NAME="Student Platform"
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database

MAIL_MAILER=log
```

### Queue worker (important)

> [!WARNING]
> **A queue worker must be running or queued emails will not be delivered.**  
> Notification jobs are stored in the `jobs` table and are only processed when a worker runs. Without a worker, jobs accumulate (often with `attempts = 0`) and students/recipients won’t receive email.

Local development:

```bash
php artisan queue:work
```

Production (example, supervised process):

```bash
php artisan queue:work --tries=3 --backoff=60 --max-time=3600
```

Useful checks:

```bash
php artisan queue:failed
php artisan tinker --execute="dump(DB::table('jobs')->count());"
```

### School SMTP & email notifications

Student/activity notifications (lessons, quizzes, assignments, graded submissions, etc.) are sent through the **SMTP settings stored on each school**. Super admins/school admins configure sender/host/port/encryption/password on the school settings/edit flow.

<details>
<summary><strong>Lesson, assignment, and quiz email rules</strong></summary>

Lessons, assignments, and quizzes follow the same general rules:

- Creating a **published** item emails the students of that class and sets `email_sent = true`.
- Creating a **draft** sends nothing and leaves `email_sent = false`.
- Standard **Submit** on edit sends nothing when `email_sent` is already `true` (prevents spam on minor edits).
- If `email_sent` is `false` and the item is published, the first **Submit** sends and sets `email_sent = true`.
- **Submit & Email Students** (when available after initial send) saves and resends notifications.

Recipients are resolved from active enrollments in the class, using student user accounts that have an email address.

</details>

<details>
<summary><strong>Teacher email composer</strong></summary>

Teachers can send custom messages to students in their own classes from the teacher panel (**Email Students**). The composer supports:

- Selecting a learning class
- Choosing recipients (limited to active enrollments in that class)
- Writing the message body
- Optionally attaching assignment/quiz score details per student
- Optionally copying a parent/guardian

Parent copies require a parent email on the student record. Parents without an email on file are skipped. Parent emails are sent directly (parents do not need portal logins). Composer mail is queued and sent via the owning school’s SMTP settings.

</details>

## 🧪 Development & Quality Checks

```bash
# Run tests
php artisan test

# Lint (Pint)
vendor/bin/pint
vendor/bin/pint --test

# Static analysis (Larastan/PHPStan)
vendor/bin/phpstan analyse --memory-limit=512M
```

Composer helpers:

```bash
composer test          # config clear + lint check + types check + tests
composer lint          # Pint (parallel)
composer lint:check    # Pint check (parallel)
composer types:check   # PHPStan/Larastan
```

## 🗂️ Project Structure (high level)

```text
app/
  Filament/        # Filament panels, resources, pages, widgets, etc.
  Http/            # Controllers (media, profile photos/logos, imports/templates)
  Models/          # Core domain models (School, Class, Lesson, Quiz, Assignment, ...)
  Notifications/   # Notification classes
  Services/        # Application services (e.g., email/notification/platform logic)
resources/
  views/           # Blade views, including landing page
  css/             # Frontend styles
routes/
  web.php          # Public/auth routes and utility endpoints
database/
  migrations/      # Schema
  seeders/         # Seed data
```

## 🤝 Contributing

1. Create a feature branch
2. Make focused changes with tests where applicable
3. Run quality checks (`composer test` / Pint / PHPStan)
4. Open a pull request with a clear description and verification notes

## 📄 License

This project is licensed under the **MIT License** (see [`composer.json`](composer.json)). If you add a standalone `LICENSE` file, link it here for GitHub’s license detector.

---

<div align="center">
  <sub>Student Platform — built for practical school workflows.</sub>
</div>
