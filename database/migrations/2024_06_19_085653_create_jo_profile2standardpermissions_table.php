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
            Schema::create('jo_profile2standardpermissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('profileid');
                $table->unsignedBigInteger('tabid');
                $table->integer('operations');
                $table->integer('permissions');
                $table->timestamps();

                $table->foreign('profileid')->references('profileid')->on('jo_profiles')->onDelete('cascade');
                $table->foreign('tabid')->references('tabid')->on('jo_tabs')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            Log::error('Failed to create jo_profile2standardpermissions table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_profile2standardpermissions');
        } catch (\Exception $e) {
            Log::error('Failed to drop jo_profile2standardpermissions table: ' . $e->getMessage());
            throw $e;
        }
    }
};
