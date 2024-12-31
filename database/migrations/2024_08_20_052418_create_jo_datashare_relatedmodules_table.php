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
        Schema::create('jo_datashare_relatedmodules', function (Blueprint $table) {
            $table->id('datashare_relatedmodule_id');
            $table->unsignedBigInteger('tabid')->nullable();
            $table->unsignedBigInteger('relatedto_tabid')->nullable();
            $table->foreign('tabid')->references('tabid')->on('jo_tabs')->onDelete('cascade');
            $table->foreign('relatedto_tabid')->references('tabid')->on('jo_tabs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_datashare_relatedmodules');
    }
};
