<?php

namespace App\Filament\Teacher\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\Pages\CreateQuiz as BaseCreateQuiz;
use App\Filament\Teacher\Resources\Quizzes\QuizResource;
use App\Models\Quiz;
use App\Services\SchoolEmailNotificationService;

class CreateQuiz extends BaseCreateQuiz
{
    protected static string $resource = QuizResource::class;

    public function mount(): void
    {
        parent::mount();
    }

    protected function afterCreate(): void
    {
        parent::afterCreate();

        if ($this->record instanceof Quiz) {
            app(SchoolEmailNotificationService::class)->quizCreated($this->record);
        }
    }

    public function getLayout(): string
    {
        return 'filament.teacher.layouts.app';
    }
}
