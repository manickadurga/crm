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
            Schema::create('jo_opportunities', function (Blueprint $table) {
                $table->id(); 
                $table->unsignedBigInteger('contact_id');
                $table->unsignedBigInteger('select_pipeline');
                $table->string('select_stage')->nullable();
                $table->string('opportunity_name')->nullable();
                $table->string('opportunity_source')->nullable();
                $table->unsignedBigInteger('lead_value')->nullable();
                $table->string('opportunity_status')->nullable();
                $table->string('action')->nullable();
                $table->timestamps();
                //$table->foreign('owner')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            Log::error('Error creating jo_opportunites table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_opportunities');
        } catch (\Exception $e) {
            Log::error('Error dropping jo_opportunities table: ' . $e->getMessage());
            throw $e;
        }
    }
};
