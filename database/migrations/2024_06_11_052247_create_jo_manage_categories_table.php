<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::create('jo_manage_categories', function (Blueprint $table) {
                $table->id();
                $table->string('expense_name')->nullable(false);
                $table->json('tags')->nullable();
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Error creating jo_manage_categories table: ' . $e->getMessage());
            throw $e;
        }
    }
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_manage_categories');
        } catch (\Exception $e) {
            Log::error('Error dropping jo_manage_categories table: ' . $e->getMessage());
            throw $e;
        }
    }
};
