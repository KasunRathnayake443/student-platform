<?php

namespace App\Services;

use App\Enums\NotificationMentionType;
use App\Enums\NotificationRecipientType;
use App\Filament\Resources\Assignments\AssignmentResource;
use App\Filament\Resources\Lessons\LessonResource;
use App\Filament\Resources\Quizzes\QuizResource;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\LearningClass;
use App\Models\Lesson;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\Quiz;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Resolve the recipient user ids for a notification based on the sender's role,
     * the chosen recipient type, optional school/grade/class scope and a target strategy
     * (send to ALL matching within scope, or to specific individuals).
     *
     * All scope parameters are clamped to what the sender is actually allowed to reach.
     *
     * @param  array{recipient_type: NotificationRecipientType, schools?: array<int>, grades?: array<int>, classes?: array<int>, all_matching: bool, recipient_ids?: array<int>}  $params
     * @return Collection<int, int>
     */
    public function resolveRecipients(User $sender, array $params): Collection
    {
        $type = $params['recipient_type'] ?? null;
        $allMatching = (bool) ($params['all_matching'] ?? false);

        if (! $type instanceof NotificationRecipientType) {
            return collect();
        }

        $base = $this->recipientBaseQuery($sender, $type, $params);

        if ($allMatching) {
            $ids = (clone $base)->pluck('users.id');
        } else {
            $requested = array_values(array_map(
                'intval',
                $params['recipient_ids'] ?? []
            ));

            $ids = (clone $base)
                ->whereIn('users.id', $requested)
                ->whereNot('users.id', $sender->getKey())
                ->pluck('users.id');
        }

        return collect($ids)->unique()->values();
    }

    /**
     * Build the base User query for a recipient type, scoped to the sender.
     *
     * @param  array{schools?: array<int>, grades?: array<int>, classes?: array<int>}  $params
     */
    protected function recipientBaseQuery(User $sender, NotificationRecipientType $type, array $params): Builder
    {
        $query = User::query();

        switch ($type) {
            case NotificationRecipientType::Student:
                $query->whereHas('student', function ($student) use ($sender, $params) {
                    $this->scopeStudentBySender($student, $sender, $params);
                });
                break;

            case NotificationRecipientType::Teacher:
                $query->whereHas('teacher', function ($teacher) use ($sender, $params) {
                    $this->scopeTeacherBySender($teacher, $sender, $params);
                });
                break;

            case NotificationRecipientType::SchoolAdmin:
                $query->whereHas('schoolAdmin', function ($admin) use ($sender, $params) {
                    $this->scopeSchoolAdminBySender($admin, $sender, $params);
                });
                break;

            case NotificationRecipientType::SuperAdmin:
                $query->role('super_admin');
                break;
        }

        return $query;
    }

    /**
     * Clamp the requested school ids to those the sender can reach (super admin: any,
     * school admin: their assigned schools; teachers/students get their own schools).
     *
     * @param  array{schools?: array<int>}  $params
     * @return array<int>
     */
    protected function allowedSchoolIds(User $sender, array $params): array
    {
        $requested = array_values(array_map('intval', $params['schools'] ?? []));

        if ($sender->hasRole('super_admin')) {
            return $requested ?: [];
        }

        if ($adminSchools = $sender->schools()->pluck('schools.id')) {
            return $requested ? $adminSchools->intersect($requested)->values()->all() : $adminSchools->all();
        }

        // Teacher or student: derive from their own schools.
        $ownSchools = $sender->student?->schools()->pluck('schools.id')
            ?? $sender->teacher?->schools()->pluck('schools.id')
            ?? collect();

        return $requested ? $ownSchools->intersect($requested)->values()->all() : $ownSchools->all();
    }

    /**
     * @param  HasOne  $student
     * @param  array{schools?: array<int>, grades?: array<int>, classes?: array<int>}  $params
     */
    protected function scopeStudentBySender($student, User $sender, array $params): void
    {
        $schoolIds = $this->allowedSchoolIds($sender, $params);
        $gradeIds = array_values(array_map('intval', $params['grades'] ?? []));
        $classIds = array_values(array_map('intval', $params['classes'] ?? []));

        // Clamp requested grades/classes to what the sender may actually reach.
        $gradeIds = $this->allowedGradeIds($sender, $gradeIds);
        $classIds = $this->allowedClassIds($sender, $classIds, $schoolIds, $gradeIds);

        if ($classIds) {
            $student->whereHas('classes', function ($class) use ($classIds) {
                return $class->whereIn('learning_classes.id', $classIds);
            });

            return;
        }

        if ($gradeIds) {
            $student->whereHas('enrollments', function ($enrollment) use ($gradeIds) {
                return $enrollment->whereIn('grade_id', $gradeIds);
            });

            return;
        }

        if ($schoolIds) {
            $student->whereHas('schools', function ($school) use ($schoolIds) {
                return $school->whereIn('schools.id', $schoolIds);
            });
        }
    }

    /**
     * @param  HasOne  $teacher
     * @param  array{schools?: array<int>}  $params
     */
    protected function scopeTeacherBySender($teacher, User $sender, array $params): void
    {
        $schoolIds = $this->allowedSchoolIds($sender, $params);

        if (! $schoolIds) {
            // No school selected and sender is a teacher/school admin with fixed scope.
            if ($sender->hasRole('teacher')) {
                $teacher->whereKey($sender->teacher_id); // effectively none / self-owned
            }

            return;
        }

        $teacher->whereHas('schools', function ($school) use ($schoolIds) {
            return $school->whereIn('schools.id', $schoolIds);
        });
    }

    /**
     * @param  HasOne  $admin
     * @param  array{schools?: array<int>}  $params
     */
    protected function scopeSchoolAdminBySender($admin, User $sender, array $params): void
    {
        $schoolIds = $this->allowedSchoolIds($sender, $params);

        if (! $schoolIds) {
            return;
        }

        $admin->whereHas('schools', function ($school) use ($schoolIds) {
            return $school->whereIn('schools.id', $schoolIds);
        });
    }

    /**
     * The school ids a sender can reach (super admin: none/none-scoped; otherwise
     * the sender's own schools, falling back to their teacher/student schools).
     *
     * @return Collection<int, int>
     */
    protected function senderReachableSchoolIds(User $sender): Collection
    {
        if ($sender->hasRole('super_admin')) {
            return collect();
        }

        if (($schools = $sender->schools()->pluck('schools.id'))->isNotEmpty()) {
            return $schools;
        }

        return $sender->student?->schools()->pluck('schools.id')
            ?? $sender->teacher?->schools()->pluck('schools.id')
            ?? collect();
    }

    /**
     * Clamp the requested grade ids to the sending user's own schools.
     *
     * @param  array<int>  $requested
     * @return array<int>
     */
    protected function allowedGradeIds(User $sender, array $requested): array
    {
        if ($sender->hasRole('super_admin')) {
            return $requested;
        }

        $allowed = Grade::query()
            ->whereIn('school_id', $this->senderReachableSchoolIds($sender))
            ->pluck('id');

        return $requested ? $allowed->intersect($requested)->values()->all() : $allowed->all();
    }

    /**
     * Clamp the requested class ids to classes within the sender's reachable
     * grades/schools (super admin: any; others: their own schools).
     *
     * @param  array<int>  $requested
     * @param  array<int>  $schoolIds
     * @param  array<int>  $gradeIds
     * @return array<int>
     */
    protected function allowedClassIds(User $sender, array $requested, array $schoolIds = [], array $gradeIds = []): array
    {
        if ($sender->hasRole('super_admin')) {
            return $requested;
        }

        $query = LearningClass::query()
            ->join('grades', 'grades.id', '=', 'learning_classes.grade_id')
            ->whereIn('grades.school_id', $this->senderReachableSchoolIds($sender));

        if ($schoolIds) {
            $query->whereIn('grades.school_id', $schoolIds);
        }

        if ($gradeIds) {
            $query->whereIn('learning_classes.grade_id', $gradeIds);
        }

        $allowed = $query->pluck('learning_classes.id');

        // A sender may only target classes they can actually see.
        if ($sender->hasRole('teacher')) {
            $allowed = $allowed->intersect($sender->teacher?->classes()->pluck('learning_classes.id') ?? collect());
        } elseif ($sender->hasRole('school_admin')) {
            $allowed = $allowed->intersect($this->schoolAdminVisibleClassIds($sender));
        }

        return $requested ? $allowed->intersect($requested)->values()->all() : $allowed->all();
    }

    /**
     * Learning class ids visible to a school admin (all classes in their assigned schools).
     *
     * @return Collection<int, int>
     */
    protected function schoolAdminVisibleClassIds(User $sender): Collection
    {
        if (! $sender->hasRole('school_admin')) {
            return collect();
        }

        $schoolIds = $sender->schools()->pluck('schools.id');

        return LearningClass::query()
            ->whereHas('grade', fn ($q) => $q->whereIn('school_id', $schoolIds))
            ->pluck('learning_classes.id');
    }

    /**
     * Create a notification and its per-recipient rows.
     *
     * @param  array{title: string, body?: string|null, mention_type?: NotificationMentionType|null, mention_id?: int|null}  $data
     * @param  iterable<int, int>  $recipientIds
     */
    public function send(User $sender, array $data, iterable $recipientIds, string $icon = 'heroicon-o-bell', string $color = 'primary'): ?Notification
    {
        $recipientIds = collect($recipientIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->reject(fn ($id) => $id === (int) $sender->getKey())
            ->values();

        if ($recipientIds->isEmpty()) {
            return null;
        }

        /** @var Notification $notification */
        $notification = Notification::create([
            'sender_id' => $sender->getKey(),
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'icon' => $icon,
            'color' => $color,
            'mention_type' => $data['mention_type'] ?? null,
            'mention_id' => $data['mention_id'] ?? null,
        ]);

        $notification->recipients()->createMany(
            $recipientIds->map(fn ($id) => ['recipient_id' => $id])->all()
        );

        return $notification;
    }

    /**
     * Scope a Notification query to what the given user may see.
     */
    public function scopeQueryFor(User $user, Builder $query): Builder
    {
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        $recipientIds = $user->receivedNotifications()->pluck('notifications.id');

        // A sender can always see the notifications they sent.
        $sentIds = $user->sentNotifications()->pluck('notifications.id');

        // School admins additionally see notifications created by teachers and other
        // school admins assigned to their same schools.
        $monitoredIds = collect();
        if ($user->hasRole('school_admin')) {
            $schoolIds = $user->schools()->pluck('schools.id');

            $teacherMade = Notification::query()
                ->whereHas('sender', fn ($sender) => $sender->whereHas('teacher'))
                ->whereHas('sender.teacher.schools', fn ($school) => $school->whereIn('schools.id', $schoolIds))
                ->pluck('notifications.id');

            $adminMade = Notification::query()
                ->whereHas('sender', fn ($sender) => $sender->whereHas('schoolAdmin'))
                ->whereHas('sender.schoolAdmin.schools', fn ($school) => $school->whereIn('schools.id', $schoolIds))
                ->pluck('notifications.id');

            $monitoredIds = $teacherMade->concat($adminMade);
        }

        $allowed = $recipientIds->concat($sentIds)->concat($monitoredIds)->unique()->values();

        return $query->whereIn('notifications.id', $allowed);
    }

    public function canDelete(User $user, Notification $notification): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ((int) $notification->sender_id === (int) $user->getKey()) {
            return true;
        }

        // School admins may delete notifications created by teachers and other school
        // admins assigned to the same schools (but never super admin ones).
        if ($user->hasRole('school_admin')) {
            $schoolIds = $user->schools()->pluck('schools.id');

            if ($notification->sender?->teacher) {
                return $notification->sender->teacher->schools()
                    ->whereIn('schools.id', $schoolIds)
                    ->exists();
            }

            if ($notification->sender?->schoolAdmin) {
                return $notification->sender->schoolAdmin->schools()
                    ->whereIn('schools.id', $schoolIds)
                    ->exists();
            }
        }

        return false;
    }

    /**
     * After delivery, notifications are delete-only (no editing).
     */
    public function canEdit(User $user, Notification $notification): bool
    {
        return false;
    }

    /**
     * The human-readable label of the mentioned item, if any.
     */
    public function mentionLabel(Notification $notification): ?string
    {
        $item = $notification->getMentionedItem();

        if (! $item) {
            return null;
        }

        return match (true) {
            $item instanceof Lesson => $item->title,
            $item instanceof Assignment => $item->title,
            $item instanceof Quiz => $item->title,
            default => null,
        };
    }

    /**
     * Build a navigation URL to the mentioned item within the viewer's panel.
     * Returns null when the viewer has no reachable representation of the item.
     */
    public function mentionUrl(User $viewer, Notification $notification): ?string
    {
        $item = $notification->getMentionedItem();

        if (! $item) {
            return null;
        }

        $class = match ($notification->mention_type) {
            NotificationMentionType::Lesson => LessonResource::class,
            NotificationMentionType::Assignment => AssignmentResource::class,
            NotificationMentionType::Quiz => QuizResource::class,
            default => null,
        };

        if (! $class) {
            return null;
        }

        if ($viewer->hasRole('super_admin')) {
            return $class::getUrl('view', ['record' => $item->getKey()], panel: 'admin');
        }

        if ($viewer->hasRole('school_admin')) {
            $schoolClass = str_replace(
                'App\Filament\Resources\\',
                'App\Filament\SchoolAdmin\Resources\\',
                $class
            );

            return $schoolClass::getUrl('view', ['record' => $item->getKey()], panel: 'school-admin');
        }

        if ($viewer->hasRole('teacher')) {
            $teacherClass = str_replace(
                'App\Filament\Resources\\',
                'App\Filament\Teacher\Resources\\',
                $class
            );

            return $teacherClass::getUrl('view', ['record' => $item->getKey()], panel: 'teacher');
        }

        if ($student = $viewer->student) {
            return $this->studentMentionUrl($student, $notification, $item);
        }

        return null;
    }

    protected function studentMentionUrl(Student $student, Notification $notification, $item): ?string
    {
        $learningClassId = match ($notification->mention_type) {
            NotificationMentionType::Lesson => $item->learning_class_id,
            NotificationMentionType::Assignment => $item->learning_class_id,
            NotificationMentionType::Quiz => $item->learning_class_id,
            default => null,
        };

        $isPublished = match ($notification->mention_type) {
            NotificationMentionType::Lesson => $item->is_published,
            NotificationMentionType::Assignment => $item->is_published,
            NotificationMentionType::Quiz => $item->is_published,
            default => false,
        };

        if (! $learningClassId || ! $isPublished) {
            return null;
        }

        $enrolled = $student->classes()
            ->where('learning_classes.id', $learningClassId)
            ->exists();

        if (! $enrolled) {
            return null;
        }

        return match ($notification->mention_type) {
            NotificationMentionType::Lesson => route('filament.student.pages.lesson').'?lesson='.$item->getKey(),
            NotificationMentionType::Assignment => route('filament.student.pages.assignment').'?assignment='.$item->getKey(),
            NotificationMentionType::Quiz => route('filament.student.pages.quiz-attempt').'?quiz='.$item->getKey(),
            default => null,
        };
    }

    /**
     * Paginate the notifications visible to a user, newest first.
     */
    public function paginatedFor(User $user, int $page = 1): LengthAwarePaginator
    {
        return $this->scopeQueryFor($user, Notification::query())
            ->with(['sender', 'recipients'])
            ->latest('notifications.created_at')
            ->paginate(15);
    }

    public function unreadCount(User $user): int
    {
        return $user->receivedNotifications()
            ->wherePivot('is_read', false)
            ->count();
    }

    /**
     * The ids of notifications the given user has already read.
     *
     * @return Collection<int, int>
     */
    public function readRecipientIdsFor(User $user): Collection
    {
        return $user->receivedNotifications()
            ->wherePivot('is_read', true)
            ->pluck('notifications.id');
    }

    public function markAsRead(User $user, Notification $notification): void
    {
        NotificationRecipient::query()
            ->where('notification_id', $notification->getKey())
            ->where('recipient_id', $user->getKey())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * Marks every notification received by the given user as read.
     */
    public function markAllAsRead(User $user): void
    {
        NotificationRecipient::query()
            ->where('recipient_id', $user->getKey())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }
}
