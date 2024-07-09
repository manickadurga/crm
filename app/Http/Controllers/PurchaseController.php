<?php
namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    public function index()
    {
        $Purchases = Purchase::all();
        return response()->json(['status' => 200, 'data' => $Purchases], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 422, 'errors' => $validator->messages()], 422);
        }

        $data = $request->all();
        $Purchase = Purchase::create($data);

        return response()->json(['status' => 200, 'message' => 'Purchase created successfully', 'data' => $Purchase], 200);
    }

    public function show($id)
    {
        $Purchase = Purchase::find($id);
        if ($Purchase) {
            return response()->json(['status' => 200, 'data' => $Purchase], 200);
        } else {
            return response()->json(['status' => 404, 'message' => 'Purchase not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 422, 'errors' => $validator->messages()], 422);
        }

        $Purchase = Purchase::find($id);
        if ($Purchase) {
            $Purchase->update($request->all());
            return response()->json(['status' => 200, 'message' => 'Purchase updated successfully', 'data' => $Purchase], 200);
        } else {
            return response()->json(['status' => 404, 'message' => 'Purchase not found'], 404);
        }
    }

    public function destroy($id)
    {
        $Purchase = Purchase::find($id);
        if ($Purchase) {
            $Purchase->delete();
            return response()->json(['status' => 200, 'message' => 'Purchase deleted successfully'], 200);
        } else {
            return response()->json(['status' => 404, 'message' => 'Purchase not found'], 404);
        }
    }
}