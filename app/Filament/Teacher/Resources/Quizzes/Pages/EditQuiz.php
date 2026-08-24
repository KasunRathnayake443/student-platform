<?php

namespace App\Filament\Teacher\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\Pages\EditQuiz as BaseEditQuiz;
use App\Filament\Teacher\Resources\Quizzes\QuizResource;
use App\Models\Quiz;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class EditQuiz extends BaseEditQuiz
{
    protected static string $resource = QuizResource::class;

    public function getLayout(): string
    {
        return 'filament.teacher.layouts.app';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('View Quiz')
                ->icon(Heroicon::OutlinedEye)
                ->url(fn () => static::getResource()::getUrl('view', ['record' => $this->getRecord()], panel: 'teacher')),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Prefill assigned teachers alongside the question data
    |--------------------------------------------------------------------------
    */

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        $record = $this->getRecord();

        if (! $record instanceof Quiz) {
            return $data;
        }

        $assigneeIds = $record->teachers()->pluck('teachers.id');

        if (! $assigneeIds->contains($record->teacher_id)) {
            $assigneeIds->push($record->teacher_id);
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
    | Keep the quiz_teacher pivot in sync after saving
    |--------------------------------------------------------------------------
    */

    protected function afterSave(): void
    {
        parent::afterSave();

        $record = $this->getRecord();

        if (! $record instanceof Quiz) {
            return;
        }

        $state = $this->form->getState();

        $rawTeacherIds = $state['teacher_ids'] ?? [];

        if (! is_array($rawTeacherIds)) {
            return;
        }

        $teacherIds = collect($rawTeacherIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($teacherIds->isNotEmpty()) {
            $record->teachers()->sync($teacherIds->toArray());
        }
    }
}
