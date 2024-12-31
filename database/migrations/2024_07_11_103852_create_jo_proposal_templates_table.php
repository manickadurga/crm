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
            Schema::create('jo_proposal_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('select_employee');
                $table->string('name');
                $table->text('content')->nullable();
                $table->timestamps();

                $table->foreign('select_employee')->references('id')->on('jo_employees')->onDelete('set null');
            });
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to create jo_proposal_templates table: ' . $e->getMessage());
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
            Schema::dropIfExists('jo_proposal_templates');
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to drop jo_proposal_templates table: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // Optionally rethrow the exception to indicate migration failure
            throw $e;
        }
    }
};
