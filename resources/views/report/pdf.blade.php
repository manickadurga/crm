<!DOCTYPE html>
<html>
<head>
    <title>{{ $reportName }}</title>
    <style>
        /* Add your styles here */
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .header-left {
            font-size: 14px;
            width: 50%;
            text-align: left;
        }
        .header-right {
            font-size: 14px;
            width: 50%;
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        h1 {
            text-align: center;
        }
        .summary-table {
            margin-top: 20px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <p>{{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</p>
            <h1>{{ $reportName }}</h1>
        </div>
        <div class="header-right">
            <p>{{ $totalRecords }} Records</p>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                @foreach ($formattedColumns as $column)
                    <th>{{ $column['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($exportData as $row)
                <tr>
                    @foreach ($formattedColumns as $column)
                        <td>
                            {{ $row[$column['label']] ?? '' }} <!-- Handle missing data with null coalescing -->
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>


    <!-- Add calculations section -->
    @if (!empty($calculations))
        <div class="summary-table">
            <h2>Calculations</h2>
            <table>
                <thead>
                    <tr>
                        <th>Field Name</th>
                        <th>Sum</th>
                        <th>Average</th>
                        <th>Min</th>
                        <th>Max</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($calculations as $field => $calc)
                        <tr>
                            <td>{{ $field }}</td>
                            <td>{{ isset($calc['sum']) ? number_format($calc['sum'], 2) : '' }}</td>
                            <td>{{ isset($calc['avg']) ? number_format($calc['avg'], 2) : '' }}</td>
                            <td>{{ isset($calc['min']) ? number_format($calc['min'], 2) : '' }}</td>
                            <td>{{ isset($calc['max']) ? number_format($calc['max'], 2) : ''}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</body>
</html>
