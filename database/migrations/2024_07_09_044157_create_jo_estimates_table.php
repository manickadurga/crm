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
        Schema::create('jo_estimates', function (Blueprint $table) {
            $table->id();
            $table->integer('estimatenumber');
            $table->unsignedBigInteger('contacts')->nullable(false);
            $table->date('estimatedate');
            $table->date('duedate');
            $table->string('discount')->default('20')->nullable();
            $table->enum('discount_suffix', ['%', 'flat'])->default('%');
            $table->string('currency')->default('none');
            $table->string('terms')->nullable();
            $table->json('tags')->default(json_encode(['important', 'pending']));
            $table->integer('tax1')->default(20)->nullable();
            $table->enum('tax1_suffix', ['%', 'flat'])->default('%');
            $table->integer('tax2')->default(20)->nullable();
            $table->enum('tax2_suffix', ['%', 'flat'])->default('%');
            $table->boolean('applydiscount')->default(true);
            $table->string('taxtype')->nullable();
            $table->decimal('subtotal')->nullable();
            $table->decimal('total')->nullable();
            $table->decimal('tax_percent')->nullable();
            $table->decimal('discount_percent')->nullable();
            $table->decimal('tax_amount')->nullable();
            $table->string('estimate_status')->nullable();
            $table->unsignedBigInteger('organization_name')->nullable();
            $table->foreign('organization_name')->references('id')->on('jo_organizations')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_estimates');
    }
};
