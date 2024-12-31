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
        Schema::create('jo_request_timeoff', function (Blueprint $table) {
            $table->id();
            $table->json('employee')->nullable();
            $table->enum('policy', ['default_policy'])->nullable();
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->enum('download_request_form', ['paid_daysoff', 'unpaid_daysoff'])->nullable();
            $table->string('upload_request_document')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_request_timeoff');
    }
};
