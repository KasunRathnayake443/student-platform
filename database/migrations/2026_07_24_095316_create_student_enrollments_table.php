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
        Schema::create('student_enrollments', function (Blueprint $table) {
    
            $table->id();
    
    
            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();
    
    
            $table->foreignId('school_id')
                ->constrained()
                ->cascadeOnDelete();
    
    
            $table->foreignId('grade_id')
                ->constrained()
                ->cascadeOnDelete();
    
    
            $table->string('academic_year');
    
    
            $table->enum('status', [
                'active',
                'completed',
                'transferred',
                'inactive'
            ])
            ->default('active');
    
    
            $table->timestamps();
    
    
            $table->unique(
                [
                    'student_id',
                    'academic_year'
                ],
                'student_year_unique'
            );
    
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
