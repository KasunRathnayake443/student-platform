<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('note_date');
            $table->text('content');
            $table->timestamps();

            $table->unique(['student_id', 'note_date']);
            $table->index('note_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_notes');
    }
};
