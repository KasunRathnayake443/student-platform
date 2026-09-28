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

### Lesson and assignment email rules

Lessons and assignments follow the same rules:

- Creating a published lesson or assignment emails the students of that class and sets `email_sent = true`.
- Creating a draft sends nothing and leaves `email_sent = false`.
- Standard `Submit` on the edit page sends nothing when `email_sent` is already `true`, so minor
  edits never spam students. When `email_sent` is `false` and the record is published, the first
  `Submit` sends and sets `email_sent = true`.
- `Submit & Email Students` appears in the edit form actions once `email_sent` is `true`. It saves
  the record, resends the notification regardless of the previous state, and keeps `email_sent = true`.

Assignment emails include the class, teacher, start date, and end date:

```
Class: 10-B Science & Physics
Teacher: Nimal Perera
Start date: 5 Oct 2026, 8:00 AM
End date: 12 Oct 2026, 5:00 PM
Maximum score: 100
```

Assignments without a scheduled start report `Start date: Available immediately`, and assignments
without a deadline report `End date: No deadline`. When late submissions are enabled and an end
date is set, a `Late submissions accepted until:` line is added.

Recipients are the active enrolments of the lesson or assignment's class, resolved to the student
user accounts that have an email address.

## Checks

```bash
php artisan test
vendor/bin/pint --test
vendor/bin/phpstan analyse --memory-limit=512M
```
