<?php

namespace App\Http\Controllers;

use App\Events\OpportunityCreated;
use App\Events\OpportunityStageUpdated;
use App\Models\Opportunity;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Events\OpportunityStatusUpdated;
use App\Models\Pipelines;
use Illuminate\Support\Facades\Log;

class OpportunityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $opportunities=Opportunity::all();
            return response()->json([
                'message' => 'Opportunities Retrieved Successfully',
                'data' => $opportunities
            ], 200);
        }
        catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to Retrieve Opportunities',
                'message' => $e->getMessage()
            ], 500);
        }
    
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    try {
        $validatedData = $request->validate([
            'contact_id' => 'required|integer',
            'select_pipeline' => 'required|integer|exists:jo_pipelines,id',
            'select_stage' => 'nullable|string',
            'select_status' => 'nullable|string',
            'opportunity_name' => 'nullable|string',
            'opportunity_source' => 'nullable|string',
            'lead_value'=>'nullable|integer|exists:jo_leads,id',
            'opportunity_status'=>'nullable|string',
            'action'=>'nullable|string',
        ]);

        $isValidContact = DB::table('jo_customers')->where('id', $validatedData['contact_id'])->exists() ||
                          DB::table('jo_clients')->where('id', $validatedData['contact_id'])->exists() ||
                          DB::table('jo_leads')->where('id', $validatedData['contact_id'])->exists();

        if (!$isValidContact) {
            return back()->withErrors(['contact_id' => 'Invalid contact ID.']);
        }

        // Step 3: Retrieve pipeline and validate the select_stage against its stages array
        $pipeline = Pipelines::find($validatedData['select_pipeline']);
        if ($pipeline && $validatedData['select_stage'] && !in_array($validatedData['select_stage'], $pipeline->stages)) {
            return response()->json([
                'error' => 'Invalid stage. The selected stage does not exist in the specified pipeline.'
            ], 400);
        }

        $opportunity = new Opportunity();
        $opportunity->contact_id = $validatedData['contact_id'];
        $opportunity->select_pipeline = $validatedData['select_pipeline'];
        $opportunity->select_stage = $validatedData['select_stage'] ?? null;
        $opportunity->opportunity_name = $validatedData['opportunity_name'] ?? null;
        $opportunity->opportunity_source = $validatedData['opportunity_source'] ?? null;
        $opportunity->lead_value=$validatedData['lead_value'] ?? null;
        $opportunity->opportunity_status = $validatedData['opportunity_status'] ?? null;
        $opportunity->action=$validatedData['action'] ?? null;
        $opportunity->save();

        // Dispatch the event after saving the opportunity
        event(new OpportunityCreated($opportunity));

        return response()->json([
            'message' => 'Opportunity Created Successfully',
            'data' => $opportunity
        ], 201);
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Failed to Create Opportunity',
            'message' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try{
            $opportunity=Opportunity::findorFail($id);
            return response()->json([
                "message"=>"Opportunity Retrieved Successfully",
                "data"=>$opportunity
            ]);
        }
        catch(Exception $e){
            return response()->json([
                'error' => 'Failed to Retrieve Opportunity',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        try{
            $opportunity=Opportunity::findorFail($id);
            $validatedData = $request->validate([
                'contact_id' => 'required|integer',
                'select_pipeline' => 'required|integer|exists:jo_pipelines,id',
                'select_stage' => 'nullable|string',
                'select_status' => 'nullable|string',
                'opportunity_name' => 'nullable|string',
                'opportunity_source' => 'nullable|string',
                'lead_value'=>'nullable|integer|exists:jo_leads,id',
                'opportunity_status'=>'nullable|string',
                'action'=>'nullable|string',
            ]);
    
            $isValidContact = DB::table('jo_customers')->where('id', $validatedData['contact_id'])->exists() ||
                              DB::table('jo_clients')->where('id', $validatedData['contact_id'])->exists() ||
                              DB::table('jo_leads')->where('id', $validatedData['contact_id'])->exists();
    
            if (!$isValidContact) {
                return back()->withErrors(['contact_id' => 'Invalid contact ID.']);
            }

            // Step 3: Retrieve pipeline and validate the select_stage against its stages array
            $pipeline = Pipelines::find($validatedData['select_pipeline']);
            if ($pipeline && $validatedData['select_stage'] && !in_array($validatedData['select_stage'], $pipeline->stages)) {
            return response()->json([
                'error' => 'Invalid stage. The selected stage does not exist in the specified pipeline.'
            ], 400);
            }

            // Check if the select_stage has changed
            $stageChanged = isset($validatedData['select_stage']) && 
                            $validatedData['select_stage'] !== $opportunity->select_stage;
            
            // Check if the opportunity status has changed
            $statusChanged = isset($validatedData['opportunity_status']) &&
                             $validatedData['opportunity_status'] !== $opportunity->opportunity_status;
                        
            
            $opportunity->contact_id = $validatedData['contact_id'];
            $opportunity->select_pipeline = $validatedData['select_pipeline'];
            $opportunity->select_stage = $validatedData['select_stage'] ?? null;
            $opportunity->opportunity_name = $validatedData['opportunity_name'] ?? null;
            $opportunity->opportunity_source = $validatedData['opportunity_source'] ?? null;
            $opportunity->lead_value=$validatedData['lead_value'] ?? null;
            $opportunity->opportunity_status = $validatedData['opportunity_status'] ?? null;
            $opportunity->action=$validatedData['action'] ?? null;
            $opportunity->save();

            // Trigger event if stage has changed
            if ($stageChanged) {
            event(new OpportunityStageUpdated($opportunity));
            }

             // Trigger event if status has changed
             if ($statusChanged) {
                event(new OpportunityStatusUpdated($opportunity));
            }
    
            return response()->json([
                'message' => 'Opportunity Updated Successfully',
                'data' => $opportunity
            ], 201);
        }
        catch (Exception $e) {
            Log::error('Failed to update opportunity: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to Create Opportunity',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $opportunity = Opportunity::findOrFail($id); // Retrieves the record or throws a 404 error
            $opportunity->delete(); // Deletes the record
    
            return response()->json([
                'message' => 'Opportunity Deleted Successfully'
            ], 200);
        } 
        catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to Delete Opportunity',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
