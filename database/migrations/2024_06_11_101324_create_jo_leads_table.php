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
            Schema::create('jo_leads', function (Blueprint $table) {
                $table->id();
                $table->string('image')->nullable();
                $table->string('name')->nullable(false);
                $table->string('primary_email')->nullable();
                $table->string('primary_phone')->nullable();
                $table->string('website')->nullable();
                $table->string('fax')->nullable();
                $table->text('fiscal_information')->nullable();
                $table->json('projects')->nullable();
                $table->string('contact_type')->nullable();
                $table->json('tags')->nullable();
                $table->json('location')->nullable();
                $table->string('type')->nullable();
                $table->integer('type_suffix')->nullable();
                $table->integer('orgid')->nullable();
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Failed to create jo_leads table: ' . $e->getMessage());
            throw $e; // Re-throw the exception to make sure the migration fails
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_leads');
        } catch (\Exception $e) {
            Log::error('Failed to drop jo_leads table: ' . $e->getMessage());
            throw $e; // Re-throw the exception to make sure the rollback fails
        }
    }
};
