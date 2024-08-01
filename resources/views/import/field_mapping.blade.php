<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Field Mapping</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .thead-dark th {
            background-color: #343a40;
            color: #fff;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <h1>Field Mapping for {{ ucfirst($module) }}</h1>
            <hr>

            <!-- Form for selecting saved mapping -->
            <div class="mt-4">
                <form action="{{ route('import.processImport', ['module' => $module]) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="savedMappingSelect">Use Saved Mapping</label>
                        <select id="savedMappingSelect" name="saved_mapping_id" class="form-control">
                            <option value="" disabled selected>Select a saved mapping</option>
                            <!-- Add options dynamically if needed -->
                        </select>
                    </div>
                </form>
            </div>

            <!-- CRM Fields and Default Values Table -->
            <div class="mt-5">
                <h4>CRM Fields and Default Values</h4>
                <div class="table-responsive">
                    <form action="{{ route('import.processImport', ['module' => $module]) }}" method="POST">
                        @csrf
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    @if ($hasHeader)
                                        <th>CSV Header</th>
                                    @endif
                                    <th>First Row Value</th>
                                    <th>CRM Field</th>
                                    <th>Default Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($csvHeaders as $index => $header)
                                    <tr>
                                        @if ($hasHeader)
                                            <td>{{ $header }}</td>
                                        @endif
                                        <td>{{ $firstRow[$header] ?? '' }}</td>
                                        <td>
                                            <select name="crm_fields[{{ $index }}]" class="form-control">
                                                <option value="">Select CRM Field</option>
                                                @foreach ($fields as $field)
                                                    @php
                                                        $fieldType = $field->typeofdata;
                                                        $asterisk = in_array($fieldType, ['V~M', 'I~M', 'D~M']) ? ' (*)' : '';
                                                    @endphp
                                                    <option value="{{ $field->fieldname }}">{{ $field->fieldname }}{{ $asterisk }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                        <input type="text" name="default_values[{{ $index }}]" class="form-control" placeholder="Enter Default Value" value="{{ $defaultValues[$header] ?? '' }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="form-inline mt-3">
                            <div class="form-group form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="saveAsCustomMapping" name="save_as_custom_mapping">
                                <label class="form-check-label small" for="saveAsCustomMapping">Save as Custom Mapping</label>
                            </div>
                            <input type="text" name="custom_mapping_name" class="form-control ml-2">
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Import</button>
                        <a href="{{ route('import.cancel', ['module' => $module]) }}" class="btn btn-secondary mt-3">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>