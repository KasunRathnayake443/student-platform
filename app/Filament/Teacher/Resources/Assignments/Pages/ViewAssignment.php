<?php

namespace App\Filament\Teacher\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\Pages\ViewAssignment as BaseViewAssignment;
use App\Filament\Teacher\Resources\Assignments\AssignmentResource;
use Filament\Actions\EditAction;

class ViewAssignment extends BaseViewAssignment
{
    protected static string $resource = AssignmentResource::class;

    public function getLayout(): string
    {
        return 'filament.teacher.layouts.app';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit Assignment')
                ->icon('heroicon-o-pencil')
                ->url(
                    fn () => AssignmentResource::getUrl('edit', ['record' => $this->getRecord()], panel: 'teacher')
                ),
        ];
    }
}
