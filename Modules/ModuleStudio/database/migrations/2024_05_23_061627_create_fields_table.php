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
        Schema::create('fields', function (Blueprint $table) {
            $table->increments('fieldid'); // Primary key with auto_increment
            $table->integer('tabid')->unsigned(); // Foreign key reference
            $table->string('columnname', 30);
            $table->string('tablename', 100)->nullable();
            $table->integer('generatedtype')->default(0);
            $table->string('uitype', 30);
            $table->string('fieldname', 50);
            $table->string('fieldlabel', 50);
            $table->tinyInteger('readonly'); // Use tinyInteger for 1-bit storage
            $table->integer('presence')->default(1);
            $table->text('defaultvalue')->nullable();
            $table->integer('maximumlength')->nullable();
            $table->integer('sequence')->nullable();
            $table->integer('block')->unsigned()->nullable(); // Foreign key reference
            $table->integer('displaytype')->nullable();
            $table->string('typeofdata', 100)->nullable();
            $table->integer('quickcreate')->default(1);
            $table->integer('quickcreatesequence')->nullable();
            $table->string('info_type', 20)->nullable();
            $table->integer('masseditable')->default(1);
            $table->text('helpinfo')->nullable();
            $table->integer('summaryfield')->default(0);
            $table->tinyInteger('headerfield')->default(0); // Use tinyInteger for 1-bit storage
            $table->timestamps(); // Adds created_at and updated_at columns

            // Define indexes
            $table->index('tabid');
            $table->index('fieldname');
            $table->index('block');
            $table->index('displaytype');

            // Define foreign key constraints
            $table->foreign('tabid')
                  ->references('tabid')
                  ->on('tabs')
                  ->onDelete('cascade');

            $table->foreign('block')
                  ->references('blockid')
                  ->on('blocks')
                  ->onDelete('set null'); // or 'cascade' if you want to delete fields when a block is deleted

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
