<?php

namespace App\Filament\Widgets;

use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class SuperAdminWelcomeWidget extends Widget
{
    protected string $view = 'filament.widgets.super-admin-welcome-widget';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function getUserName(): string
    {
        return Auth::user()?->name ?? 'Super Admin';
    }

    public function getSummaryData(): array
    {
        $totalSchools = School::count();
        $activeSchools = School::where('is_active', true)->count();
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalUsers = User::count();

        return [
            'total_schools' => $totalSchools,
            'active_schools' => $activeSchools,
            'total_students' => $totalStudents,
            'total_teachers' => $totalTeachers,
            'total_users' => $totalUsers,
        ];
    }

    protected function getViewData(): array
    {
        return [
            'data' => $this->getSummaryData(),
            'userName' => $this->getUserName(),
            'today' => now()->format('l, F j, Y'),
        ];
    }
}
