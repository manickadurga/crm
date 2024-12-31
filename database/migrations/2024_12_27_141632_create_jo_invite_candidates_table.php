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
        Schema::create('jo_invite_candidates', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->date('date')->nullable();
            $table->unsignedBigInteger('departments')->nullable(true);
            $table->foreign('departments')->references('id')->on('jo_departments')->onDelete('set null');
            $table->string('invitation_expiration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_invite_candidates');
    }
};
