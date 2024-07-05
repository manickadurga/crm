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
        Schema::create('jo_organizations', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->binary('image')->nullable(); // Binary data for image storage
            $table->string('organization_name'); // Required organization name
            $table->string('currency')->nullable(); // Nullable currency field
            $table->string('official_name')->nullable(); // Nullable official name field
            $table->string('tax_id')->nullable(); // Nullable tax ID field
            $table->json('tags')->nullable(); // Nullable JSON field for tags
            $table->json('location')->nullable(); // Nullable JSON field for location
            $table->string('employee_bonus_type')->nullable(); // Nullable employee bonus type
            $table->string('choose_time_zone')->nullable(); // Nullable timezone selection
            $table->string('start_week_on')->nullable(); // Nullable start week selection
            $table->string('default_date_type')->nullable(); // Nullable default date type
            $table->string('regions')->nullable(); // Nullable regions selection
            $table->string('select_number_format')->nullable(); // Nullable number format selection
            $table->string('date_format')->nullable(); // Nullable date format selection
            $table->string('fiscal_year_start_date')->nullable(); // Nullable fiscal year start date
            $table->string('fiscal_year_end_date')->nullable(); // Nullable fiscal year end date
            $table->string('enable_disable_invites')->nullable(); // Nullable invite status
            $table->string('invite_expiry_period')->nullable(); // Nullable invite expiry period
            $table->string('primary_email')->nullable(); // Nullable primary email address
            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jo_organizations');
    }
};
