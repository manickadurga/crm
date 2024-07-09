<?php

namespace Modules\Customers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Modules\Customers\Models\Customer;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       
        $customers = Customer::all();

        if($customers->count() > 0){

            return response()->json([

                'status' => 200,
                'teamtasks' => $customers 
            ], 200);
        }else{
            return response()->json([

                'status' => 404,
                'message' => 'No Data Found'
            ], 404);

        }
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
    public function store(Request $request)
    {
        
        $validator = Validator::make($request->all(),[
            'contactowner' => 'required|string',
            'leadsource' => 'required|string',
            'first_name_prefix' => 'nullable|string',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'accountname' => 'required|string',
            'vendorname' => 'required|string',
            'dob' => 'required|date',
            'emailoptout' => 'required|boolean',
            'mailingstreet' => 'required|string',
            'otherstreet' => 'required|string',
            'mailingcity' => 'required|string',
            'othercity' => 'required|string',
            'mailingstate' => 'required|string',
            'otherstate' => 'required|string',
            'mailingcountry' => 'required|string',
            'othercountry' => 'required|string',
            'description' => 'required|string'
            
            
        ]);
         if($validator->fails()){
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
                ], 422);
        }
        else{
            $customer = Customer::create($request->all());

            if($customer){

                return response()->json([
                    'status' => 200,
                    'message' => 'Customer Added Successfully'
                ],200);
            }else{

                return response()->json([
                    'status' => 500,
                    'message' => 'Customer Added Failed'

                ], 500);
            }
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('customers::show');
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
