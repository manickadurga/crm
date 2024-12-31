<?php

namespace Database\Seeders;

use App\Models\TriggerFilter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TriggerFilterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filters = [
            ['trigger_id' => 1, 'filter_name' => 'Contact Status', 'filter_value' => 'New'],
            ['trigger_id' => 1, 'filter_name' => 'Contact Type', 'filter_value' => 'Lead'],
            // Add more filters as needed
        ];

        foreach ($filters as $filter) {
            TriggerFilter::create($filter);
        }
    }
}
