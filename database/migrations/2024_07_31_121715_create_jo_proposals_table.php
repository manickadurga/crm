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
            Schema::create('jo_proposals', function (Blueprint $table) {
                $table->id();
                $table->string('author')->nullable();
                $table->string('template')->nullable();
                $table->unsignedBigInteger('contacts')->nullable();
                $table->string('job_post_url')->nullable();
                $table->date('proposal_date')->nullable();
                $table->json('tags')->nullable();
                $table->string('job_post_content')->nullable(false);
                $table->string('proposal_content')->nullable(false);
                $table->timestamps();
            });

            // Log success message
            Log::info('jo_proposals table created successfully.');
        } catch (\Exception $e) {
            // Log error message
            Log::error('Failed to create jo_proposals table: ' . $e->getMessage());

            // Optionally, rethrow the exception if you want to stop the migration
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_proposals');

            // Log success message
            Log::info('jo_proposals table dropped successfully.');
        } catch (\Exception $e) {
            // Log error message
            Log::error('Failed to drop jo_proposals table: ' . $e->getMessage());

            // Optionally, rethrow the exception if you want to stop the rollback
            throw $e;
        }
    }
};
