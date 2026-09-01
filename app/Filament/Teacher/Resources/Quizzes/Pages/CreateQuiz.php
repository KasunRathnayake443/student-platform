<?php

namespace App\Filament\Teacher\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\Pages\CreateQuiz as BaseCreateQuiz;
use App\Filament\Teacher\Resources\Quizzes\QuizResource;

class CreateQuiz extends BaseCreateQuiz
{
    protected static string $resource = QuizResource::class;

    public function mount(): void
    {
        parent::mount();

        if ($teacher = auth()->user()?->teacher) {
            $existing = $this->data['teacher_ids'] ?? [];
            if (empty($existing)) {
                $this->data['teacher_ids'] = [$teacher->getKey()];
            }
        }
    }

    public function getLayout(): string
    {
        return 'filament.teacher.layouts.app';
    }
}
