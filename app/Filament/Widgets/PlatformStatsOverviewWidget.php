<?php

namespace App\Filament\Widgets;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Grade;
use App\Models\LearningClass;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PlatformStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // 1. Schools
        $totalSchools = School::count();
        $activeSchools = School::where('is_active', true)->count();
        $inactiveSchools = $totalSchools - $activeSchools;

        // 2. Students & Enrollments
        $totalStudents = Student::count();
        $activeEnrollments = StudentEnrollment::where('status', 'active')->count();

        // 3. Teachers
        $totalTeachers = Teacher::count();

        // 4. Learning Classes & Grades
        $totalClasses = LearningClass::count();
        $totalGrades = Grade::count();

        // 5. Lessons
        $totalLessons = Lesson::count();
        $publishedLessons = Lesson::where('is_published', true)->count();

        // 6. Assessments (Quizzes & Assignments)
        $totalQuizzes = Quiz::count();
        $totalAssignments = Assignment::count();
        $totalAttempts = QuizAttempt::count();
        $totalSubmissions = AssignmentSubmission::count();
        $totalEvaluations = $totalAttempts + $totalSubmissions;

        // Calculate 6-month historical counts for sparklines
        $studentSpark = $this->getMonthlyTrend(Student::query());
        $schoolSpark = $this->getMonthlyTrend(School::query());
        $attemptSpark = $this->getMonthlyTrend(QuizAttempt::query());

        return [
            Stat::make('Total Schools', (string) $totalSchools)
                ->description("{$activeSchools} Active".($inactiveSchools > 0 ? " • {$inactiveSchools} Inactive" : ''))
                ->descriptionIcon('heroicon-m-building-office-2')
                ->chart($schoolSpark)
                ->color($activeSchools > 0 ? 'primary' : 'gray'),

            Stat::make('Total Students', number_format($totalStudents))
                ->description("{$activeEnrollments} Active Enrollments")
                ->descriptionIcon('heroicon-m-academic-cap')
                ->chart($studentSpark)
                ->color('success'),

            Stat::make('Teaching Staff', number_format($totalTeachers))
                ->description('Educators Across Schools')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Classes & Grades', number_format($totalClasses))
                ->description("Across {$totalGrades} Curriculum Grades")
                ->descriptionIcon('heroicon-m-book-open')
                ->color('warning'),

            Stat::make('Lessons & Content', number_format($totalLessons))
                ->description("{$publishedLessons} Published Modules")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Completed Assessments', number_format($totalEvaluations))
                ->description("{$totalAttempts} Quiz Attempts • {$totalSubmissions} Submissions")
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->chart($attemptSpark)
                ->color('success'),
        ];
    }

    /**
     * @param  Builder  $query
     * @return array<int>
     */
    protected function getMonthlyTrend($query): array
    {
        $counts = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = (clone $query)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $counts[] = $count;
        }

        // If all zeroes, provide default baseline array for sparkline aesthetics
        if (array_sum($counts) === 0) {
            return [1, 2, 3, 2, 4, max(1, $query->count())];
        }

        return $counts;
    }
}
