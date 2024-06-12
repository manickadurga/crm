<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class CreateJoMerchantsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::create('jo_merchants', function (Blueprint $table) {
                $table->id();
                $table->string('image')->nullable();
                $table->string('name')->nullable(false);
                $table->string('code')->nullable(false);
                $table->string('email')->nullable(false);
                $table->string('phone')->nullable();
                $table->string('currency')->nullable()->default('Bulgarian LEV(BGN)');
                $table->string('fax')->nullable();
                $table->string('fiscal_information')->nullable();
                $table->string('website')->nullable();
                $table->text('description')->nullable();
                $table->json('tags')->nullable();
                $table->boolean('is_active')->default(false)->nullable();
                $table->json('location')->nullable();
                $table->string('warehouses')->nullable(); // Column to store the name of the warehouse
                $table->foreign('warehouses')->references('name')->on('jo_warehouses')->onDelete('set null');
                $table->integer('orgid')->nullable();
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Failed to create jo_merchants table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_merchants');
        } catch (\Exception $e) {
            Log::error('Failed to drop jo_merchants table: ' . $e->getMessage());
            throw $e;
        }
    }
}
