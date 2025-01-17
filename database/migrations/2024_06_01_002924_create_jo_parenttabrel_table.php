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
        Schema::create('jo_parenttabrel', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('parenttabid'); 
            $table->unsignedInteger('tabid'); 
            $table->integer('sequence'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_parenttabrel');
    }
};
