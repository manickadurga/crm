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
        Schema::create('jo_reportmodules', function (Blueprint $table) {
            $table->id('reportmodulesid');
            //$table->integer('reportmodulesid')->nullable();
            $table->string('primarymodule')->nullable();
            $table->string('secondarymodules')->nullable();
            $table->timestamps();
            $table->foreign('reportmodulesid')->references('reportid')->on('jo_reports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_reportmodules');
    }
};