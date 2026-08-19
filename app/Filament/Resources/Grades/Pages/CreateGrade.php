<?php

namespace App\Filament\Resources\Grades\Pages;

use App\Filament\Resources\Grades\GradeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGrade extends CreateRecord
{
    protected static string $resource = GradeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (request()->has('school_id')) {
            $data['school_id'] = request('school_id');
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        if (request()->has('school_id')) {
            return route(
                'filament.admin.resources.schools.view',
                [
                    'record' => request('school_id'),
                    'relation' => 1,
                ]
            );
        }

        return parent::getRedirectUrl();
    }
}
