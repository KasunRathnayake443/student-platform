<?php

namespace App\Filament\Resources\Quizzes;

use App\Filament\Resources\Quizzes\Pages\CreateQuiz;
use App\Filament\Resources\Quizzes\Pages\EditQuiz;
use App\Filament\Resources\Quizzes\Pages\ListQuizzes;
use App\Filament\Resources\Quizzes\Pages\ViewQuiz;
use App\Filament\Resources\Quizzes\RelationManagers\QuizAttemptsRelationManager;
use App\Filament\Resources\Quizzes\Schemas\QuizForm;
use App\Filament\Resources\Quizzes\Schemas\QuizInfolist;
use App\Filament\Resources\Quizzes\Tables\QuizzesTable;
use App\Models\Quiz;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $recordTitleAttribute = 'title';

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    |
    | Quizzes are primarily accessed through Learning Classes.
    |
    */

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $modelLabel = 'Quiz';

    protected static ?string $pluralModelLabel = 'Quizzes';

    public static function form(Schema $schema): Schema
    {
        return QuizForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QuizInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuizzesTable::configure($table);
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
            'index' => ListQuizzes::route('/'),
            'create' => CreateQuiz::route('/create'),
            'view' => ViewQuiz::route('/{record}'),
            'edit' => EditQuiz::route('/{record}/edit'),
        ];
    }
}
