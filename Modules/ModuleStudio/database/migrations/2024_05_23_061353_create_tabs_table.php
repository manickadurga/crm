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
        Schema::create('tabs', function (Blueprint $table) {
            $table->increments('tabid'); // Auto-incrementing primary key
            $table->string('name', 25)->unique();
            $table->integer('presence')->default(1);
            $table->integer('tabsequence')->nullable();
            $table->string('tablabel', 100)->nullable();
            $table->integer('modifiedby')->nullable()->index();
            $table->timestamp('modifiedtime')->nullable(); // Changed to timestamp
            $table->integer('customized')->nullable();
            $table->integer('ownedby')->nullable();
            $table->integer('isentitytype')->default(1);
            $table->integer('trial')->default(0);
            $table->string('version', 10)->nullable();
            $table->string('parent', 30)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tabs');
    }
};
