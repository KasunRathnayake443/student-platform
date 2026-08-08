<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_submissions', function (Blueprint $table) {

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Assignment
            |--------------------------------------------------------------------------
            */

            $table->foreignId('assignment_id')
                ->constrained('assignments')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Student
            |--------------------------------------------------------------------------
            */

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Submission Information
            |--------------------------------------------------------------------------
            */

            $table->longText('content')
                ->nullable();


            $table->dateTime('submitted_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Late Submission
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_late')
                ->default(false);


            /*
            |--------------------------------------------------------------------------
            | Grading
            |--------------------------------------------------------------------------
            */

            $table->decimal('score', 8, 2)
                ->nullable();


            $table->longText('feedback')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Graded By
            |--------------------------------------------------------------------------
            |
            | This will point to the teacher who actually graded
            | the submission.
            |
            */

            $table->foreignId('graded_by')
                ->nullable()
                ->constrained('teachers')
                ->nullOnDelete();


            $table->dateTime('graded_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'draft',
                'submitted',
                'graded',
                'returned',
            ])->default('draft');


            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | One submission per student per assignment
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'assignment_id',
                'student_id',
            ], 'assignment_student_unique');


            /*
            |--------------------------------------------------------------------------
            | Useful indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'assignment_id',
                'status',
            ]);

            $table->index([
                'student_id',
                'status',
            ]);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};