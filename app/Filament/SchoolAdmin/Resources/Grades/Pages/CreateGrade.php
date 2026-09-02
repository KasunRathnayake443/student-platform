<?php

namespace App\Filament\SchoolAdmin\Resources\Grades\Pages;

use App\Filament\SchoolAdmin\Resources\Grades\GradeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGrade extends CreateRecord
{
    protected static string $resource = GradeResource::class;
}
