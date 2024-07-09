<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateJoPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        try {
            Schema::create('jo_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_number')->nullable();
                $table->foreign('invoice_number')->references('id')->on('jo_invoices')->onDelete('set null');
                $table->unsignedBigInteger('contacts')->nullable();
                $table->foreign('contacts')->references('crmid')->on('jo_crmentity')->onDelete('set null');
                $table->string('projects');
                $table->date('payment_date')->default(DB::raw('CURRENT_DATE'));
                $table->string('payment_method')->nullable(false);
                $table->string('currency')->nullable()->default('Bulgarian LEV(BGN)');
                $table->json('tags')->nullable();
                $table->bigInteger('amount')->nullable(false);
                $table->text('note')->nullable();
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Failed to run the migration for creating jo_payments table: ' . $e->getMessage());
            throw $e; // Optionally, rethrow the exception to halt the migration process
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_payments');
        } catch (\Exception $e) {
            Log::error('Failed to reverse the migration for dropping jo_payments table: ' . $e->getMessage());
            throw $e; // Optionally, rethrow the exception to halt the migration rollback process
        }
    }
};
