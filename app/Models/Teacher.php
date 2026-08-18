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


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(
            School::class,
            'school_teacher'
        )->withTimestamps();
    }


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

    public function lessons(): HasMany
    {
        return $this->hasMany(
            Lesson::class,
            'teacher_id'
        );
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(
            Quiz::class,
            'teacher_id'
        );
    }
}