<?php

namespace Modules\Customers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('customers::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $customers = Customers::findOrFail($id);
            return response()->json($customers);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Invoice not found'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Server Error'], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('customers::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $customer = Customers::findOrFail($id);
            $customer->delete();
            return response()->json(['message' => 'Customer deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Customer not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete customer: ' . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred while processing your request. Please try again later.'], 500);
        }
    }

    }

