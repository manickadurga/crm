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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('contactowner')->default('Zohodemo');
            $table->string('leadsource')->default('none');
            $table->string('first_name_prefix')->nullable();
            $table->string('firstname')->default('Big');
            $table->string('lastname');
            $table->string('accountname');
            $table->string('vendorname');
            $table->date('dob');
            $table->boolean('emailoptout')->default(false);
            $table->string('mailingstreet');
            $table->string('otherstreet');
            $table->string('mailingcity');
            $table->string('othercity');
            $table->string('mailingstate');
            $table->string('otherstate');
            $table->string('mailingcountry');
            $table->string('othercountry');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
