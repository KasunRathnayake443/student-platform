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
        Schema::create('assignment_teacher', function (Blueprint $table) {
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
            | Assigned Teacher
            |--------------------------------------------------------------------------
            |
            | Teachers assigned to the assignment. Only these teachers (plus
            | assignments.teacher_id) may view submissions and grade them.
            |
            */

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'assignment_id',
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
        Schema::dropIfExists('assignment_teacher');
    }
};
