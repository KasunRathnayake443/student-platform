<?php

namespace App\Filament\SchoolAdmin\Resources\LearningClasses\Pages;

use App\Filament\SchoolAdmin\Resources\LearningClasses\LearningClassResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLearningClass extends EditRecord
{
    protected static string $resource = LearningClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
