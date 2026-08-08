<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Illuminate\Database\Eloquent\Relations\HasMany;



class LearningClass extends Model
{


    protected $fillable = [


        'grade_id',

        'name',

        'medium',

        'is_active',


    ];







    public function grade(): BelongsTo
    {


        return $this->belongsTo(Grade::class);


    }








    /*
    |--------------------------------------------------------------------------
    | Students Assigned To This Class
    |--------------------------------------------------------------------------
    */


    public function students(): BelongsToMany
    {


        return $this->belongsToMany(


            Student::class,


            'class_student',


            'learning_class_id',


            'student_id'


        )

        ->withPivot([

            'student_enrollment_id'

        ])

        ->withTimestamps();


    }








    /*
    |--------------------------------------------------------------------------
    | Student Enrollments Assigned To This Class
    |--------------------------------------------------------------------------
    */


    public function enrollments(): BelongsToMany
    {


        return $this->belongsToMany(


            StudentEnrollment::class,


            'class_student',


            'learning_class_id',


            'student_enrollment_id'


        )

        ->withPivot([

            'student_id'

        ])

        ->withTimestamps();


    }








    /*
    |--------------------------------------------------------------------------
    | Teachers
    |--------------------------------------------------------------------------
    */


    public function teachers(): BelongsToMany
    {


        return $this->belongsToMany(


            Teacher::class,


            'learning_class_teacher',


            'learning_class_id',


            'teacher_id'


        )

        ->withTimestamps();


    }

 /*
    |--------------------------------------------------------------------------
    | Lessons
    |--------------------------------------------------------------------------
    */

    public function lessons(): HasMany
{
    return $this->hasMany(
        Lesson::class,
        'learning_class_id'
    )->orderBy('sort_order');
}

}