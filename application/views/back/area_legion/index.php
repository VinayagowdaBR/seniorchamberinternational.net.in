<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Areas and Legions</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .btn-delete-legion {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            padding: 0.85rem 3.5rem;
            font-size: 1.30rem;
            margin-left: 0.5rem;
            border-radius: 4px;
        }

        #content-container {
            max-width: 1500px;
            margin: 0 auto;
            padding: 2rem 3rem;
            /* Add top margin to avoid header overlap */
            margin-top: 80px; /* Adjust this value based on your admin header height */
            padding-top: 1rem; /* Reduced top padding since we have margin-top */
        }

        #page-head {
            margin-bottom: 2rem;
        }

        #page-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .page-header {
            font-size: 2.25rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        /* Button Styles */
        button {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-size: 1.60rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        button:hover {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            transform: translateY(-1px);
            box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.2);
        }

        button:active {
            transform: translateY(0);
        }

        #btnAddArea {
            font-size: 2rem;
            padding: 0.875rem 1.75rem;
        }

        .btn-add-legion {
            background: linear-gradient(135deg, #10b981, #059669);
            padding: 0.5rem 1rem;
            font-size: 1.8rem;
        }

        .btn-add-legion:hover {
            background: linear-gradient(135deg, #059669, #047857);
        }

        /* Alert Styles */
        .alert {
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            font-size: 1.95rem; /* Slightly larger font */
            min-width: 1000px; /* Add minimum width */
        }

        thead {
            background: linear-gradient(135deg, #475569, #334155);
        }

        th {
            padding: 1.25rem 1rem;
            text-align: left;
            font-weight: 600;
            color: white;
            font-size: 1.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: background-color 0.2s ease;
        }

        tbody tr:hover {
            background-color: #f8fafc;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        td {
            padding: 1.25rem 1rem;
            vertical-align: top;
        }

        td:first-child {
            font-weight: 600;
            color: #64748b;
        }

        td:nth-child(2) {
            font-weight: 600;
            color: #1e293b;
        }

        /* Legion List Styles */
        ul {
            margin: 0;
            padding-left: 1.25rem;
            list-style-type: none;
        }

        li {
            position: relative;
            padding: 0.25rem 0;
            color: #475569;
        }

        li:before {
            content: "•";
            color: #3b82f6;
            font-weight: bold;
            position: absolute;
            left: -1rem;
        }

        em {
            color: #94a3b8;
            font-style: italic;
        }

        .no-areas {
            text-align: center;
            padding: 3rem;
            color: #64748b;
            font-size: 1.125rem;
        }

        /* Ensure modals appear above admin elements */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999; /* High z-index to appear above admin elements */
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            /* Additional centering fallbacks */
            -webkit-box-align: center;
            -webkit-box-pack: center;
            -ms-flex-align: center;
            -ms-flex-pack: center;
        }

        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            width: 90%;
            max-width: 400px;
            margin: 0 auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: scale(0.95);
            transition: transform 0.3s ease;
            position: relative;
            /* Additional centering styles */
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%) scale(0.95);
            margin: 0;
        }

        .modal-overlay.show .modal-content {
            transform: translate(-50%, -50%) scale(1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        #modalCloseBtn {
            background: #f1f5f9;
            color: #64748b;
            border: none;
            border-radius: 8px;
            width: 2.5rem;
            height: 2.5rem;
            font-size: 1.25rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            padding: 0;
        }

        #modalCloseBtn:hover {
            background: #e2e8f0;
            color: #475569;
            transform: none;
            box-shadow: none;
        }

        /* Form Styles */
        form {
            margin-top: 0;
        }

        label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        input[type="text"] {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
            margin-bottom: 1.5rem;
            background: #f8fafc;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        form button[type="submit"] {
            width: 100%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            font-size: 1rem;
            padding: 0.875rem;
            margin-top: 0.5rem;
        }



        /* Add this to your existing CSS */
.legion-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.25rem 0;
}

.legion-info {
    display: flex;
    align-items: center;
    flex: 1;
}

