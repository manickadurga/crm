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
            Schema::create('jo_products', function (Blueprint $table) {
                $table->id();
                $table->string('image')->nullable();
                $table->string('name')->nullable(false);
                $table->string('code')->nullable(false);
                $table->string('product_type')->nullable(false); // Store the name of the product type
                //$table->foreign('product_type')->references('name')->on('jo_product_types')->onDelete('cascade');
                $table->string('product_category')->nullable(false); // Store the name of the product category
                $table->text('description')->nullable();
                $table->boolean('enabled')->default(false);
                $table->json('options')->nullable();
                $table->json('tags')->nullable();
                $table->json('add_variants')->nullable();
                $table->string('list_price')->nullable();
                $table->integer('quantity_in_stock')->nullable();
                $table->timestamps();

                
            });
        } catch (\Exception $e) {
            Log::error('Failed to create jo_products table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_products');
        } catch (\Exception $e) {
            Log::error('Failed to drop jo_products table: ' . $e->getMessage());
            throw $e;
        }
    }

};
