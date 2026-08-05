<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $fillable = [

        'user_id',
        'profile_photo',
        'admission_no',
        'date_of_birth',
        'gender',
        'phone',
        'address',
        'parent_name',
        'parent_phone',
    
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }


    public function currentEnrollment(): HasOne
    {
        return $this->hasOne(StudentEnrollment::class)
            ->latestOfMany();
    }


    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(
            LearningClass::class,
            'class_student',
            'student_id',
            'learning_class_id'
        )
        ->withTimestamps();
    }
}