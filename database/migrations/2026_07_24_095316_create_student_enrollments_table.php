<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{


    public function up(): void
    {


        Schema::create('student_enrollments', function (Blueprint $table) {



            $table->id();




            /*
            |--------------------------------------------------------------------------
            | Student
            |--------------------------------------------------------------------------
            */


            $table->foreignId('student_id')

                ->constrained()

                ->cascadeOnDelete();






            /*
            |--------------------------------------------------------------------------
            | School
            |--------------------------------------------------------------------------
            */


            $table->foreignId('school_id')

                ->constrained()

                ->cascadeOnDelete();







            /*
            |--------------------------------------------------------------------------
            | Grade
            |--------------------------------------------------------------------------
            */


            $table->foreignId('grade_id')

                ->constrained()

                ->cascadeOnDelete();








            $table->string('academic_year');







            $table->enum('status', [

                'active',

                'completed',

                'transferred',

                'inactive'

            ])

            ->default('active');







            $table->timestamps();








            /*
            |--------------------------------------------------------------------------
            | A student can have multiple schools in the same year
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | Student A
            | 2026
            |   Royal College Grade 6
            |
            |   ABC School Grade 8
            |
            */


            $table->unique([

                'student_id',

                'school_id',

                'grade_id',

                'academic_year'

            ], 'student_school_grade_year_unique');




        });


    }






    public function down(): void
    {

        Schema::dropIfExists('student_enrollments');

    }


};