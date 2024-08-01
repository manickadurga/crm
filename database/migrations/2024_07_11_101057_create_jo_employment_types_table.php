<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::create('jo_employment_types', function (Blueprint $table) {
                $table->id();
                $table->string('employment_type_name');
                $table->json('tags')->nullable();
                $table->timestamps();
            });

        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to create jo_employment_types table: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // Optionally rethrow the exception to indicate migration failure
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_employment_types');
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to drop jo_employment_types table: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // Optionally rethrow the exception to indicate migration failure
            throw $e;
        }
    }
};
