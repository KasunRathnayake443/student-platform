<?php

namespace App\Filament\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\QuizResource;
use App\Models\Quiz;
use App\Services\QuizImportService;
use App\Services\QuizQuestionsService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use RuntimeException;

class EditQuiz extends EditRecord
{
    protected static string $resource = QuizResource::class;

    /** @var array<int, array<string, mixed>>|null */
    protected ?array $deferredQuestions = null;

    protected mixed $deferredImportFile = null;

    /** @var array<int, int> */
    protected array $deferredTeacherIds = [];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Quiz $record */
        $record = $this->record;

        $record->load('questions.options');

        $data['questions'] = $record->questions->map(function ($q) {
            return [
                'id' => $q->id,
                'question_text' => $q->question_text,
                'points' => $q->points,
                'explanation' => $q->explanation,
                'question_image' => $q->question_image,
                'question_video' => $q->question_video,
                'options' => $q->options->map(function ($opt) {
                    return [
                        'id' => $opt->id,
                        'option_text' => $opt->option_text,
                        'is_correct' => (bool) $opt->is_correct,
                    ];
                })->toArray(),
            ];
        })->toArray();

        $data['teacher_ids'] = $this->assignedTeacherIds($record);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->deferredQuestions = $data['questions'] ?? [];
        $this->deferredImportFile = $data['import_file'] ?? null;

        $this->deferredTeacherIds = $this->normaliseTeacherIds($data['teacher_ids'] ?? []);
        $data['teacher_id'] = Arr::first($this->deferredTeacherIds) ?? $data['teacher_id'] ?? null;

        unset($data['questions'], $data['import_file'], $data['teacher_ids']);

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var Quiz $record */
        $record = $this->record;

        $questionsData = $this->deferredQuestions ?? [];

        if ($questionsData === [] && ! blank($this->deferredImportFile)) {
            try {
                $importFile = $this->deferredImportFile;
                $imported = app(QuizImportService::class)
                    ->parseStoredPath(is_array($importFile) ? $importFile[0] : $importFile);

                foreach ($imported as $q) {
                    $questionsData[] = $q;
                }
            } catch (RuntimeException $e) {
                $this->notifyImportFailure($e);

                return;
            }
        }

        $record->questions()->delete();

        app(QuizQuestionsService::class)->saveQuestions($record, $questionsData);

        $teacherIds = $this->deferredTeacherIds;

        if ($teacherIds === []) {
            $teacherIds = Arr::wrap($record->teacher_id);
        }

        $record->teachers()->sync(
            array_values(array_filter(array_map(intval(...), $teacherIds)))
        );
    }

    /**
     * @return array<int, int>
     */
    protected function assignedTeacherIds(Quiz $quiz): array
    {
        $ids = $quiz->teachers()->pluck('teachers.id');

        if (! $ids->contains($quiz->teacher_id)) {
            $ids->push($quiz->teacher_id);
        }

        return $ids
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * @return array<int, int>
     */
    protected function normaliseTeacherIds(mixed $value): array
    {
        if (! is_array($value)) {
            $value = [$value];
        }

        return array_values(
            array_unique(
                array_filter(array_map(intval(...), $value))
            )
        );
    }

    protected function notifyImportFailure(RuntimeException $e): void
    {
        Notification::make()
            ->title('Could not import questions')
            ->body($e->getMessage())
            ->danger()
            ->send();
    }
}
