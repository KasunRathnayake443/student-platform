<?php

namespace App\Filament\SchoolAdmin\Tables\Filters;

use App\Models\Grade;
use App\Models\LearningClass;
use App\Models\School;
use App\Models\Teacher;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Reusable filters shared by the school-admin Lessons, Assignments and
 * Quizzes tables. Every relationship filter is scoped to the schools the
 * current school admin is assigned to.
 */
class TeachingContentFilters
{
    /**
     * Filters that apply to all teaching content tables.
     *
     * @param  array<int, int>  $schoolIds
     * @return array<int, SelectFilter|TernaryFilter|Filter>
     */
    public static function common(array $schoolIds): array
    {
        return [
            static::school($schoolIds),
            static::grade($schoolIds),
            static::learningClass($schoolIds),
            static::teacher($schoolIds),
            static::published(),
            static::createdBetween(),
        ];
    }

    /**
     * @param  array<int, int>  $schoolIds
     */
    public static function school(array $schoolIds): SelectFilter
    {
        return SelectFilter::make('school_id')
            ->label('School')
            ->options(fn (): array => School::query()
                ->whereIn('id', $schoolIds)
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all())
            ->searchable()
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(
                    filled($data['value'] ?? null),
                    fn (Builder $query) => $query->whereHas(
                        'learningClass.grade',
                        fn (Builder $query) => $query->where('school_id', $data['value']),
                    ),
                ));
    }

    /**
     * @param  array<int, int>  $schoolIds
     */
    public static function grade(array $schoolIds): SelectFilter
    {
        return SelectFilter::make('grade_id')
            ->label('Grade')
            ->options(fn (): array => Grade::query()
                ->whereIn('school_id', $schoolIds)
                ->with('school')
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn (Grade $grade): array => [
                    $grade->getKey() => trim(
                        ($grade->school?->name ? $grade->school->name.' → ' : '').$grade->name,
                    ),
                ])
                ->all())
            ->searchable()
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(
                    filled($data['value'] ?? null),
                    fn (Builder $query) => $query->whereHas(
                        'learningClass',
                        fn (Builder $query) => $query->where('grade_id', $data['value']),
                    ),
                ));
    }

    /**
     * @param  array<int, int>  $schoolIds
     */
    public static function learningClass(array $schoolIds): SelectFilter
    {
        return SelectFilter::make('learning_class_id')
            ->label('Class')
            ->options(fn (): array => LearningClass::query()
                ->whereHas('grade', fn (Builder $query) => $query->whereIn('school_id', $schoolIds))
                ->with('grade.school')
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn (LearningClass $class): array => [
                    $class->getKey() => implode(' → ', array_filter([
                        $class->grade?->school?->name,
                        $class->grade?->name ? 'Grade '.$class->grade->name : null,
                        $class->name,
                    ])),
                ])
                ->all())
            ->searchable()
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(
                    filled($data['value'] ?? null),
                    fn (Builder $query) => $query->where('learning_class_id', $data['value']),
                ));
    }

    /**
     * @param  array<int, int>  $schoolIds
     */
    public static function teacher(array $schoolIds): SelectFilter
    {
        return SelectFilter::make('teacher_id')
            ->label('Teacher')
            ->options(fn (): array => Teacher::query()
                ->whereHas('classes', fn (Builder $query) => $query->whereHas(
                    'grade',
                    fn (Builder $query) => $query->whereIn('school_id', $schoolIds),
                ))
                ->with('user')
                ->get()
                ->sortBy(fn (Teacher $teacher): string => $teacher->user->name)
                ->mapWithKeys(fn (Teacher $teacher): array => [
                    $teacher->getKey() => trim(
                        $teacher->user->name
                        .($teacher->employee_no ? ' - '.$teacher->employee_no : ''),
                    ),
                ])
                ->all())
            ->searchable()
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(
                    filled($data['value'] ?? null),
                    fn (Builder $query) => $query->where('teacher_id', $data['value']),
                ));
    }

    public static function published(): TernaryFilter
    {
        return TernaryFilter::make('is_published')
            ->label('Published')
            ->trueLabel('Published')
            ->falseLabel('Draft / unpublished')
            ->placeholder('All records');
    }

    public static function createdBetween(): Filter
    {
        return Filter::make('created_at')
            ->label('Created date')
            ->schema([
                DatePicker::make('created_from')
                    ->label('From')
                    ->native(false),

                DatePicker::make('created_until')
                    ->label('Until')
                    ->native(false),
            ])
            ->columns(2)
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(
                    filled($data['created_from'] ?? null),
                    fn (Builder $query) => $query->whereDate('created_at', '>=', $data['created_from']),
                )
                ->when(
                    filled($data['created_until'] ?? null),
                    fn (Builder $query) => $query->whereDate('created_at', '<=', $data['created_until']),
                ));
    }

    /**
     * Filter by the current availability of a published assignment or quiz.
     */
    public static function availabilityStatus(): SelectFilter
    {
        return SelectFilter::make('availability_status')
            ->label('Availability')
            ->options([
                'active' => 'Active now',
                'upcoming' => 'Upcoming',
                'expired' => 'Expired',
            ])
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(
                    filled($data['value'] ?? null),
                    fn (Builder $query) => $query
                        ->where('is_published', true)
                        ->when(
                            $data['value'] === 'active',
                            fn (Builder $query) => $query
                                ->where(fn (Builder $query) => $query
                                    ->where('availability_type', 'immediate')
                                    ->orWhere(fn (Builder $query) => $query
                                        ->whereNotNull('start_at')
                                        ->where('start_at', '<=', now())))
                                ->where(fn (Builder $query) => $query
                                    ->whereNull('end_at')
                                    ->orWhere('end_at', '>=', now())),
                        )
                        ->when(
                            $data['value'] === 'upcoming',
                            fn (Builder $query) => $query
                                ->where('availability_type', 'scheduled')
                                ->whereNotNull('start_at')
                                ->where('start_at', '>', now()),
                        )
                        ->when(
                            $data['value'] === 'expired',
                            fn (Builder $query) => $query
                                ->whereNotNull('end_at')
                                ->where('end_at', '<', now()),
                        ),
                ));
    }

    /**
     * Filter assignments by their submission deadline.
     */
    public static function deadlineBetween(): Filter
    {
        return Filter::make('deadline')
            ->label('Deadline')
            ->schema([
                DatePicker::make('deadline_from')
                    ->label('From')
                    ->native(false),

                DatePicker::make('deadline_until')
                    ->label('Until')
                    ->native(false),
            ])
            ->columns(2)
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(
                    filled($data['deadline_from'] ?? null),
                    fn (Builder $query) => $query->whereDate('end_at', '>=', $data['deadline_from']),
                )
                ->when(
                    filled($data['deadline_until'] ?? null),
                    fn (Builder $query) => $query->whereDate('end_at', '<=', $data['deadline_until']),
                ));
    }

    public static function lateSubmissions(): TernaryFilter
    {
        return TernaryFilter::make('allow_late_submissions')
            ->label('Late submissions')
            ->trueLabel('Allowed')
            ->falseLabel('Not allowed')
            ->placeholder('All records');
    }

    public static function hasSubmissions(): TernaryFilter
    {
        return TernaryFilter::make('has_submissions')
            ->label('Submissions')
            ->trueLabel('Has submissions')
            ->falseLabel('No submissions')
            ->placeholder('All records')
            ->queries(
                true: fn (Builder $query) => $query->whereHas('submissions'),
                false: fn (Builder $query) => $query->whereDoesntHave('submissions'),
            );
    }

    public static function hasAttachments(): TernaryFilter
    {
        return TernaryFilter::make('has_attachments')
            ->label('Attachments')
            ->trueLabel('Has attachments')
            ->falseLabel('No attachments')
            ->placeholder('All records')
            ->queries(
                true: fn (Builder $query) => $query->whereHas('attachments'),
                false: fn (Builder $query) => $query->whereDoesntHave('attachments'),
            );
    }

    public static function hasVideo(): TernaryFilter
    {
        return TernaryFilter::make('has_video')
            ->label('Video')
            ->trueLabel('Has video')
            ->falseLabel('No video')
            ->placeholder('All records')
            ->queries(
                true: fn (Builder $query) => $query->whereNotNull('video_url')->where('video_url', '!=', ''),
                false: fn (Builder $query) => $query
                    ->where(fn (Builder $query) => $query
                        ->whereNull('video_url')
                        ->orWhere('video_url', '')),
            );
    }

    public static function hasAttempts(): TernaryFilter
    {
        return TernaryFilter::make('has_attempts')
            ->label('Attempts')
            ->trueLabel('Has attempts')
            ->falseLabel('No attempts')
            ->placeholder('All records')
            ->queries(
                true: fn (Builder $query) => $query->whereHas('attempts'),
                false: fn (Builder $query) => $query->whereDoesntHave('attempts'),
            );
    }

    public static function hasTimeLimit(): TernaryFilter
    {
        return TernaryFilter::make('has_time_limit')
            ->label('Time limit')
            ->trueLabel('Timed')
            ->falseLabel('Untimed')
            ->placeholder('All records')
            ->queries(
                true: fn (Builder $query) => $query->whereNotNull('time_limit_minutes'),
                false: fn (Builder $query) => $query->whereNull('time_limit_minutes'),
            );
    }
}
