<?php

namespace App\Filament\SchoolAdmin\Resources\Quizzes;

use App\Filament\Resources\Quizzes\RelationManagers\QuizAttemptsRelationManager;
use App\Filament\Resources\Quizzes\Schemas\QuizForm;
use App\Filament\Resources\Quizzes\Schemas\QuizInfolist;
use App\Filament\Resources\Quizzes\Tables\QuizzesTable;
use App\Filament\SchoolAdmin\Resources\Quizzes\Pages\CreateQuiz;
use App\Filament\SchoolAdmin\Resources\Quizzes\Pages\EditQuiz;
use App\Filament\SchoolAdmin\Resources\Quizzes\Pages\ListQuizzes;
use App\Filament\SchoolAdmin\Resources\Quizzes\Pages\ViewQuiz;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Models\Quiz;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Teaching Content';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Quiz';

    protected static ?string $pluralModelLabel = 'Quizzes';

    public static function form(Schema $schema): Schema
    {
        return QuizForm::configure($schema, ['schoolIds' => SchoolAdminScopes::schoolIds()]);
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('learningClass.grade', fn (Builder $query) => $query->whereIn('school_id', SchoolAdminScopes::schoolIds()));
    }
}
