<?php 

namespace App\Http\Controllers;

use App\Models\Trigger;
use Illuminate\Http\Request;

class TriggerController extends Controller
{
    /**
     * Display a listing of the triggers.
     */
    public function index()
    {
        $triggers = Trigger::all(); // Fetch all triggers
        return response()->json($triggers);
    }

    /**
     * Store a newly created trigger.
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'trigger_name' => 'required|string|max:255',
            'filters' => 'nullable|array', // Ensure filters are an array
        ]);

        // Create the trigger
        $trigger = Trigger::create([
            'trigger_name' => $request->trigger_name,
            'filters' => $request->filters, // Save filters as JSON
        ]);

        return response()->json($trigger, 201); // Return the created trigger
    }

    /**
     * Display the specified trigger.
     */
    public function show($id)
    {
        $trigger = Trigger::find($id);

        if (!$trigger) {
            return response()->json(['message' => 'Trigger not found'], 404);
        }

        return response()->json($trigger);
    }

    /**
     * Update the specified trigger in storage.
     */
    public function update(Request $request, $id)
    {
        $trigger = Trigger::find($id);

        if (!$trigger) {
            return response()->json(['message' => 'Trigger not found'], 404);
        }

        // Validate the incoming request data
        $request->validate([
            'trigger_name' => 'sometimes|required|string|max:255',
            'filters' => 'nullable|array', // Ensure filters are an array
        ]);

        // Update the trigger
        $trigger->update([
            'trigger_name' => $request->trigger_name ?? $trigger->trigger_name,
            'filters' => $request->filters ?? $trigger->filters, // Update filters if provided
        ]);

        return response()->json($trigger);
    }

    /**
     * Remove the specified trigger from storage.
     */
    public function destroy($id)
    {
        $trigger = Trigger::find($id);

        if (!$trigger) {
            return response()->json(['message' => 'Trigger not found'], 404);
        }

        $trigger->delete(); // Delete the trigger
        return response()->json(['message' => 'Trigger deleted successfully'], 200);
    }
}
