<?php

namespace App\Filament\SchoolAdmin\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\Pages\ViewQuiz as BaseViewQuiz;
use App\Filament\SchoolAdmin\Resources\Quizzes\QuizResource;

class ViewQuiz extends BaseViewQuiz
{
    protected static string $resource = QuizResource::class;
}
