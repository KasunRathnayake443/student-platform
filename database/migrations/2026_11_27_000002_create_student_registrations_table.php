<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('institution');
            $table->string('grade');
            $table->string('language');
            $table->string('email');
            $table->string('phone');
            $table->string('referred_by')->nullable();
            $table->string('payment_method');
            $table->date('payment_date');
            $table->string('transaction_reference');
            $table->string('receipt_path')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_registrations');
    }
};
