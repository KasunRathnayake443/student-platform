<?php

namespace App\Filament\Teacher\Resources\Students;

use App\Filament\Teacher\Resources\Students\Pages\CreateStudent;
use App\Filament\Teacher\Resources\Students\Pages\EditStudent;
use App\Filament\Teacher\Resources\Students\Pages\ListStudents;
use App\Filament\Teacher\Resources\Students\Pages\ViewStudent;
use App\Filament\Teacher\Resources\Students\Schemas\StudentForm;
use App\Filament\Teacher\Resources\Students\Schemas\StudentInfolist;
use App\Filament\Teacher\Resources\Students\Tables\StudentsTable;
use App\Models\Student;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'School Users';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'user.name';

    public static function form(Schema $schema): Schema
    {
        return StudentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StudentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [

            RelationManagers\StudentEnrollmentsRelationManager::class,

            RelationManagers\StudentClassesRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [

            'index' => ListStudents::route('/'),

            'create' => CreateStudent::route('/create'),

            'view' => ViewStudent::route('/{record}'),

            'edit' => EditStudent::route('/{record}/edit'),

        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $teacher = auth()->user()->teacher;
        
        return parent::getEloquentQuery()
            ->whereHas('classes', function ($query) use ($teacher) {
                $query->whereHas('teachers', function ($t) use ($teacher) {
                    $t->where('teachers.id', $teacher?->id);
                });
            });
    }
}
