<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowTriggersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $triggers = [
            [
                'values' => json_encode([
                    [
                        'columnname' => 'trigger_name',
                        'fieldname' => 'trigger_name',
                        'fieldlabel' => 'Workflow Trigger Name',
                        'typeofdata' => 'V~M',
                        'uitype' => 2,
                        'options' => null,
                        'info_type' => 'BAS',
                    ],
                    [
                        'columnname' => 'filters',
                        'fieldname' => 'filters',
                        'fieldlabel' => 'Filters',
                        'typeofdata' => 'V~O',
                        'uitype' => 16,
                        'options' => null,
                        'info_type' => 'BAS',
                    ],
                ]),
            ],
        ];

        foreach ($triggers as $trigger) {
            // Check if a record with matching `values` exists
            $exists = DB::table('workflowtriggers')
                ->whereRaw("values::text = ?", [json_encode($trigger['values'])])
                ->exists();

            if ($exists) {
                // Update the existing record if necessary
                DB::table('workflowtriggers')
                    ->whereRaw("values::text = ?", [json_encode($trigger['values'])])
                    ->update(['updated_at' => now()]);
            } else {
                // Insert a new record
                DB::table('workflowtriggers')->insert([
                    'values' => $trigger['values'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

