<?php

namespace App\Filament\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\QuizResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuiz extends CreateRecord
{
    protected static string $resource = QuizResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['questions']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $state = $this->form->getState();
        $questionsData = $state['questions'] ?? [];

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
