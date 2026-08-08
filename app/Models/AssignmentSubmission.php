<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id',
        'student_id',
        'content',
        'submitted_at',
        'is_late',
        'score',
        'feedback',
        'graded_by',
        'graded_at',
        'status',
    ];


    protected $casts = [
        'submitted_at' => 'datetime',

        'graded_at' => 'datetime',

        'is_late' => 'boolean',

        'score' => 'decimal:2',
    ];


    /*
    |--------------------------------------------------------------------------
    | Assignment
    |--------------------------------------------------------------------------
    */

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(
            Assignment::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Student
    |--------------------------------------------------------------------------
    */

    public function student(): BelongsTo
    {
        return $this->belongsTo(
            Student::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Teacher Who Graded
    |--------------------------------------------------------------------------
    */

    public function grader(): BelongsTo
    {
        return $this->belongsTo(
            Teacher::class,
            'graded_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Submission Attachments
    |--------------------------------------------------------------------------
    */

    public function attachments(): HasMany
    {
        return $this->hasMany(
            AssignmentSubmissionAttachment::class
        )->orderBy('sort_order');
    }


    /*
    |--------------------------------------------------------------------------
    | Grading Helpers
    |--------------------------------------------------------------------------
    */

    public function isGraded(): bool
    {
        return $this->status === 'graded';
    }


    public function percentage(): ?float
    {
        if (
            $this->score === null ||
            ! $this->assignment ||
            $this->assignment->max_score <= 0
        ) {
            return null;
        }

        return round(
            ((float) $this->score /
                (float) $this->assignment->max_score) * 100,
            2
        );
    }
}