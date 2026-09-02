<?php

namespace App\Filament\SchoolAdmin\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\Pages\EditAssignment as BaseEditAssignment;
use App\Filament\SchoolAdmin\Resources\Assignments\AssignmentResource;

class EditAssignment extends BaseEditAssignment
{
    protected static string $resource = AssignmentResource::class;

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
