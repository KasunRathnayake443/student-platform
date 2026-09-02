<?php

namespace App\Filament\SchoolAdmin\Resources\Teachers;

use App\Filament\Resources\Teachers\Schemas\TeacherForm;
use App\Filament\Resources\Teachers\Schemas\TeacherInfolist;
use App\Filament\Resources\Teachers\Tables\TeachersTable;
use App\Filament\SchoolAdmin\Resources\Teachers\Pages\CreateTeacher;
use App\Filament\SchoolAdmin\Resources\Teachers\Pages\EditTeacher;
use App\Filament\SchoolAdmin\Resources\Teachers\Pages\ListTeachers;
use App\Filament\SchoolAdmin\Resources\Teachers\Pages\ViewTeacher;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Models\Teacher;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|\UnitEnum|null $navigationGroup = 'School Users';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TeacherForm::configure($schema, [
            'schoolIds' => SchoolAdminScopes::schoolIds(),
            'passwordRequired' => false,
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TeacherInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeachersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeachers::route('/'),
            'create' => CreateTeacher::route('/create'),
            'view' => ViewTeacher::route('/{record}'),
            'edit' => EditTeacher::route('/{record}/edit'),
        ];
    }

    /**
     * School admins only see teachers assigned to at least one of their schools.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('schools', fn (Builder $query) => $query->whereIn('schools.id', SchoolAdminScopes::schoolIds()));
    }
}
