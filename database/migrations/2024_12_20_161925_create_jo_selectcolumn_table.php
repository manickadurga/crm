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
        Schema::create('jo_selectcolumn', function (Blueprint $table) {
            $table->id();  
            $table->integer('queryid');
            //$table->integer('queryid');
            $table->integer('columnindex');
            $table->string('columnname')->nullable();
            $table->timestamps();
            $table->foreign('queryid')->references('queryid')->on('jo_selectquery')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_selectcolumn');
    }
};
