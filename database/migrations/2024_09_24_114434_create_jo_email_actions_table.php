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
        Schema::create('jo_email_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trigger_id');
           // $table->foreignId('trigger_id')->constrained()->onDelete('cascade');
            $table->string('action_name'); // e.g., 'send_email'
            $table->string('from_name');
            $table->string('from_email');
            $table->string('subject');
            $table->text('template'); // Template with placeholders
            $table->text('message'); // Full message with placeholders
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->foreign('trigger_id')->references('id')->on('jo_triggers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_email_actions');
    }
};
