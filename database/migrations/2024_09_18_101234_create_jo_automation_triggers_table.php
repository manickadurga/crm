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
        Schema::create('jo_automation_triggers', function (Blueprint $table) {
            $table->id();
            $table->string('event');
            $table->string('action');
            $table->json('message_details');
            $table->string('recipient_field');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_automation_triggers');
    }
};
