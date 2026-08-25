<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class QuizQuestion extends Model
{
    protected $fillable = [
        'quiz_id',
        'question_text',
        'explanation',
        'points',
        'sort_order',
        'question_image',
        'question_video',
    ];

    protected $casts = [
        'points' => 'integer',
        'sort_order' => 'integer',
    ];

    public function getQuestionImageUrlAttribute(): ?string
    {
        if (blank($this->question_image)) {
            return null;
        }

        if ((string) config('filament.default_filesystem_disk', 'local') === 'public') {
            return Storage::disk('public')->url($this->question_image);
        }

        return route('quiz-questions.media', ['quizQuestion' => $this, 'type' => 'image']);
    }

    public function getQuestionVideoUrlAttribute(): ?string
    {
        if (blank($this->question_video)) {
            return null;
        }

        if ((string) config('filament.default_filesystem_disk', 'local') === 'public') {
            return Storage::disk('public')->url($this->question_video);
        }

        return route('quiz-questions.media', ['quizQuestion' => $this, 'type' => 'video']);
    }

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * @return HasMany<QuizQuestionOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuizQuestionOption::class)->orderBy('sort_order');
    }

    /**
     * @return HasOne<QuizQuestionOption, $this>
     */
    public function correctOption(): HasOne
    {
        return $this->hasOne(QuizQuestionOption::class)->where('is_correct', true);
    }
}
