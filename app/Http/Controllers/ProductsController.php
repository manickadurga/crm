<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Tags;

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
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|string',
            'name' => 'required|string',
            'code' => 'required|string',
            'product_type' => 'required|string|exists:jo_product_types,name',
            'product_category' => 'required|string|exists:jo_product_categories,name',
            'description' => 'nullable|string',
            'enabled' => 'boolean',
            'options' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*.tags_name' => 'exists:jo_tags,tags_name',
            'tags.*.tag_color' => 'exists:jo_tags,tag_color',
            'add_variants' => 'nullable|array',
            'list_price' => 'nullable|numeric',
            'quantity_in_stock' => 'nullable|integer|exists:jo_product_types,quantity_in_stock',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $validator->validated();
        try {
            $product = Product::create($data);
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
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|string',
            'name' => 'required|string',
            'code' => 'required|string',
            'product_type' => 'required|string|exists:jo_product_types,name',
            'product_category' => 'required|string|exists:jo_product_categories,name',
            'description' => 'nullable|string',
            'enabled' => 'boolean',
            'options' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*.tags_name' => 'exists:jo_tags,tags_name',
            'tags.*.tag_color' => 'exists:jo_tags,tag_color',
            'add_variants' => 'nullable|array',
            'list_price' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        if (isset($data['tags'])) {
            $tags = [];
            foreach ($data['tags'] as $tagName) {
                $tag = Tags::where('tags_name', $tagName)->first();
                if ($tag) {
                    $tags[] = $tag->tags_name;
                } else {
                    Log::warning("Tag '$tagName' does not exist in the 'jo_tags' table");
                    return response()->json(['errors' => ["Tag '$tagName' does not exist"]], 400);
                }
            }
            $data['tags'] = json_encode($tags);
        }

        try {
            $product = Product::findOrFail($id);
            $product->update($data);
            return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to update product: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update product: ' . $e->getMessage()], 500);
        }
    }
    public function showByType($typeName)
    {
        try {
            $productType = ProductTypes::where('name', $typeName)->firstOrFail();
            $products = $productType->products;

            if ($products->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No products found for this type',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'products' => $products,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product type not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to retrieve products by type: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve products by type: ' . $e->getMessage()], 500);
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
