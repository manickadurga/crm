<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkflowTriggersController extends Controller
{
    public function show($id)
{
    $trigger = DB::table('workflowtriggers')->where('id', $id)->first();

    if (!$trigger) {
        return response()->json([
            'error' => 'Workflow Trigger not found',
        ], 404);
    }

    // Decode 'values' JSON if it exists
    $trigger->values = $trigger->values ? json_decode($trigger->values) : null;

    return response()->json($trigger, 200);
}
public function index()
    {
        try {
            // Fetch all workflow triggers from the database
            $triggers = DB::table('workflowtriggers')->get();

            // Decode 'values' JSON for each trigger
            $triggers->transform(function ($trigger) {
                $trigger->values = $trigger->values ? json_decode($trigger->values) : null;
                return $trigger;
            });

            return response()->json($triggers, 200);
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }
}
