<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Summary</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Import Summary for {{ $module }}</h2><br><br>
        <div class="mb-4">
            <p class="h5">Total number of records imported: {{ $summary['total_records'] ?? 0 }}</p>
            <p>Number of records created: {{ $summary['created'] ?? 0 }}</p>
            <p>Number of records overwritten: {{ $summary['overwritten'] ?? 0 }}</p>
            <p>Number of records skipped: {{ $summary['skipped'] ?? 0 }}</p>
            <p>Number of records merged: {{ $summary['merged'] ?? 0 }}</p>
            <p>Number of records failed: {{ $summary['failed'] ?? 0 }}</p>
        </div>
        <a href="{{ route('import.form', ['module' => $module]) }}" class="btn btn-primary">Import More</a>
        <a href="{{ route('imported.data', ['module' => $module]) }}" class="btn btn-secondary">Finish</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>