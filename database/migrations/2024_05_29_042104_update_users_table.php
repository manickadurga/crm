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
        Schema::table('users', function (Blueprint $table) {
            $table->string('imageurl')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->enum('role', ['SUPER_ADMIN', 'ADMIN', 'DATA_ENTRY', 'EMPLOYEE', 'CANDIDATE', 'MANAGER', 'VIEWER', 'INTERVIEWER'])->default('VIEWER');
            $table->date('applied_date')->nullable();
            $table->date('rejection_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['imageurl', 'first_name', 'last_name', 'role', 'applied_date', 'rejection_date']);
        });
    }
};
