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
            height: 200px;
            background: #f9f9f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            position: relative;
        }

        .edit-button {
            position: absolute;
            top: 10px;
            right: 10px;
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

        .add-block-button {
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .add-block-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <form id="step2Form" action="{{ route('form.step2.post') }}" method="POST">
        @csrf
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
                <button type="button" class="add-block-button" onclick="addBlock()">Add Block</button>
                <div id="blocksContainer">
                    <div class="drop-area" id="block1">
                        Drop items here
                        <button type="button" class="edit-button" onclick="openEditModal('block1')">Edit</button>
                    </div>
                </div>
                <input type="hidden" id="tableName" name="tableName"> <!-- Table name input field -->
                <input type="hidden" id="hiddenShowTitle" name="show_title" value="0">
                <input type="hidden" id="hiddenVisible" name="visible" value="0">
                <input type="hidden" id="hiddenCreateView" name="create_view" value="0">
                <input type="hidden" id="hiddenEditView" name="edit_view" value="0">
                <input type="hidden" id="hiddenDetailView" name="detail_view" value="0">
                <input type="hidden" id="hiddenDisplayStatus" name="display_status" value="0">
                <input type="hidden" id="hiddenIsCustom" name="is_custom" value="0">
                <input type="hidden" id="hiddenIsList" name="is_list" value="0">
                
                <!-- Hidden fields for storing modal data -->
                <input type="hidden" id="blockModalData" name="blockModalData">
                <input type="hidden" id="dragDropModalData" name="dragDropModalData">
                
                <button type="button" onclick="goToPrevious()">Previous</button>
                {{-- <button type="button" onclick="goToNext()">Next</button> --}}
            </div>
        </div>

        <!-- Modal for drag and drop fields -->
        <div class="modal" id="dragDropModal">
            <div class="modal-content">
                <label>Field Name:</label>
                <input type="text" id="fieldName">
                <label>Label Name:</label>
                <input type="text" id="labelName">
                <label>Table Name:</label>
                <input type="text" id="tableNameModal">
                <label>Column Name:</label>
                <input type="text" id="columnName">
                <label>Column Type:</label>
                <input type="text" id="columnType">
                <div class="checkbox-container">
                    <label for="mandatory">Mandatory</label>
                    <input type="checkbox" id="mandatory">
                </div>
                <button type="button" id="saveDragDropField">Save</button>
            </div>
        </div>

        <!-- Modal for edit button -->
        <div class="modal" id="editModal">
            <div class="modal-content">
                <label>Label:</label>
                <input type="text" name="label" id="editLabel">
                <label>Label Translation:</label>
                <input type="text" name="label_translation" id="editLabelTranslation">
                <div class="checkbox-container">
                    <label for="showTitle">Show Title</label>
                    <input type="checkbox" name="show_title" id="showTitle" checked>
                </div>
                <div class="checkbox-container">
                    <label for="visible">Visible</label>
                    <input type="checkbox" name="visible" id="visible" checked>
                </div>
                <div class="checkbox-container">
                    <label for="createView">Create View</label>
                    <input type="checkbox" name="create_view" id="createView">
                </div>
                <div class="checkbox-container">
                    <label for="editView">Edit View</label>
                    <input type="checkbox" name="edit_view" id="editView">
                </div>
                <div class="checkbox-container">
                    <label for="detailView">Detail View</label>
                    <input type="checkbox" name="detail_view" id="detailView">
                </div>
                <div class="checkbox-container">
                    <label for="displayStatus">Display Status</label>
                    <input type="checkbox" name="display_status" id="displayStatus" checked>
                </div>
                <div class="checkbox-container">
                    <label for="isCustom">Is Custom</label>
                    <input type="checkbox" name="is_custom" id="isCustom">
                </div>
                <div class="checkbox-container">
                    <label for="isList">Is List</label>
                    <input type="checkbox" name="is_list" id="isList">
                </div>
                <button type="button" id="saveEditField">Save</button>
            </div>
        </div>
        <button type="submit">Next</button>
    </form>

    <script>
        document.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', e.target.getAttribute('data-type'));
        });

        document.addEventListener('dragover', (e) => {
            e.preventDefault();
        });

        document.addEventListener('drop', (e) => {
            e.preventDefault();
            const dropArea = e.target.closest('.drop-area');
            if (dropArea) {
                currentDropArea = dropArea;
                dragDropModal.style.display = 'flex';
            }
        });

        const dragDropModal = document.getElementById('dragDropModal');
        const saveDragDropFieldButton = document.getElementById('saveDragDropField');
        let currentDropArea = null;
        const dragDropFieldsData = [];

        saveDragDropFieldButton.addEventListener('click', () => {
            const fieldName = document.getElementById('fieldName').value;
            const labelName = document.getElementById('labelName').value;
            const tableNameModal = document.getElementById('tableNameModal').value;
            const columnName = document.getElementById('columnName').value;
            const columnType = document.getElementById('columnType').value;
            const mandatory = document.getElementById('mandatory').checked ? 'Mandatory' : 'Optional';

            const newItem = document.createElement('div');
            newItem.className = 'draggable-item';
            newItem.textContent = `${labelName} (${fieldName}, ${columnName}, ${columnType}, ${mandatory})`;
            currentDropArea.appendChild(newItem);

            const dragDropData = {
                fieldName,
                labelName,
                tableNameModal,
                columnName,
                columnType,
                mandatory
            };

            dragDropFieldsData.push(dragDropData);
            document.getElementById('dragDropModalData').value = JSON.stringify(dragDropFieldsData);

            dragDropModal.style.display = 'none';
        });

        const editModal = document.getElementById('editModal');
        const saveEditFieldButton = document.getElementById('saveEditField');
        let currentBlock = null;
        const blockFieldsData = [];

        function openEditModal(blockId) {
            currentBlock = document.getElementById(blockId);
            editModal.style.display = 'flex';
        }

        saveEditFieldButton.addEventListener('click', () => {
            const editedLabel = document.getElementById('editLabel').value;
            const editedLabelTranslation = document.getElementById('editLabelTranslation').value;
            const showTitle = document.getElementById('showTitle').checked ? 1 : 0;
            const visible = document.getElementById('visible').checked ? 1 : 0;
            const createView = document.getElementById('createView').checked ? 1 : 0;
            const editView = document.getElementById('editView').checked ? 1 : 0;
            const detailView = document.getElementById('detailView').checked ? 1 : 0;
            const displayStatus = document.getElementById('displayStatus').checked ? 1 : 0;
            const isCustom = document.getElementById('isCustom').checked ? 1 : 0;
            const isList = document.getElementById('isList').checked ? 1 : 0;

            const blockData = {
                editedLabel,
                editedLabelTranslation,
                showTitle,
                visible,
                createView,
                editView,
                detailView,
                displayStatus,
                isCustom,
                isList
            };

            blockFieldsData.push(blockData);
            document.getElementById('blockModalData').value = JSON.stringify(blockFieldsData);

            editModal.style.display = 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target == dragDropModal) {
                dragDropModal.style.display = 'none';
            } else if (e.target == editModal) {
                editModal.style.display = 'none';
            }
        });

        function addBlock() {
            const blockId = 'block' + (document.querySelectorAll('.drop-area').length + 1);
            const newBlock = document.createElement('div');
            newBlock.className = 'drop-area';
            newBlock.id = blockId;
            newBlock.innerHTML = `Drop items here
                <button type="button" class="edit-button" onclick="openEditModal('${blockId}')">Edit</button>`;
            document.getElementById('blocksContainer').appendChild(newBlock);
        }

        function goToPrevious() {
            // Redirect to the previous step, assuming step1.blade.php is the previous step
            window.location.href = "{{ route('form.step1.post') }}";
        }

        // Function to navigate to the next step
        function goToNext() {
            // Redirect to the next step, assuming step3.blade.php is the next step
            window.location.href = "{{ route('form.step3.post') }}";
        }
        // Update hidden inputs on checkbox change
        document.querySelectorAll('.modal-content input[type=checkbox]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const hiddenInput = document.getElementById('hidden' + checkbox.name.charAt(0).toUpperCase() + checkbox.name.slice(1));
                hiddenInput.value = checkbox.checked ? '1' : '0';
            });
        });
    </script>
</body>
</html>
