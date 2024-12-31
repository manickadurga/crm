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
        Schema::create('jo_schedulereports', function (Blueprint $table) {
            $table->id();
            $table->integer('reportid');
            $table->integer('scheduleid');
            $table->string('recipients'); // Define recipients column correctly
            $table->string('schdate');
            $table->string('schtime');
            $table->string('schdayoftheweek')->nullable();
            $table->string('schdayofthemonth')->nullable();
            $table->string('schannualdates')->nullable();
            $table->string('specificemails')->nullable();
            $table->string('next_trigger_time');
            $table->string('fileformat');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_schedulereports');
    }
};
