<?php

namespace Database\Seeders;

use App\Models\Trigger;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TriggerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $triggers = [
            ['name' => 'Contact Created', 'description' => 'Triggered when a contact is created.'],
            ['name' => 'Contact Changed', 'description' => 'Triggered when a contact is updated.'],
            ['name' => 'Task Completed', 'description' => 'Triggered when a task is marked as completed.'],
            ['name'=>'Contact Deleted'],
        ];

        foreach ($triggers as $trigger) {
            Trigger::create($trigger);
        }
    }
}
