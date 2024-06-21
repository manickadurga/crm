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
            Schema::create('jo_groups', function (Blueprint $table) {
                $table->id();
                $table->string('group_name')->nullable(false);
                $table->string('description')->nullable();
                $table->json('group_members')->nullable(false);
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Error creating jo_groups table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_groups');
        } catch (\Exception $e) {
            Log::error('Error dropping jo_groups table: ' . $e->getMessage());
            throw $e;
        }
    }
};
