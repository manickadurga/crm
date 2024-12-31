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
        Schema::create('jo_sms_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trigger_id');
            $table->string('action_name');
            $table->string('templates');
            $table->text('message'); // SMS message content
            $table->text('attachments')->nullable(); // JSON or text with attachment URLs
            $table->text('file_url')->nullable(); // Add file URL column
            $table->string('test_phone_number')->nullable();
            $table->timestamps();
            $table->foreign('trigger_id')->references('id')->on('jo_triggers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_sms_actions');
    }
};
