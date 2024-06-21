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
            Schema::create('jo_expenses', function (Blueprint $table) {
                $table->id();
                $table->boolean('tax_deductible')->default(false)->nullable();
                $table->boolean('not_tax_deductible')->default(false)->nullable();
                $table->boolean('billable_to_contact')->default(false)->nullable();
                $table->string('employees_that_generate')->nullable()->default('all employees');
                $table->string('currency')->nullable()->default('Bulgarian LEV(BGN)');
                $table->string('categories')->nullable();
                $table->date('date')->default(DB::raw('CURRENT_DATE'))->nullable();
                $table->string('vendor')->nullable();
                $table->integer('amount')->nullable(false);
                $table->text('purpose')->nullable();
                $table->string('contact')->nullable();
                $table->json('project')->nullable();
                $table->json('tags')->nullable();
                $table->string('select_status')->nullable();
                $table->text('notes')->nullable();
                $table->json('include_taxes')->nullable();
                $table->binary('attach_a_receipt')->nullable();
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Error creating jo_expenses table: ' . $e->getMessage());
            throw $e; // Optionally rethrow the exception if you want the migration to fail
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_expenses');
        } catch (\Exception $e) {
            Log::error('Error dropping jo_expenses table: ' . $e->getMessage());
            throw $e; // Optionally rethrow the exception if you want the migration rollback to fail
        }
    }
};
