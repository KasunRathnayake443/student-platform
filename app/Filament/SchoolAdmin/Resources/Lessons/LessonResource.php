<?php

namespace App\Filament\SchoolAdmin\Resources\Lessons;

use App\Filament\Resources\Lessons\Schemas\LessonForm;
use App\Filament\Resources\Lessons\Schemas\LessonInfolist;
use App\Filament\Resources\Lessons\Tables\LessonsTable;
use App\Filament\SchoolAdmin\Resources\Lessons\Pages\CreateLesson;
use App\Filament\SchoolAdmin\Resources\Lessons\Pages\EditLesson;
use App\Filament\SchoolAdmin\Resources\Lessons\Pages\ListLessons;
use App\Filament\SchoolAdmin\Resources\Lessons\Pages\ViewLesson;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Models\Lesson;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'Teaching Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Lesson';

    protected static ?string $pluralModelLabel = 'Lessons';

    public static function form(Schema $schema): Schema
    {
        return LessonForm::configure($schema, ['schoolIds' => SchoolAdminScopes::schoolIds()]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LessonInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LessonsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLessons::route('/'),
            'create' => CreateLesson::route('/create'),
            'view' => ViewLesson::route('/{record}'),
            'edit' => EditLesson::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('learningClass.grade', fn (Builder $query) => $query->whereIn('school_id', SchoolAdminScopes::schoolIds()));
    }
}
