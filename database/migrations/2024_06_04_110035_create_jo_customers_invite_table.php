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
            Schema::create('jo_customers_invite', function (Blueprint $table) {
                $table->id();
                $table->string('contact_name')->nullable(false);
                $table->string('primary_phone')->nullable(false);
                $table->string('email')->nullable(false);
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Failed to create jo_customers_invite table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_customers_invite');
        } catch (\Exception $e) {
            Log::error('Failed to drop jo_customers_invite table: ' . $e->getMessage());
            throw $e;
        }
    }
};
