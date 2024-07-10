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
        Schema::create('jo_incomes', function (Blueprint $table) {
            $table->id();
            $table->string('Employees that generate income')->nullable();
            $table->string('Contact')->nullable();
            $table->date('pick_date')->nullable();
            $table->string('currency')->nullable();
            $table->integer('amount');
            $table->jsonb('tags')->nullable();
            $table->enum('choose', ['Bonus'])->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_incomes');
    }
};
