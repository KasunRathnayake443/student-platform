<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->string('facebook_url')->nullable()->after('contact_phone');
            $table->string('youtube_url')->nullable()->after('facebook_url');
            $table->string('tiktok_url')->nullable()->after('youtube_url');
            $table->string('instagram_url')->nullable()->after('tiktok_url');
        });
    }

    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_url',
                'youtube_url',
                'tiktok_url',
                'instagram_url',
            ]);
        });
    }
};
