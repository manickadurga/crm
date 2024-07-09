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
            Schema::create('jo_warehouses', function (Blueprint $table) {
                $table->id();
                $table->string('image')->nullable();
                $table->string('name')->nullable(false);
                $table->json('tags')->nullable();
                $table->string('code')->nullable(false);
                $table->string('email')->nullable();
                $table->boolean('active')->nullable();
                $table->text('description')->nullable();
                $table->json('location')->nullable();
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Failed to create jo_warehouses table: ' . $e->getMessage());
            throw $e; // Re-throw the exception to let the migration fail
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_warehouses');
        } catch (\Exception $e) {
            Log::error('Failed to drop jo_warehouses table: ' . $e->getMessage());
            throw $e; // Re-throw the exception to let the rollback fail
        }
    }
};
