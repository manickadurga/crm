<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jo_reportsummary', function (Blueprint $table) {
            $table->id('reportsummaryid');
            //$table->integer('reportsummaryid');
            $table->integer('summarytype');
            $table->string('columnname');
            $table->timestamps();
            $table->foreign('reportsummaryid')->references('reportid')->on('jo_reports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_reportsummary');
    }
};
