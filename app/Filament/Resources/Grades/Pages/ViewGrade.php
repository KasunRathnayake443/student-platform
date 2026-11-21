<?php

namespace App\Filament\Resources\Grades\Pages;

use App\Filament\Resources\Grades\GradeResource;
use App\Filament\Resources\Grades\RelationManagers\LearningClassesRelationManager;
use App\Filament\Resources\LearningClasses\LearningClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGrade extends ViewRecord
{
    protected static string $resource = GradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createClass')
                ->label('Create Class')
                ->icon('heroicon-o-plus')
                ->url(fn () => LearningClassResource::getUrl('create', [
                    'grade_id' => $this->getRecord()->id,
                    'school_id' => $this->getRecord()->school_id,
                ])),
            Actions\EditAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            LearningClassesRelationManager::class,
        ];
    }
}
