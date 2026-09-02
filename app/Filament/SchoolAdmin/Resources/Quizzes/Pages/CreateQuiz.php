<?php

namespace App\Filament\SchoolAdmin\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\Pages\CreateQuiz as BaseCreateQuiz;
use App\Filament\SchoolAdmin\Resources\Quizzes\QuizResource;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Models\LearningClass;

class CreateQuiz extends BaseCreateQuiz
{
    protected static string $resource = QuizResource::class;

    public function mount(): void
    {
        parent::mount();

        $classId = (int) request()->query('learningClassId');

        if ($classId && ! $this->learningClassInScope($classId)) {
            $this->form->fill(['learning_class_id' => null]);
        }
    }

    protected function learningClassInScope(int $classId): bool
    {
        return LearningClass::query()
            ->whereKey($classId)
            ->whereHas('grade', fn ($query) => $query->whereIn('school_id', SchoolAdminScopes::schoolIds()))
            ->exists();
    }

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
