<!DOCTYPE html>
<html>
<head>
    <title>Step 2</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            background-color: #f4f4f4;
            height: 100vh;
            padding: 20px;
        }

        .container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }

        .left-panel {
            width: 25%;
            padding: 20px;
            border-right: 1px solid #ccc;
            background: #f9f9f9;
        }

        .right-panel {
            width: 75%;
            padding: 20px;
        }

        .draggable-item {
            padding: 10px;
            border: 1px solid #ccc;
            margin: 5px 0;
            cursor: pointer;
            background: #fff;
            border-radius: 4px;
            text-align: center;
        }

        .drop-area {
            border: 2px dashed #ccc;
            padding: 20px;
            height: 400px;
            background: #f9f9f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            width: 400px;
        }

        .modal-content label, .modal-content input {
            display: block;
            margin-bottom: 10px;
        }

        .modal-content .checkbox-container {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .modal-content .checkbox-container label {
            width: auto;
            margin-right: 10px;
        }

        .modal-content button {
            display: block;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="draggable-item" draggable="true" data-type="Text input">Text input</div>
            <div class="draggable-item" draggable="true" data-type="Text input Mandatory">Text input Mandatory</div>
            <div class="draggable-item" draggable="true" data-type="Auto increment">Auto increment</div>
            <div class="draggable-item" draggable="true" data-type="Auto number">Auto number</div>
            <div class="draggable-item" draggable="true" data-type="Date">Date</div>
            <div class="draggable-item" draggable="true" data-type="Date time">Date time</div>
            <div class="draggable-item" draggable="true" data-type="Numeric input">Numeric input</div>
            <div class="draggable-item" draggable="true" data-type="Json array">Json array</div>
        </div>
        <div class="right-panel">
            <form id="step2Form" action="{{ route('form.step2.post') }}" method="POST">
                @csrf
                <input type="hidden" id="moduleName" name="moduleName" value="{{ old('moduleName', request()->get('moduleName')) }}">
                <div class="drop-area" id="dropArea">
                    Drop items here
                </div>
                <input type="hidden" id="tableName" name="tableName"> <!-- Table name input field -->
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <div class="modal" id="modal">
        <div class="modal-content">
            <label>Field Name:</label>
            <input type="text" id="fieldName">
            <label>Label:</label>
            <input type="text" id="label">
            <label>Table Name:</label>
            <input type="text" id="tableName">
            <label>Column Name:</label>
            <input type="text" id="columnName">
            <label>Column Type:</label>
            <input type="text" id="columnType">
            <div class="checkbox-container">
                <label for="mandatory">Mandatory:</label>
                <input type="checkbox" id="mandatory">
            </div>
            <button type="button" id="saveField">Save</button>
        </div>
    </div>

    <script>
        // Fetch module name and prefill table name field
        const moduleName = document.getElementById('moduleName').value;
        const tableNameInput = document.getElementById('tableName');
        tableNameInput.value = moduleName.toLowerCase() + 's'; // Lowercase module name with 's' appended

        // Your existing JavaScript code
        const draggableItems = document.querySelectorAll('.draggable-item');
        const dropArea = document.getElementById('dropArea');
        const modal = document.getElementById('modal');
        const saveFieldButton = document.getElementById('saveField');

        let currentDraggedItem = null;
        let fields = [];

        draggableItems.forEach(item => {
            item.addEventListener('dragstart', (e) => {
                currentDraggedItem = e.target;
            });
        });

        dropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
        });

        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            document.getElementById('tableName').value = tableNameInput.value;
            modal.style.display = 'flex';
        });

        saveFieldButton.addEventListener('click', () => {
            const fieldName = document.getElementById('fieldName').value;
            const label = document.getElementById('label').value;
            const tableName = tableNameInput.value; // Corrected this line
            const columnName = document.getElementById('columnName').value;
            const columnType = document.getElementById('columnType').value;
            const mandatory = document.getElementById('mandatory').checked;

            fields.push({
                type: currentDraggedItem.getAttribute('data-type'),
                fieldName,
                label,
                tableName,
                columnName,
                columnType,
                mandatory
            });

            const hiddenFieldsInput = document.createElement('input');
            hiddenFieldsInput.type = 'hidden';
            hiddenFieldsInput.name = 'fields[]';
            hiddenFieldsInput.value = JSON.stringify(fields[fields.length - 1]);
            document.getElementById('step2Form').appendChild(hiddenFieldsInput);

            modal.style.display = 'none';

            // Clear the modal inputs
            document.getElementById('fieldName').value = '';
            document.getElementById('label').value = '';
            document.getElementById('tableName').value = tableNameInput.value;
            document.getElementById('columnName').value = '';
            document.getElementById('columnType').value = '';
            document.getElementById('mandatory').checked = false;
        });

        // Close modal when clicking outside of the modal content
        window.addEventListener('click', (e) => {
            if (e.target == modal) {
                modal.style.display = 'none';
            }
        });
    </script>

</body>
</html>
