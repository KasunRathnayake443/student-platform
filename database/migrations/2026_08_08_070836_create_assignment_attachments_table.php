<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_attachments', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Assignment
            |--------------------------------------------------------------------------
            */

            $table->foreignId('assignment_id')
                ->constrained('assignments')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | File Information
            |--------------------------------------------------------------------------
            */

            $table->string('original_name');

            $table->string('file_path');

            $table->string('mime_type')
                ->nullable();

            $table->unsignedBigInteger('file_size')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Display Order
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('sort_order')
                ->default(0);


            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Index
            |--------------------------------------------------------------------------
            */

            $table->index([
                'assignment_id',
                'sort_order',
            ]);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('assignment_attachments');
    }
};