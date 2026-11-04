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
        Schema::table('learning_classes', function (Blueprint $table) {
            $table->string('emoji', 16)
                ->nullable()
                ->after('medium');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_classes', function (Blueprint $table) {
            $table->dropColumn('emoji');
        });
    }
};
