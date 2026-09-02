<?php

namespace App\Filament\SchoolAdmin\Resources\Grades\Pages;

use App\Filament\SchoolAdmin\Resources\Grades\GradeResource;
use App\Filament\SchoolAdmin\Resources\Grades\RelationManagers\LearningClassesRelationManager;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGrade extends ViewRecord
{
    protected static string $resource = GradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            LearningClassesRelationManager::class,
        ];
    }
}
