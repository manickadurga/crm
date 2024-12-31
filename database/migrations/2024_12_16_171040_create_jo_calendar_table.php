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
        Schema::create('jo_calendar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id'); // Refers to jo_manage_employees
            $table->date('date');
            $table->string('start_time');
            $table->string('end_time');
            $table->boolean('is_billable')->default(false);
            $table->unsignedBigInteger('client_id')->nullable(); // Refers to jo_clients
            $table->unsignedBigInteger('project_id')->nullable(); // Refers to jo_projects
            $table->unsignedBigInteger('team_id')->nullable(); // Refers to jo_teams
            $table->unsignedBigInteger('task_id')->nullable(); // Refers to jo_tasks
            $table->text('description')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('employee_id')->references('id')->on('jo_manage_employees')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('jo_clients')->onDelete('set null');
            $table->foreign('project_id')->references('id')->on('jo_projects')->onDelete('set null');
            $table->foreign('team_id')->references('id')->on('jo_teams')->onDelete('set null');
            $table->foreign('task_id')->references('id')->on('jo_tasks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_calendar');
    }
};
