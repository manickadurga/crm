<?php

namespace App\Http\Controllers;

use App\Models\ProductTypes;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
class ProductTypesController extends Controller
{

    public function index()
    {
        try {
            $productTypes = ProductTypes::all();
            return response()->json($productTypes);
        } catch (Exception $e) {
            Log::error('Failed to retrieve product types: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve product types'], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|in:Inventory,Non Inventory',
                'quantity_in_stock' => 'required_if:name,Inventory|integer|min:0',
            ]);
    
            $productType = ProductTypes::create($request->all());
            return response()->json($productType, 201);
        } catch (Exception $e) {
            Log::error('Failed to create product type: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create product type', 'error' => $e->getMessage()], 500); // Include error message for debugging
        }
    }
    public function show($id)
    {
        try {
            $productType = ProductTypes::find($id);

            if ($productType) {
                return response()->json($productType);
            } else {
                return response()->json(['message' => 'Product type not found'], 404);
            }
        } catch (Exception $e) {
            Log::error('Failed to retrieve product type: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve product type'], 500);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $productType = ProductTypes::find($id);

            if ($productType) {
                $productType->update($request->all());
                return response()->json($productType);
            } else {
                return response()->json(['message' => 'Product type not found'], 404);
            }
        } catch (Exception $e) {
            Log::error('Failed to update product type: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update product type'], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $productType = ProductTypes::find($id);

            if ($productType) {
                $productType->delete();
                return response()->json(['message' => 'Product type deleted successfully']);
            } else {
                return response()->json(['message' => 'Product type not found'], 404);
            }
        } catch (Exception $e) {
            Log::error('Failed to delete product type: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete product type'], 500);
        }
    }
    public function showProductsByType($typeName)
    {
        try {
            $productType = ProductTypes::where('name', $typeName)->firstOrFail();
            $products = Product::where('product_type', $typeName)->get(); // Adjust this based on your actual relationship

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
            Log::error('Product type not found: ' . $e->getMessage());
            return response()->json(['message' => 'Product type not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to retrieve products by type: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve products by type'], 500);
        }
    }
    public function search(Request $request)
{
    try {
        // Validate the search input
        $validatedData = $request->validate([
            'name' => 'nullable|string',
            'quantity_in_stock' => 'nullable|integer|min:0',
            // Add more validation rules as needed
        ]);

        // Initialize the query builder
        $query = ProductTypes::query();

        // Apply search filters
        if (isset($validatedData['name'])) {
            $query->where('name', 'like', '%' . $validatedData['name'] . '%');
        }

        if (isset($validatedData['quantity_in_stock'])) {
            $query->where('quantity_in_stock', $validatedData['quantity_in_stock']);
        }
        $productTypes = $query->paginate(10);
        if ($productTypes->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No matching records found',
            ], 404);
        }
        return response()->json([
            'status' => 200,
            'product_types' => $productTypes->items(),
            'pagination' => [
                'total' => $productTypes->total(),
                'per_page' => $productTypes->perPage(),
                'current_page' => $productTypes->currentPage(),
                'last_page' => $productTypes->lastPage(),
                'from' => $productTypes->firstItem(),
                'to' => $productTypes->lastItem(),
            ],
        ], 200);

    } catch (Exception $e) {
        Log::error('Failed to search product types: ' . $e->getMessage());
        return response()->json([
            'status' => 500,
            'message' => 'Failed to search product types',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}