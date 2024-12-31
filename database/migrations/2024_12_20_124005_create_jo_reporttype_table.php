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
        Schema::create('jo_reporttype', function (Blueprint $table) {
            $table->id('reportid');
            $table->string('data');
            $table->timestamps();
            $table->foreign('reportid')->references('reportid')->on('jo_reports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_reporttype');
    }
};