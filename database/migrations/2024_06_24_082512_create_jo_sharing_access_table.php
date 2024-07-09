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
            Schema::create('jo_sharing_access', function (Blueprint $table) {
                $table->id();
                $table->string('calendar_of');
                $table->string('can_be_accessed_by');
                $table->enum('with_permissions', ['Read', 'Read and write']); // Enum type for with_permissions
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Failed to create jo_sharing_access table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_sharing_access');
        } catch (\Exception $e) {
            Log::error('Failed to drop jo_sharing_access table: ' . $e->getMessage());
            throw $e;
        }
    }
};
