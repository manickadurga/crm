<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin</title>
    <!-- @viteReactRefresh -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite([ 'resources/css/app.css', 'resources/js/app.jsx', ])
</head>
<body>
    <div id="root"></div>
</body>
</html>