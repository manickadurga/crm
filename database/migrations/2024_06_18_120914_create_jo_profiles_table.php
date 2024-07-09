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
            Schema::create('jo_profiles', function (Blueprint $table) {
                $table->id();
                $table->integer('profileid')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('directly_related_to_role')->nullable();
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Error creating jo_profiles table: ' . $e->getMessage());
            throw $e; // Rethrow the exception to prevent the migration from proceeding
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_profiles');
        } catch (\Exception $e) {
            Log::error('Error dropping jo_profiles table: ' . $e->getMessage());
            throw $e; // Rethrow the exception to ensure rollback consistency
        }
    }
};

