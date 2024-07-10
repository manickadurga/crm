<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InviteLeads;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Mail\InviteLeadsMail;
use Illuminate\Support\Facades\Mail;



class InviteleadsController extends Controller
{

    public function store(Request $request){
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'contactname'=>'required|string',
                'primary_phone'=>'required|string',
                'email'=>'required|string|email',// Ensure email format is validated
                'org_id'=>'nullable|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            
            // Create estimate
            $inviteLead = InviteLeads::create($request->all());

            // Send invite email
            Mail::to($request->email)->send(new InviteLeadsMail($inviteLead));
            


            return response()->json(['message' => 'InviteLeads created and email sent successfully'], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create invitelead: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create invitelead: ' . $e->getMessage()], 500);
        }
    }
public function index()
{
    try {
        $inviteLead = InviteLeads::all();
        return response()->json($inviteLead);
    } catch (\Exception $e) {
        Log::error('Failed to retrieve leads: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to retrieve leads'], 500);
    }
}
}

