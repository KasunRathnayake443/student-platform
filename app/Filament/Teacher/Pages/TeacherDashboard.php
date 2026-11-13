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
                ->with(['quiz.learningClass', 'quiz.questions.options', 'answers'])
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
                    'attemptDetails' => $attempts->mapWithKeys(
                        fn (QuizAttempt $attempt) => [$attempt->id => $this->buildAttemptDetail($attempt)]
                    ),
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

    /**
     * Normalize a quiz attempt into a view-ready question breakdown.
     *
     * @return array<string, mixed>
     */
    protected function buildAttemptDetail(QuizAttempt $attempt): array
    {
        $quiz = $attempt->quiz;
        $questions = $quiz?->questions ?? collect();

        $answers = $attempt->answers->keyBy('quiz_question_id');

        $questionDetails = $questions->values()->map(function ($question, $index) use ($answers): array {
            $answer = $answers->get($question->id);
            $selectedId = (int) ($answer?->quiz_question_option_id ?? 0);
            $selectedOption = $selectedId > 0
                ? $question->options->firstWhere('id', $selectedId)
                : null;
            $correctOption = $question->options->firstWhere('is_correct', true);
            $isAnswered = $selectedOption !== null;

            return [
                'number' => $index + 1,
                'question' => $question->question_text,
                'question_image_url' => $question->question_image_url,
                'question_video_url' => $question->question_video_url,
                'selected_answer' => $selectedOption?->option_text,
                'correct_answer' => $correctOption?->option_text,
                'is_answered' => $isAnswered,
                'is_correct' => (bool) ($answer?->is_correct ?? false),
                'points_awarded' => (float) ($answer?->points_awarded ?? 0),
                'points_possible' => (float) $question->points,
                'explanation' => $question->explanation,
            ];
        })->all();

        return [
            'questions' => $questionDetails,
            'correct_count' => collect($questionDetails)->where('is_correct', true)->count(),
            'wrong_count' => collect($questionDetails)
                ->filter(fn (array $question) => $question['is_answered'] && ! $question['is_correct'])
                ->count(),
            'unanswered_count' => collect($questionDetails)
                ->reject(fn (array $question) => $question['is_answered'])
                ->count(),
            'total_questions' => count($questionDetails),
            'ai_analysis' => is_array($attempt->ai_analysis) ? $attempt->ai_analysis : null,
            'ai_analysis_status' => $attempt->ai_analysis_status,
        ];
    }
}
