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
        Schema::create('jo_reports', function (Blueprint $table) {
            $table->id('reportid');
            $table->integer('folderid');
            $table->string('reportname');
            $table->text('description')->nullable();
            $table->string('reporttype')->nullable();
            $table->unsignedBigInteger('queryid')->nullable();
            $table->string('state')->nullable();
            $table->integer('customizable')->nullable();
            $table->integer('category')->nullable();
            $table->integer('owner')->nullable();
            $table->string('sharingtype')->nullable();
            $table->timestamps();
            $table->foreign('queryid')->references('queryid')->on('jo_selectquery')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_reports');
    }
};
