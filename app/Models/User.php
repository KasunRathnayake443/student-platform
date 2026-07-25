<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;



class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;


    protected $fillable = [
        'name',
        'email',
        'password',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class);
    }




    public function teachingSchools(): BelongsToMany
    {
        return $this->belongsToMany(
            School::class,
            'teacher_schools',
            'teacher_id'
        )->withTimestamps();
    }

    public function teachingClasses(): BelongsToMany
    {
        return $this->belongsToMany(
            LearningClass::class,
            'class_teacher',
            'teacher_id'
        )
        ->withPivot('role')
        ->withTimestamps();
    }

public function learningClasses(): BelongsToMany
{
    return $this->belongsToMany(
        LearningClass::class,
        'class_student'
    )
    ->withPivot('student_enrollment_id')
    ->withTimestamps();
}

public function studentEnrollments(): HasMany
{
    return $this->hasMany(StudentEnrollment::class, 'student_id');
}

public function student(): HasOne
{
    return $this->hasOne(Student::class);
}

}