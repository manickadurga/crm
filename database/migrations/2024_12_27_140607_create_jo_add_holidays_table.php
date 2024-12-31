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
        Schema::create('jo_add_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('holiday_name')->nullable(false);
            $table->json('employee')->nullable();
            $table->enum('policy', ['default_policy'])->nullable();
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_add_holidays');
    }
};
