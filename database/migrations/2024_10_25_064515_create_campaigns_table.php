<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsTable extends Migration
{
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('template_id')->constrained('jo_templates'); // Foreign key reference to templates table
            $table->timestamps(); // For created_at and updated_at timestamps
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaigns');
    }
}
