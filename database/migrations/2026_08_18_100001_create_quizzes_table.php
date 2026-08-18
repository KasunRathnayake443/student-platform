<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('learning_class_id')
                ->constrained('learning_classes')
                ->cascadeOnDelete();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('instructions')->nullable();

            $table->unsignedInteger('time_limit_minutes')->nullable();
            $table->unsignedInteger('max_attempts')->nullable()->default(1);
            $table->unsignedInteger('passing_percentage')->default(50);
            $table->unsignedInteger('total_points')->default(0);

            $table->boolean('show_correct_answers_after_submission')->default(true);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);

            $table->enum('availability_type', [
                'immediate',
                'scheduled',
            ])->default('immediate');

            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();

            $table->boolean('is_published')->default(true);

            $table->timestamps();

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
        Schema::dropIfExists('quizzes');
    }
};
