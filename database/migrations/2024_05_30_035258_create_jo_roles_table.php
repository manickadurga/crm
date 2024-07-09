<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::create('jo_roles', function (Blueprint $table) {
            $table->id();
            $table->string('roleid')->unique();
            $table->string('rolename');
            $table->string('parentrole'); // This will store the hierarchical path as a string
            $table->integer('depth')->default(0); // Default depth is 0
            $table->integer('allowassignedrecordsto')->default(1);
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Error creating jo_roles table: ' . $e->getMessage());
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('jo_roles');
        } catch (\Exception $e) {
            Log::error('Error dropping jo_roles table: ' . $e->getMessage());
            throw $e;
        }
    }
};
