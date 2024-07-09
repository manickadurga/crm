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
        Schema::create('jo_teams', function (Blueprint $table) {
            $table->id();
            $table->binary('image')->nullable();
            $table->string('team_name')->nullable()->unique();
            $table->string('add_or_remove_projects')->nullable();
            $table->string('add_or_remove_managers');
            $table->string('add_or_remove_members');
            $table->json('tags')->nullable();
            $table->integer('orgid')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_teams');
    }
};
