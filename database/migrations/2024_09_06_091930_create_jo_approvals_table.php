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
        Schema::create('jo_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('min_count');
            $table->integer('approval_policy')->nullable();
            $table->enum('choose',['employees','teams'])->default('employees');;
            $table->json('choose_employees')->nullable();
            $table->json('choose_teams')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->foreign('approval_policy')->references('id')->on('jo_approval_policy')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_approvals');
    }
};
