<?php

// database/migrations/2024_xx_xx_create_actions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionsTable extends Migration
{
    public function up()
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            //$table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade'); // Foreign key referencing workflows
            $table->string('action_name'); // Name of the action
            $table->string('type'); // Type of action (e.g., send_email, send_sms)
            $table->json('action_data'); // JSON field for action-specific data
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('actions');
    }
}
