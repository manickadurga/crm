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
        Schema::create('formfields', function (Blueprint $table) {
            $table->id('fieldid'); // Primary key
            $table->unsignedInteger('tabid');
            $table->unsignedInteger('block');
            $table->string('trigger_or_action'); // To specify if it's a trigger or action
            $table->string('columnname');
            $table->string('tablename');
            $table->tinyInteger('generatedtype')->default(1); // 1 = System, 0 = Manual
            $table->tinyInteger('uitype'); // UI type (e.g., text, dropdown)
            $table->string('fieldname');
            $table->string('fieldlabel');
            $table->tinyInteger('readonly')->default(0); // 1 = Yes, 0 = No
            $table->tinyInteger('presence')->default(1); // 1 = Visible, 0 = Hidden
            $table->string('defaultvalue')->nullable();
            $table->integer('maximumlength')->nullable();
            $table->integer('sequence');
            $table->tinyInteger('displaytype')->default(1); // 1 = Visible
            $table->string('typeofdata');
            $table->tinyInteger('quickcreate')->default(0); // 1 = Enabled, 0 = Disabled
            $table->integer('quickcreatesequence')->default(0);
            $table->string('info_type')->nullable();
            $table->tinyInteger('masseditable')->default(0); // 1 = Yes, 0 = No
            $table->string('helpinfo')->nullable();
            $table->tinyInteger('summaryfield')->default(0); // 1 = Yes, 0 = No
            $table->tinyInteger('headerfield')->default(0); // 1 = Yes, 0 = No
            $table->unsignedInteger('orgid')->nullable(); // Organization ID
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formfields');
    }
};
