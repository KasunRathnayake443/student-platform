<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Assignment extends Model
{
    protected $fillable = [
        'learning_class_id',
        'teacher_id',
        'title',
        'description',
        'instructions',
        'max_score',
        'availability_type',
        'start_at',
        'end_at',
        'allow_late_submissions',
        'late_submission_value',
        'late_submission_unit',
        'allowed_submission_types',
        'is_published',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',

        'allow_late_submissions' => 'boolean',

        'allowed_submission_types' => 'array',

        'is_published' => 'boolean',

        'max_score' => 'integer',
        'late_submission_value' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Learning Class
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo<LearningClass, $this>
     */
    public function learningClass(): BelongsTo
    {
        return $this->belongsTo(
            LearningClass::class,
            'learning_class_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Responsible Teacher
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(
            Teacher::class,
            'teacher_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Assigned Teachers
    |--------------------------------------------------------------------------
    |
    | All teachers assigned to this assignment (the creator included).
    | Only these teachers may view submissions and grade them.
    |
    */

    /**
     * @return BelongsToMany<Teacher, $this>
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(
            Teacher::class,
            'assignment_teacher'
        )->withTimestamps();
    }

    /**
     * Whether the given teacher is assigned to this assignment
     * (either as the responsible teacher or via the pivot table).
     */
    public function isAssignedTo(Teacher $teacher): bool
    {
        if (
            (int) $this->teacher_id ===
            (int) $teacher->getKey()
        ) {
            return true;
        }

        return $this->teachers()
            ->whereKey($teacher->getKey())
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Assignment Attachments
    |--------------------------------------------------------------------------
    */

    /**
     * @return HasMany<AssignmentAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(
            AssignmentAttachment::class
        )->orderBy('sort_order');
    }

    /*
    |--------------------------------------------------------------------------
    | Student Submissions
    |--------------------------------------------------------------------------
    */

    /**
     * @return HasMany<AssignmentSubmission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(
            AssignmentSubmission::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Availability Helpers
    |--------------------------------------------------------------------------
    */

    public function isAvailable(): bool
    {
        if (! $this->is_published) {
            return false;
        }

        if (
            $this->availability_type === 'scheduled' &&
            $this->start_at &&
            now()->lt($this->start_at)
        ) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        return $this->end_at
            ? now()->gt($this->end_at)
            : false;
    }

    public function acceptsLateSubmissions(): bool
    {
        if (! $this->allow_late_submissions) {
            return false;
        }

        if (
            ! $this->end_at ||
            ! $this->late_submission_value ||
            ! $this->late_submission_unit
        ) {
            return false;
        }

        return now()->lte(
            $this->lateSubmissionDeadline()
        );
    }

    public function lateSubmissionDeadline(): ?Carbon
    {
        if (
            ! $this->end_at ||
            ! $this->late_submission_value ||
            ! $this->late_submission_unit
        ) {
            return $this->end_at;
        }

        /*
         * The unit column is a database enum of exactly these values,
         * so the match below is exhaustive.
         */
        return match ($this->late_submission_unit) {
            'minutes' => $this->end_at->copy()->addMinutes(
                $this->late_submission_value
            ),

            'hours' => $this->end_at->copy()->addHours(
                $this->late_submission_value
            ),

            'days' => $this->end_at->copy()->addDays(
                $this->late_submission_value
            ),
        };
    }
}
