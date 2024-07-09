<!DOCTYPE html>
<html>
<head>
    <title>Step 1</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <form action="{{ route('form.step1.post') }}" method="POST">
        @csrf
        <label>Module's Name:</label>
        <input type="text" name="module_name" id="moduleName" required>
        <span id="moduleNameError" style="color:red;display:none;">Module name already exists.</span>

        <label>Version:</label>
        <input type="text" name="version">
        <label>Singular Translation:</label>
        <input type="text" name="singular_translation">
        <label>Plural Translation:</label>
        <input type="text" name="plural_translation">
        <label>Menu:</label>
        <select name="menu">
            <option value="">Select a Value</option>
            <option value="Accounting">Accounting</option>
            <option value="Sales">Sales</option>
            <option value="Tasks">Tasks</option>
            <option value="Jobs">Jobs</option>
            <option value="Employees">Employees</option>
            <option value="Organization">Organization</option>
            <option value="Contacts">Contacts</option>
            <option value="Goals">Goals</option>

        </select>
    
        <button type="submit">Next</button>
    </form>
    <script>
        $(document).ready(function() {
            console.log('dd');
            
            $('#moduleName').on('blur', function() {
                console.log('dd');
                var moduleName = $(this).val();
                if (moduleName) {
                    $.ajax({
                        url: '{{ route("form.checkModuleName") }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            module_name: moduleName
                        },
                        success: function(response) {
                            console.log(response);
                            if (response.exists) {
                                $('#moduleNameError').show();
                                $('#moduleName').focus();
                            } else {
                                $('#moduleNameError').hide();
                            }
                        }
                    });
                }
            });

            $('#step1Form').on('submit', function(e) {
                if ($('#moduleNameError').is(':visible')) {
                    e.preventDefault();
                    alert('Please fix the errors before submitting the form.');
                }
            });
        });
    </script>
</body>
</html>
