<?php

namespace App\Filament\SchoolAdmin\Resources\Schools\Pages;

use App\Filament\SchoolAdmin\Resources\Schools\SchoolResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSchool extends EditRecord
{
    protected static string $resource = SchoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
