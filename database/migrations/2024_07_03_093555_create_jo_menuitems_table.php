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
        Schema::create('jo_menuitems', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('icon');
            $table->string('label');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();

            //$table->foreign('parent_id')->references('id')->on('menuitems')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_menuitems');
    }
};
