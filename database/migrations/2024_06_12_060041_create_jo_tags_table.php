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
            Schema::create('jo_tags', function (Blueprint $table) {
                $table->id();
                $table->string('tags_name');
                $table->string('tag_color');
                $table->boolean('tenant_level')->nullable();
                $table->string('description')->nullable();
                $table->integer('orgid')->nullable();
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Error creating jo_tags table: ' . $e->getMessage());
            throw $e; // Optionally rethrow the exception if you want the migration to fail
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_tags');
        } catch (\Exception $e) {
            Log::error('Error dropping jo_tags table: ' . $e->getMessage());
            throw $e; // Optionally rethrow the exception if you want the migration rollback to fail
        }
    }
};
