<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = [

        'user_id',
        'profile_photo',
        'employee_no',
        'phone',
        'address',

    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<School, $this>
     */
    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(
            School::class,
            'school_teacher'
        )->withTimestamps();
    }

    /**
     * @return BelongsToMany<LearningClass, $this>
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(
            LearningClass::class,
            'learning_class_teacher',
            'teacher_id',
            'learning_class_id'
        )
            ->withTimestamps();
    }

    /**
     * @return HasMany<Lesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(
            Lesson::class,
            'teacher_id'
        );
    }

    /**
     * @return HasMany<Quiz, $this>
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(
            Quiz::class,
            'teacher_id'
        );
    }
}
