<?php

namespace App\Filament\Teacher\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class TeacherDashboard extends BaseDashboard
{
    protected string $view = 'filament.teacher.pages.dashboard';

    protected static ?string $title = 'Overview';

    public function getViewData(): array
    {
        $teacher = auth()->user()->teacher;

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

        $totalClasses  = $schools->flatMap(fn ($s) => $s->grades->flatMap(fn ($g) => $g->learningClasses))->count();
        $totalStudents = $schools->flatMap(fn ($s) => $s->grades->flatMap(fn ($g) => $g->learningClasses))->sum('students_count');

        return [
            'schools'       => $schools,
            'totalClasses'  => $totalClasses,
            'totalStudents' => $totalStudents,
        ];
    }
}
