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
            Schema::create('jo_vendors', function (Blueprint $table) {
                $table->id();
                $table->string('vendor_name')->nullable(false)->unique();
                $table->integer('phone')->nullable();
                $table->string('email')->nullable(false);
                $table->string('website')->nullable();
                $table->json('tags')->nullable();
                $table->integer('orgid')->nullable();
                $table->timestamps();
            });
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Error creating jo_vendors table: ' . $e->getMessage());
            // You can also choose to throw the exception again or handle it as needed
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_vendors');
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Error dropping jo_vendors table: ' . $e->getMessage());
            // You can also choose to throw the exception again or handle it as needed
            throw $e;
        }
    }
};
