<?php

namespace App\Services;

use App\Models\Quiz;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

class QuizQuestionsService
{
    /**
     * Persist the questions repeater data onto a quiz, including options
     * and any media (image/video) stored by the FileUpload fields.
     *
     * @param  array<int, array<string, mixed>>  $questionsData
     */
    public function saveQuestions(Quiz $quiz, array $questionsData): int
    {
        $totalPoints = 0;
        $sortOrder = 1;

        foreach ($questionsData as $qData) {
            $text = trim((string) ($qData['question_text'] ?? ''));

            if ($text === '') {
                continue;
            }

            $points = max(1, (int) ($qData['points'] ?? 1));
            $totalPoints += $points;

            $question = $quiz->questions()->create([
                'question_text' => $text,
                'points' => $points,
                'explanation' => $qData['explanation'] ?? null,
                'sort_order' => $sortOrder++,
                'question_image' => $this->resolveMedia($qData['question_image'] ?? null),
                'question_video' => $this->resolveMedia($qData['question_video'] ?? null),
            ]);

            $optionsData = $qData['options'] ?? [];
            $optOrder = 1;

            foreach ($optionsData as $optData) {
                $optText = trim((string) (Arr::get($optData, 'option_text') ?? ''));

                if ($optText === '') {
                    continue;
                }

                $question->options()->create([
                    'option_text' => $optText,
                    'is_correct' => (bool) (Arr::get($optData, 'is_correct') ?? false),
                    'sort_order' => $optOrder++,
                ]);
            }
        }

        $quiz->updateQuietly(['total_points' => $totalPoints]);

        return $totalPoints;
    }

    /**
     * FileUploads dehydrate to either a plain stored path string, an array of path strings,
     * or an UploadedFile/TemporaryUploadedFile instance.
     * We ensure any media is safely stored and its relative path string is returned.
     */
    protected function resolveMedia(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = Arr::first($value);
        }

        if ($value instanceof UploadedFile) {
            $disk = (string) config('filament.default_filesystem_disk', 'local');

            return $value->store('quiz_questions/media', $disk) ?: null;
        }

        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        return null;
    }
}
