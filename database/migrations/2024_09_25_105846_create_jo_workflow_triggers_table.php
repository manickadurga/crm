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
        Schema::create('jo_workflow_triggers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_id');
            $table->string('type');  // E.g., "contact_created"
            $table->string('workflow_trigger_name');  // E.g., "New Contact"
            $table->json('filters')->nullable();
            $table->timestamps();
            $table->foreign('workflow_id')->references('id')->on('jo_workflows')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_workflow_triggers');
    }
};
