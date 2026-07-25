<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Grade extends Model
{

    protected $fillable = [
        'school_id',
        'name',
        'is_active',
    ];


    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }


    public function learningClasses(): HasMany
    {
        return $this->hasMany(LearningClass::class);
    }


    public function studentEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }


    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            StudentEnrollment::class,
            'grade_id',
            'id',
            'id',
            'student_id'
        );
    }

}