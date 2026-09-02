<?php

namespace App\Filament\SchoolAdmin\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\Pages\EditQuiz as BaseEditQuiz;
use App\Filament\SchoolAdmin\Resources\Quizzes\QuizResource;

class EditQuiz extends BaseEditQuiz
{
    protected static string $resource = QuizResource::class;
}
