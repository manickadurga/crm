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
        Schema::create('jo_trigger_filters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trigger_id');
           // $table->foreignId('trigger_id')->constrained('triggers')->onDelete('cascade');
            $table->string('filter_name');
            $table->string('filter_value')->nullable();
            $table->timestamps();
            $table->foreign('trigger_id')->references('id')->on('jo_triggers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_trigger_filters');
    }
};
