<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id',
        'student_id',
        'attempt_number',
        'started_at',
        'expires_at',
        'completed_at',
        'score',
        'percentage',
        'is_passed',
        'status',
    ];

    protected $casts = [
        'attempt_number' => 'integer',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_passed' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Timer & Expiration Helpers
    |--------------------------------------------------------------------------
    */

    public function isTimeExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return now()->gte($this->expires_at);
    }

    public function getRemainingSeconds(): ?int
    {
        if ($this->expires_at === null) {
            return null; // Unlimited time
        }

        if ($this->isFinished()) {
            return 0;
        }

        $remaining = now()->diffInSeconds($this->expires_at, false);

        return max(0, (int) $remaining);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['submitted', 'time_expired', 'abandoned'], true);
    }

    public function autoSubmitIfExpired(): bool
    {
        if ($this->status === 'in_progress' && $this->isTimeExpired()) {
            $this->submit('time_expired');

            return true;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Answer Recording (Continuous Progress Saving)
    |--------------------------------------------------------------------------
    */

    public function recordAnswer(int $questionId, ?int $optionId): ?QuizAttemptAnswer
    {
        // If already completed or expired, auto-submit and deny further changes
        if ($this->isFinished() || $this->isTimeExpired()) {
            $this->autoSubmitIfExpired();

            return null;
        }

        $question = QuizQuestion::where('quiz_id', $this->quiz_id)->find($questionId);
        if (! $question) {
            return null;
        }

        $isCorrect = false;
        $pointsAwarded = 0;

        if ($optionId) {
            $option = QuizQuestionOption::where('quiz_question_id', $questionId)->find($optionId);
            if ($option && $option->is_correct) {
                $isCorrect = true;
                $pointsAwarded = $question->points;
            }
        }

        return QuizAttemptAnswer::updateOrCreate(
            [
                'quiz_attempt_id' => $this->id,
                'quiz_question_id' => $questionId,
            ],
            [
                'quiz_question_option_id' => $optionId,
                'is_correct' => $isCorrect,
                'points_awarded' => $pointsAwarded,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Submission & Auto-Grading
    |--------------------------------------------------------------------------
    */

    public function submit(string $status = 'submitted'): self
    {
        return DB::transaction(function () use ($status) {
            $quiz = $this->quiz()->with('questions.options')->first();

            // Evaluate all questions
            $totalPointsEarned = 0;
            $totalPossiblePoints = 0;

            foreach ($quiz->questions as $question) {
                $totalPossiblePoints += $question->points;

                $answer = $this->answers()->where('quiz_question_id', $question->id)->first();

                if ($answer && $answer->quiz_question_option_id) {
                    $option = $question->options->firstWhere('id', $answer->quiz_question_option_id);
                    $isCorrect = $option ? (bool) $option->is_correct : false;
                    $points = $isCorrect ? $question->points : 0;

                    $answer->update([
                        'is_correct' => $isCorrect,
                        'points_awarded' => $points,
                    ]);

                    $totalPointsEarned += $points;
                } else {
                    // Record unattempted answer record if missing
                    QuizAttemptAnswer::updateOrCreate(
                        [
                            'quiz_attempt_id' => $this->id,
                            'quiz_question_id' => $question->id,
                        ],
                        [
                            'quiz_question_option_id' => null,
                            'is_correct' => false,
                            'points_awarded' => 0,
                        ]
                    );
                }
            }

            $percentage = $totalPossiblePoints > 0
                ? round(($totalPointsEarned / $totalPossiblePoints) * 100, 2)
                : 0;

            $isPassed = $percentage >= $quiz->passing_percentage;

            $this->update([
                'score' => $totalPointsEarned,
                'percentage' => $percentage,
                'is_passed' => $isPassed,
                'completed_at' => now(),
                'status' => $status,
            ]);

            return $this;
        });
    }
}
