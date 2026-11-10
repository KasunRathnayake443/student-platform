<?php

namespace App\Filament\Resources\Teachers;

use App\Filament\Resources\Teachers\Pages\CreateTeacher;
use App\Filament\Resources\Teachers\Pages\EditTeacher;
use App\Filament\Resources\Teachers\Pages\ListTeachers;
use App\Filament\Resources\Teachers\Pages\ViewTeacher;
use App\Filament\Resources\Teachers\Schemas\TeacherForm;
use App\Filament\Resources\Teachers\Schemas\TeacherInfolist;
use App\Filament\Resources\Teachers\Tables\TeachersTable;
use App\Models\Teacher;
use App\Services\TeacherService;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
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
        return TeacherForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TeacherInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeachersTable::configure($table);
    }

    /**
     * Delete action that fully removes the teacher from the system.
     *
     * This is only used by the super-admin panel. It deletes the teacher
     * profile (and everything that belongs to it) and the linked user account.
     */
    public static function deleteFromSystemAction(): DeleteAction
    {
        return DeleteAction::make()
            ->label('Delete Teacher')
            ->modalHeading(fn (Teacher $record): string => 'Delete '.$record->user->name)
            ->modalDescription('This permanently deletes the teacher, their account, classes, lessons, assignments and quizzes. This cannot be undone.')
            ->using(function (Teacher $record): bool {
                app(TeacherService::class)->deleteFromSystem($record);

                return true;
            });
    }

    public static function canDelete(Model $record): bool
    {
        return true;
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
}
