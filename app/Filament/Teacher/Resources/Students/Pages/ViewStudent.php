<?php

namespace App\Filament\Teacher\Resources\Students\Pages;

use App\Filament\Teacher\Resources\Students\StudentResource;
use App\Models\Student;
use App\Services\ClassContextService;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\Url;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    #[Url]
    public ?int $classId = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $student = $this->getRecord();

        $this->classId = $student instanceof Student
            ? app(ClassContextService::class)->resolveForStudent($this->classId, $student)
            : null;
    }

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
        return 'Student Profile';
    }
}
