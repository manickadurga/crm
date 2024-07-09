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
            Schema::create('jo_clients', function (Blueprint $table) {
                $table->id();
                $table->string('image')->nullable();
                $table->string('clientsname')->nullable(false)->unique();
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
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Failed to create jo_clients table: ' . $e->getMessage());
            throw $e; // Re-throw the exception to ensure the migration fails
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_clients');
        } catch (\Exception $e) {
            Log::error('Failed to drop jo_clients table: ' . $e->getMessage());
            throw $e; // Re-throw the exception to ensure the rollback fails
        }
    }
};
