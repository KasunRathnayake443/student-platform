<?php

namespace App\Filament\Widgets;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Filament\Widgets\ChartWidget;

class PlatformResourceDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Academic Resource Breakdown';

    protected static ?int $sort = 4;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $lessons = Lesson::count();
        $assignments = Assignment::count();
        $quizzes = Quiz::count();
        $attempts = QuizAttempt::count();
        $submissions = AssignmentSubmission::count();

        // If no data yet, provide sample distribution so chart renders nicely
        $isAllZero = ($lessons + $assignments + $quizzes + $attempts + $submissions) === 0;

        return [
            'datasets' => [
                [
                    'label' => 'Total Count',
                    'data' => $isAllZero ? [5, 3, 2, 8, 4] : [$lessons, $assignments, $quizzes, $attempts, $submissions],
                    'backgroundColor' => [
                        '#6366f1', // Lessons - Indigo
                        '#06b6d4', // Assignments - Cyan
                        '#f43f5e', // Quizzes - Rose
                        '#10b981', // Quiz Attempts - Emerald
                        '#f59e0b', // Assignment Submissions - Amber
                    ],
                    'borderWidth' => 2,
                    'hoverOffset' => 6,
                ],
            ],
            'labels' => [
                'Lessons ('.($isAllZero ? 'Sample' : $lessons).')',
                'Assignments ('.($isAllZero ? 'Sample' : $assignments).')',
                'Quizzes ('.($isAllZero ? 'Sample' : $quizzes).')',
                'Quiz Attempts ('.($isAllZero ? 'Sample' : $attempts).')',
                'Submissions ('.($isAllZero ? 'Sample' : $submissions).')',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'boxWidth' => 12,
                        'padding' => 16,
                    ],
                ],
            ],
            'cutout' => '65%',
        ];
    }
}
