<?php

namespace App\Filament\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\QuizResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditQuiz extends EditRecord
{
    protected static string $resource = QuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->load('questions.options');

        $data['questions'] = $this->record->questions->map(function ($q) {
            return [
                'id' => $q->id,
                'question_text' => $q->question_text,
                'points' => $q->points,
                'explanation' => $q->explanation,
                'options' => $q->options->map(function ($opt) {
                    return [
                        'id' => $opt->id,
                        'option_text' => $opt->option_text,
                        'is_correct' => (bool) $opt->is_correct,
                    ];
                })->toArray(),
            ];
        })->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['questions']);

        return $data;
    }

    protected function afterSave(): void
    {
        $state = $this->form->getState();
        $questionsData = $state['questions'] ?? [];

        // Delete existing questions and options, recreate with updated state
        $this->record->questions()->delete();

        $totalPoints = 0;
        foreach ($questionsData as $index => $qData) {
            $points = (int) ($qData['points'] ?? 1);
            $totalPoints += $points;

            $question = $this->record->questions()->create([
                'question_text' => $qData['question_text'],
                'points' => $points,
                'explanation' => $qData['explanation'] ?? null,
                'sort_order' => $index + 1,
            ]);

            $optionsData = $qData['options'] ?? [];
            foreach ($optionsData as $optIndex => $optData) {
                $question->options()->create([
                    'option_text' => $optData['option_text'],
                    'is_correct' => (bool) ($optData['is_correct'] ?? false),
                    'sort_order' => $optIndex + 1,
                ]);
            }
        }

        $this->record->updateQuietly(['total_points' => $totalPoints]);
    }
}
