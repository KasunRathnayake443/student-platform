<?php

namespace App\Filament\Resources\Schools\Pages;

use App\Filament\Resources\Schools\SchoolResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\Schools\RelationManagers\GradesRelationManager;

class ViewSchool extends ViewRecord
{
    protected static string $resource = SchoolResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }


    public function getRelationManagers(): array
    {
        return [
            GradesRelationManager::class,
        ];
    }
}