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
        Schema::create('jo_relatedlists', function (Blueprint $table) {
            $table->increments('relationid'); // Primary key with auto_increment
            $table->integer('tabid')->unsigned()->nullable();
            $table->integer('related_tabid')->unsigned()->nullable();
            $table->string('name', 100)->nullable();
            $table->integer('sequence')->nullable();
            $table->string('label', 100)->nullable();
            $table->integer('presence')->default(0);
            $table->string('actions', 50)->default('');
            $table->integer('relationfieldid')->unsigned()->nullable();
            $table->string('source', 25)->nullable();
            $table->string('relationtype', 10)->nullable();
            $table->timestamps();
            $table->index('tabid');
            $table->index('related_tabid');
            $table->index('relationfieldid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_relatedlists');
    }
};
