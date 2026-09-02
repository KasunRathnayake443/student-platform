<?php

namespace App\Filament\SchoolAdmin\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\Pages\CreateAssignment as BaseCreateAssignment;
use App\Filament\SchoolAdmin\Resources\Assignments\AssignmentResource;

class CreateAssignment extends BaseCreateAssignment
{
    protected static string $resource = AssignmentResource::class;

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
