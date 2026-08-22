<?php

namespace App\Filament\Teacher\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\Pages\EditAssignment as BaseEditAssignment;
use App\Filament\Teacher\Resources\Assignments\AssignmentResource;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class EditAssignment extends BaseEditAssignment
{
    protected static string $resource = AssignmentResource::class;

    public function getLayout(): string
    {
        return 'filament.teacher.layouts.app';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('View Assignment')
                ->icon(Heroicon::OutlinedEye)
                ->url(fn () => static::getResource()::getUrl('view', ['record' => $this->getRecord()], panel: 'teacher')),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Prefill assigned teachers alongside the attachment data
    |--------------------------------------------------------------------------
    */

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        $assigneeIds = $this->record->teachers()->pluck('teachers.id');

        if (! $assigneeIds->contains($this->record->teacher_id)) {
            $assigneeIds->push($this->record->teacher_id);
        }

        $data['teacher_ids'] = $assigneeIds
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = parent::mutateFormDataBeforeSave($data);

        unset($data['teacher_ids']);

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Keep the assignment_teacher pivot in sync after saving
    |--------------------------------------------------------------------------
    */

    protected function afterSave(): void
    {
        parent::afterSave();

        $state = $this->form->getState();

        $teacherIds = collect($state['teacher_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($teacherIds->isNotEmpty()) {
            $this->record->teachers()->sync($teacherIds->toArray());
        }
    }
}
