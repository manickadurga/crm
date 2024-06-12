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
            Schema::create('jo_equipments', function (Blueprint $table) {
                $table->id();
                $table->string('image')->nullable();
                $table->string('name')->nullable(false);
                $table->string('type')->nullable();
                $table->integer('manufactured_year')->nullable();
                $table->string('sn')->nullable();
                $table->integer('max_share_period')->nullable();
                $table->integer('initial_cost')->nullable();
                $table->string('currency')->nullable();
                $table->json('tags')->nullable();
                $table->boolean('auto_approve')->nullable();
                $table->integer('orgid')->nullable();
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Failed to create jo_equipments table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_equipments');
        } catch (\Exception $e) {
            Log::error('Failed to drop jo_equipments table: ' . $e->getMessage());
            throw $e;
        }
    }
};
