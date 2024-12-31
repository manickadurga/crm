<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trigger>
 */
class TriggerFactory extends Factory
{
    protected $model = \App\Models\Trigger::class;
    public function definition(): array
    {
        $triggerNames = [ 
            'contact_created', 
            'contact_updated', 
            'invoice_due_date', 
            'task_due_date', 
            'opportunity_stage_updated', 
            'opportunity_status_updated', 
            'opportunity_created', 
            'invoice_status_changed', 
            'estimate_created', 
            'task_created', 
            'contact_tag_updated', 
            'task_completed', 
            'payment_received', 
            'document_created' ]; 
            // Define a set of possible fields 
            $possibleFields = [ 
                ['field' => 'payment_method', 'value' => 'upi'], 
                ['field' => 'select_pipeline', 'value' => '285'], 
                ['field' => 'priority', 'value' => 'high'], 
                ['field' => 'status', 'value' => 'completed'], 
                ["field" => "tag_added","value" => ["347"]],
            ];
            $selectedFields = $this->faker->optional()->randomElements($possibleFields, rand(1, 3));
            return [ 
                'trigger_name' => $this->faker->unique()->randomElement($triggerNames), 
                'trigger_fields' => $selectedFields ? $selectedFields : null,
                ];
    }
}
