<?php

namespace App\Filament\Resources\Schools\Pages;

use App\Filament\Resources\Schools\SchoolResource;
use App\Filament\Resources\Schools\Widgets\SchoolStats;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSchool extends ViewRecord
{
    protected static string $resource = SchoolResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }


    protected function getHeaderWidgets(): array
    {
        return [
            SchoolStats::class,
        ];
    }


    public function getHeaderWidgetsData(): array
    {
        return [
            'record' => $this->record,
        ];
    }
}