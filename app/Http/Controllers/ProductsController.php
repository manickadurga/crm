<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Crmentity;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $products = Product::all();
            if ($products->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'products' => $products,
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to retrieve products: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    DB::beginTransaction();

    try {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|string',
            'name' => 'required|string',
            'code' => 'required|string',
            'product_type' => 'required|string',
            'product_category' => 'required|string',
            'description' => 'nullable|string',
            'enabled' => 'boolean',
            'options' => 'nullable|array',
            'tags' => 'nullable|array',
            'add_variants' => 'nullable|array',
            'orgid' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validated = $validator->validated();

        // Retrieve or create a new Crmentity record
        $defaultCrmentity = Crmentity::where('setype', 'Invoices')->first();

        if (!$defaultCrmentity) {
            // Log an error if default Crmentity not found
            Log::error('Default Crmentity for Products not found');
            throw new \Exception('Default Crmentity not found');
        }

        // Create a new Crmentity record with a new crmid
        $newCrmentity = new Crmentity();
        $newCrmentity->crmid = Crmentity::max('crmid') + 1;
        $newCrmentity->smcreatorid = $defaultCrmentity->smcreatorid ?? 0; // Replace with appropriate default
        $newCrmentity->smownerid = $defaultCrmentity->smownerid ?? 0; // Replace with appropriate default
        $newCrmentity->setype = 'Products';
        $newCrmentity->description = $defaultCrmentity->description ?? '';
        $newCrmentity->createdtime = now();
        $newCrmentity->modifiedtime = now();
        $newCrmentity->viewedtime = now();
        $newCrmentity->status = $defaultCrmentity->status ?? '';
        $newCrmentity->version = $defaultCrmentity->version ?? 0;
        $newCrmentity->presence = $defaultCrmentity->presence ?? 0;
        $newCrmentity->deleted = $defaultCrmentity->deleted ?? 0;
        $newCrmentity->smgroupid = $defaultCrmentity->smgroupid ?? 0;
        $newCrmentity->source = $defaultCrmentity->source ?? '';
        $newCrmentity->label = $validated['name'];
        $newCrmentity->save();

        // Set the new crmid as the product ID
        $validated['id'] = $newCrmentity->crmid;

        // Create the product entry
        $product = Product::create($validated);

        DB::commit();

        return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
    } catch (ValidationException $e) {
        DB::rollBack();
        Log::error('Validation failed while creating product: ' . $e->getMessage());
        return response()->json(['errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to create product: ' . $e->getMessage());
        Log::error($e->getTraceAsString()); // Log the stack trace for detailed debugging
        return response()->json(['error' => 'Failed to create product: ' . $e->getMessage()], 500);
    }
}

    
}