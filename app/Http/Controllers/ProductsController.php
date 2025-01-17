<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Crmentity;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    public function index(Request $request)
{
    try {
        $perPage = $request->input('per_page', 10); 
        $products = Product::paginate($perPage, ['image', 'name', 'code', 'product_type', 'product_category', 'tags']);
        if ($products->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No records found',
            ], 404);
        }

        // Transform each product to ensure tags are formatted properly
        $formattedProducts = $products->getCollection()->map(function ($product) {
            // Check if tags are already an array (due to Eloquent casting from JSON)
            if (is_array($product->tags)) {
                $product->tags = $product->tags; // Assuming tags are already in the correct format
            } else {
                $product->tags = json_decode($product->tags, true); // Decode tags array from JSON if it's a string
            }
            return $product;
        });

        return response()->json([
            'status' => 200,
            'products' => $formattedProducts,
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
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
public function store(Request $request)
{
    DB::beginTransaction();

    try {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string',
            'code' => 'required|string',
            'product_type' => 'required|exists:jo_product_types,id',
            'product_category' => 'required|exists:jo_product_categories,id',
            'description' => 'nullable|string',
            'enabled' => 'boolean',
            'options' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'add_variants' => 'nullable|array',
            'list_price' => 'nullable|numeric',
            'quantity_in_stock' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images'), $imageName); 
            $data['image'] = 'images/' . $imageName;
        }

        // Create Crmentity record via CrmentityController
        $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('Products', $data['name']);

        // Add crmid to data array
        $data['id'] = $crmid;

        // Create the Product with the crmid
        $product = Product::create($data);

        if (!$product) {
            throw new Exception('Product creation failed');
        }

        DB::commit();

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);

    } catch (Exception $e) {
        DB::rollBack();
        Log::error('Product creation failed: ' . $e->getMessage());
        return response()->json([
            'error' => 'Failed to create product',
            'message' => $e->getMessage()
        ], 500);
    }
}

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
    public function update(Request $request, $id)
    {
        // Validate incoming form data
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048', // Max size in KB
            'name' => 'nullable|string',
            'code' => 'nullable|string',
            'product_type' => 'nullable|exists:jo_product_types,id',
            'product_category' => 'nullable|exists:jo_product_categories,id',
            'description' => 'nullable|string',
            'enabled' => 'boolean',
            'options' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'add_variants' => 'nullable|array',
            'list_price' => 'nullable|numeric',
            'quantity_in_stock' => 'nullable|integer|exists:jo_product_types,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
    
        $data = $request->all();
        try {
            // Find and update the product record
            $product = Product::findOrFail($id);
    
            // Handle image upload if present
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $imageName);
                if ($product->image) {
                    $oldImagePath = public_path($product->image);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $data['image'] = 'images/' . $imageName;
            }
            
            // Update the product
            $product->update($data);
    
            // Update the corresponding Crmentity record
            $crmentity = Crmentity::where('crmid', $id)->first(); // Assuming 'crmid' is the identifier for Crmentity
            if ($crmentity) {
                $crmentity->label = $data['name'] ?? $crmentity->label; // Update Crmentity label with Product name
                //$crmentity->description = $data['description'] ?? $crmentity->description; // Optional: Update description if provided
                $crmentity->save();
            } else {
                // Handle the case where the Crmentity record does not exist (if needed)
                Log::warning("Crmentity record not found for product ID {$id}");
            }
    
            return response()->json([
                'message' => 'Product and Crmentity updated successfully',
                'product' => $product,
            ], 200);
    
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to update product or Crmentity: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update product or Crmentity: ' . $e->getMessage()], 500);
        }
    }
    
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
    public function search(Request $request)
    {
        try {
            // Validate the search input
            $validatedData = $request->validate([
                'name' => 'nullable|string',
                'code' => 'nullable|string',
                'product_type' => 'nullable|string',
                'product_category' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:tags,id', // Validate each tag ID exists in the tags table
                'quantity_in_stock' => 'nullable|integer',
                'per_page' => 'nullable|integer|min:1', // Add validation for per_page
            ]);

            // Initialize the query builder
            $query = Product::query();

            // Apply search filters
            foreach ($validatedData as $key => $value) {
                if ($value !== null && in_array($key, ['name', 'code', 'product_type', 'product_category'])) {
                    $query->where($key, 'like', '%' . $value . '%');
                }

                if ($key === 'tags' && $value !== null) {
                    $query->whereHas('tags', function ($q) use ($value) {
                        $q->whereIn('id', $value);
                    });
                }

                if ($key === 'quantity_in_stock' && $value !== null) {
                    $query->where('quantity_in_stock', $value);
                }
            }

            // Paginate the search results
            $perPage = $validatedData['per_page'] ?? 10; // Default per_page value
            $products = $query->paginate($perPage);

            // Check if any products found
            if ($products->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No matching records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'products' => $products->items(),
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to search products: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search products: ' . $e->getMessage()], 500);
        }
    }
}


