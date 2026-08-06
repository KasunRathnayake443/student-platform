<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}