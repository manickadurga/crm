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
            $table->increments('organizationid');
            $table->string('organization_name');
            $table->string('currency')->nullable();
            $table->string('official_name')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('tags')->nullable();
            $table->string('find_address')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('address')->nullable();
            $table->string('address_2')->nullable();
            $table->enum('employee_bonus_type', ['None', 'Profit Based Bonus', 'Revenue Based Bonus'])->nullable();
            $table->integer('bonus_percentage')->default(0);
            $table->string('timezone')->nullable();
            $table->enum('start_week_on', ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])->nullable();
            $table->enum('default_date_type', ['Today', 'End of Month', 'Start of Month'])->nullable();
            $table->enum('regions', ['English (United States)', 'English (United Kingdom)'])->nullable();
            $table->enum('number_format', ['$12,345.67'])->nullable();
            $table->enum('date_format', ['06/01/2024', 'June 1,2024', 'Saturday,June 1,2024'])->nullable();
            $table->date('fiscal_year_start_date')->nullable();
            $table->date('fiscal_year_end_date')->nullable();
            $table->integer('invite_expiry_period')->default(7);
            $table->boolean('allow_users_to_send_invites')->default(false);
            $table->timestamps();
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
