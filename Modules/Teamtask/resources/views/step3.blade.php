<!DOCTYPE html>
<html>
<head>
    <title>Step 3</title>
    <!-- Include necessary stylesheets -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            background-color: #f4f4f4;
            height: 100vh;
            padding: 20px;
            font-family: Arial, sans-serif;
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

        .module-fields {
            margin-top: 20px;
        }

        .module-field {
            padding: 10px;
            margin-bottom: 5px;
            background-color: #f0f0f0;
            border-radius: 4px;
            cursor: pointer;
        }

        .module-field:hover {
            background-color: #e0e0e0;
        }

        .drop-area {
            border: 2px dashed #ccc;
            padding: 20px;
            background: #f9f9f9;
            margin-bottom: 20px;
            position: relative;
            text-align: center;
            font-size: 16px;
            color: #666;
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
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .modal-content button:hover {
            background-color: #0056b3;
        }
        .modal-content {
    position: relative;
}

.close {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 20px;
    cursor: pointer;
}

.close:hover {
    color: red;
}

    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <h2>Modules</h2>
            <div class="module-fields">
                @foreach($modules as $module)
                    <div class="module-field draggable-item" draggable="true" data-module="{{ $module }}">{{ $module }}</div>
                @endforeach
            </div>
        </div>
        <div class="right-panel">
            <form id="step3Form" action="{{ route('form.step3.post') }}" method="POST">
                @csrf
                <div class="drop-area" id="moduleDropArea">
                    Drop modules here
                </div>
                <div class="modal" id="moduleModal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <label>Label:</label>
                        <input type="text" id="moduleNameInput" name="label" readonly>
                        <label>Translation (en_us):</label>
                        <input type="text" id="labelTranslationInput" name="translation">
                        <label>Method:</label>
                        <select id="methodInput" name="method">
                            <option value="get_related_list">Get Related List</option>
                            <option value="get_dependents_list">Get Dependents List</option>
                            <option value="get_attachments">Get Attachments</option>
                            <option value="get_history">Get History</option>
                            <option value="custom">Custom Method</option>
                        </select>
                        <div class="checkbox-container">
                            <label for="isActiveInput">Is Active:</label>
                            <input type="checkbox" id="isActiveInput" name="isActive">
                        </div>
                        <div class="checkbox-container">
                            <label for="addInput">Add:</label>
                            <input type="checkbox" id="addInput" name="add">
                        </div>
                        <div class="checkbox-container">
                            <label for="selectInput">Select:</label>
                            <input type="checkbox" id="selectInput" name="select">
                        </div>
                            <button type="button" id="saveModule">Save</button>
                        </div>
                    </div>
                    <button type="button" onclick="goToPrevious()">Previous</button>
                    <button type="submit">Next</button>
                </form>
            </div>
        </div>
    
        <script>
            // JavaScript code for drag and drop functionality
            const draggableItems = document.querySelectorAll('.draggable-item');
            const moduleDropArea = document.getElementById('moduleDropArea');
            const moduleModal = document.getElementById('moduleModal');
            const saveModuleButton = document.getElementById('saveModule');
            let currentDraggedModule = null;
            let modules = [];
    
            // Event listeners for draggable modules
            draggableItems.forEach(item => {
                item.addEventListener('dragstart', (e) => {
                    currentDraggedModule = e.target;
                });
            });
    
            // Event listeners for module drop area
            moduleDropArea.addEventListener('dragover', (e) => {
                e.preventDefault();
            });
    
            moduleDropArea.addEventListener('drop', (e) => {
                e.preventDefault();
                const moduleName = currentDraggedModule.getAttribute('data-module');
                moduleModal.style.display = 'flex';
                document.getElementById('moduleNameInput').value = moduleName;
            });
    
            // Function to save module details
            saveModuleButton.addEventListener('click', () => {
                const moduleName = document.getElementById('moduleNameInput').value;
                const labelTranslation = document.getElementById('labelTranslationInput').value;
                const method = document.getElementById('methodInput').value;
                const isActive = document.getElementById('isActiveInput').checked ? 1 : 0;
                const add = document.getElementById('addInput').checked ? 1 : 0;
                const select = document.getElementById('selectInput').checked ? 1 : 0;
    
                modules.push({
                    moduleName,
                    labelTranslation,
                    method,
                    isActive,
                    add,
                    select
                });
    
                // Additional processing or submission can be done here
    
                moduleModal.style.display = 'none';
            });
            const closeButton = document.querySelector('.close');

closeButton.addEventListener('click', () => {
    moduleModal.style.display = 'none';
});
    
            // Function to navigate to the previous step
            function goToPrevious() {
                // Redirect to the previous step, assuming step2.blade.php is the previous step
                window.location.href = "{{ route('form.step2.post') }}";
            }
        </script>
    </body>
    </html>
    