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
                $table->string('image')->nullable();
                $table->string('project_name')->nullable(false);
                $table->string('code')->nullable();
                $table->string('project_url')->nullable();
                $table->string('owner')->nullable();
                $table->unsignedBigInteger('clients')->nullable(); // Change to unsignedBigInteger
                $table->json('add_or_remove_employees')->nullable();
                $table->json('add_or_remove_teams')->nullable();
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
                $table->timestamps();
                
                $table->index('clients');
                $table->foreign('clients')->references('id')->on('jo_clients')->onDelete('set null');
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
