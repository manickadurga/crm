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
        Schema::create('blocks', function (Blueprint $table) {
            $table->increments('blockid');
            $table->integer('tabid');
            $table->string('blocklabel', 100);
            $table->integer('sequence')->nullable();
            $table->integer('show_title')->nullable();
            $table->integer('visible')->default(0);
            $table->integer('create_view')->default(0);
            $table->integer('edit_view')->default(0);
            $table->integer('detail_view')->default(0);
            $table->integer('display_status')->default(1);
            $table->integer('iscustom')->default(0);
            $table->timestamps();

            $table->foreign('tabid')->references('tabid')->on('tabs')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
