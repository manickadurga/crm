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
        Schema::create('jo_relcriteria_grouping', function (Blueprint $table) {
            $table->id('groupid');
            $table->integer('queryid');
            $table->string('group_condition')->nullable();
            $table->string('condition_expression')->nullable();
            $table->timestamps();
            $table->foreign('groupid')->references('id')->on('jo_groups')->onDelete('cascade');
            $table->foreign('queryid')->references('queryid')->on('jo_selectquery')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_relcriteria_grouping');
    }
};
