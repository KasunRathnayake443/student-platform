<?php

namespace App\Filament\SchoolAdmin\Resources\Quizzes\Pages;

use App\Filament\SchoolAdmin\Resources\Quizzes\QuizResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuizzes extends ListRecords
{
    protected static string $resource = QuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
