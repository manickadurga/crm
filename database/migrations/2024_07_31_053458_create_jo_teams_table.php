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
            $table->string('image')->nullable();
            $table->string('team_name')->nullable(false);
            $table->json('add_or_remove_projects')->nullable();
            $table->json('add_or_remove_managers')->nullable(false);
            $table->json('add_or_remove_members')->nullable();
            $table->json('tags')->nullable();
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
