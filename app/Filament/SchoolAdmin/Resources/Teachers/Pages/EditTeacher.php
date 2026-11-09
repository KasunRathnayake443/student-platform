<?php

namespace App\Filament\SchoolAdmin\Resources\Teachers\Pages;

use App\Filament\Resources\Teachers\Pages\EditTeacher as BaseEditTeacher;
use App\Filament\SchoolAdmin\Resources\Teachers\TeacherResource;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Models\LearningClass;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;

class EditTeacher extends BaseEditTeacher
{
    protected static string $resource = TeacherResource::class;

    /**
     * Only show the schools and classes the admin is allowed to manage.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        $schoolIds = SchoolAdminScopes::schoolIds();

        $data['schools'] = $this->record->schools()
            ->whereIn('schools.id', $schoolIds)
            ->pluck('schools.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $data['classes'] = $this->record->classes()
            ->whereHas('grade', fn (Builder $query) => $query->whereIn('school_id', $schoolIds))
            ->pluck('learning_classes.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $data;
    }

    /**
     * Sync only the admin's schools/classes and leave every out-of-scope
     * assignment exactly as it was.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $schoolIds = SchoolAdminScopes::schoolIds();

        $outOfScopeSchoolIds = $this->record->schools()
            ->whereNotIn('schools.id', $schoolIds)
            ->pluck('schools.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $outOfScopeClassIds = $this->record->classes()
            ->whereHas('grade', fn (Builder $query) => $query->whereNotIn('school_id', $schoolIds))
            ->pluck('learning_classes.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $selectedSchoolIds = array_values(array_intersect(
            array_map('intval', (array) ($data['schools'] ?? [])),
            $schoolIds,
        ));

        $scopedClassIds = LearningClass::query()
            ->whereHas('grade', fn (Builder $query) => $query->whereIn('school_id', $selectedSchoolIds))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $data['schools'] = array_values(array_unique(array_merge(
            $selectedSchoolIds,
            $outOfScopeSchoolIds,
        )));

        $data['classes'] = array_values(array_unique(array_merge(
            array_intersect(array_map('intval', (array) ($data['classes'] ?? [])), $scopedClassIds),
            $outOfScopeClassIds,
        )));

        return parent::mutateFormDataBeforeSave($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            TeacherResource::removeFromSchoolAction(),
        ];
    }

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }
}
