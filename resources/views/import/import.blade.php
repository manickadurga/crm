<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload CSV</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .custom-file-upload {
            color: white;
            background-color: blue;
            border: 1px solid blue;
            cursor: pointer;
            display: inline-block;
            padding: 5px 10px;
            font-size: 0.875rem;
            text-align: center;
            text-decoration: none;
            white-space: nowrap;
            vertical-align: middle;
            border-radius: 4px;
            width: auto;
            box-sizing: border-box;
        }
        .custom-file-upload:hover {
            background-color: #0056b3;
        }
        .custom-file input[type=file] {
            display: block;
            opacity: 0;
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            cursor: pointer;
        }
        .file-selected {
            margin-top: 10px;
        }
        .radio-inline .form-check-input {
            margin-right: 5px;
        }
        .form-check-label {
            margin-left: 10px;
        }
        .form-section {
            margin-bottom: 20px;
        }
        .form-section label {
            font-weight: bold;
        }
        .task-actions {
            margin-top: 20px;
        }
        .label-small {
            font-size: 0.875rem;
        }
        .label-normal {
            font-size: inherit;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h3 class="mb-4">Import from CSV File</h3>
    <form id="importForm" action="{{ route('import.process', ['module' => $module]) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-section">
            <div class="form-group row align-items-center">
                <label for="csv_file" class="col-sm-4 col-form-label">Select CSV File <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <!-- <label for="csv_file" class="custom-file-upload" id="fileInputLabel">Select from My Computer</label> -->
                    <input type="file" id="csv_file" name="csv_file" onchange="updateFileName(this)" accept=".csv">
                    <small id="fileSelected" class="form-text text-muted file-selected"></small>
                    @error('csv_file')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group row align-items-center">
                <label for="hasHeader" class="col-sm-4 col-form-label">Has Header</label>
                <div class="col-sm-8">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="hasHeader" name="hasHeader" value="1" checked>
                        <label class="form-check-label" for="hasHeader"></label>
                    </div>
                </div>
            </div>

            <div class="form-group row align-items-center">
                <label for="encoding" class="col-sm-4 col-form-label label-small">Character Encoding</label>
                <div class="col-sm-8">
                    <select class="form-control" id="encoding" name="encoding">
                        <option value="UTF-8">UTF-8</option>
                        <option value="ISO-8859-1">ISO-8859-1</option>
                    </select>
                </div>
            </div>

            <div class="form-group row align-items-center">
                <label class="col-sm-4 col-form-label">Delimiter</label>
                <div class="col-sm-8">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="delimiter" id="comma" value="comma" checked>
                        <label class="form-check-label" for="comma">Comma</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="delimiter" id="semicolon" value="semicolon">
                        <label class="form-check-label" for="semicolon">Semicolon</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="delimiter" id="pipe" value="pipe">
                        <label class="form-check-label" for="pipe">Pipe</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="delimiter" id="caret" value="caret">
                        <label class="form-check-label" for="caret">Caret</label>
                    </div>
                </div>
            </div>

            <div class="form-group row align-items-center">
                <div class="col-sm-4"></div>
                <div class="col-sm-8">
                    <button type="button" class="btn btn-danger" onclick="window.location.href='{{ url('/') }}'">Cancel</button>
                    <button type="submit" class="btn btn-primary">Next</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    function updateFileName(input) {
        var fileName = input.files[0].name;
        var fileInputLabel = document.getElementById('fileInputLabel');
        var truncatedFileName = truncateFileName(fileName);
        fileInputLabel.innerText = 'Selected: ' + truncatedFileName;
        document.getElementById('fileSelected').innerText = '';
    }

    function truncateFileName(fileName) {
        var maxLength = 25;
        if (fileName.length > maxLength) {
            return fileName.substring(0, maxLength - 3) + '...';
        }
        return fileName;
    }

    document.getElementById('fileInputLabel').addEventListener('click', function() {
        document.getElementById('csv_file').click();
    });
</script>
</body>
</html>