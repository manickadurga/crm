<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

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
        try {
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
                'list_price'=>'nullable|numeric',
                'orgid'=>'nullable|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $product = Product::create($request->all());

            return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
        } catch (Exception $e) {
            Log::error('Failed to create product: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create product: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $product = Product::findOrFail($id);
            return response()->json(['product' => $product], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to retrieve product: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve product: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'nullable|string',
                'name' => 'string|nullable',
                'code' => 'string|nullable',
                'product_type' => 'string|nullable',
                'product_category' => 'string|nullable',
                'description' => 'string|nullable',
                'enabled' => 'boolean|nullable',
                'options' => 'array|nullable',
                'tags' => 'array|nullable',
                'add_variants' => 'array|nullable',
                'list_price'=>'nullable|numeric',
                'orgid'=>'nullable|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $product = Product::findOrFail($id);
            $product->update($request->all());

            return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to update product: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update product: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return response()->json(['message' => 'Product deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete product: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete product: ' . $e->getMessage()], 500);
        }
    }
}
