<?php

namespace App\Filament\Teacher\Resources\LearningClasses;

use App\Filament\Resources\LearningClasses\RelationManagers\QuizzesRelationManager;
use App\Filament\Resources\LearningClasses\Schemas\LearningClassInfolist;
use App\Filament\Teacher\Pages\TeacherDashboard;
use App\Filament\Teacher\Resources\LearningClasses\Pages\ViewLearningClass;
use App\Models\LearningClass;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LearningClassResource extends Resource
{
    protected static ?string $model = LearningClass::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'My Classes';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $shouldRegisterNavigation = false;

    public static function infolist(Schema $schema): Schema
    {
        return LearningClassInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StudentsRelationManager::class,
            RelationManagers\TeachersRelationManager::class,
            RelationManagers\LessonsRelationManager::class,
            RelationManagers\AssignmentsRelationManager::class,
            QuizzesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'view' => ViewLearningClass::route('/{record}'),
        ];
    }

    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        return TeacherDashboard::getUrl();
    }

    public static function getEloquentQuery(): Builder
    {
        $teacher = auth()->user()->teacher;

        return parent::getEloquentQuery()
            ->whereHas('teachers', function ($q) use ($teacher) {
                $q->where('teachers.id', $teacher?->id);
            })
            ->withCount([
                'students',
                'teachers',
            ])
            ->with([
                'grade.school',
            ]);
    }
}
