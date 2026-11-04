<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    public const FEEDBACK_END_OF_QUIZ = 'end_of_quiz';

    public const FEEDBACK_SCORE_ONLY = 'score_only';

    public const FEEDBACK_AFTER_EACH_ANSWER = 'after_each_answer';

    /**
     * @var array<string, string>
     */
    public const FEEDBACK_MODES = [
        self::FEEDBACK_END_OF_QUIZ => 'Show all correct answers at the end of the quiz',
        self::FEEDBACK_SCORE_ONLY => 'Only show the score — hide correct answers',
        self::FEEDBACK_AFTER_EACH_ANSWER => 'Show the correct answer & explanation after each question',
    ];

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
        'feedback_mode',
        'shuffle_questions',
        'shuffle_options',
        'availability_type',
        'start_at',
        'end_at',
        'is_published',
        'email_sent',
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
        'email_sent' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo<LearningClass, $this>
     */
    public function learningClass(): BelongsTo
    {
        return $this->belongsTo(LearningClass::class, 'learning_class_id');
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Assigned Teachers
    |--------------------------------------------------------------------------
    |
    | All teachers assigned to this quiz (the creator included).
    |
    */

    /**
     * @return BelongsToMany<Teacher, $this>
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(
            Teacher::class,
            'quiz_teacher'
        )->withTimestamps();
    }

    /**
     * Whether the given teacher is assigned to this quiz
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

    /**
     * @return HasMany<QuizQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<QuizAttempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @return array<string, string>
     */
    public static function feedbackModeOptions(): array
    {
        return self::FEEDBACK_MODES;
    }

    public function showsCorrectAnswersAtEnd(): bool
    {
        return $this->feedback_mode === self::FEEDBACK_END_OF_QUIZ;
    }

    public function showsFeedbackAfterEachAnswer(): bool
    {
        return $this->feedback_mode === self::FEEDBACK_AFTER_EACH_ANSWER;
    }

    public function onlyShowsScore(): bool
    {
        return $this->feedback_mode === self::FEEDBACK_SCORE_ONLY;
    }

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
