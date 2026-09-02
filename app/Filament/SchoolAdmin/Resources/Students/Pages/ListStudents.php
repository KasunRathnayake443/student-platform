<?php

namespace App\Filament\SchoolAdmin\Resources\Students\Pages;

use App\Filament\Resources\Students\Pages\ListStudents as BaseListStudents;
use App\Filament\SchoolAdmin\Resources\Students\StudentResource;

class ListStudents extends BaseListStudents
{
    protected static string $resource = StudentResource::class;
}
