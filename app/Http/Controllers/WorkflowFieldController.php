<?php
namespace App\Http\Controllers;

use App\Models\WorkflowAction;
use App\Models\WorkflowTrigger;
use Illuminate\Http\Request;
use App\Models\Workflow; // Assuming you have a Workflow model

class WorkflowFieldController extends Controller
{
    public function getWorkflowData()
    {
      // Fetch all workflows to select from
      $workflows = Workflow::all();

      // Fetch all actions related to workflows
      $actions = WorkflowAction::all(); // or filter actions based on specific workflows if needed

      // Fetch all existing triggers
      $triggers = WorkflowTrigger::with('workflow')->get(); // Fetch triggers with workflow relationship

      // Define dynamic filter fields
      $filterFields = [
          [
              'field' => 'status',
              'label' => 'Status',
              'type' => 'select',
              'options' => [
                  ['value' => 'active', 'label' => 'Active'],
                  ['value' => 'inactive', 'label' => 'Inactive'],
              ],
              'required' => true,
          ],
          [
              'field' => 'tag_added',
              'label' => 'Tag Added',
              'type' => 'text',
              'required' => false,
          ],
          [
              'field' => 'tag_removed',
              'label' => 'Tag Removed',
              'type' => 'text',
              'required' => false,
          ],
          // Add other filter fields as needed
      ];

      // Define dynamic action fields
      $actionFields = [
          [
              'field' => 'send_sms',
              'label' => 'Send SMS',
              'type' => 'action',
              'action_data' => [
                  'message' => '',
                  'phone_number' => '',
                  // Add any additional fields for SMS action
              ],
          ],
          [
              'field' => 'send_email',
              'label' => 'Send Email',
              'type' => 'action',
              'action_data' => [
                  'subject' => '',
                  'message' => '',
                  // Add any additional fields for email action
              ],
          ],
          // Add other action fields as needed
      ];

      return response()->json([
          'workflows' => $workflows,
          'fields' => [
              'filter_fields' => $filterFields,
              'action_fields' => $actionFields, // Include actions in the response
              'triggers' => $triggers, // Include triggers in the response
          ],
      ]);
  }
}
