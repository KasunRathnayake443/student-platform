<?php

namespace App\Filament\Concerns;

use Illuminate\Validation\ValidationException;

trait HasPaginatedQuestions
{
    public int $questionsPage = 1;

    public int $questionsPerPage = 10;

    public function gotoQuestionsPage(int $page): void
    {
        $this->questionsPage = max(1, $page);
    }

    /**
     * When validation fails on a question that is not on the page the user is
     * currently viewing, move them to the page that contains the first error.
     */
    protected function focusFirstInvalidQuestion(ValidationException $exception): void
    {
        $questions = $this->data['questions'] ?? [];

        if (! is_array($questions) || $questions === []) {
            return;
        }

        $keys = array_keys($questions);
        $firstIndex = null;

        foreach (array_keys($exception->errors()) as $field) {
            if (! is_string($field) || ! preg_match('/(?:^|\.)questions\.([^.]+)(?:\.|$)/', $field, $matches)) {
                continue;
            }

            foreach ($keys as $index => $key) {
                if ((string) $key !== (string) $matches[1]) {
                    continue;
                }

                $firstIndex = $firstIndex === null ? $index : min($firstIndex, $index);

                break;
            }
        }

        if ($firstIndex === null) {
            return;
        }

        $perPage = max(1, (int) ($this->questionsPerPage ?? 10));

        $this->questionsPage = max(1, (int) floor($firstIndex / $perPage) + 1);
    }
}
