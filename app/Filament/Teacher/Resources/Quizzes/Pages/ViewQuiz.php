<?php

namespace App\Filament\Teacher\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\Pages\ViewQuiz as BaseViewQuiz;
use App\Filament\Teacher\Resources\Quizzes\QuizResource;
use Filament\Actions\EditAction;

class ViewQuiz extends BaseViewQuiz
{
    protected static string $resource = QuizResource::class;

    public function getLayout(): string
    {
        return 'filament.teacher.layouts.app';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->url(
                    fn () => QuizResource::getUrl('edit', ['record' => $this->getRecord()], panel: 'teacher')
                ),
        ];
    }
}
