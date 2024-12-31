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
            $table->string('name')->nullable();
            $table->integer('min_count')->nullable();
            $table->string('approval_policy')->nullable();
            $table->string('created_by')->nullable();
            $table->string('created_At')->nullable(); // This stores the string representation
            $table->string('employees')->nullable();
            $table->string('teams')->nullable();
            $table->string('status')->nullable();
            $table->timestamps(); // This will still add 'created_at' and 'updated_at' as timestamps
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
