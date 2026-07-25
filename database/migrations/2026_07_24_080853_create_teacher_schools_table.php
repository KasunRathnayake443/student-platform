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
        Schema::create('teacher_schools', function (Blueprint $table) {

            $table->id();
        
        
            $table->foreignId('teacher_id')
                ->constrained('users')
                ->cascadeOnDelete();
        
        
            $table->foreignId('school_id')
                ->constrained()
                ->cascadeOnDelete();
        
        
            $table->timestamps();
        
        
            $table->unique(
                [
                    'teacher_id',
                    'school_id'
                ],
                'teacher_school_unique'
            );
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_schools');
    }
};
