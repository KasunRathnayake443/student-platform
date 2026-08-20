<?php

namespace App\Filament\Teacher\Resources\Students\Pages;

use App\Filament\Teacher\Resources\Students\StudentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {

        return [

            EditAction::make(),

            DeleteAction::make(),

        ];

    }
}
