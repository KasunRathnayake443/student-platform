<?php

namespace App\Filament\SchoolAdmin\Resources\Teachers\Pages;

use App\Filament\Resources\Teachers\Pages\ListTeachers as BaseListTeachers;
use App\Filament\SchoolAdmin\Resources\Teachers\TeacherResource;

class ListTeachers extends BaseListTeachers
{
    protected static string $resource = TeacherResource::class;
}
