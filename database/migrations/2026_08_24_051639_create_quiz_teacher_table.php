<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quiz_teacher', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Quiz
            |--------------------------------------------------------------------------
            */

            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Assigned Teacher
            |--------------------------------------------------------------------------
            |
            | Teachers assigned to the quiz. Only these teachers (plus
            | quizzes.teacher_id) may manage and review it.
            |
            */

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'quiz_id',
                'teacher_id',
            ]);

            $table->index('teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_teacher');
    }
};
