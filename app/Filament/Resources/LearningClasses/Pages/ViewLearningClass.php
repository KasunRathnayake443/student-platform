<?php

namespace App\Filament\Resources\LearningClasses\Pages;

use App\Filament\Resources\LearningClasses\LearningClassResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLearningClass extends ViewRecord
{
    protected static string $resource = LearningClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
