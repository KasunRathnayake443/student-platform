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

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return HasMany<LearningClass, $this>
     */
    public function learningClasses(): HasMany
    {
        return $this->hasMany(LearningClass::class);
    }

    /**
     * @return HasMany<StudentEnrollment, $this>
     */
    public function studentEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    /**
     * @return HasManyThrough<Student, StudentEnrollment, $this>
     */
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
