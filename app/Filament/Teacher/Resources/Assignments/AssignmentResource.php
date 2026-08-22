<?php

namespace App\Filament\Teacher\Resources\Assignments;

use App\Filament\Resources\Assignments\Schemas\AssignmentInfolist;
use App\Filament\Teacher\Pages\TeacherDashboard;
use App\Filament\Teacher\Resources\Assignments\Pages\EditAssignment;
use App\Filament\Teacher\Resources\Assignments\Pages\ViewAssignment;
use App\Filament\Teacher\Resources\Assignments\RelationManagers\SubmissionsRelationManager;
use App\Filament\Teacher\Resources\Assignments\Schemas\AssignmentForm;
use App\Models\Assignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

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
        return AssignmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AssignmentInfolist::configure($schema);
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
            'view' => ViewAssignment::route('/{record}'),
            'edit' => EditAssignment::route('/{record}/edit'),
        ];
    }

    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        return TeacherDashboard::getUrl();
    }

    /**
     * Teachers only see assignments of classes they teach.
     */
    public static function getEloquentQuery(): Builder
    {
        $teacher = auth()->user()->teacher;

        return parent::getEloquentQuery()
            ->whereHas('learningClass.teachers', function ($query) use ($teacher) {
                $query->where('teachers.id', $teacher?->id);
            })
            ->with(['teacher.user', 'teachers.user', 'learningClass', 'attachments']);
    }
}
