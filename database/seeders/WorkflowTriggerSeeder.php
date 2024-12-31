<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trigger;

class WorkflowTriggerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear the table before seeding to avoid duplicates
        Trigger::truncate();

        $triggers = [
            ['trigger_name' => 'contact_created', 'trigger_fields' => null],
            ['trigger_name' => 'contact_updated', 'trigger_fields' => [['field' => 'payment_method', 'value' => 'upi']]],
            ['trigger_name' => 'invoice_due_date', 'trigger_fields' => null],
            ['trigger_name' => 'task_due_date', 'trigger_fields' => [['field' => 'select_pipeline', 'value' => '285']]],
            ['trigger_name' => 'opportunity_stage_updated', 'trigger_fields' => null],
            ['trigger_name' => 'opportunity_status_updated', 'trigger_fields' => null],
            ['trigger_name' => 'opportunity_created', 'trigger_fields' => [['field' => 'payment_method', 'value' => 'upi']]],
            ['trigger_name' => 'invoice_status_changed', 'trigger_fields' => null],
            ['trigger_name' => 'estimate_created', 'trigger_fields' => null],
            ['trigger_name' => 'task_created', 'trigger_fields' => null],
            ['trigger_name' => 'contact_tag_updated', 'trigger_fields' => [['field' => 'select_pipeline', 'value' => '285']]],
            ['trigger_name' => 'task_completed', 'trigger_fields' => null],
            ['trigger_name' => 'payment_received', 'trigger_fields' => [['field' => 'payment_method', 'value' => 'upi']]],
            ['trigger_name' => 'document_created', 'trigger_fields' => null],
        ];

        foreach ($triggers as $trigger) {
            Trigger::updateOrCreate(
                ['trigger_name' => $trigger['trigger_name']],
                ['filters' => $trigger['trigger_fields'] ? $trigger['trigger_fields'] : null]
            );
        }
    }
}

