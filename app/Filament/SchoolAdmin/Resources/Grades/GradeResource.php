<?php

namespace App\Filament\SchoolAdmin\Resources\Grades;

use App\Filament\Resources\Grades\Schemas\GradeForm;
use App\Filament\Resources\Grades\Schemas\GradeInfolist;
use App\Filament\Resources\Grades\Tables\GradesTable;
use App\Filament\SchoolAdmin\Resources\Grades\Pages\CreateGrade;
use App\Filament\SchoolAdmin\Resources\Grades\Pages\EditGrade;
use App\Filament\SchoolAdmin\Resources\Grades\Pages\ListGrades;
use App\Filament\SchoolAdmin\Resources\Grades\Pages\ViewGrade;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Models\Grade;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GradeResource extends Resource
{
    protected static ?string $model = Grade::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookmarkSquare;

    protected static string|\UnitEnum|null $navigationGroup = 'Institution Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return GradeForm::configure($schema, ['schoolIds' => SchoolAdminScopes::schoolIds()]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GradeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GradesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGrades::route('/'),
            'create' => CreateGrade::route('/create'),
            'view' => ViewGrade::route('/{record}'),
            'edit' => EditGrade::route('/{record}/edit'),
        ];
    }

    /**
     * School admins only see grades belonging to their assigned schools.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('school_id', SchoolAdminScopes::schoolIds());
    }
}
