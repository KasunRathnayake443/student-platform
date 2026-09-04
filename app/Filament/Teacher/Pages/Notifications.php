<?php

namespace App\Filament\Teacher\Pages;

use App\Enums\NotificationRecipientType;
use App\Filament\Notifications\NotificationsCenter;
use App\Models\Grade;
use App\Models\LearningClass;
use App\Models\School;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Collection;

class Notifications extends NotificationsCenter
{
    protected string $view = 'filament.pages.notifications';

    protected static ?string $title = 'Notifications';

    public function getLayout(): string
    {
        return 'filament.teacher.layouts.app';
    }

    protected function recipientTypeOptions(): array
    {
        return [
            NotificationRecipientType::Student->value => 'Students',
        ];
    }

    protected function schoolOptions(): array
    {
        $schoolIds = $this->teacherSchoolIds();

        if ($schoolIds->isEmpty()) {
            return [];
        }

        return School::query()->whereIn('id', $schoolIds)->orderBy('name')->pluck('name', 'id')->all();
    }

    protected function gradeOptions(Get $get): array
    {
        $gradeIds = $this->teacherReachableGradeIds();

        $query = Grade::query()->whereIn('id', $gradeIds);

        if ($schools = array_map('intval', $get('school') ?? [])) {
            $query->whereIn('school_id', $schools);
        }

        return $query->orderBy('name')->pluck('name', 'id')->all();
    }

    protected function classOptions(Get $get): array
    {
        $classIds = $this->teacherClassIds();

        $query = LearningClass::query()->with('grade')->whereIn('id', $classIds);

        if ($grades = array_map('intval', $get('grade') ?? [])) {
            $query->whereIn('grade_id', $grades);
        }

        return $query->orderBy('name')->get()
            ->mapWithKeys(fn (LearningClass $class) => [
                $class->getKey() => trim(($class->grade?->name ?? '').' · '.($class->name ?? ''), ' ·'),
            ])
            ->all();
    }

    protected function clampSchools(array $requested): array
    {
        $allowed = $this->teacherSchoolIds();

        return $requested ? $allowed->intersect($requested)->values()->all() : $allowed->all();
    }

    protected function clampGrades(array $requested): array
    {
        $allowed = $this->teacherReachableGradeIds();

        return $requested ? $allowed->intersect($requested)->values()->all() : $allowed->all();
    }

    /**
     * @return Collection<int, int>
     */
    protected function teacherSchoolIds()
    {
        $teacher = auth()->user()->teacher;

        return collect($teacher?->schools ?? [])->pluck('id')->unique()->values();
    }

    /**
     * @return Collection<int, int>
     */
    protected function teacherClassIds()
    {
        $teacher = auth()->user()->teacher;

        return collect($teacher?->classes ?? [])->pluck('id')->unique()->values();
    }

    /**
     * Grade ids across all classes assigned to the teacher.
     *
     * @return Collection<int, int>
     */
    protected function teacherReachableGradeIds()
    {
        return LearningClass::query()
            ->whereIn('id', $this->teacherClassIds())
            ->pluck('grade_id');
    }
}
