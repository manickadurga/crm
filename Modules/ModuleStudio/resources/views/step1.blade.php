<!DOCTYPE html>
<html>
<head>
    <title>Step 1</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
</head>
<body>
    <form action="{{ route('form.step1.post') }}" method="POST">
        @csrf
        <label>Module's Name:</label>
        <input type="text" name="module_name" id="moduleName" required>
        <label>Version:</label>
        <input type="text" name="version">
        <label>Singular Translation:</label>
        <input type="text" name="singular_translation">
        <label>Plural Translation:</label>
        <input type="text" name="plural_translation">
        <label>Menu:</label>
        <select name="menu">
            <option value="">Select a Value</option>
            <option value="Inventory">Marketing</option>
            <option value="Orders">Sales</option>
            <option value="Contacts">Inventory</option>
            <option value="Goals">Support</option>
        </select>
    
        <button type="submit">Next</button>
    </form>
</body>
</html>
