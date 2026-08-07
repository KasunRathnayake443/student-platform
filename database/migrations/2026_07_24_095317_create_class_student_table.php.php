<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {

        Schema::create('class_student', function (Blueprint $table) {


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
            | Student Enrollment
            |--------------------------------------------------------------------------
            |
            | Links the class assignment to a specific
            | school + grade enrollment
            |
            */


            $table->foreignId('student_enrollment_id')

                ->constrained('student_enrollments')

                ->cascadeOnDelete();






            /*
            |--------------------------------------------------------------------------
            | Learning Class
            |--------------------------------------------------------------------------
            */


            $table->foreignId('learning_class_id')

                ->constrained()

                ->cascadeOnDelete();





            $table->timestamps();





            /*
            |--------------------------------------------------------------------------
            | Prevent duplicate class assignments
            |--------------------------------------------------------------------------
            */


            $table->unique([

                'student_enrollment_id',

                'learning_class_id'

            ]);



        });

    }





    public function down(): void
    {

        Schema::dropIfExists('class_student');

    }

};