<?php

namespace App\Filament\Teacher\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\Pages\EditQuiz as BaseEditQuiz;
use App\Filament\Teacher\Resources\Quizzes\QuizResource;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class EditQuiz extends BaseEditQuiz
{
    protected static string $resource = QuizResource::class;

    public function getLayout(): string
    {
        return 'filament.teacher.layouts.app';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('View Quiz')
                ->icon(Heroicon::OutlinedEye)
                ->url(fn () => static::getResource()::getUrl('view', ['record' => $this->getRecord()], panel: 'teacher')),
        ];
    }
}
