<?php

namespace App\Filament\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\AssignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAssignment extends ViewRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [

            EditAction::make()
                ->label('Edit Assignment')
                ->icon('heroicon-o-pencil'),

            DeleteAction::make()
                ->label('Delete Assignment')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation(),

        ];
    }

    protected function afterFill(): void
    {
        $this->record->load([
            'learningClass',
            'teacher.user',
            'attachments',
            'submissions',
        ]);
    }
}
