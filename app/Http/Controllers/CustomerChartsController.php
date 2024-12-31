<?php

namespace App\Http\Controllers;

use App\Models\Customers; // Adjust based on your model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerChartsController extends Controller
{
    public function search(Request $request)
    {
        try {
            // Validate the search input
            $validatedData = $request->validate([
                'requests' => 'required|array',
                'requests.*.field_name' => 'required|string',
                'requests.*.field_value' => 'required|string',
                'requests.*.condition' => 'required|string|in:starts_with,ends_with,equals,not_equals,contains,not_contains',
                'requests.*.chart_type' => 'required|string|in:pie,bar,line',
                'group_by_field' => 'required|string|in:name,primary_email,primary_phone', // Add more fields as needed
            ]);

            $query = Customers::query();

            // Iterate through each condition
            foreach ($validatedData['requests'] as $data) {
                $fieldName = $data['field_name'];
                $fieldValue = $data['field_value'];
                $condition = $data['condition'];

                switch ($condition) {
                    case 'starts_with':
                        $query->where($fieldName, 'like', $fieldValue . '%');
                        break;
                    case 'ends_with':
                        $query->where($fieldName, 'like', '%' . $fieldValue);
                        break;
                    case 'equals':
                        $query->where($fieldName, '=', $fieldValue);
                        break;
                    case 'not_equals':
                        $query->where($fieldName, '!=', $fieldValue);
                        break;
                    case 'contains':
                        $query->where($fieldName, 'like', '%' . $fieldValue . '%');
                        break;
                    case 'not_contains':
                        $query->where($fieldName, 'not like', '%' . $fieldValue . '%');
                        break;
                    default:
                        // Handle invalid condition if needed
                        break;
                }
            }

            // Group by the selected field
            $groupByField = $validatedData['group_by_field'];
            $query->groupBy($groupByField);

            // Prepare chart data based on the chart type
            $chartType = $validatedData['requests'][0]['chart_type']; // Assuming the chart type is the same for all requests
            $chartData = $this->prepareChartData($query, $groupByField, $chartType);

            // Count of grouped results
            $count = count($chartData);

            return response()->json([
                'status' => 200,
                'count' => $count,
                'chart_type' => $chartType,
                'chart_data' => $chartData,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to search customers: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search customers: ' . $e->getMessage()], 500);
        }
    }

    private function prepareChartData($query, $groupByField, $chartType)
    {
        // Fetch grouped data and prepare chart data based on the chart type
        $groupedData = $query->get([$groupByField])->toArray();

        $chartData = [];

        foreach ($groupedData as $data) {
            $chartData[] = [
                'label' => $data[$groupByField],
                'value' => count($data), // Example: Count of records per group
            ];
        }

        return $chartData;
    }
    private function preparePieChartData($query, $groupByField)
    {
        // Fetch grouped data and prepare pie chart data
        $groupedData = $query->get([$groupByField])->toArray();

        $chartData = [];

        foreach ($groupedData as $data) {
            $chartData[] = [
                'label' => $data[$groupByField],
                'value' => count($data), // Example: Count of records per group
            ];
        }

        return $chartData;
    }

    private function prepareBarChartData($query, $groupByField)
    {
        // Fetch grouped data and prepare bar chart data
        $groupedData = $query->get([$groupByField])->toArray();

        $chartData = [];

        foreach ($groupedData as $data) {
            $chartData[] = [
                'label' => $data[$groupByField],
                'value' => count($data), // Example: Count of records per group
            ];
        }

        return $chartData;
    }

    private function prepareLineChartData($query, $groupByField)
    {
        // Fetch grouped data and prepare line chart data
        $groupedData = $query->get([$groupByField])->toArray();

        $chartData = [];

        foreach ($groupedData as $data) {
            $chartData[] = [
                'label' => $data[$groupByField],
                'value' => count($data), // Example: Count of records per group
            ];
        }

        return $chartData;
    }
}
