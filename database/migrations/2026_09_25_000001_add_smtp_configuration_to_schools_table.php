<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('smtp_host')
                ->nullable()
                ->after('email');
            $table->text('smtp_password')
                ->nullable()
                ->after('smtp_host');
            $table->unsignedSmallInteger('smtp_port')
                ->default(587)
                ->after('smtp_password');
            $table->string('smtp_encryption', 20)
                ->default('tls')
                ->after('smtp_port');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'smtp_host',
                'smtp_password',
                'smtp_port',
                'smtp_encryption',
            ]);
        });
    }
};
