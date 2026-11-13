<?php

namespace App\Filament\Parent\Pages;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Lesson;
use App\Models\QuizAttempt;
use App\Models\Student;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.parent.pages.dashboard';

    protected static ?string $title = 'Parent Dashboard';

    public ?int $activeChildId = null;

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $requested = (int) request()->query('child');

        $this->activeChildId = $user->children()
            ->whereKey($requested)
            ->value('id')
            ?? $user->children()->orderBy('id')->value('id');
    }

    public function selectChild(int $childId): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $childId = (int) $user->children()
            ->whereKey($childId)
            ->value('id');

        if (! $childId) {
            return;
        }

        $this->activeChildId = $childId;
    }

    /**
     * @return array<string, mixed>
     */
    public function getViewData(): array
    {
        $user = Auth::user();

        if (! $user) {
            return $this->emptyViewData(collect());
        }

        $children = $user->children()
            ->with(['user', 'enrollments.school', 'enrollments.grade'])
            ->orderBy('id')
            ->get();

        $student = $children->firstWhere('id', $this->activeChildId) ?? $children->first();

        if (! $student instanceof Student) {
            return $this->emptyViewData($children);
        }

        $classIds = $student->classes()
            ->pluck('learning_classes.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $quizQuery = QuizAttempt::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['submitted', 'time_expired'])
            ->with(['quiz.learningClass.grade.school']);

        $quizStats = [
            'count' => (clone $quizQuery)->count(),
            'average' => round((float) ((clone $quizQuery)->avg('percentage') ?? 0), 1),
            'passed' => (clone $quizQuery)->where('is_passed', true)->count(),
        ];

        $quizAttempts = $quizQuery
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->get();

        [$assignments, $assignmentStats] = $this->assignmentProgress($student, $classIds);

        $lessons = collect();
        $lessonCount = 0;

        if ($classIds) {
            $lessonQuery = Lesson::query()
                ->whereIn('learning_class_id', $classIds)
                ->where('is_published', true)
                ->with(['learningClass.grade.school', 'teacher.user']);

            $lessonCount = (clone $lessonQuery)->count();

            $lessons = $lessonQuery
                ->orderBy('sort_order')
                ->orderByDesc('created_at')
                ->get();
        }

        $notifications = collect();
        $notificationStats = [
            'total' => 0,
            'unread' => 0,
        ];

        if ($student->user) {
            $notifications = $student->user
                ->receivedNotifications()
                ->with('sender')
                ->orderByDesc('notifications.created_at')
                ->get();

            $notificationStats = [
                'total' => $notifications->count(),
                'unread' => $notifications
                    ->filter(fn ($notification) => ! $notification->pivot->is_read)
                    ->count(),
            ];
        }

        return [
            'parentName' => $user->name,
            'children' => $children,
            'totalChildren' => $children->count(),
            'student' => $student,
            'activeChildId' => $student->id,
            'enrollments' => $student->enrollments
                ->sortBy(fn ($enrollment) => $enrollment->school?->name ?? '')
                ->values(),
            'quizAttempts' => $quizAttempts,
            'quizStats' => $quizStats,
            'assignments' => $assignments,
            'assignmentStats' => $assignmentStats,
            'lessons' => $lessons,
            'lessonCount' => $lessonCount,
            'notifications' => $notifications,
            'notificationStats' => $notificationStats,
        ];
    }

    /**
     * Build assignment rows for the selected child and their summary stats.
     *
     * @param  array<int, int>  $classIds
     * @return array{0: Collection<int, array<string, mixed>>, 1: array<string, mixed>}
     */
    protected function assignmentProgress(Student $student, array $classIds): array
    {
        $emptyStats = [
            'total' => 0,
            'submitted' => 0,
            'graded' => 0,
            'pending' => 0,
            'average' => 0.0,
        ];

        if (empty($classIds)) {
            return [collect(), $emptyStats];
        }

        $assignments = Assignment::query()
            ->whereIn('learning_class_id', $classIds)
            ->where('is_published', true)
            ->with(['learningClass.grade.school', 'teacher.user'])
            ->get();

        $submissions = AssignmentSubmission::query()
            ->where('student_id', $student->id)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        $rows = $assignments
            ->sortBy(fn (Assignment $assignment) => $assignment->end_at?->timestamp ?? PHP_INT_MAX)
            ->values()
            ->map(function (Assignment $assignment) use ($submissions): array {
                $submission = $submissions->get($assignment->id);

                $percentage = $submission?->score !== null && $assignment->max_score > 0
                    ? round(((float) $submission->score / (float) $assignment->max_score) * 100, 1)
                    : null;

                $state = match (true) {
                    $submission?->isGraded() => 'graded',
                    $submission !== null => 'submitted',
                    $assignment->isExpired() => 'missing',
                    default => 'pending',
                };

                return [
                    'assignment' => $assignment,
                    'submission' => $submission,
                    'percentage' => $percentage,
                    'state' => $state,
                ];
            });

        $gradedPercentages = $rows
            ->pluck('percentage')
            ->filter(fn ($percentage) => $percentage !== null);

        $stats = [
            'total' => $rows->count(),
            'submitted' => $rows->whereNotNull('submission')->count(),
            'graded' => $rows->filter(fn (array $row) => $row['state'] === 'graded')->count(),
            'pending' => $rows->filter(fn (array $row) => in_array($row['state'], ['pending', 'missing'], true))->count(),
            'average' => $gradedPercentages->isNotEmpty()
                ? round((float) $gradedPercentages->avg(), 1)
                : 0.0,
        ];

        return [$rows, $stats];
    }

    /**
     * @param  Collection<int, Student>  $children
     * @return array<string, mixed>
     */
    protected function emptyViewData(Collection $children): array
    {
        return [
            'parentName' => Auth::user()?->name ?? 'Parent',
            'children' => $children,
            'totalChildren' => $children->count(),
            'student' => null,
            'activeChildId' => null,
            'enrollments' => collect(),
            'quizAttempts' => collect(),
            'quizStats' => [
                'count' => 0,
                'average' => 0.0,
                'passed' => 0,
            ],
            'assignments' => collect(),
            'assignmentStats' => [
                'total' => 0,
                'submitted' => 0,
                'graded' => 0,
                'pending' => 0,
                'average' => 0.0,
            ],
            'lessons' => collect(),
            'lessonCount' => 0,
            'notifications' => collect(),
            'notificationStats' => [
                'total' => 0,
                'unread' => 0,
            ],
        ];
    }
}
