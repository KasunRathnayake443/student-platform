<?php

namespace App\Filament\Teacher\Pages;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\Notification;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\School;
use App\Models\Student;
use App\Services\NotificationService;
use Filament\Pages\Dashboard as BaseDashboard;

class TeacherDashboard extends BaseDashboard
{
    protected string $view = 'filament.teacher.pages.dashboard';

    public function getLayout(): string
    {
        return 'filament.teacher.layouts.app';
    }

    protected static ?string $title = 'Overview';

    public function getViewData(): array
    {
        $user = auth()->user();
        $teacher = $user?->teacher;

        $schools = collect();

        if ($teacher) {
            $schools = $teacher->schools()->with([
                'grades' => function ($query) use ($teacher) {
                    $query
                        ->whereHas('learningClasses', function ($q) use ($teacher) {
                            $q->whereHas('teachers', fn ($t) => $t->where('teachers.id', $teacher->id));
                        })
                        ->orderBy('name')
                        ->with([
                            'learningClasses' => function ($q) use ($teacher) {
                                $q->whereHas('teachers', fn ($t) => $t->where('teachers.id', $teacher->id))
                                    ->orderBy('name')
                                    ->withCount('students');
                            },
                        ]);
                },
            ])
                ->orderBy('name')
                ->get();
        }

        $allClasses = $schools->flatMap(
            fn (School $s) => $s->grades->flatMap(fn (Grade $g) => $g->learningClasses)
        );

        $totalClasses = $allClasses->count();
        $totalStudents = $allClasses->sum('students_count');
        $classIds = $allClasses->pluck('id')->filter()->all();

        // Students Grades — full score profile for every student in the teacher's classes
        $studentGrades = collect();

        if (! empty($classIds)) {
            $classStudents = Student::query()
                ->whereHas('classes', fn ($q) => $q->whereIn('learning_class_id', $classIds))
                ->with([
                    'user',
                    'classes' => fn ($q) => $q->whereIn('learning_class_id', $classIds)->with('grade'),
                ])
                ->orderBy('id')
                ->get();

            $classStudentIds = $classStudents->pluck('id')->all();

            $attemptsByStudent = QuizAttempt::query()
                ->whereIn('student_id', $classStudentIds)
                ->whereIn('status', ['submitted', 'time_expired'])
                ->whereHas('quiz', fn ($q) => $q->whereIn('learning_class_id', $classIds))
                ->with(['quiz.learningClass'])
                ->get()
                ->groupBy('student_id');

            $submissionsByStudent = AssignmentSubmission::query()
                ->whereIn('student_id', $classStudentIds)
                ->whereHas('assignment', fn ($q) => $q->whereIn('learning_class_id', $classIds))
                ->with(['assignment.learningClass'])
                ->get()
                ->groupBy('student_id');

            foreach ($classStudents as $student) {
                $attempts = $attemptsByStudent->get($student->id, collect())
                    ->sortByDesc('completed_at')
                    ->values();

                $submissions = $submissionsByStudent->get($student->id, collect())
                    ->sortByDesc('graded_at')
                    ->values();

                $graded = $submissions->where('status', 'graded');

                $quizAvg = $attempts->avg('percentage');
                $gradedPct = $graded->map(fn ($s) => $s->percentage())->filter()->avg();

                $studentGrades->push([
                    'student' => $student,
                    'classes' => $student->classes,
                    'attempts' => $attempts,
                    'submissions' => $submissions,
                    'quizCount' => $attempts->count(),
                    'quizPassed' => $attempts->where('is_passed', true)->count(),
                    'quizAvg' => $quizAvg === null ? null : round((float) $quizAvg, 1),
                    'gradedCount' => $graded->count(),
                    'pendingCount' => $submissions->where('status', '!=', 'graded')->count(),
                    'assignmentAvg' => $gradedPct === null ? null : round((float) $gradedPct, 1),
                ]);
            }

            $studentGrades = $studentGrades
                ->sortBy(fn ($g) => strtolower($g['student']->user?->name ?? ''))
                ->values();
        }

        // Pending Submissions to Grade
        $pendingSubmissions = collect();
        $pendingSubmissionsCount = 0;
        if (! empty($classIds)) {
            $pendingQuery = AssignmentSubmission::query()
                ->whereHas('assignment', fn ($q) => $q->whereIn('learning_class_id', $classIds))
                ->where('status', '!=', 'graded');

            $pendingSubmissionsCount = (clone $pendingQuery)->count();
            $pendingSubmissions = $pendingQuery
                ->with(['student.user', 'assignment.learningClass'])
                ->latest('submitted_at')
                ->take(5)
                ->get();
        }

        // Quizzes
        $totalQuizzes = 0;
        $recentQuizzes = collect();
        if (! empty($classIds)) {
            $quizzesQuery = Quiz::query()->whereIn('learning_class_id', $classIds);
            $totalQuizzes = (clone $quizzesQuery)->count();
            $recentQuizzes = $quizzesQuery
                ->with(['learningClass'])
                ->withCount(['questions', 'attempts'])
                ->latest()
                ->take(4)
                ->get();
        }

        // Lessons
        $totalLessons = 0;
        $recentLessons = collect();
        if (! empty($classIds)) {
            $lessonsQuery = Lesson::query()->whereIn('learning_class_id', $classIds);
            $totalLessons = (clone $lessonsQuery)->count();
            $recentLessons = $lessonsQuery
                ->with(['learningClass'])
                ->withCount('attachments')
                ->latest()
                ->take(4)
                ->get();
        }

        // Recent Notifications
        $recentNotifications = collect();
        $unreadNotificationsCount = 0;
        if ($user) {
            $notificationService = app(NotificationService::class);
            $unreadNotificationsCount = $notificationService->unreadCount($user);
            $recentNotifications = $notificationService
                ->scopeQueryFor($user, Notification::query())
                ->with('sender')
                ->latest('created_at')
                ->take(4)
                ->get();
        }

        return [
            'teacher' => $teacher,
            'schools' => $schools,
            'allClasses' => $allClasses,
            'totalClasses' => $totalClasses,
            'totalStudents' => $totalStudents,
            'pendingSubmissions' => $pendingSubmissions,
            'pendingSubmissionsCount' => $pendingSubmissionsCount,
            'totalQuizzes' => $totalQuizzes,
            'recentQuizzes' => $recentQuizzes,
            'totalLessons' => $totalLessons,
            'recentLessons' => $recentLessons,
            'recentNotifications' => $recentNotifications,
            'unreadNotificationsCount' => $unreadNotificationsCount,
            'studentGrades' => $studentGrades,
        ];
    }
}
