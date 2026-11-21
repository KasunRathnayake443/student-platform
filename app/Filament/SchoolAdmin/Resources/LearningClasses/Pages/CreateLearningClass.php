<?php

namespace App\Filament\SchoolAdmin\Resources\LearningClasses\Pages;

use App\Filament\SchoolAdmin\Resources\LearningClasses\LearningClassResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLearningClass extends CreateRecord
{
    protected static string $resource = LearningClassResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (request()->has('grade_id')) {
            $data['grade_id'] = request()->get('grade_id');
        }

        return $data;
    }

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
