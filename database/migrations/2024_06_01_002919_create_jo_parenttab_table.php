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
        Schema::create('jo_parenttab', function (Blueprint $table) {
            $table->id('parenttabid'); 
            $table->string('parenttab_label', 100); 
            $table->integer('sequence');
            $table->integer('visible')->default(0);
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_parenttab');
    }
};
