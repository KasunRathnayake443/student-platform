<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use App\Models\StudentEnrollment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class EnrollmentTrendChart extends ChartWidget
{
    protected ?string $heading = 'Student Enrollment & Growth Trends';

    protected static ?int $sort = 3;

    protected ?string $maxHeight = '300px';

    public ?string $filter = '6m';

    protected function getFilters(): ?array
    {
        return [
            '30d' => 'Last 30 Days',
            '6m' => 'Last 6 Months',
            '12m' => 'Last 12 Months',
            'this_year' => 'This Year',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter ?? '6m';

        $labels = [];
        $studentsData = [];
        $enrollmentsData = [];

        if ($activeFilter === '30d') {
            // Daily breakdown for the last 30 days
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $date->format('M d');

                $studentsData[] = Student::whereDate('created_at', $date)->count();
                $enrollmentsData[] = StudentEnrollment::whereDate('created_at', $date)->count();
            }
        } elseif ($activeFilter === '12m') {
            // Monthly breakdown for last 12 months
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $labels[] = $date->format('M Y');

                $studentsData[] = Student::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();

                $enrollmentsData[] = StudentEnrollment::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
            }
        } elseif ($activeFilter === 'this_year') {
            // Months of current year
            $currentMonth = Carbon::now()->month;
            for ($m = 1; $m <= $currentMonth; $m++) {
                $date = Carbon::create(Carbon::now()->year, $m, 1);
                $labels[] = $date->format('M');

                $studentsData[] = Student::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $m)
                    ->count();

                $enrollmentsData[] = StudentEnrollment::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $m)
                    ->count();
            }
        } else {
            // Default 6m: Monthly breakdown for last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $labels[] = $date->format('M Y');

                $studentsData[] = Student::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();

                $enrollmentsData[] = StudentEnrollment::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Students Registered',
                    'data' => $studentsData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Class Enrollments',
                    'data' => $enrollmentsData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
