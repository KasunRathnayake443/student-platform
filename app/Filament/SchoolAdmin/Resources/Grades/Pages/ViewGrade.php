<?php

namespace App\Filament\SchoolAdmin\Resources\Grades\Pages;

use App\Filament\SchoolAdmin\Resources\Grades\GradeResource;
use App\Filament\SchoolAdmin\Resources\Grades\RelationManagers\LearningClassesRelationManager;
use App\Filament\SchoolAdmin\Resources\LearningClasses\LearningClassResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGrade extends ViewRecord
{
    protected static string $resource = GradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createClass')
                ->label('Create Class')
                ->icon('heroicon-o-plus')
                ->url(fn () => LearningClassResource::getUrl('create', [
                    'grade_id' => $this->getRecord()->id,
                    'school_id' => $this->getRecord()->school_id,
                ])),
            EditAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            LearningClassesRelationManager::class,
        ];
    }

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
