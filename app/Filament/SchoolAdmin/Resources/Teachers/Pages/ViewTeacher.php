<?php

namespace App\Filament\SchoolAdmin\Resources\Teachers\Pages;

use App\Filament\Resources\Teachers\Pages\ViewTeacher as BaseViewTeacher;
use App\Filament\SchoolAdmin\Resources\Teachers\TeacherResource;

class ViewTeacher extends BaseViewTeacher
{
    protected static string $resource = TeacherResource::class;

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
