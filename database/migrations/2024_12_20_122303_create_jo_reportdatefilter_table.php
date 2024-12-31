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
        Schema::create('jo_reportdatefilter', function (Blueprint $table) {
            $table->id('datefilterid');
            //$table->unsignedBigInteger('datefilderid');
            $table->string('datecolumnname');
            $table->string('datefilder');
            $table->date('startdate');
            $table->date('enddate');
            $table->timestamps();
            $table->foreign('datefilterid')->references('reportid')->on('jo_reports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_reportdatefilter');
    }
};
