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
            Schema::create('jo_recuring_expenses', function (Blueprint $table) {
                $table->id();
                $table->string('category_name')->nullable(false);
                $table->boolean('split_expense')->default(false)->nullable();
                $table->decimal('value')->nullable(false);
                $table->string('currency')->default('BGN')->nullable();
                $table->integer('orgid')->nullable();
                $table->timestamps();
            });
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Error creating table jo_recuring_expenses: ' . $e->getMessage());
            
            // Optionally, you can rethrow the exception to stop the migration process
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_recuring_expenses');
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Error dropping table jo_recuring_expenses: ' . $e->getMessage());
            
            // Optionally, you can rethrow the exception to stop the rollback process
            throw $e;
        }
    }
};
