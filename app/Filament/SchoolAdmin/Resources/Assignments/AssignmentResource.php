<?php

namespace App\Filament\SchoolAdmin\Resources\Assignments;

use App\Filament\Resources\Assignments\RelationManagers\SubmissionsRelationManager;
use App\Filament\Resources\Assignments\Schemas\AssignmentForm;
use App\Filament\Resources\Assignments\Schemas\AssignmentInfolist;
use App\Filament\SchoolAdmin\Resources\Assignments\Pages\CreateAssignment;
use App\Filament\SchoolAdmin\Resources\Assignments\Pages\EditAssignment;
use App\Filament\SchoolAdmin\Resources\Assignments\Pages\ListAssignments;
use App\Filament\SchoolAdmin\Resources\Assignments\Pages\ViewAssignment;
use App\Filament\SchoolAdmin\Resources\Assignments\Tables\AssignmentsTable;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Models\Assignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Teaching Content';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Assignment';

    protected static ?string $pluralModelLabel = 'Assignments';

    public static function form(Schema $schema): Schema
    {
        return AssignmentForm::configure($schema, ['schoolIds' => SchoolAdminScopes::schoolIds()]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AssignmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssignmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssignments::route('/'),
            'create' => CreateAssignment::route('/create'),
            'view' => ViewAssignment::route('/{record}'),
            'edit' => EditAssignment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('learningClass.grade', fn (Builder $query) => $query->whereIn('school_id', SchoolAdminScopes::schoolIds()));
    }
}
