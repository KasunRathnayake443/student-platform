<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Learning Class
            |--------------------------------------------------------------------------
            |
            | Every assignment belongs to exactly one learning class.
            |
            */

            $table->foreignId('learning_class_id')
                ->constrained('learning_classes')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Responsible Teacher
            |--------------------------------------------------------------------------
            |
            | This is the teacher who is responsible for grading.
            |
            */

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Assignment Information
            |--------------------------------------------------------------------------
            */

            $table->string('title');

            $table->text('description')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Maximum Score
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('max_score')
                ->default(100);


            /*
            |--------------------------------------------------------------------------
            | Availability
            |--------------------------------------------------------------------------
            |
            | immediate = available immediately
            | scheduled = becomes available at start_at
            |
            */

            $table->enum('availability_type', [
                'immediate',
                'scheduled',
            ])->default('immediate');


            /*
            |--------------------------------------------------------------------------
            | Start Date / Time
            |--------------------------------------------------------------------------
            */

            $table->dateTime('start_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | End Date / Time
            |--------------------------------------------------------------------------
            */

            $table->dateTime('end_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Late Submissions
            |--------------------------------------------------------------------------
            */

            $table->boolean('allow_late_submissions')
                ->default(false);


            $table->unsignedInteger('late_submission_value')
                ->nullable();


            $table->enum('late_submission_unit', [
                'minutes',
                'hours',
                'days',
            ])->nullable();


            /*
            |--------------------------------------------------------------------------
            | Allowed Submission File Types
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | [
            |     "pdf",
            |     "docx",
            |     "pptx",
            |     "mp4"
            | ]
            |
            */

            $table->json('allowed_submission_types')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Published
            |--------------------------------------------------------------------------
            |
            | Allows an assignment to be created without immediately
            | making it visible to students.
            |
            */

            $table->boolean('is_published')
                ->default(true);


            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'learning_class_id',
                'is_published',
            ]);

            $table->index([
                'start_at',
                'end_at',
            ]);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};