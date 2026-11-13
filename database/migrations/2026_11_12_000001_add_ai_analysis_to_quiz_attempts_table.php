<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->json('ai_analysis')->nullable();
            $table->string('ai_analysis_status', 20)->nullable();
            $table->text('ai_analysis_error')->nullable();
            $table->timestamp('ai_analysis_generated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn([
                'ai_analysis',
                'ai_analysis_status',
                'ai_analysis_error',
                'ai_analysis_generated_at',
            ]);
        });
    }
};
