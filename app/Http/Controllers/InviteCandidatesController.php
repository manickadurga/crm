<?php

namespace App\Http\Controllers;

use App\Models\InviteCandidates;
use App\Mail\CandidatesInvite;
//use App\Mail\InviteMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InviteCandidatesController extends Controller
{
    public function index()
    {
        try {
            $candidates = InviteCandidates::all();
            if ($candidates->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }
    
            return response()->json([
                'status' => 200,
                'customers' => $candidates,
            ], 200);
        } catch (Exception $e) {
            
            // Log the error
            Log::error('Failed to retrieve candidates: ' . $e->getMessage());
    
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve candidates',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'date' => 'nullable|date',
                'departments' => 'nullable|exists:jo_departments,id',
                'invitation_expiration' => 'nullable|string'
            ]);

            // Create a new client record
            $candidate = InviteCandidates::create($validated);

            // Send email to the provided email address
            Mail::to($validated['email'])->send(new CandidatesInvite($candidate));

            return response()->json($candidate, 201);
        } catch (\Exception $e) {
            Log::error('Failed to store client invite: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to store candidate invite', 'details' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'date' => 'nullable|date',
                'departments' => 'nullable|exists:jo_departments,id',
                'invitation_expiration' => 'nullable|string'
            ]);

            // Find the customer invite by ID
            $candidate = InviteCandidates::findOrFail($id);

            // Update the customer invite with new data
            $candidate->update($validated);

            return response()->json(['message' => 'Candidate invite updated successfully'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Candidate invite not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to update candidate invite: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update candidate invite'], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $candidate = InviteCandidates::findOrFail($id);
            $candidate->delete();
            return response()->json(['message' => 'Candidate deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Candidate not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete candidate: ' . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred while processing your request. Please try again later.'], 500);
        }
    }
}
