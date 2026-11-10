<?php

namespace App\Filament\SchoolAdmin\Resources\Teachers;

use App\Filament\Resources\Teachers\Schemas\TeacherForm;
use App\Filament\Resources\Teachers\Schemas\TeacherInfolist;
use App\Filament\Rules\InScope;
use App\Filament\SchoolAdmin\Resources\Teachers\Pages\CreateTeacher;
use App\Filament\SchoolAdmin\Resources\Teachers\Pages\EditTeacher;
use App\Filament\SchoolAdmin\Resources\Teachers\Pages\ListTeachers;
use App\Filament\SchoolAdmin\Resources\Teachers\Pages\ViewTeacher;
use App\Filament\SchoolAdmin\Resources\Teachers\Tables\TeachersTable as SchoolTeachersTable;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Models\Teacher;
use App\Services\TeacherService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
        return SchoolTeachersTable::configure($table);
    }

    /**
     * School admins may never delete a teacher from the system.
     */
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    /**
     * Remove a teacher from one or more of the admin's schools.
     *
     * Only the selected schools and the classes that belong to them are
     * detached. The teacher account and any other school assignments remain.
     */
    public static function removeFromSchoolAction(): Action
    {
        return Action::make('removeFromSchool')
            ->label('Remove from School')
            ->icon(Heroicon::OutlinedBuildingOffice2)
            ->color('danger')
            ->modalHeading(fn (Teacher $record): string => 'Remove '.$record->user->name.' from school')
            ->modalDescription('This only removes the teacher from the selected schools and their classes. The teacher account and other schools are not affected.')
            ->form(fn (Teacher $record): array => [
                Select::make('schools')
                    ->label('Schools')
                    ->multiple()
                    ->options(fn (): array => $record->schools()
                        ->whereIn('schools.id', SchoolAdminScopes::schoolIds())
                        ->orderBy('schools.name')
                        ->pluck('schools.name', 'schools.id')
                        ->all())
                    ->rules([
                        new InScope(SchoolAdminScopes::schoolIds(), 'One or more selected schools are outside your assigned schools.'),
                    ])
                    ->required(),
            ])
            ->visible(fn (Teacher $record): bool => $record->schools()
                ->whereIn('schools.id', SchoolAdminScopes::schoolIds())
                ->exists())
            ->action(function (Teacher $record, array $data): void {
                $schoolIds = array_values(array_intersect(
                    array_map('intval', (array) ($data['schools'] ?? [])),
                    SchoolAdminScopes::schoolIds(),
                ));

                app(TeacherService::class)->removeFromSchools($record, $schoolIds);
            })
            ->successNotificationTitle('Teacher removed from the selected schools');
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
