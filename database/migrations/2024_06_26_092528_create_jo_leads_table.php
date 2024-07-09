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
        Schema::create('jo_leads', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->string('name')->nullable(false);
            $table->string('primary_email')->nullable();
            $table->string('primary_phone')->nullable();
            $table->string('website')->nullable();
            $table->string('fax')->nullable();
            $table->text('fiscal_information')->nullable();
            $table->json('projects')->nullable();
            $table->string('contact_type')->nullable();
            $table->json('tags')->nullable();
            $table->json('location')->nullable();
            $table->integer('type')->nullable();
            $table->enum('type_suffix', ['cost', 'hours'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_leads');
    }
};
