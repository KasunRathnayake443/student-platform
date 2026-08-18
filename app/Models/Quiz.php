<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'learning_class_id',
        'teacher_id',
        'title',
        'description',
        'instructions',
        'time_limit_minutes',
        'max_attempts',
        'passing_percentage',
        'total_points',
        'show_correct_answers_after_submission',
        'shuffle_questions',
        'shuffle_options',
        'availability_type',
        'start_at',
        'end_at',
        'is_published',
    ];

    protected $casts = [
        'time_limit_minutes' => 'integer',
        'max_attempts' => 'integer',
        'passing_percentage' => 'integer',
        'total_points' => 'integer',
        'show_correct_answers_after_submission' => 'boolean',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function learningClass(): BelongsTo
    {
        return $this->belongsTo(LearningClass::class, 'learning_class_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
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

    public function recalculateTotalPoints(): int
    {
        $total = (int) $this->questions()->sum('points');
        $this->updateQuietly(['total_points' => $total]);

        return $total;
    }

    public function canStudentAttempt(Student $student): bool
    {
        if (! $this->isAvailable() || $this->isExpired()) {
            return false;
        }

        if ($this->max_attempts === null || $this->max_attempts === 0) {
            return true;
        }

        $completedAttempts = $this->attempts()
            ->where('student_id', $student->id)
            ->whereIn('status', ['submitted', 'time_expired'])
            ->count();

        return $completedAttempts < $this->max_attempts;
    }

    public function getRemainingAttempts(Student $student): ?int
    {
        if ($this->max_attempts === null || $this->max_attempts === 0) {
            return null;
        }

        $completedAttempts = $this->attempts()
            ->where('student_id', $student->id)
            ->whereIn('status', ['submitted', 'time_expired'])
            ->count();

        return max(0, $this->max_attempts - $completedAttempts);
    }
}
