<?php

namespace App\Filament\Teacher\Resources\Teachers;

use App\Filament\Resources\Teachers\Schemas\TeacherInfolist;
use App\Filament\Teacher\Pages\TeacherDashboard;
use App\Filament\Teacher\Resources\Teachers\Pages\ViewTeacher;
use App\Models\Teacher;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|\UnitEnum|null $navigationGroup = 'School Users';

    protected static ?int $navigationSort = 4;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'user.name';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return TeacherInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'view' => ViewTeacher::route('/{record}'),
        ];
    }

    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        return TeacherDashboard::getUrl();
    }

    /**
     * Teachers are only visible to colleagues they share at least one class with.
     */
    public static function getEloquentQuery(): Builder
    {
        $teacher = auth()->user()->teacher;

        return parent::getEloquentQuery()
            ->whereHas('classes', function ($query) use ($teacher) {
                $query->whereHas('teachers', function ($t) use ($teacher) {
                    $t->where('teachers.id', $teacher?->id);
                });
            })
            ->with(['user', 'schools']);
    }
}
