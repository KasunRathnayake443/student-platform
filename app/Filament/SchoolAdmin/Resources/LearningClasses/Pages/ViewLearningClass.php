<?php

namespace App\Filament\SchoolAdmin\Resources\LearningClasses\Pages;

use App\Filament\SchoolAdmin\Resources\LearningClasses\LearningClassResource;
use App\Filament\SchoolAdmin\Resources\LearningClasses\RelationManagers\AssignmentsRelationManager;
use App\Filament\SchoolAdmin\Resources\LearningClasses\RelationManagers\LessonsRelationManager;
use App\Filament\SchoolAdmin\Resources\LearningClasses\RelationManagers\QuizzesRelationManager;
use App\Filament\SchoolAdmin\Resources\LearningClasses\RelationManagers\StudentsRelationManager;
use App\Filament\SchoolAdmin\Resources\LearningClasses\RelationManagers\TeachersRelationManager;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLearningClass extends ViewRecord
{
    protected static string $resource = LearningClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            StudentsRelationManager::class,
            TeachersRelationManager::class,
            LessonsRelationManager::class,
            AssignmentsRelationManager::class,
            QuizzesRelationManager::class,
        ];
    }
}
