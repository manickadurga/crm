<?php
namespace App\Services;

use App\Models\DripAction;
use App\Models\Action;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendEmailJob;

class DripService
{
    public function processDripWorkflow($workflowId)
    {
        // Retrieve workflow
        $workflow = DB::table('workflows')->find($workflowId);

        // Decode actions IDs
        $actionIds = json_decode($workflow->actions_id, true);

        // Process drip action
        foreach ($actionIds as $actionId) {
            $action = Action::find($actionId);

            if ($action->type === 'drip') {
                $this->processDripAction($action, $actionIds);
            }
        }
    }

    private function processDripAction($dripAction, $actionIds)
    {
        $actionData = $dripAction->action_data;

        $batchSize = $actionData['batch_size'];
        $dripInterval = $actionData['drip_interval'];

        // Retrieve customers for drip processing
        $pendingDripCustomers = DripAction::where('action_id', $dripAction->id)
            ->limit($batchSize)
            ->get();

        if ($pendingDripCustomers->count() === $batchSize) {
            $this->executeDripBatch($pendingDripCustomers, $actionIds, $dripInterval);
            // Remove processed records
            DripAction::whereIn('id', $pendingDripCustomers->pluck('id'))->delete();
        }
    }

    private function executeDripBatch($customers, $actionIds, $interval)
    {
        $emailAction = $this->getEmailAction($actionIds);

        foreach ($customers as $customer) {
            $customerDetails = DB::table('jo_customers')->find($customer->customer_id);

            if ($customerDetails && $emailAction) {
                SendEmailJob::dispatch($customerDetails->primary_email, $emailAction->action_data)
                    ->delay(now()->addMinutes($interval));
            }
        }
    }

    private function getEmailAction($actionIds)
    {
        foreach ($actionIds as $actionId) {
            $action = Action::find($actionId);

            if ($action->type === 'send_email') {
                return $action;
            }
        }

        return null;
    }
}
