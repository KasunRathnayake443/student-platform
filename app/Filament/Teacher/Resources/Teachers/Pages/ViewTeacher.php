<?php

namespace App\Filament\Teacher\Resources\Teachers\Pages;

use App\Filament\Teacher\Resources\Teachers\TeacherResource;
use Filament\Resources\Pages\ViewRecord;

class ViewTeacher extends ViewRecord
{
    protected static string $resource = TeacherResource::class;

    public function getLayout(): string
    {
        return 'filament.teacher.layouts.app';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Teacher Profile';
    }
}
