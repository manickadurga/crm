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
            Schema::create('jo_group2grouprel', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('groupid');
                $table->unsignedBigInteger('containsgroupid');
                $table->timestamps();

                // Define foreign key constraints
                $table->foreign('groupid')->references('id')->on('jo_groups')->onDelete('cascade');
                $table->foreign('containsgroupid')->references('id')->on('jo_groups')->onDelete('cascade');
            });

            Log::info('Migration jo_group2grouprel table created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating jo_group2grouprel table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_group2grouprel');

            // Log a message indicating success
            Log::info('Migration jo_group2grouprel table dropped successfully.');
        } catch (\Exception $e) {
            Log::error('Error dropping jo_group2grouprel table: ' . $e->getMessage());
            throw $e;
        }
    }
};
