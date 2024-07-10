<?php

namespace App\Http\Controllers;

use App\Models\inviteClient;
use App\Mail\ClientInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class inviteClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $clients = inviteClient::all();
        return response()->json(['clients' => $clients], 200);
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
                'contact_name' => 'required|string|max:255',
                'primary_phone' => 'required|string',
                'email' => 'required|email',
                
            ]);

            // Create a new client record
            $client = inviteClient::create($validated);

            // Send email to the provided email address
            Mail::to($validated['email'])->send(new ClientInvite($client));

            return response()->json($client, 201);
        } catch (\Exception $e) {
            Log::error('Failed to store client invite: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to store client invite', 'details' => $e->getMessage()], 500);
        }
    }
}
