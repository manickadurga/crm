<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
        Schema::create('jo_candidates', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->string('first_name')->nullable()->unique();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable(false);
            $table->string('password')->nullable(false);
            $table->date('applied_date')->nullable();
            $table->date('reject_date')->nullable();
            $table->json('tags')->nullable();
            $table->string('source')->nullable();
            $table->string('cv_url')->nullable();
        //    $table->integer('orgid')->default(1);
            $table->timestamps();
        });
        }catch (\Exception $e) {
            Log::error('Failed to create jo_candidates table: ' . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_candidates');
        } catch (\Exception $e) {
            Log::error('Failed to drop jo_candidates table: ' . $e->getMessage());
            throw $e;
        }
    }
};
