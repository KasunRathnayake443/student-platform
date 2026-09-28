<?php

namespace App\Filament\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\QuizResource;
use App\Models\Quiz;
use App\Services\QuizImportService;
use App\Services\QuizQuestionsService;
use App\Services\SchoolEmailNotificationService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class EditQuiz extends EditRecord
{
    protected static string $resource = QuizResource::class;

    /** @var array<int, array<string, mixed>>|null */
    protected ?array $deferredQuestions = null;

    protected mixed $deferredImportFile = null;

    /** @var array<int, int> */
    protected array $deferredTeacherIds = [];

    protected bool $suppressQuizEmail = false;

    protected bool $manualQuizSaveCompleted = false;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function submitAndEmailStudentsAction(): Action
    {
        return Action::make('submitAndEmailStudents')
            ->label('Submit & Email Students')
            ->icon('heroicon-o-paper-airplane')
            ->color('primary')
            ->visible(function (): bool {
                $quiz = $this->getRecord();

                return $quiz instanceof Quiz && (bool) $quiz->email_sent;
            })
            ->action(function (): void {
                $this->handleSubmitAndEmailStudents();
            });
    }

    /** @return array<Action | ActionGroup> */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->submitAndEmailStudentsAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Submit');
    }

    protected function handleSubmitAndEmailStudents(): void
    {
        $this->suppressQuizEmail = true;
        $this->manualQuizSaveCompleted = false;

        try {
            $this->save(shouldRedirect: false, shouldSendSavedNotification: false);

            $quiz = $this->getRecord();

            if (! $this->manualQuizSaveCompleted || ! $quiz instanceof Quiz) {
                return;
            }

            $service = app(SchoolEmailNotificationService::class);
            $service->sendQuizNotification($quiz);
            $service->markQuizEmailSent($quiz);

            Notification::make()
                ->title('Quiz saved and students notified.')
                ->success()
                ->send();
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Quiz saved, but students could not be notified.')
                ->danger()
                ->send();
        } finally {
            $this->suppressQuizEmail = false;
            $this->manualQuizSaveCompleted = false;
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        unset($data['email_sent']);

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

        $data['available_immediately'] = ($record->availability_type ?? 'immediate') !== 'scheduled';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->deferredQuestions = $data['questions'] ?? [];
        $this->deferredImportFile = $data['import_file'] ?? null;

        $this->deferredTeacherIds = $this->normaliseTeacherIds($data['teacher_ids'] ?? []);
        $data['teacher_id'] = Arr::first($this->deferredTeacherIds) ?? $data['teacher_id'] ?? null;

        $isImmediate = (bool) ($data['available_immediately'] ?? false);
        $data['availability_type'] = $isImmediate ? 'immediate' : 'scheduled';

        if ($isImmediate) {
            $data['start_at'] = null;
        }

        unset(
            $data['questions'],
            $data['import_file'],
            $data['teacher_ids'],
            $data['available_immediately'],
            $data['email_sent'],
        );

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

        $this->handleQuizEmailAfterSave($record);
    }

    protected function handleQuizEmailAfterSave(Quiz $quiz): void
    {
        if ($this->suppressQuizEmail) {
            $this->manualQuizSaveCompleted = true;

            return;
        }

        if (! $quiz->email_sent && $quiz->is_published) {
            app(SchoolEmailNotificationService::class)->notifyPublishedQuiz($quiz);
        }
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
