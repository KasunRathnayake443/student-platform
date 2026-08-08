<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('learning_class_id')
                ->constrained('learning_classes')
                ->cascadeOnDelete();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->cascadeOnDelete();

            $table->string('title');

            $table->text('description')
                ->nullable();

            $table->longText('content')
                ->nullable();

            $table->string('video_url')
                ->nullable();

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->boolean('is_published')
                ->default(false);

            $table->timestamps();

            $table->index([
                'learning_class_id',
                'is_published',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};