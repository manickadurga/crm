<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductTypes;
use App\Models\ProductCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Tags;
use App\Models\Crmentity;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    try {
        // Get pagination parameter from the request
        $perPage = $request->input('per_page', 10); // Default to 10 items per page if not specified

        // Get paginated results with specific fields
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
                'quantity_in_stock' => 'nullable|integer|exists:jo_product_types,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $data = $validator->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $imageName); // Move the file to public/images directory

                // Save image path in database
                $data['image'] = 'images/' . $imageName; // Store relative path
            }

            // // Retrieve names/values based on IDs
            // $productType = ProductTypes::find($data['product_type']);
            // $productCategory = ProductCategories::find($data['product_category']);

            // if (!$productType || !$productCategory) {
            //     return response()->json(['error' => 'Product type or category not found'], 404);
            // }

            // // Replace IDs with names/values
            // $data['product_type'] = $productType->name;
            // $data['product_category'] = $productCategory->name;

            // // Handle quantity_in_stock
            // if (isset($data['quantity_in_stock'])) {
            //     $quantityInStockProductType = ProductTypes::find($data['quantity_in_stock']);
            //     if ($quantityInStockProductType) {
            //         $data['quantity_in_stock'] = $quantityInStockProductType->quantity_in_stock;
            //     } else {
            //         return response()->json(['errors' => ["Quantity in stock with ID '{$data['quantity_in_stock']}' not found"]], 400);
            //     }
            // }

            // // Handle tags
            // if (isset($data['tags'])) {
            //     $tags = [];
            //     foreach ($data['tags'] as $id) {
            //         $tag = Tags::find($id);
            //         if ($tag) {
            //             $tags[] = [
            //                 'tags_name' => $tag->tags_name,
            //                 'tag_color' => $tag->tag_color,
            //             ];
            //         } else {
            //             return response()->json(['errors' => ["Tag with ID '$id' not found"]], 400);
            //         }
            //     }
            //     $data['tags'] = json_encode($tags);
            // }

            // Create the product
            $product = Product::create($data);

            // Check product creation
            if (!$product) {
                throw new \Exception('Product creation failed');
            }

            // Retrieve or create default Crmentity for 'Products'
            $defaultCrmentity = Crmentity::where('setype', 'Products')->first();

            if (!$defaultCrmentity) {
                // If no default Crmentity exists, create one
                $defaultCrmentity = Crmentity::create([
                    'crmid' => Crmentity::max('crmid') + 1,
                    'smcreatorid' => 0, // Replace with appropriate value
                    'smownerid' => 0, // Replace with appropriate value
                    'setype' => 'Products',
                    'description' => '',
                    'createdtime' => now(),
                    'modifiedtime' => now(),
                    'viewedtime' => now(),
                    'status' => '',
                    'version' => 0,
                    'presence' => 0,
                    'deleted' => 0,
                    'smgroupid' => 0,
                    'source' => '',
                    'label' => '',
                ]);
            }

            // Generate new crmid
            $newCrmid = Crmentity::max('crmid') + 1;

            // Create Crmentity record
            $crmentity = new Crmentity();
            $crmentity->crmid = $newCrmid;
            $crmentity->smcreatorid = $defaultCrmentity->smcreatorid;
            $crmentity->smownerid = $defaultCrmentity->smownerid;
            $crmentity->setype = 'Products';
            $crmentity->description = $defaultCrmentity->description ?? '';
            $crmentity->createdtime = now();
            $crmentity->modifiedtime = now();
            $crmentity->viewedtime = now();
            $crmentity->status = $defaultCrmentity->status ?? '';
            $crmentity->version = $defaultCrmentity->version ?? 0;
            $crmentity->presence = $defaultCrmentity->presence ?? 0;
            $crmentity->deleted = $defaultCrmentity->deleted ?? 0;
            $crmentity->smgroupid = $defaultCrmentity->smgroupid ?? 0;
            $crmentity->source = $defaultCrmentity->source ?? '';
            $crmentity->label = $product->name;

            // Save the Crmentity record
            $crmentity->save();

            // Check Crmentity creation
            if (!$crmentity) {
                throw new \Exception('Crmentity creation failed');
            }

            // Update the product with crmid
            $product->update(['id' => $crmentity->crmid]);

            DB::commit();

            // Return success response
            return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error message
            Log::error('Product creation failed: ' . $e->getMessage());

            // Debugging output
            //dd($e->getMessage());

            // Handle any exceptions or errors
            return response()->json(['error' => 'Failed to create product', 'message' => $e->getMessage()], 500);
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
        // Retrieve the product to update
        $product = Product::findOrFail($id);

        // Handle image update if a new image is provided
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images'), $imageName); // Move the file to public/images directory

            // Delete old image if exists
            if ($product->image) {
                $oldImagePath = public_path($product->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Delete old image file from server
                }
            }

            // Update image path in database
            $data['image'] = 'images/' . $imageName; // Store relative path
        }

        // Update the product with validated data
        $product->update($data);

        return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Product not found'], 404);
    } catch (Exception $e) {
        Log::error('Failed to update product: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to update product: ' . $e->getMessage()], 500);
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


