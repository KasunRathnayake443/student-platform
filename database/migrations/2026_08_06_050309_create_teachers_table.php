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
        Schema::create('teachers', function (Blueprint $table) {
    
            $table->id();
    
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
    
            $table->string('profile_photo')->nullable();
    
            $table->string('employee_no')
                ->unique()
                ->nullable();
    
            $table->string('phone')
                ->nullable();
    
            $table->text('address')
                ->nullable();
    
            $table->timestamps();
    
            $table->unique('user_id');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
