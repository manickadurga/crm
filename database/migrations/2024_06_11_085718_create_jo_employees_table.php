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
            Schema::create('jo_employees', function (Blueprint $table) {
                $table->id();
                $table->string('image')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('username')->nullable();
                $table->string('email')->nullable(false);
                $table->string('password')->nullable(false);
                $table->date('date')->nullable();
                $table->date('reject_date')->nullable();
                $table->date('offer_date')->nullable();
                $table->date('accept_date')->nullable();
                $table->json('tags')->nullable();
                $table->integer('orgid')->default(1);
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Failed to create jo_employees table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_employees');
        } catch (\Exception $e) {
            Log::error('Failed to drop jo_employees table: ' . $e->getMessage());
            throw $e;
        }
    }
};

