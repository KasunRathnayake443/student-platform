<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->decimal('registration_fee', 10, 2)->nullable()->after('contact_phone');
            $table->string('bank_name')->nullable()->after('registration_fee');
            $table->string('bank_account_name')->nullable()->after('bank_name');
            $table->string('bank_account_number')->nullable()->after('bank_account_name');
            $table->string('bank_branch')->nullable()->after('bank_account_number');
            $table->string('ezcash_number')->nullable()->after('bank_branch');
            $table->string('ezcash_merchant_alias')->nullable()->after('ezcash_number');
            $table->string('mcash_number')->nullable()->after('ezcash_merchant_alias');
            $table->string('mcash_merchant_id')->nullable()->after('mcash_number');
            $table->json('registration_languages')->nullable()->after('mcash_merchant_id');
            $table->json('registration_grades')->nullable()->after('registration_languages');
        });
    }

    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->dropColumn([
                'registration_fee',
                'bank_name',
                'bank_account_name',
                'bank_account_number',
                'bank_branch',
                'ezcash_number',
                'ezcash_merchant_alias',
                'mcash_number',
                'mcash_merchant_id',
                'registration_languages',
                'registration_grades',
            ]);
        });
    }
};
