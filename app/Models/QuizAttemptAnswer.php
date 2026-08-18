<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttemptAnswer extends Model
{
    protected $fillable = [
        'quiz_attempt_id',
        'quiz_question_id',
        'quiz_question_option_id',
        'is_correct',
        'points_awarded',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'points_awarded' => 'decimal:2',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuizQuestionOption::class, 'quiz_question_option_id');
    }
}
