<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duplicate Record Handling</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-section {
            margin-bottom: 20px;
        }
        .form-section label {
            font-weight: bold;
        }
        .button-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }
        .button-container button {
            margin: 5px;
        }
        .btn-skip {
            background-color: #007bff;
            color: white;
        }
        .btn-cancel {
            background-color: #dc3545;
            color: white;
        }
        .btn-next {
            background-color: #28a745;
            color: white;
        }
        .btn-back {
            color: #000000;
            border-color: #000000;
            background-color: transparent;
        }
        .btn-back:hover {
            background-color: #000000;
            color: white;
        }
        .rectangle-dropdown {
            width: 100%;
            min-height: 150px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 10px;
            font-size: 14px;
            background-color: #fff;
            box-shadow: none;
            overflow-y: auto;
        }
        .text-box {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h3 class="mb-4">Duplicate Record Handling</h3>
    <form id="importStep2Form" action="{{ route('import.step2', ['module' => $module]) }}" method="POST">
        @csrf
        <div class="form-section">
            <div class="form-group">
                <label for="duplicateHandling">Select how duplicate records should be handled</label>
                <select class="form-control" id="duplicateHandling" name="duplicateHandling">
                    <option value="skip">Skip</option>
                    <option value="overwrite">Overwrite</option>
                    <option value="merge">Merge</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group col-md-5">
                    <label for="availableFields">Available Fields</label>
                    <select class="form-control rectangle-dropdown" id="availableFields" name="availableFields[]" multiple>
                        @foreach($fields as $field)
                            <option value="{{ $field->fieldlabel }}" data-type="{{ $field->typeofdata }}">{{ $field->fieldlabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-2 text-center">
                    <button type="button" class="btn btn-outline-primary mt-2" id="moveRightBtn">&gt;&gt;</button>
                    <button type="button" class="btn btn-outline-primary mt-2" id="moveLeftBtn">&lt;&lt;</button>
                </div>

                <div class="form-group col-md-5">
                    <label for="matchingFields">Fields to be Matched On</label>
                    <select class="form-control rectangle-dropdown" id="matchingFields" name="matchingFields[]" multiple>
                        {{-- Display fields with typeofdata as 'V~M' by default --}}
                        @foreach($fields as $field)
                            @if($field->typeofdata === 'V~M')
                                <option value="{{ $field->fieldlabel }}" data-type="{{ $field->typeofdata }}">{{ $field->fieldlabel }}</option>
                            @endif
                        @endforeach

                        {{-- Display previously selected fields from old input --}}
                        @foreach(old('matchingFields', []) as $value)
                            <option value="{{ $value }}" data-type="{{ old('matchingFieldTypes.' . $loop->index) }}" selected>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row align-items-center">
                <div class="button-container">
                <button type="button" class="btn btn-back btn-outline-dark" onclick="window.history.back()">Back</button>
                    <button type="button" class="btn btn-cancel" onclick="window.location.href='{{ url('/') }}'">Cancel</button>
                    <button type="submit" class="btn btn-next">Next</button>
                    <button type="button" class="btn btn-skip" onclick="skipThisStep()">Skip This Step</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
  $(document).ready(function() {
    // Move selected fields from availableFields to matchingFields
    $('#moveRightBtn').click(function() {
        $('#availableFields option:selected').each(function() {
            var $this = $(this);
            var value = $this.val();
            var type = $this.data('type');

            // Only move if the field is not already in matchingFields
            if ($('#matchingFields option[value="' + value + '"]').length === 0) {
                var option = $('<option>', {
                    value: value,
                    text: value,
                    'data-type': type,
                    selected: true
                });
                $('#matchingFields').append(option);

                // Disable the option in availableFields
                $this.prop('disabled', true);
            }
        });
    });

    // Move selected fields from matchingFields to availableFields
    $('#moveLeftBtn').click(function() {
        $('#matchingFields option:selected').each(function() {
            var $this = $(this);
            var value = $this.val();

            // Only move if the field is not already in availableFields
            if ($('#availableFields option[value="' + value + '"]').length === 0) {
                var option = $('<option>', {
                    value: value,
                    text: value,
                    'data-type': $this.data('type')
                });
                $('#availableFields').append(option);

                // Remove the option from matchingFields
                $this.remove();
            } else {
                $('#availableFields option[value="' + value + '"]').prop('disabled', false);
                $this.remove();
            }
        });
    });

    // Handle Skip This Step button
    window.skipThisStep = function() {
        window.location.href = "{{ route('import.fieldMapping', ['module' => $module]) }}";
    }
});

</script>

</body>
</html>