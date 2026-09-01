<?php

namespace App\Filament\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\QuizResource;
use App\Models\Quiz;
use App\Services\QuizImportService;
use App\Services\QuizQuestionsService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use RuntimeException;

class CreateQuiz extends CreateRecord
{
    protected static string $resource = QuizResource::class;

    /** @var array<int, array<string, mixed>>|null */
    protected ?array $deferredQuestions = null;

    protected mixed $deferredImportFile = null;

    /** @var array<int, int> */
    protected array $deferredTeacherIds = [];

    public function mount(): void
    {
        parent::mount();

        $fillData = [
            'max_attempts' => 1,
            'passing_percentage' => 50,
            'show_correct_answers_after_submission' => true,
            'available_immediately' => true,
            'availability_type' => 'immediate',
            'is_published' => true,
        ];

        if ($classId = (int) request()->query('learningClassId')) {
            $fillData['learning_class_id'] = $classId;
        }

        if ($teacher = auth()->user()?->teacher) {
            $fillData['teacher_ids'] = [$teacher->getKey()];
        }

        $this->form->fill(array_merge($fillData, array_filter($this->data ?? [], fn ($v) => $v !== null)));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->deferredQuestions = $data['questions'] ?? [];
        $this->deferredImportFile = $data['import_file'] ?? null;

        $this->deferredTeacherIds = $this->normaliseTeacherIds($data['teacher_ids'] ?? []);

        if (empty($this->deferredTeacherIds) && auth()->user()?->teacher) {
            $this->deferredTeacherIds = [auth()->user()->teacher->getKey()];
        }

        $data['teacher_id'] = Arr::first($this->deferredTeacherIds);

        unset($data['questions'], $data['import_file'], $data['teacher_ids']);

        return $data;
    }

    protected function afterCreate(): void
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

        app(QuizQuestionsService::class)->saveQuestions($record, $questionsData);

        $this->syncAssignedTeachers($record);
    }

    /**
     * All selected teachers become assignees for the quiz.
     */
    protected function syncAssignedTeachers(Quiz $quiz): void
    {
        $teacherIds = $this->deferredTeacherIds;

        if ($teacherIds === []) {
            $teacherIds = Arr::wrap($quiz->teacher_id);
        }

        $quiz->teachers()->sync(
            array_values(array_filter(array_map(intval(...), $teacherIds)))
        );
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

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
