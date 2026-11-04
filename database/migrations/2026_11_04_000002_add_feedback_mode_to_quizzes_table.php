<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('feedback_mode', 30)
                ->default('end_of_quiz')
                ->after('show_correct_answers_after_submission');
        });

        // Preserve the behaviour of existing quizzes: when the old "show correct
        // answers" toggle was off, the quiz only ever revealed the score.
        DB::table('quizzes')
            ->where('show_correct_answers_after_submission', false)
            ->update(['feedback_mode' => 'score_only']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('feedback_mode');
        });
    }
};
