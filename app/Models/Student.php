<?php

namespace App\Models;

use Carbon\Carbon;
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

    /**
     * Determine dashboard tier based on date_of_birth.
     * Returns: 'kids' | 'junior' | 'senior'
     */
    public function getAgeTier(): string
    {
        if (! $this->date_of_birth) {
            return 'senior';
        }

        $age = \Illuminate\Support\Carbon::parse($this->date_of_birth)->age;

        return match (true) {
            $age <= 10  => 'kids',    // Age 5 to 10 -> Kids Dashboard
            $age <= 15  => 'junior',  // Age 11 to 15 -> Teens Dashboard
            default     => 'senior',  // Age 16+ -> Seniors Dashboard
        };
    }

}