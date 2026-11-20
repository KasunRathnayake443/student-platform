<?php

namespace App\Filament\SchoolAdmin\Pages;

use App\Models\School;
use App\Models\Student;
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

        $schoolIdArray = $schoolIds->all();

        $schools = School::query()
            ->whereIn('id', $schoolIdArray)
            ->orderBy('name')
            ->withCount([
                'grades',
                'students',
                'classes',
                'teachers',
            ])
            ->get();

        $totalIntroduced = Student::query()
            ->whereNotNull('introduced_by_teacher_id')
            ->whereHas('enrollments', fn ($query) => $query->whereIn('school_id', $schoolIdArray))
            ->count();

        return [
            'schools' => $schools,
            'totalSchools' => $schools->count(),
            'totalTeachers' => $schools->sum('teachers_count'),
            'totalStudents' => $schools->sum('students_count'),
            'totalClasses' => $schools->sum('classes_count'),
            'totalIntroduced' => $totalIntroduced,
        ];
    }

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
