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
        Schema::create('modulestudios', function (Blueprint $table) {
            $table->id();
            $table->string('module_name');
            $table->string('version')->nullable();
            $table->string('singular_translation')->nullable();
            $table->string('plural_translation')->nullable();
            $table->json('fields');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modulestudios');
    }
};
