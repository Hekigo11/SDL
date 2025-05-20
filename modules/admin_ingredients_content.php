<?php
require_once __DIR__ . '/../config.php';

// Check if user is admin
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

include("dbconi.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>    <style>
        .card {
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 15px;
            border: none;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
        }
        .main-content.expanded {
            margin-left: 70px;
        }
        /* Modal Enhancements */
        .modal-content {
            border: none;
            box-shadow: 0 5px 30px rgba(0,0,0,0.15);
            border-radius: 15px;
        }
        .modal-header {
            padding: 1.5rem;
            border-bottom: none;
            border-radius: 15px 15px 0 0;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark, #007bff) 100%);
            color: white;
        }
        .modal-header .close {
            color: white;
            text-shadow: none;
            opacity: 0.8;
        }
        .modal-header .close:hover {
            opacity: 1;
        }
        .modal-body {
            padding: 1.8rem;
        }
        .modal-footer {
            padding: 1.2rem 1.8rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid rgba(0,0,0,0.1);
            padding: 0.6rem 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.15);
            border-color: var(--accent);
        }
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1.2rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: var(--accent);
            border: none;
            box-shadow: 0 2px 6px rgba(var(--accent-rgb), 0.2);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(var(--accent-rgb), 0.3);
        }
        .btn-secondary {
            background: #f8f9fa;
            border: 1px solid rgba(0,0,0,0.1);
            color: #6c757d;
        }
        .btn-secondary:hover {
            background: #e9ecef;
        }
        .form-group label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        .form-text {
            color: #6c757d;
        }
        /* Table enhancements */
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
            background: #f8f9fa;
        }
        .table td {
            vertical-align: middle;
        }
        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }
        /* Tab Navigation */
        .nav-tabs {
            border-bottom: 2px solid #eee;
            margin-bottom: 1.5rem;
        }
        .nav-tabs .nav-link {
            font-weight: bold;
            color: var(--accent);
            transition: color 0.2s, background 0.2s, box-shadow 0.2s;
            border: none;
            margin: 0 10px;
            padding: 0.75rem 1.25rem;
        }
        .nav-tabs .nav-link:hover {
            color: var(--accent);
            background: #e9ecef;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            border-bottom: 2.5px var(--accent);
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            color: var(--accent);
            background: #e9ecef;
            border-bottom: 3px solid var(--accent);
            box-shadow: 0 4px 12px rgba(0,123,255,0.08);
        }
    </style>
</head>
<body>

