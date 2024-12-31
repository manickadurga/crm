<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Action;

class ActionTriggerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Load data from the JSON file
        $json = File::get(database_path('data/actions.json'));
        $actions = json_decode($json, true);
        foreach ($actions as $action) {
            Action::updateOrCreate(
                ['action_name' => $action['action_name']], 
                [ 
                    'type' => $action['type'], 
                    'action_data' => $action['action_data'],
                ]
            );
        }
       
    }
}
