<?php

namespace App\Filament\Teacher\Resources\Lessons;

use App\Filament\Resources\Lessons\Schemas\LessonInfolist;
use App\Filament\Teacher\Pages\TeacherDashboard;
use App\Filament\Teacher\Resources\Lessons\Pages\EditLesson;
use App\Filament\Teacher\Resources\Lessons\Pages\ViewLesson;
use App\Filament\Teacher\Resources\Lessons\Schemas\LessonForm;
use App\Models\Lesson;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'title';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return LessonForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LessonInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'view' => ViewLesson::route('/{record}'),
            'edit' => EditLesson::route('/{record}/edit'),
        ];
    }

    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        return TeacherDashboard::getUrl();
    }

    /**
     * Teachers only see lessons belonging to classes they teach.
     */
    public static function getEloquentQuery(): Builder
    {
        $teacher = auth()->user()->teacher;

        return parent::getEloquentQuery()
            ->whereHas('learningClass', function ($query) use ($teacher) {
                $query->whereHas('teachers', function ($t) use ($teacher) {
                    $t->where('teachers.id', $teacher?->id);
                });
            })
            ->with(['teacher.user', 'learningClass', 'attachments']);
    }
}
