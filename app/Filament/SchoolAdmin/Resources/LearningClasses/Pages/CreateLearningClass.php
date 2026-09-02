<?php

namespace App\Filament\SchoolAdmin\Resources\LearningClasses\Pages;

use App\Filament\SchoolAdmin\Resources\LearningClasses\LearningClassResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLearningClass extends CreateRecord
{
    protected static string $resource = LearningClassResource::class;
}
