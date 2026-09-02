<?php

namespace App\Filament\SchoolAdmin\Resources\Schools\Pages;

use App\Filament\Resources\Schools\Widgets\SchoolStats;
use App\Filament\SchoolAdmin\Resources\Schools\SchoolResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSchool extends ViewRecord
{
    protected static string $resource = SchoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
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
