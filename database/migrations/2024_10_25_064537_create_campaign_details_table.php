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
        Schema::create('campaign_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');  // Foreign key to campaigns table
            $table->string('send_method');
            $table->timestamp('schedule_at')->nullable();
            $table->string('sender_email');
            $table->string('sender_name');
            $table->string('subject_line');
            $table->string('preview_text');
            $table->json('recipient_to');
            $table->integer('batch_quantity')->nullable();
            $table->string('repeat_after')->nullable(); 
            $table->integer('no_of_recipients')->nullable(); // Delay between batches in minutes
            $table->string('send_on')->nullable();  // Days of the week to send emails
            $table->time('start_time')->nullable();  // Start time for sending emails
            $table->time('end_time')->nullable();  // End time for sending emails
            $table->date('start_on')->nullable();  // Start date for the campaign

            // Add the foreign key constraint
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_details');
    }
};