.prefix-badge {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 1.30rem;
    font-weight: 600;
    margin-right: 0.75rem;
    display: inline-block;
    min-width: 45px;
    text-align: center;
    text-transform: uppercase;
}

.legion-name {
    font-weight: 500;
    color: #374151;
}


        /* Responsive Design */
        @media (max-width: 768px) {
            #content-container {
                padding: 1rem;
                margin-top: 70px; /* Adjust for mobile admin header */
                padding-top: 0.5rem;
            }

            .page-header {
                font-size: 1.875rem;
            }

            #page-title {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            table {
                font-size: 0.875rem;
            }

            th, td {
                padding: .75rem 0.5rem;
            }

            th:first-child, td:first-child {
                display: none;
            }
        }

        @media (max-width: 480px) {
            #content-container {
                margin-top: 60px; /* Further adjust for smaller mobile screens */
            }

            .modal-content {
                width: 95%;
                max-width: 350px;
                padding: 1.5rem;
                /* Keep the centering on mobile */
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%) scale(0.95);
            }

            .modal-overlay.show .modal-content {
                transform: translate(-50%, -50%) scale(1);
            }

            th:nth-child(4), td:nth-child(4) {
                width: 1px;
                white-space: nowrap;
            }
        }

        /* Admin Panel Integration Styles */
        /* If your admin panel has a fixed sidebar, uncomment and adjust the following */
        /*
        #content-container {
            margin-left: 250px; /* Adjust based on your sidebar width */
        }
        
        @media (max-width: 768px) {
            #content-container {
                margin-left: 0; /* Remove sidebar margin on mobile */
            }
        }
        */

        /* Additional utility classes for admin integration */
        .admin-content {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Ensure modals appear above admin elements */
        .modal-overlay {
            z-index: 9999; /* High z-index to appear above admin elements */
        }

        /* Loading Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        tbody tr {
            animation: fadeIn 0.3s ease forwards;
        }

        tbody tr:nth-child(even) {
            animation-delay: 0.1s;
        }

        tbody tr:nth-child(odd) {
            animation-delay: 0.05s;
        }
    </style>
</head>
<body>

<!--CONTENT CONTAINER-->
<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header">Areas and their Legions</h1>
            <button id="btnAddArea">Add Area</button>
        </div>
    </div>

    <div id="page-content">
        <!-- Success Alert -->
        <div class="alert alert-success" style="display: none;" id="successAlert">
            Action completed successfully!
        </div>
        
        <!-- Danger Alert -->
        <div class="alert alert-danger" style="display: none;" id="dangerAlert">
            Error occurred while processing your request.
        </div>

        <div>
            <!-- Sample data table -->
            <table>
    <thead>
        <tr>
            <th>#</th>
            <th>Area Name</th>
            <th>Legions (Prefix - Name)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="areasTableBody">
        <!-- Loading placeholder - will be replaced by AJAX -->
        <!-- <tr id="loadingRow">
            <td colspan="4" style="text-align: center; padding: 2rem;">
                <div style="color: #64748b; font-style: italic;">
                    Loading areas and legions...
                </div>
            </td>
        </tr> -->
        
        <!-- Fallback PHP content (in case AJAX fails) -->
        <?php if (isset($areas) && !empty($areas)) : ?>
            <?php foreach ($areas as $index => $area) : ?>
                <tr class="php-generated-row">
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($area['name']) ?></td>
                    <td>
                        <?php if (!empty($area['legions'])) : ?>
                            <ul style="margin:0; padding-left:20px;">
                                <?php foreach ($area['legions'] as $legion) : ?>
                                    <li>
                                        <div class="legion-item">
                                            <div class="legion-info">
                                                <!-- Show prefix first, then legion name -->
                                                <span class="prefix-badge"><?= htmlspecialchars($legion['prefix'] ?? '') ?></span>
                                                <span class="legion-name"><?= htmlspecialchars($legion['name']) ?></span>
                                            </div>
                                            <button class="btn-delete-legion" 
                                                data-legion-id="<?= htmlspecialchars($legion['id']) ?>" 
                                                data-legion-name="<?= htmlspecialchars($legion['name']) ?>"
                                                data-legion-prefix="<?= htmlspecialchars($legion['prefix'] ?? '') ?>"
                                                data-area-id="<?= htmlspecialchars($area['id']) ?>">
                                                Delete
                                            </button>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else : ?>
                            <em>No legions assigned.</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-add-legion" 
                                data-area-id="<?= htmlspecialchars($area['id']) ?>" 
                                data-area-name="<?= htmlspecialchars($area['name']) ?>">
                                + Add Legion
                            </button>
                            <button class="btn-delete-area" 
                                data-area-id="<?= htmlspecialchars($area['id']) ?>" 
                                data-area-name="<?= htmlspecialchars($area['name']) ?>">
                                Delete Area
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

        </div>
    </div>
</div>

<!-- Add Legion Modal -->
<div id="customModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Legion to <span id="modalAreaName">Area</span></h3>
            <button class="modal-close-btn" id="modalCloseBtn">✖</button>
        </div>
        <form id="addLegionForm" action="<?= site_url('admin/add_legion') ?>" method="POST">
            <input type="hidden" name="area_id" id="modalAreaId" value="">
            <label for="legionName">Legion Name:</label>
            <input type="text" id="legionName" name="legion_name" required placeholder="Enter legion name...">
            <label for="legionPrefix">Legion Prefix (Short Form):</label>
            <input type="text" id="legionPrefix" name="prefix" required 
                placeholder="e.g., NYC, LA, CHI..." 
                maxlength="10"
                style="text-transform: uppercase;">
            <button type="submit">Add Legion</button>
        </form>
    </div>
</div>

<!-- Add Area Modal -->
<div id="addAreaModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Area</h3> 
            <button class="modal-close-btn" id="addAreaModalCloseBtn">✖</button>
        </div>
        <form id="addAreaForm" action="<?= site_url('admin/add_area') ?>" method="POST">
            <label for="areaName">Area Name:</label>
            <input type="text" id="areaName" name="area_name" required placeholder="Enter area name...">
            <button type="submit">Add Area</button>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal-overlay confirm-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Deletion</h3>
            <button class="modal-close-btn" id="deleteConfirmCloseBtn">✖</button>
        </div>
        <p id="deleteConfirmMessage">Are you sure you want to delete this item?</p>
        <div class="confirm-buttons">
            <button class="btn-confirm" id="confirmDeleteBtn">Delete</button>
            <button class="btn-cancel" id="cancelDeleteBtn">Cancel</button>
        </div>
    </div>
</div>

<!-- JavaScript -->



<!-- ///////////////////////////////////////////////////////// -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal elements
        const modal = document.getElementById('customModal');
        const modalAreaName = document.getElementById('modalAreaName');
        const modalAreaId = document.getElementById('modalAreaId');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        const form = document.getElementById('addLegionForm');
        //////////////////////////////////////////////
        const prefixInput = document.getElementById('legionPrefix');
        if (prefixInput) {
            prefixInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            });
        }

        // Add Area Modal elements
        const addAreaModal = document.getElementById('addAreaModal');
        const addAreaModalCloseBtn = document.getElementById('addAreaModalCloseBtn');
        const addAreaForm = document.getElementById('addAreaForm');
        const btnAddArea = document.getElementById('btnAddArea');

        // Delete Confirmation Modal elements
        const deleteConfirmModal = document.getElementById('deleteConfirmModal');
        const deleteConfirmCloseBtn = document.getElementById('deleteConfirmCloseBtn');
        const deleteConfirmMessage = document.getElementById('deleteConfirmMessage');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

        // Alert elements
        const successAlert = document.getElementById('successAlert');
        const dangerAlert = document.getElementById('dangerAlert');

        // Current delete operation
        let currentDeleteOperation = null;

        // Modal animation functions
        function showModal(modalElement) {
            modalElement.style.display = 'flex';
            setTimeout(() => modalElement.classList.add('show'), 10);
        }

        function hideModal(modalElement) {
            modalElement.classList.remove('show');
            setTimeout(() => modalElement.style.display = 'none', 300);
        }

        // Alert functions
        function showAlert(alertElement, message, duration = 3000) {
            alertElement.textContent = message;
            alertElement.style.display = 'block';
            setTimeout(() => {
                alertElement.style.display = 'none';
            }, duration);
        }

        // Add Legion Modal functionality
        function initAddLegionButtons() {
            const addLegionButtons = document.querySelectorAll('.btn-add-legion');
            addLegionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const areaId = this.getAttribute('data-area-id');
                    const areaName = this.getAttribute('data-area-name');

                    modalAreaName.textContent = areaName || 'Unknown Area';
                    modalAreaId.value = areaId || '';

                    showModal(modal);
                    
                    setTimeout(() => {
                        document.getElementById('legionName').focus();
                    }, 400);
                });
            });
        }

        // Delete Area functionality
        function initDeleteAreaButtons() {
            const deleteAreaButtons = document.querySelectorAll('.btn-delete-area');
            deleteAreaButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const areaId = this.getAttribute('data-area-id');
                    const areaName = this.getAttribute('data-area-name');

                    currentDeleteOperation = {
                        type: 'area',
                        id: areaId,
                        name: areaName,
                        element: this.closest('tr')
                    };

                    deleteConfirmMessage.textContent = `Are you sure you want to delete the area "${areaName}"? This will also delete all legions in this area.`;
                    showModal(deleteConfirmModal);
                });
            });
        }

        // Delete Legion functionality
        function initDeleteLegionButtons() {
            const deleteLegionButtons = document.querySelectorAll('.btn-delete-legion');
            deleteLegionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const legionId = this.getAttribute('data-legion-id');
                    const legionName = this.getAttribute('data-legion-name');
                    const areaId = this.getAttribute('data-area-id');

                    currentDeleteOperation = {
                        type: 'legion',
                        id: legionId,
                        name: legionName,
                        areaId: areaId,
                        element: this.closest('li')
                    };

                    deleteConfirmMessage.textContent = `Are you sure you want to delete the legion "${legionName}"?`;
                    showModal(deleteConfirmModal);
                });
            });
        }

        // Initialize all button events
        initAddLegionButtons();
        initDeleteAreaButtons();
        initDeleteLegionButtons();

        // Modal close events
        modalCloseBtn.addEventListener('click', () => hideModal(modal));
        addAreaModalCloseBtn.addEventListener('click', () => hideModal(addAreaModal));
        deleteConfirmCloseBtn.addEventListener('click', () => hideModal(deleteConfirmModal));
        cancelDeleteBtn.addEventListener('click', () => hideModal(deleteConfirmModal));

        // Add Area Modal functionality
        btnAddArea.addEventListener('click', function() {
            showModal(addAreaModal);
            setTimeout(() => {
                document.getElementById('areaName').focus();
            }, 400);
        });

        // Close modals when clicking outside
        [modal, addAreaModal, deleteConfirmModal].forEach(modalElement => {
            modalElement.addEventListener('click', function(e) {
                if (e.target === modalElement) {
                    hideModal(modalElement);
                }
            });
        });

        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (modal.classList.contains('show')) hideModal(modal);
                if (addAreaModal.classList.contains('show')) hideModal(addAreaModal);
                if (deleteConfirmModal.classList.contains('show')) hideModal(deleteConfirmModal);
            }
        });

        // Confirm Delete functionality
        confirmDeleteBtn.addEventListener('click', function() {
            if (!currentDeleteOperation) return;

            const operation = currentDeleteOperation;
            
            if (operation.type === 'area') {
                // Delete area
                deleteArea(operation.id, operation.name, operation.element);
            } else if (operation.type === 'legion') {
                // Delete legion
                deleteLegion(operation.id, operation.name, operation.areaId, operation.element);
            }

            hideModal(deleteConfirmModal);
            currentDeleteOperation = null;
        });

        // Delete Area function
        function deleteArea(areaId, areaName, rowElement) {
    fetch(`<?= site_url('admin/delete_area') ?>`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ area_id: areaId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            rowElement.style.animation = 'fadeOut 0.3s ease forwards';
            setTimeout(() => {
                rowElement.remove();
                updateRowNumbers();
            }, 300);
            showAlert(successAlert, `Area "${areaName}" deleted successfully!`);
        } else {
            // 👇 Show backend error message
            showAlert(dangerAlert, `Error deleting area: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert(dangerAlert, `Unexpected error while deleting area: ${error.message}`);
    });
}

        // Delete Legion function
        function deleteLegion(legionId, legionName, areaId, listElement) {
            // Here you would normally make an AJAX call to your backend
            // For demo purposes, we'll just simulate the deletion
            
            fetch(`<?= site_url('admin/delete_legion') ?>`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    legion_id: legionId,
                    area_id: areaId
                })
            })
            .then(response => response.json())
            .then(data => {
    if (data.success) {
        listElement.style.animation = 'fadeOut 0.3s ease forwards';
        setTimeout(() => {
            listElement.remove();
            const parentUl = listElement.closest('ul');
            if (parentUl && parentUl.children.length === 0) {
                parentUl.closest('td').innerHTML = '<em>No legions assigned.</em>';
            }
        }, 300);
        showAlert(successAlert, `Legion "${legionName}" deleted successfully!`);
    } else {
        // ✅ Display specific error message from backend
        showAlert(dangerAlert, `Error deleting legion: ${data.message}`);
    }
})  .catch(error => {
                console.error('Error:', error);
                // For demo purposes, we'll still remove the element
                listElement.style.animation = 'fadeOut 0.3s ease forwards';
                setTimeout(() => {
                    listElement.remove();
                    
                    const parentUl = listElement.closest('ul');
                    if (parentUl && parentUl.children.length === 0) {
                        parentUl.closest('td').innerHTML = '<em>No legions assigned.</em>';
                    }
                }, 300);
                showAlert(successAlert, `Legion "${legionName}" deleted successfully! (Demo)`);
            });
        }

        // Update row numbers after deletion
        function updateRowNumbers() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                const numberCell = row.querySelector('td:first-child');
                if (numberCell) {
                    numberCell.textContent = index + 1;
                }
            });
        }

        // Add Legion Form submission
// Add Legion Form submission - UPDATED
form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // UPDATED SUCCESS MESSAGE TO INCLUDE PREFIX
            showAlert(successAlert, `Legion "${data.legion_name}" (${data.prefix || ''}) added successfully!`);
            
            // UPDATED TO PASS PREFIX PARAMETER
            addNewLegionToArea(data.area_id, data.legion_id, data.legion_name, data.prefix);
            
            form.reset();
            hideModal(modal);
        } else {
            showAlert(dangerAlert, 'Error adding legion: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert(dangerAlert, 'An error occurred while adding legion.');
    });
});
  //////////////////////////////////////////////////
// ADD THIS NEW FUNCTION - AJAX Loading
function loadAreasData() {
    console.log('Loading areas data...');
    
    fetch('<?= site_url('admin/get_areas_ajax') ?>', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Areas data received:', data);
        
        if (data.success) {
            renderAreasTable(data.data);
        } else {
            console.error('Failed to load areas:', data.message);
        }
    })
    .catch(error => {
        console.error('Error loading areas:', error);
    });
}

// ADD THIS NEW FUNCTION - Render Table
function renderAreasTable(areas) {
    const tbody = document.querySelector('#areasTableBody') || document.querySelector('tbody');
    
    if (!areas || areas.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" style="text-align: center; padding: 2rem;">
                    <em>No areas found.</em>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    areas.forEach((area, index) => {
        let legionsHtml = '';
        
        if (area.legions && area.legions.length > 0) {
            legionsHtml = '<ul style="margin:0; padding-left:20px;">';
            area.legions.forEach(legion => {
                legionsHtml += `
                    <li>
                        <div class="legion-item">
                            <div class="legion-info">
                                <span class="prefix-badge">${legion.prefix || ''}</span>
                                <span class="legion-name">${legion.name}</span>
                            </div>
                            <button class="btn-delete-legion" 
                                data-legion-id="${legion.id}" 
                                data-legion-name="${legion.name}"
                                data-legion-prefix="${legion.prefix || ''}"
                                data-area-id="${area.id}">
                                Delete
                            </button>
                        </div>
                    </li>
                `;
            });
            legionsHtml += '</ul>';
        } else {
            legionsHtml = '<em>No legions assigned.</em>';
        }
        
        html += `
            <tr>
                <td>${index + 1}</td>
                <td>${area.name}</td>
                <td>${legionsHtml}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-add-legion" 
                            data-area-id="${area.id}" 
                            data-area-name="${area.name}">
                            + Add Legion
                        </button>
                        <button class="btn-delete-area" 
                            data-area-id="${area.id}" 
                            data-area-name="${area.name}">
                            Delete Area
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    
    // Re-initialize button events
    initAddLegionButtons();
    initDeleteAreaButtons();
    initDeleteLegionButtons();
}







        // Add Area Form submission
        addAreaForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(addAreaForm);
            
            fetch(addAreaForm.action, {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(successAlert, `Area "${data.area_name}" added successfully!`);
                    
                    // Add new area to the table
                    addNewAreaToTable(data.area_name, data.area_id);
                    
                    addAreaForm.reset();
                    hideModal(addAreaModal);
                } else {
                    showAlert(dangerAlert, 'Error adding area: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert(dangerAlert, 'An error occurred while adding area.');
            });
        });

        // Function to add new legion to existing area
        // Function to add new legion to existing area - UPDATED
function addNewLegionToArea(areaId, legionId, legionName, prefix) {
    const areaRow = document.querySelector(`button[data-area-id="${areaId}"]`).closest('tr');
    const legionCell = areaRow.querySelector('td:nth-child(3)');
    
    let legionList = legionCell.querySelector('ul');
    
    // If no existing list, create one
    if (!legionList) {
        legionCell.innerHTML = '<ul style="margin:0; padding-left:20px;"></ul>';
        legionList = legionCell.querySelector('ul');
    }
    
    // Create new legion item WITH PREFIX
    const newLegionItem = document.createElement('li');
    newLegionItem.innerHTML = `
        <div class="legion-item">
            <div class="legion-info">
                <span class="prefix-badge">${prefix || ''}</span>
                <span class="legion-name">${legionName}</span>
            </div>
            <button class="btn-delete-legion" 
                data-legion-id="${legionId}" 
                data-legion-name="${legionName}"
                data-legion-prefix="${prefix || ''}"
                data-area-id="${areaId}">
                Delete
            </button>
        </div>
    `;
    
    legionList.appendChild(newLegionItem);
    
    // Re-initialize delete button events for the new button
    initDeleteLegionButtons();
}




        // Function to add new area to table
        function addNewAreaToTable(areaName, areaId) {
            const tbody = document.querySelector('tbody');
            const rowCount = tbody.children.length + 1;
            
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${rowCount}</td>
                <td>${areaName}</td>
                <td><em>No legions assigned.</em></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-add-legion" 
                            data-area-id="${areaId}" 
                            data-area-name="${areaName}">
                            + Add Legion
                        </button>
                        <button class="btn-delete-area" 
                            data-area-id="${areaId}" 
                            data-area-name="${areaName}">
                            Delete Area
                        </button>
                    </div>
                </td>
            `;
            
            tbody.appendChild(newRow);
            
            // Re-initialize button events for the new buttons
            loadAreasData();
            initAddLegionButtons();
            initDeleteAreaButtons();
        }

        // Add fadeOut animation for deletions
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(-20px);
                }
            }
        `;
        document.head.appendChild(style);
    });
</script>

</body>
</html>