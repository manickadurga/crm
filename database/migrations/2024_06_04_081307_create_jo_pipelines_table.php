<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::create('jo_pipelines', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable(false);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(false);
                $table->json('stages')->nullable();
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Failed to create jo_pipelines table: ' . $e->getMessage());
            throw $e; // rethrow the exception after logging it
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_pipelines');
        } catch (\Exception $e) {
            Log::error('Failed to drop jo_pipelines table: ' . $e->getMessage());
            throw $e; // rethrow the exception after logging it
        }
    }
};
