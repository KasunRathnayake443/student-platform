<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_notes', function (Blueprint $table) {
            // Add a plain index on student_id first so MySQL can use it for the FK
            // (the FK currently relies on the compound unique index as its backing index)
            $table->index('student_id', 'calendar_notes_student_id_plain_index');
        });

        Schema::table('calendar_notes', function (Blueprint $table) {
            // Now safe to drop the unique constraint —
            // the FK will fall back to the plain student_id index we just created
            $table->dropUnique('calendar_notes_student_id_note_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_notes', function (Blueprint $table) {
            $table->unique(['student_id', 'note_date']);
        });

        Schema::table('calendar_notes', function (Blueprint $table) {
            $table->dropIndex('calendar_notes_student_id_plain_index');
        });
    }
};
