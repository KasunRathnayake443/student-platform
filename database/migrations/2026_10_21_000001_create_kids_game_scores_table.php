<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kids_game_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('game_key', 40);
            $table->unsignedInteger('best_score')->default(0);
            $table->unsignedTinyInteger('stars')->default(0);
            $table->unsignedInteger('plays')->default(0);
            $table->timestamp('last_played_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'game_key']);
            $table->index('game_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kids_game_scores');
    }
};
