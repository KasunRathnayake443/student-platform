<?php

namespace App\Filament\Teacher\Resources\LearningClasses\Pages;

use App\Filament\Teacher\Resources\LearningClasses\LearningClassResource;
use Filament\Resources\Pages\ViewRecord;

class ViewLearningClass extends ViewRecord
{
    protected static string $resource = LearningClassResource::class;

    public function getLayout(): string
    {
        return 'filament.teacher.layouts.app';
    }
}