<div class="main-content">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2>Manage Ingredients</h2>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" id="ingredients-tab" data-toggle="tab" href="#ingredients">Ingredients</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="types-tab" data-toggle="tab" href="#types">Ingredient Types</a>
            </li>
        </ul>

        <!-- Tab content -->
        <div class="tab-content" id="ingredientsTabsContent">
            <!-- Ingredients Tab -->
            <div class="tab-pane fade show active" id="ingredients" role="tabpanel" aria-labelledby="ingredients-tab">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h3>Ingredient List</h3>
                    </div>
                    <div class="col-md-6 text-right">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addIngredientModal">
                            <i class="fas fa-plus"></i> Add Ingredient
                        </button>
                    </div>
                </div>

                <!-- Ingredients Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Unit</th>
                                        <th>Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT i.*, t.type_name 
                                             FROM ingredients i 
                                             LEFT JOIN ingredient_types t ON i.type_id = t.type_id";
                                    $result = mysqli_query($dbc, $query);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['ingredient_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['unit']); ?></td>
                                        <td><?php echo htmlspecialchars($row['type_name']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary edit-ingredient" 
                                                    data-id="<?php echo $row['ingredient_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                                    data-unit="<?php echo htmlspecialchars($row['unit']); ?>"
                                                    data-type="<?php echo $row['type_id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-ingredient" 
                                                    data-id="<?php echo $row['ingredient_id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ingredient Types Tab -->
            <div class="tab-pane fade" id="types" role="tabpanel" aria-labelledby="types-tab">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h2>Manage Ingredient Types</h2>
                    </div>
                    <div class="col-md-6 text-right">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addTypeModal">
                            <i class="fas fa-plus"></i> Add Ingredient Type
                        </button>
                    </div>
                </div>

                <!-- Ingredient Types Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT * FROM ingredient_types ORDER BY type_name";
                                    $result = mysqli_query($dbc, $query);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['type_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['type_name']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary edit-type" 
                                                    data-id="<?php echo $row['type_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($row['type_name']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-type" 
                                                    data-id="<?php echo $row['type_id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Ingredient Modal -->
<div class="modal fade" id="addIngredientModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Ingredient</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addIngredientForm">
                <div class="modal-body">
                    <div id="ingredientModalAlert"></div>
                    <div class="form-group">
                        <label for="ingredientName">Ingredient Name</label>
                        <input type="text" class="form-control" id="ingredientName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="ingredientUnit">Unit of Measurement</label>
                        <input type="text" class="form-control" id="ingredientUnit" name="unit" 
                               placeholder="e.g. kg, g, oz, tbsp" required>
                    </div>
                    <div class="form-group">
                        <label for="ingredientType">Ingredient Type</label>
                        <select class="form-control" id="ingredientType" name="type_id" required>
                            <option value="">Select an ingredient type</option>
                            <?php
                            $query = "SELECT * FROM ingredient_types ORDER BY type_name";
                            $result = mysqli_query($dbc, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '<option value="' . $row['type_id'] . '">' . htmlspecialchars($row['type_name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Ingredient</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Ingredient Modal -->
<div class="modal fade" id="editIngredientModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Ingredient</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editIngredientForm">
                <div class="modal-body">
                    <div id="editIngredientModalAlert"></div>
                    <input type="hidden" id="editIngredientId" name="ingredient_id">
                    <div class="form-group">
                        <label for="editIngredientName">Ingredient Name</label>
                        <input type="text" class="form-control" id="editIngredientName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="editIngredientUnit">Unit of Measurement</label>
                        <input type="text" class="form-control" id="editIngredientUnit" name="unit" 
                               placeholder="e.g. kg, g, oz, tbsp" required>
                    </div>
                    <div class="form-group">
                        <label for="editIngredientType">Ingredient Type</label>
                        <select class="form-control" id="editIngredientType" name="type_id" required>
                            <option value="">Select an ingredient type</option>
                            <?php
                            $query = "SELECT * FROM ingredient_types ORDER BY type_name";
                            $result = mysqli_query($dbc, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '<option value="' . $row['type_id'] . '">' . htmlspecialchars($row['type_name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Ingredient</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Ingredient Type Modal -->
<div class="modal fade" id="addTypeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Ingredient Type</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addTypeForm">
                <div class="modal-body">
                    <div id="addTypeModalAlert"></div>
                    <div class="form-group">
                        <label for="typeName">Type Name</label>
                        <input type="text" class="form-control" id="typeName" name="type_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Ingredient Type Modal -->
<div class="modal fade" id="editTypeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Ingredient Type</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editTypeForm">
                <div class="modal-body">
                    <div id="editTypeModalAlert"></div>
                    <input type="hidden" id="editTypeId" name="type_id">
                    <div class="form-group">
                        <label for="editTypeName">Type Name</label>
                        <input type="text" class="form-control" id="editTypeName" name="type_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modals -->
<div class="modal fade" id="deleteIngredientModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="deleteIngredientModalAlert"></div>
                <p>Are you sure you want to delete this ingredient? This action cannot be undone.</p>
                <p class="text-danger">Warning: If this ingredient is used in any product recipes, 
                   deleting it may impact those recipes.</p>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="deleteIngredientId">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteIngredient">Delete</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteTypeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="deleteTypeModalAlert"></div>
                <p>Are you sure you want to delete this ingredient type? This action cannot be undone.</p>
                <p class="text-danger">Warning: If there are ingredients assigned to this type, 
                   deleting it will impact those ingredients.</p>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="deleteTypeId">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteType">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for handling Ingredients and Ingredient Types -->
<script>
$(document).ready(function() {
    // Toggle between tabs
    $('#ingredientsTabs a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    
    // Utility: showAlert for feedback
    function showAlert(type, message) {
        const alert = $(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>`);
        if ($('#alertContainer').length === 0) {
            $('.main-content .container-fluid').prepend('<div id="alertContainer"></div>');
        }
        $('#alertContainer').append(alert);
        setTimeout(() => alert.alert('close'), 4000);
    }
    
    // INGREDIENT SECTION
    // Open Edit Ingredient Modal
    $('.edit-ingredient').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const unit = $(this).data('unit');
        const typeId = $(this).data('type');
        
        $('#editIngredientId').val(id);
        $('#editIngredientName').val(name);
        $('#editIngredientUnit').val(unit);
        $('#editIngredientType').val(typeId);
        
        $('#editIngredientModal').modal('show');
    });
    
    // Open Delete Ingredient Modal
    $('.delete-ingredient').on('click', function() {
        const id = $(this).data('id');
        $('#deleteIngredientId').val(id);
        $('#deleteIngredientModal').modal('show');
    });
    
    // Add Ingredient Form Submit
    $('#addIngredientForm').on('submit', function(e) {
        e.preventDefault();
        // Clear previous modal alerts
        $('#ingredientModalAlert').empty();
        $.ajax({
            url: 'admin_ingredient_actions.php',
            type: 'POST',
            dataType: 'text',
            data: {
                action: 'add_ingredient',
                name: $('#ingredientName').val(),
                unit: $('#ingredientUnit').val(),
                type_id: $('#ingredientType').val()
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        $('#addIngredientModal').modal('hide');
                        window.location.reload();
                    } else {
                        // Show error in modal
                        const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Error adding ingredient: ${result.message}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>`);
                        $('#ingredientModalAlert').append(alert);
                    }
                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                    const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Server returned an invalid response. Please check the console for details.
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>`);
                    $('#ingredientModalAlert').append(alert);
                    console.log('Server response:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Server error occurred: ${error}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>`);
                $('#ingredientModalAlert').append(alert);
            }
        });
    });
    
    // Edit Ingredient Form Submit
    $('#editIngredientForm').on('submit', function(e) {
        e.preventDefault();
        // Clear previous modal alerts before showing a new one
        $('#editIngredientModalAlert').empty();
        $.ajax({
            url: 'admin_ingredient_actions.php',
            type: 'POST',
            dataType: 'text',
            data: {
                action: 'edit_ingredient',
                ingredient_id: $('#editIngredientId').val(),
                name: $('#editIngredientName').val(),
                unit: $('#editIngredientUnit').val(),
                type_id: $('#editIngredientType').val()
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        $('#editIngredientModal').modal('hide');
                        window.location.reload();
                    } else {
                        // Show error in modal
                        $('#editIngredientModalAlert').empty();
                        const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Error updating ingredient: ${result.message}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>`);
                        $('#editIngredientModalAlert').append(alert);
                    }
                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                    $('#editIngredientModalAlert').empty();
                    const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Server returned an invalid response. Please check the console for details.
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>`);
                    $('#editIngredientModalAlert').append(alert);
                    console.log('Server response:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                $('#editIngredientModalAlert').empty();
                const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Server error occurred: ${error}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>`);
                $('#editIngredientModalAlert').append(alert);
            }
        });
    });
    
    // Delete Ingredient Confirmation
    $('#confirmDeleteIngredient').on('click', function() {
        // Clear previous modal alerts
        $('#deleteIngredientModalAlert').empty();
        const id = $('#deleteIngredientId').val();
        $.ajax({
            url: 'admin_ingredient_actions.php',
            type: 'POST',
            dataType: 'text',
            data: {
                action: 'delete_ingredient',
                ingredient_id: id
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        $('#deleteIngredientModal').modal('hide');
                        window.location.reload();
                    } else {
                        // Show error in modal
                        const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Error deleting ingredient: ${result.message}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>`);
                        $('#deleteIngredientModalAlert').append(alert);
                    }
                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                    const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Server returned an invalid response. Please check the console for details.
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>`);
                    $('#deleteIngredientModalAlert').append(alert);
                    console.log('Server response:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Server error occurred: ${error}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>`);
                $('#deleteIngredientModalAlert').append(alert);
            }
        });
    });
    
    // INGREDIENT TYPE SECTION
    // Open Edit Type Modal
    $('.edit-type').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        $('#editTypeId').val(id);
        $('#editTypeName').val(name);
        
        $('#editTypeModal').modal('show');
    });
    
    // Open Delete Type Modal
    $('.delete-type').on('click', function() {
        const id = $(this).data('id');
        $('#deleteTypeId').val(id);
        $('#deleteTypeModal').modal('show');
    });
    
    // Add Type Form Submit
    $('#addTypeForm').on('submit', function(e) {
        e.preventDefault();
        $('#addTypeModalAlert').empty();
        const $form = $(this);
        $.ajax({
            url: 'admin_ingredient_actions.php',
            type: 'POST',
            dataType: 'json',
            data: $form.serialize() + '&action=add_type',
            success: function(result) {
                if (result.status === 'success' && result.type) {
                    // Add the new type to the table without reload
                    const type = result.type;
                    const newRow = `<tr>
                        <td>${type.id}</td>
                        <td>${type.name}</td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-type" data-id="${type.id}" data-name="${type.name}"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger delete-type" data-id="${type.id}"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>`;
                    // Find the types table and append
                    $('#types table tbody').append(newRow);
                    $('#addTypeModal').modal('hide');
                    $form[0].reset();
                    showAlert('success', 'Ingredient type added!');
                } else {
                    const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        ${result.message || 'Error adding ingredient type.'}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>`);
                    $('#addTypeModalAlert').append(alert);
                }
            },
            error: function(xhr, status, error) {
                const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Server error: ${error}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>`);
                $('#addTypeModalAlert').append(alert);
            }
        });
    });
    
    // Efficient AJAX handler for edit type
    $('#editTypeForm').on('submit', function(e) {
        e.preventDefault();
        $('#editTypeModalAlert').empty();
        const $form = $(this);
        $.ajax({
            url: 'admin_ingredient_actions.php',
            type: 'POST',
            dataType: 'json',
            data: $form.serialize() + '&action=edit_type',
            success: function(result) {
                if (result.status === 'success' && result.type) {
                    // Update the row in the table without reload
                    const type = result.type;
                    const $row = $(`button.edit-type[data-id="${type.id}"]`).closest('tr');
                    $row.find('td').eq(1).text(type.name);
                    $row.find('.edit-type').data('name', type.name);
                    $('#editTypeModal').modal('hide');
                    showAlert('success', 'Ingredient type updated!');
                } else {
                    const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        ${result.message || 'Error updating ingredient type.'}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>`);
                    $('#editTypeModalAlert').append(alert);
                }
            },
            error: function(xhr, status, error) {
                const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Server error: ${error}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>`);
                $('#editTypeModalAlert').append(alert);
            }
        });
    });
    
    // Delete Type Confirmation
    $('#confirmDeleteType').on('click', function() {
        $('#deleteTypeModalAlert').empty();
        const id = $('#deleteTypeId').val();
        $.ajax({
            url: 'admin_ingredient_actions.php',
            type: 'POST',
            dataType: 'text',
            data: {
                action: 'delete_type',
                type_id: id
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        $('#deleteTypeModal').modal('hide');
                        window.location.reload();
                    } else {
                        const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Error deleting ingredient type: ${result.message}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>`);
                        $('#deleteTypeModalAlert').append(alert);
                    }
                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                    const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Server returned an invalid response. Please check the console for details.
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>`);
                    $('#deleteTypeModalAlert').append(alert);
                    console.log('Server response:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Server error occurred: ${error}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>`);
                $('#deleteTypeModalAlert').append(alert);
            }
        });
    });
});
</script>

</body>
</html>