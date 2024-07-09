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
            Schema::create('jo_customers', function (Blueprint $table) {
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

        } catch (Exception $e) {
            Log::error('Failed to create customers table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_customers');
        } catch (Exception $e) {
            Log::error('Failed to drop customers table: ' . $e->getMessage());
            throw $e;
        }
    }
};

