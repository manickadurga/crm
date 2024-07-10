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
        Schema::create('jo_products', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->string('name')->nullable(false);
            $table->string('code')->nullable(false);
            $table->string('product_type')->nullable(false); // Store the name of the product type
            $table->string('product_category')->nullable(false); // Store the name of the product category
            $table->text('description')->nullable();
            $table->boolean('enabled')->default(false);
            $table->json('options')->nullable();
            $table->json('tags')->nullable();
            $table->json('add_variants')->nullable();
            $table->string('list_price')->nullable();
            $table->integer('orgid')->nullable();
            $table->timestamps();

            // Indexes for product_type and product_category to improve search and join performance
            $table->index('product_type');
            $table->index('product_category');

            // Foreign key constraints
            $table->foreign('product_type')->references('name')->on('jo_product_types')->onDelete('set null');
            $table->foreign('product_category')->references('name')->on('jo_product_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_products');
    }
};
