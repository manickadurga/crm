<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
        
            Schema::create('jo_projects', function (Blueprint $table) {
                $table->id();
                $table->binary('image')->nullable();
                $table->string('project_name')->unique();
                $table->string('code')->nullable();
                $table->string('project_url')->nullable();
                $table->string('owner')->nullable();
                $table->string('clients')->nullable();
                $table->string('add_or_remove_employees')->nullable();
                $table->string('add_or_remove_teams')->nullable();
                $table->date('project_start_date')->nullable();
                $table->date('project_end_date')->nullable();
                $table->string('description')->nullable();
                $table->json('tags')->nullable();
                $table->string('billing')->nullable();
                $table->string('currency')->nullable();
                $table->string('type')->nullable();
                $table->integer('cost')->nullable();
                $table->boolean('open_source')->default(false);
                $table->string('open_source_url')->nullable();
                $table->string('color')->nullable();
                $table->string('task_view_mode')->nullable();
                $table->boolean('public')->default(false);
                $table->boolean('billable')->default(false);
                $table->integer('orgid')->nullable();
                $table->timestamps();





                $table->index('clients');
                $table->index('add_or_remove_employees');
                $table->index('add_or_remove_teams');

                // Foreign key constraints
                $table->foreign('clients')->references('clientsname')->on('jo_clients')->onDelete('set null');
                $table->foreign('add_or_remove_employees')->references('first_name')->on('jo_employees')->onDelete('set null');
                $table->foreign('add_or_remove_teams')->references('team_name')->on('jo_teams')->onDelete('set null');
            });
        } catch (\Exception $e) {
            Log::error('Failed to create jo_projects table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_projects');
        } catch (\Exception $e) {
            Log::error('Failed to drop jo_projects table: ' . $e->getMessage());
            throw $e;
        }
    }

};