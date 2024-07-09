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
            Schema::create('jo_operations', function (Blueprint $table) {
                $table->id();
                $table->integer('operationid');
                $table->string('name');
                $table->string('type');
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Failed to create jo_operations table: ' . $e->getMessage());
            throw $e;  
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_operations');
        } catch (\Exception $e) {
            Log::error('Failed to drop jo_operations table: ' . $e->getMessage());
            throw $e;
        }
    }
};
