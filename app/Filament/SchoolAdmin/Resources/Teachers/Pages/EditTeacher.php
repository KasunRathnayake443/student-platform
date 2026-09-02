<?php

namespace App\Filament\SchoolAdmin\Resources\Teachers\Pages;

use App\Filament\Resources\Teachers\Pages\EditTeacher as BaseEditTeacher;
use App\Filament\SchoolAdmin\Resources\Teachers\TeacherResource;

class EditTeacher extends BaseEditTeacher
{
    protected static string $resource = TeacherResource::class;
}
