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
        Schema::create('jo_report_shareroles', function (Blueprint $table) {
            $table->id('reportid');
            $table->string('roleid');
            $table->timestamps();
            $table->foreign('reportid')->references('reportid')->on('jo_reports')->onDelete('cascade');
            $table->foreign('roleid')->references('roleid')->on('jo_roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_report_shareroles');
    }
};