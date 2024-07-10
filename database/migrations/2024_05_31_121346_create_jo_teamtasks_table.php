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
        Schema::create('jo_teamtasks', function (Blueprint $table) {
            $table->id();
            $table->integer('tasknumber')->nullable();
            $table->string('projects')->nullable();
            $table->string('status')->nullable();
            $table->string('teams')->nullable();
            $table->string('title');
            $table->string('priority')->nullable();
            $table->string('size')->nullable();
            $table->string('tags')->nullable();
            $table->date('duedate')->nullable();
            $table->integer('estimate')->nullable(); 
            // $table->integer('estimate_days')->nullable();
            // $table->integer('estimate_hours')->nullable();
            // $table->integer('estimate_minutes')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();   
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_teamtasks');
    }
};
