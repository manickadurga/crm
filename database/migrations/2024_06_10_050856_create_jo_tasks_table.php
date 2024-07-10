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
        Schema::create('jo_tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('tasksnumber')->nullable();
            $table->string('projects')->nullable();
            $table->string('status')->nullable();
            $table->enum('choose',['employees','teams'])->nullable();
            $table->string('addorremoveemployee')->nullable();
            $table->string('title');
            $table->string('priority')->nullable();
            $table->string('size')->nullable();
            $table->json('tags')->nullable();
            $table->date('duedate')->nullable();
            $table->json('estimate')->nullable();
            $table->string('description')->nullable();
            $table->integer('orgid')->nullable()->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_tasks');
    }
};
