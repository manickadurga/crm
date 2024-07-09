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
            Schema::create('jo_equipments_sharing_policy', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        } catch (Exception $e) {
            Log::error('Failed to create jo_equipments_sharing_policy table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_equipments_sharing_policy');
        } catch (Exception $e) {
            Log::error('Failed to drop jo_equipments_sharing_policy table: ' . $e->getMessage());
            throw $e;
        }
    }
};
