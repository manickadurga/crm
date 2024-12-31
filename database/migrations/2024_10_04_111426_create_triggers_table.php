<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTriggersTable extends Migration
{
    public function up()
    {
        Schema::create('triggers', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('trigger_name'); // Name of the trigger (e.g., "Contact Created")
            $table->json('filters')->nullable(); 
            $table->timestamps(); // Created at and updated at columns
        });
    }

    public function down()
    {
        Schema::dropIfExists('triggers');
    }
}

