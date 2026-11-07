<?php

namespace App\Filament\Resources\Quizzes\Pages;

use App\Filament\Concerns\HasPaginatedQuestions;
use App\Filament\Resources\Quizzes\QuizResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewQuiz extends ViewRecord
{
    use HasPaginatedQuestions;

    protected static string $resource = QuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
