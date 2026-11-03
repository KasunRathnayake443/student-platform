<?php

namespace App\Filament\SchoolAdmin\Tables\Filters;

use App\Models\Grade;
use App\Models\LearningClass;
use App\Models\School;
use App\Models\StudentEnrollment;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filters shared by the school-admin Teachers and Students tables.
 *
 * School options and every relationship filter are limited to the schools the
 * current school admin is assigned to.
 */
class SchoolPeopleFilters
{
    /**
     * @param  array<int, int>  $schoolIds
     */
    public static function teacherSchool(array $schoolIds): SelectFilter
    {
        return SelectFilter::make('school_id')
            ->label('School')
            ->options(fn (): array => self::schoolOptions($schoolIds))
            ->searchable()
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(
                    filled($data['value'] ?? null),
                    fn (Builder $query) => $query->whereHas(
                        'schools',
                        fn (Builder $query) => $query->where('schools.id', $data['value']),
                    ),
                ));
    }

    /**
     * @param  array<int, int>  $schoolIds
     */
    public static function teacherClass(array $schoolIds): SelectFilter
    {
        return SelectFilter::make('learning_class_id')
            ->label('Class')
            ->options(fn (): array => self::classOptions($schoolIds))
            ->searchable()
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(
                    filled($data['value'] ?? null),
                    fn (Builder $query) => $query->whereHas(
                        'classes',
                        fn (Builder $query) => $query
                            ->where('learning_classes.id', $data['value'])
                            ->whereHas(
                                'grade',
                                fn (Builder $query) => $query->whereIn('school_id', $schoolIds),
                            ),
                    ),
                ));
    }

    public static function teacherHasClasses(): TernaryFilter
    {
        return TernaryFilter::make('has_classes')
            ->label('Class assignment')
            ->trueLabel('Assigned to classes')
            ->falseLabel('No classes assigned')
            ->placeholder('All teachers')
            ->queries(
                true: fn (Builder $query) => $query->whereHas('classes'),
                false: fn (Builder $query) => $query->whereDoesntHave('classes'),
            );
    }

    /**
     * @param  array<int, int>  $schoolIds
     */
    public static function studentSchool(array $schoolIds): SelectFilter
    {
        return SelectFilter::make('school_id')
            ->label('School')
            ->options(fn (): array => self::schoolOptions($schoolIds))
            ->searchable()
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(
                    filled($data['value'] ?? null),
                    fn (Builder $query) => $query->whereHas(
                        'enrollments',
                        fn (Builder $query) => $query->where('student_enrollments.school_id', $data['value']),
                    ),
                ));
    }

    /**
     * @param  array<int, int>  $schoolIds
     */
    public static function studentGrade(array $schoolIds): SelectFilter
    {
        return SelectFilter::make('grade_id')
            ->label('Grade')
            ->options(fn (): array => self::gradeOptions($schoolIds))
            ->searchable()
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(
                    filled($data['value'] ?? null),
                    fn (Builder $query) => $query->whereHas(
                        'enrollments',
                        fn (Builder $query) => $query
                            ->whereIn('student_enrollments.school_id', $schoolIds)
                            ->where('student_enrollments.grade_id', $data['value']),
                    ),
                ));
    }

    /**
     * @param  array<int, int>  $schoolIds
     */
    public static function studentClass(array $schoolIds): SelectFilter
    {
        return SelectFilter::make('learning_class_id')
            ->label('Class')
            ->options(fn (): array => self::classOptions($schoolIds))
            ->searchable()
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(
                    filled($data['value'] ?? null),
                    fn (Builder $query) => $query->whereHas(
                        'classes',
                        fn (Builder $query) => $query
                            ->where('learning_classes.id', $data['value'])
                            ->whereHas(
                                'grade',
                                fn (Builder $query) => $query->whereIn('school_id', $schoolIds),
                            ),
                    ),
                ));
    }

    /**
     * @param  array<int, int>  $schoolIds
     */
    public static function studentAcademicYear(array $schoolIds): SelectFilter
    {
        return SelectFilter::make('academic_year')
            ->label('Academic year')
            ->options(fn (): array => StudentEnrollment::query()
                ->whereIn('school_id', $schoolIds)
                ->orderByDesc('academic_year')
                ->distinct()
                ->pluck('academic_year')
                ->mapWithKeys(fn ($year): array => [(string) $year => (string) $year])
                ->all())
            ->searchable()
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(
                    filled($data['value'] ?? null),
                    fn (Builder $query) => $query->whereHas(
                        'enrollments',
                        fn (Builder $query) => $query
                            ->whereIn('student_enrollments.school_id', $schoolIds)
                            ->where('student_enrollments.academic_year', $data['value']),
                    ),
                ));
    }

    /**
     * @param  array<int, int>  $schoolIds
     */
    public static function studentEnrollmentStatus(array $schoolIds): SelectFilter
    {
        return SelectFilter::make('enrollment_status')
            ->label('Enrollment status')
            ->options([
                'active' => 'Active',
                'completed' => 'Completed',
                'transferred' => 'Transferred',
                'inactive' => 'Inactive',
            ])
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(
                    filled($data['value'] ?? null),
                    fn (Builder $query) => $query->whereHas(
                        'enrollments',
                        fn (Builder $query) => $query
                            ->whereIn('student_enrollments.school_id', $schoolIds)
                            ->where('student_enrollments.status', $data['value']),
                    ),
                ));
    }

    public static function studentGender(): SelectFilter
    {
        return SelectFilter::make('gender')
            ->label('Gender')
            ->options([
                'male' => 'Male',
                'female' => 'Female',
                'other' => 'Other',
            ]);
    }

    public static function passwordPending(): TernaryFilter
    {
        return TernaryFilter::make('password_pending')
            ->label('Password status')
            ->trueLabel('Change pending')
            ->falseLabel('Password set')
            ->placeholder('All accounts')
            ->queries(
                true: fn (Builder $query) => $query->whereHas(
                    'user',
                    fn (Builder $query) => $query->where('must_change_password', true),
                ),
                false: fn (Builder $query) => $query->whereHas(
                    'user',
                    fn (Builder $query) => $query->where('must_change_password', false),
                ),
            );
    }

    /**
     * @param  array<int, int>  $schoolIds
     * @return array<int, string>
     */
    private static function schoolOptions(array $schoolIds): array
    {
        return School::query()
            ->whereIn('id', $schoolIds)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @param  array<int, int>  $schoolIds
     * @return array<int, string>
     */
    private static function gradeOptions(array $schoolIds): array
    {
        return Grade::query()
            ->whereIn('school_id', $schoolIds)
            ->with('school')
            ->orderBy('name')
            ->get()
            ->sortBy(fn (Grade $grade): string => sprintf(
                '%s %s',
                $grade->school?->name,
                $grade->name,
            ))
            ->mapWithKeys(fn (Grade $grade): array => [
                $grade->getKey() => trim(
                    ($grade->school?->name ? $grade->school->name.' → ' : '').$grade->name,
                ),
            ])
            ->all();
    }

    /**
     * @param  array<int, int>  $schoolIds
     * @return array<int, string>
     */
    private static function classOptions(array $schoolIds): array
    {
        return LearningClass::query()
            ->whereHas('grade', fn (Builder $query) => $query->whereIn('school_id', $schoolIds))
            ->with('grade.school')
            ->get()
            ->sortBy(fn (LearningClass $class): string => implode(' ', array_filter([
                $class->grade?->school?->name,
                $class->grade?->name,
                $class->name,
            ])))
            ->mapWithKeys(fn (LearningClass $class): array => [
                $class->getKey() => implode(' → ', array_filter([
                    $class->grade?->school?->name,
                    $class->grade?->name,
                    $class->name,
                ])),
            ])
            ->all();
    }
}
