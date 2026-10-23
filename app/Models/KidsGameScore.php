<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KidsGameScore extends Model
{
    protected $fillable = [
        'student_id',
        'game_key',
        'best_score',
        'stars',
        'plays',
        'last_played_at',
    ];

    protected $casts = [
        'best_score' => 'integer',
        'stars' => 'integer',
        'plays' => 'integer',
        'last_played_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
