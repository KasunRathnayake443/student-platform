# Student Platform

Filament-based platform for schools, teachers, and students (lessons, quizzes, assignments, grading).

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

## Email notifications

Student notifications (new lesson, quiz, assignment, and graded submissions) are sent through
the SMTP settings stored on each school. A super admin or school admin configures the sender
email, host, port, encryption, and password on the school edit page.

Notifications are **queued** (`App\Services\SchoolActivityNotification implements ShouldQueue`).
They are written to the `jobs` table and only leave the application when a queue worker runs.

**A queue worker must be running or no emails will be delivered.** Without it, jobs accumulate in
the `jobs` table with `attempts = 0` and students never receive anything.

Local development:

```bash
php artisan queue:work
```

Production: run the worker under a process supervisor, for example with a systemd unit or
Laravel Horizon, and make sure it restarts on failure:

```bash
php artisan queue:work --tries=3 --backoff=60 --max-time=3600
```

Check the queue state at any time:

```bash
php artisan queue:failed     # jobs that exhausted their retries
php artisan tinker --execute="dump(DB::table('jobs')->count());"
```

### Lesson email rules

- Creating a published lesson emails the students of that lesson's class and sets `email_sent = true`.
- Creating a draft lesson sends nothing and leaves `email_sent = false`.
- Standard `Submit` on the edit page sends nothing when `email_sent` is already `true`, so minor
  edits never spam students. When `email_sent` is `false` and the lesson is published, the first
  `Submit` sends and sets `email_sent = true`.
- `Submit & Email Students` appears on the edit page once `email_sent` is `true`. It saves the
  lesson, resends the notification regardless of the previous state, and keeps `email_sent = true`.

Recipients are the active enrolments of the lesson's class, resolved to the student user accounts
that have an email address.

## Checks

```bash
php artisan test
vendor/bin/pint --test
vendor/bin/phpstan analyse --memory-limit=512M
```
