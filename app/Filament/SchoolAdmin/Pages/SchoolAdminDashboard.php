<?php

namespace App\Filament\SchoolAdmin\Pages;

use App\Models\School;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SchoolAdminDashboard extends Page
{
    protected static string $routePath = '/';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected string $view = 'filament.school-admin.pages.dashboard';

    protected static ?string $title = 'Overview';

    public function getViewData(): array
    {
        $schoolIds = auth()->user()->schools()->pluck('schools.id');

        $schools = School::query()
            ->whereIn('id', $schoolIds)
            ->orderBy('name')
            ->withCount([
                'grades',
                'students',
                'classes',
                'teachers',
            ])
            ->get();

        return [
            'schools' => $schools,
            'totalSchools' => $schools->count(),
            'totalTeachers' => $schools->sum('teachers_count'),
            'totalStudents' => $schools->sum('students_count'),
            'totalClasses' => $schools->sum('classes_count'),
        ];
    }
}
