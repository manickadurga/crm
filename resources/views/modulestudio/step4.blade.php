<!DOCTYPE html>
<html>
<head>
    <title>Step 4</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

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

        .left-panel, .right-panel {
            width: 50%;
            padding: 20px;
            box-sizing: border-box;
        }

        .field-item {
            padding: 10px;
            margin-bottom: 5px;
            background-color: #f0f0f0;
            border-radius: 4px;
            cursor: pointer;
        }

        .field-item:hover {
            background-color: #e0e0e0;
        }

        .drop-area {
            border: 2px dashed #ccc;
            padding: 20px;
            background: #f9f9f9;
            position: relative;
            text-align: center;
            font-size: 16px;
            color: #666;
        }

        .drop-area .field-item {
            background-color: #d0d0d0;
            cursor: move;
        }

        button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <h2>Fields</h2>
            <div class="field-list">
                @foreach($modules as $field)
                    <div class="field-item draggable-item" draggable="true" data-field="{{ $field }}">{{ $field }}</div>
                @endforeach
                    <div class="field-item draggable-item" draggable="true" data-field="createdtime">createdtime</div>
                    <div class="field-item draggable-item" draggable="true" data-field="modifiedtime">modifiedtime</div>
                    
            </div>
        </div>
        <div class="right-panel">
            <h2>Arrange Fields</h2>
            <form id="fieldForm" action="{{ route('form.step4.post') }}" method="POST">
                @csrf 
                <div class="drop-area" id="fieldDropArea">
                    Drop fields here
                </div>
                
                <button type="submit">Save</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
        const draggableItems = document.querySelectorAll('.draggable-item');
        const fieldDropArea = document.getElementById('fieldDropArea');
        let fields = [];

        // Event listeners for draggable fields
        draggableItems.forEach(item => {
            item.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', e.target.getAttribute('data-field'));
            });
        });

        // Event listeners for field drop area
        fieldDropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
        });

        fieldDropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            const fieldName = e.dataTransfer.getData('text/plain');
            if (!fields.includes(fieldName)) {
                const fieldItem = document.querySelector(`.draggable-item[data-field="${fieldName}"]`).cloneNode(true);
                fieldItem.classList.remove('draggable-item');
                fieldItem.classList.add('dropped-item');
                fieldItem.setAttribute('draggable', true);
                fieldItem.addEventListener('dragstart', handleDragStart);
                fieldItem.addEventListener('dragend', handleDragEnd);
                fieldDropArea.appendChild(fieldItem);
                fields.push(fieldName);
            }
        });

        // Allow reordering within the drop area
     fieldDropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            const afterElement = getDragAfterElement(fieldDropArea, e.clientY);
            const dragging = document.querySelector('.dragging');
            if (afterElement == null) {
                fieldDropArea.appendChild(dragging);
            } else {
                fieldDropArea.insertBefore(dragging, afterElement);
            }
        });
        $('#submit4').click(function(e) {  
        e.preventDefault(); // Prevent the default form submission
        console.log('asd'); // Debugging purpose
        $.ajax({
            url: '{{ route("form.step4.post") }}',
            type: 'POST',
            data: $('#step4Form').serialize(), // Serialize the form data
            success: function(response) {
                console.log('ds');
                console.log(response.message);
                window.location.href = "{{ route('form.success') }}";
            },
            error: function(xhr, status, error) {
                console.log('error');
                console.error(xhr.responseText);
            }
        });
    });
     
    })

        function handleDragStart(e) {
            e.dataTransfer.setData('text/plain', e.target.getAttribute('data-field'));
            e.target.classList.add('dragging');
        }

        function handleDragEnd(e) {
            e.target.classList.remove('dragging');
        }

       

        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.dropped-item:not(.dragging)')];

            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        // Function to save arranged fields
        function saveFields() {
            const orderedFields = [];
            document.querySelectorAll('.dropped-item').forEach(item => {
                orderedFields.push(item.getAttribute('data-field'));
            });
            console.log(orderedFields);
            // Further processing of orderedFields array here
        }
    </script>
</body>
</html>

