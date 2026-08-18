<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->unsignedInteger('attempt_number')->default(1);
            $table->dateTime('started_at');
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('completed_at')->nullable();

            $table->decimal('score', 8, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('is_passed')->default(false);

            $table->enum('status', [
                'in_progress',
                'submitted',
                'time_expired',
                'abandoned',
            ])->default('in_progress');

            $table->timestamps();

            $table->index([
                'quiz_id',
                'student_id',
            ]);

            $table->index([
                'status',
                'expires_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
