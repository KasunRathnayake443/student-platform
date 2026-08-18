<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;



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






    /*
    |--------------------------------------------------------------------------
    | User Account
    |--------------------------------------------------------------------------
    */


    public function user(): BelongsTo
    {

        return $this->belongsTo(User::class);

    }








    /*
    |--------------------------------------------------------------------------
    | School / Grade Enrollments
    |--------------------------------------------------------------------------
    */


    public function enrollments(): HasMany
    {

        return $this->hasMany(StudentEnrollment::class);

    }








    /*
    |--------------------------------------------------------------------------
    | Latest Enrollment
    |--------------------------------------------------------------------------
    |
    | Kept for compatibility with existing Filament pages.
    |
    */


    public function currentEnrollment(): HasOne
    {

        return $this->hasOne(StudentEnrollment::class)

            ->latestOfMany();

    }








    /*
    |--------------------------------------------------------------------------
    | All Schools
    |--------------------------------------------------------------------------
    |
    | A student can belong to multiple schools.
    |
    */


    public function schools(): BelongsToMany
    {

        return $this->belongsToMany(

            School::class,

            'student_enrollments',

            'student_id',

            'school_id'

        )

        ->withPivot([

            'grade_id',

            'academic_year',

            'status'

        ])

        ->withTimestamps();

    }








    /*
    |--------------------------------------------------------------------------
    | All Grades
    |--------------------------------------------------------------------------
    |
    | A student can belong to multiple grades.
    |
    */


    public function grades(): BelongsToMany
    {

        return $this->belongsToMany(

            Grade::class,

            'student_enrollments',

            'student_id',

            'grade_id'

        )

        ->withPivot([

            'school_id',

            'academic_year',

            'status'

        ])

        ->withTimestamps();

    }








    /*
    |--------------------------------------------------------------------------
    | Learning Classes
    |--------------------------------------------------------------------------
    |
    | A student can belong to multiple learning classes.
    |
    */


    public function classes(): BelongsToMany
    {

        return $this->belongsToMany(

            LearningClass::class,

            'class_student',

            'student_id',

            'learning_class_id'

        )

        ->withPivot(

            'student_enrollment_id'

        )

        ->withTimestamps();

    }








    /*
    |--------------------------------------------------------------------------
    | Enrollment Records With Classes
    |--------------------------------------------------------------------------
    */


    public function enrollmentClasses(): HasMany
    {
        return $this->hasMany(
            StudentEnrollment::class
        );
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(
            QuizAttempt::class,
            'student_id'
        );
    }

}