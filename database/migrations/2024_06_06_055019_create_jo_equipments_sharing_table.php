<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::create('jo_equipments_sharing', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable(false);
                $table->string('select_equipment')->nullable(false);
                $table->string('choose_approve_policy')->nullable(false);
                $table->enum('choice', ['employees', 'teams'])->nullable(false);
                $table->json('add_or_remove_employees')->nullable();
                $table->date('select_request_date')->default(DB::raw('CURRENT_DATE'));
                $table->date('select_start_date')->nullable(false);
                $table->date('select_end_date')->nullable(false);
                $table->timestamps();
            });
        } catch (Exception $e) {
            Log::error('Failed to create jo_equipments_sharing table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_equipments_sharing');
        } catch (Exception $e) {
            Log::error('Failed to drop jo_equipments_sharing table: ' . $e->getMessage());
            throw $e;
        }
    }
};
