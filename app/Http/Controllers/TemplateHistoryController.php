<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TemplateHistory;
use Illuminate\Support\Facades\Log;

class TemplateHistoryController extends Controller
{
    public function storeTemplateHistory($templateTitle)
    {
        // Store the template history
        TemplateHistory::create([
            'template_name' => $templateTitle
        ]);
    }

    /**
     * Fetch all template histories with pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Set default items per page to 10, can be adjusted via request
            $perPage = $request->input('per_page', 10); 

            // Retrieve paginated template history records
            $templateHistories = TemplateHistory::select('template_name', 'created_at')
                                                ->paginate($perPage);

            // Return JSON response with paginated template histories and pagination information
            return response()->json([
                'status' => 200,
                'template_histories' => $templateHistories->items(), // Retrieve items from the paginator
                'pagination' => [
                    'total' => $templateHistories->total(),
                    'title' => 'Template Histories',
                    'per_page' => $templateHistories->perPage(),
                    'current_page' => $templateHistories->currentPage(),
                    'last_page' => $templateHistories->lastPage(),
                    'from' => $templateHistories->firstItem(),
                    'to' => $templateHistories->lastItem(),
                ],
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Failed to retrieve template histories: ' . $e->getMessage());
            // Return a generic server error response
            return response()->json(['message' => 'Server Error'], 500);
        }
    }
}
