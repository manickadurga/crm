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
        Schema::create('parenttabrel', function (Blueprint $table) {
            $table->unsignedInteger('parenttabid'); 
            $table->unsignedInteger('tabid'); 
            $table->integer('sequence'); 
            
            $table->timestamps(); 

            $table->foreign('parenttabid')->references('parenttabid')->on('parenttab')->onDelete('cascade');

            $table->foreign('tabid')
            ->references('tabid')
            ->on('tabs')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parenttabrel');
    }
};
