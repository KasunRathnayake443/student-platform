<?php

namespace App\Filament\Teacher\Resources\Quizzes;

use App\Filament\Resources\Quizzes\Schemas\QuizInfolist;
use App\Filament\Teacher\Pages\TeacherDashboard;
use App\Filament\Teacher\Resources\Quizzes\Pages\EditQuiz;
use App\Filament\Teacher\Resources\Quizzes\Pages\ViewQuiz;
use App\Filament\Teacher\Resources\Quizzes\RelationManagers\QuizAttemptsRelationManager;
use App\Filament\Teacher\Resources\Quizzes\Schemas\QuizForm;
use App\Models\Quiz;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'title';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return QuizForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QuizInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            QuizAttemptsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'view' => ViewQuiz::route('/{record}'),
            'edit' => EditQuiz::route('/{record}/edit'),
        ];
    }

    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        return TeacherDashboard::getUrl();
    }

    /**
     * Teachers only see quizzes belonging to classes they teach.
     */
    public static function getEloquentQuery(): Builder
    {
        $teacher = auth()->user()->teacher;

        return parent::getEloquentQuery()
            ->whereHas('learningClass.teachers', function ($query) use ($teacher) {
                $query->where('teachers.id', $teacher?->id);
            })
            ->with(['teacher.user', 'teachers.user', 'learningClass', 'questions']);
    }
}
