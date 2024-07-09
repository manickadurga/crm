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
            Schema::create('jo_product_types', function (Blueprint $table) {
                $table->id();
                //$table->string('language')->nullable()->default('english');
                //$table->string('icon')->nullable()->default('star');
                $table->enum('name', ['Inventory', 'Non Inventory'])->default('Inventory');
                $table->integer('quantity_in_stock')->nullable();
                //$table->string('description')->nullable();
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Failed to create jo_product_types table: ' . $e->getMessage());
            throw $e; // Re-throw the exception to let the migration fail
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_product_types');
        } catch (\Exception $e) {
            Log::error('Failed to drop jo_product_types table: ' . $e->getMessage());
            throw $e; // Re-throw the exception to let the rollback fail
        }
    }
};
