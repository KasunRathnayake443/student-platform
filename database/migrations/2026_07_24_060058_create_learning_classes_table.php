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
        Schema::create('learning_classes', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('grade_id')
                ->constrained()
                ->cascadeOnDelete();
        
            $table->string('name');
        
            $table->enum('medium', [
                'Sinhala',
                'English',
                'Tamil',
            ]);
        
            $table->boolean('is_active')
                ->default(true);
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_classes');
    }
};
