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
    /**
     * Display a listing of the product types.
     *
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Store a newly created product type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
              'name' => 'required|string|in:Inventory,Non Inventory',
              'quantity_in_stock' => 'required_if:name,Inventory|integer|min:0|default:0',
            ]);

            $productType = ProductTypes::create($request->all());
            return response()->json($productType, 201);
        } catch (Exception $e) {
            Log::error('Failed to create product type: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create product type'], 500);
        }
    }

    /**
     * Display the specified product type.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Update the specified product type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Remove the specified product type from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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
}
