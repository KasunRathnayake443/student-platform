<?php

namespace App\Filament\Teacher\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class TeacherDashboard extends BaseDashboard
{
    protected string $view = 'filament.teacher.pages.dashboard';
    
    protected static ?string $title = 'Teacher Dashboard';

    public function getViewData(): array
    {
        $teacher = auth()->user()->teacher;
        
        $schools = [];
        if ($teacher) {
            // Load schools with grades and classes that belong to this teacher
            $schools = $teacher->schools()->with(['grades' => function ($query) use ($teacher) {
                $query->whereHas('learningClasses', function ($q) use ($teacher) {
                    $q->whereHas('teachers', function ($t) use ($teacher) {
                        $t->where('teachers.id', $teacher->id);
                    });
                })->with(['learningClasses' => function ($query) use ($teacher) {
                    $query->whereHas('teachers', function ($t) use ($teacher) {
                        $t->where('teachers.id', $teacher->id);
                    });
                }]);
            }])->get();
        }

        return [
            'schools' => $schools,
        ];
    }
}
