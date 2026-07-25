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
        Schema::create('class_teacher', function (Blueprint $table) {

            $table->id();
        
        
            $table->foreignId('teacher_id')
                ->constrained('users')
                ->cascadeOnDelete();
        
        
            $table->foreignId('learning_class_id')
                ->constrained()
                ->cascadeOnDelete();
        
        
            $table->enum('role', [
                'main',
                'assistant',
                'substitute'
            ])->default('main');
        
        
            $table->timestamps();
        
        
            $table->unique(
                [
                    'teacher_id',
                    'learning_class_id'
                ],
                'teacher_class_unique'
            );
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_teacher');
    }
};
