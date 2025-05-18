<?php
require_once __DIR__ . '/../config.php';

// Check if user is admin
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

include("dbconi.php");

// Get all packages
$query = "SELECT * FROM packages ORDER BY base_price";
$result = mysqli_query($dbc, $query);

// Check if we were able to retrieve packages
if (!$result) {
    echo '<div class="alert alert-danger">Failed to retrieve packages: ' . mysqli_error($dbc) . '</div>';
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Catering Management</h2>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-secondary mr-2" data-toggle="modal" data-target="#manageCategoriesModal">
                <i class="fas fa-list"></i> Manage Categories
            </button>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addPackageModal">
                <i class="fas fa-plus"></i> Add Package
            </button>
        </div>
    </div>

    <!-- Packages Management Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Catering Packages</h5>
            <small class="text-muted">Manage your catering package offerings</small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="packagesTable">
                    <thead>
                        <tr>
                            <th>Package Name</th>
                            <th>Base Price (₱)</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Menu Requirements</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                // Reset requirements array for each package
                                $requirements = [];
                                // Get menu requirements for this package
                                $req_query = "SELECT pp.*, c.category_name 
                                            FROM package_products pp 
                                            JOIN categories c ON pp.category_id = c.category_id
                                            WHERE pp.package_id = " . $row['package_id'];
                                $req_result = mysqli_query($dbc, $req_query);
                                
                                if ($req_result) {
                                    while($req = mysqli_fetch_assoc($req_result)) {
                                        $requirements[] = $req['category_name'] . ': ' . $req['amount'];
                                    }
                                }
                                
                                echo '<tr>
                                    <td>' . htmlspecialchars($row['name']) . '</td>
                                    <td>' . number_format($row['base_price'], 2) . '</td>
                                    <td>' . htmlspecialchars($row['description']) . '</td>
                                    <td>' . ($row['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>') . '</td>
                                    <td>' . (!empty($requirements) ? implode('<br>', $requirements) : '<em>None</em>') . '</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-package" 
                                            data-id="' . $row['package_id'] . '"
                                            data-name="' . htmlspecialchars($row['name']) . '"
                                            data-price="' . $row['base_price'] . '"
                                            data-description="' . htmlspecialchars($row['description']) . '"
                                            data-active="' . $row['is_active'] . '">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm ' . ($row['is_active'] ? 'btn-danger' : 'btn-success') . ' toggle-status"
                                            data-id="' . $row['package_id'] . '"
                                            data-status="' . $row['is_active'] . '">
                                            <i class="fas ' . ($row['is_active'] ? 'fa-toggle-off' : 'fa-toggle-on') . '"></i>
                                        </button>
                                    </td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center">No packages found.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- End of Packages Management Section -->

    <!-- The Menu Categories table section has been moved to another page/section. Only the Manage Categories modal is accessible from here. -->
</div>

<!-- Add Package Modal -->
<div class="modal fade" id="addPackageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Catering Package</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addPackageForm">
                <div class="modal-body">
                    <div id="addPackageAlert"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="packageName">Package Name</label>
                                <input type="text" class="form-control" id="packageName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="basePrice">Base Price (₱ per person)</label>
                                <input type="number" class="form-control" id="basePrice" name="base_price" min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="packageDescription">Description</label>
                        <textarea class="form-control" id="packageDescription" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Menu Requirements</label>
                        <div class="alert alert-light">
                            <small class="text-muted">Menu requirements specify how many items from each category customers must select when ordering this package.</small>
                        </div>
                        
                        <div id="menuRequirements">
                            <?php
                            // Get all categories for menu requirements
                            $cat_query = "SELECT * FROM categories ORDER BY category_name";
                            $cat_result = mysqli_query($dbc, $cat_query);
                            
                            if ($cat_result && mysqli_num_rows($cat_result) > 0) {
                                while($cat = mysqli_fetch_assoc($cat_result)) {
                                    echo '<div class="form-row align-items-center mb-2">
                                        <div class="col">
                                            <label class="mb-0">' . htmlspecialchars($cat['category_name']) . '</label>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="requirements[' . $cat['category_id'] . ']" min="0" value="0">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">items</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="isActive" name="is_active" checked>
                            <label class="custom-control-label" for="isActive">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Package</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Package Modal -->
<div class="modal fade" id="editPackageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Catering Package</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editPackageForm">
                <div class="modal-body">
                    <div id="editPackageAlert"></div>
                    <input type="hidden" id="editPackageId" name="package_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editPackageName">Package Name</label>
                                <input type="text" class="form-control" id="editPackageName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editBasePrice">Base Price (₱ per person)</label>
                                <input type="number" class="form-control" id="editBasePrice" name="base_price" min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editPackageDescription">Description</label>
                        <textarea class="form-control" id="editPackageDescription" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Menu Requirements</label>
                        <div class="alert alert-light">
                            <small class="text-muted">Menu requirements specify how many items from each category customers must select when ordering this package.</small>
                        </div>
                        
                        <div id="editMenuRequirements">
                            <?php
                            // Reset pointer to the beginning of the result set
                            if ($cat_result) {
                                mysqli_data_seek($cat_result, 0);
                                while($cat = mysqli_fetch_assoc($cat_result)) {
                                    echo '<div class="form-row align-items-center mb-2">
                                        <div class="col">
                                            <label class="mb-0">' . htmlspecialchars($cat['category_name']) . '</label>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="edit_requirements[' . $cat['category_id'] . ']" id="edit_req_' . $cat['category_id'] . '" min="0" value="0">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">items</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="editIsActive" name="is_active">
                            <label class="custom-control-label" for="editIsActive">Active</label>
                        </div>
                    </div>
                </div>                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger" id="deletePackageBtn">Delete Package</button>
                    <div>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Package</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manage Categories Modal -->
<div class="modal fade" id="manageCategoriesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Categories</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="manageCategoriesAlert"></div>
                
                <!-- Add Category Form -->
                <form id="addCategoryForm" class="mb-4">
                    <div class="form-group">
                        <label for="categoryName">New Category Name</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="categoryName" name="category_name" placeholder="Enter category name" required>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">Add</button>
                            </div>
                        </div>
                    </div>
                </form>
                
                <hr>
                
                <!-- Categories List -->
                <h6 class="mb-3">Current Categories</h6>
                <div id="categoriesList">
                    <?php
                    $cat_query = "SELECT * FROM categories ORDER BY category_name";
                    $cat_result = mysqli_query($dbc, $cat_query);
                    if ($cat_result && mysqli_num_rows($cat_result) > 0) {
                        while($cat = mysqli_fetch_assoc($cat_result)) {
                            // Get product count for this category
                            $count_query = "SELECT COUNT(*) as product_count FROM products WHERE prod_cat_id = " . $cat['category_id'];
                            $count_result = mysqli_query($dbc, $count_query);
                            $product_count = 0;
                            if ($count_result) {
                                $count_row = mysqli_fetch_assoc($count_result);
                                $product_count = $count_row['product_count'];
                            }
                            echo '<div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                                <span>' . htmlspecialchars($cat['category_name']) . ' <small class="text-muted">(' . $product_count . ' products)</small></span>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-primary modal-edit-category" data-id="' . $cat['category_id'] . '" data-name="' . htmlspecialchars($cat['category_name']) . '">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger modal-delete-category" ' . ($product_count > 0 ? 'disabled' : '') . ' data-id="' . $cat['category_id'] . '" data-name="' . htmlspecialchars($cat['category_name']) . '">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>';
                        }
                    } else {
                        echo '<p class="text-muted">No categories found.</p>';
                    }
                    ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editCategoryForm">
                <div class="modal-body">
                    <div id="editCategoryAlert"></div>
                    <input type="hidden" id="editCategoryId" name="category_id">
                    <div class="form-group">
                        <label for="editCategoryName">Category Name</label>
                        <input type="text" class="form-control" id="editCategoryName" name="category_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTables
    $('#packagesTable').DataTable({
        "order": [[0, "asc"]],
        "columnDefs": [
            { "orderable": false, "targets": [4, 5] }
        ],
        "language": {
            "search": "Filter packages: "
        }
    });
    
    // Add Package form submission
    $('#addPackageForm').submit(function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const formData = form.serialize();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');
        
        $.ajax({
            url: 'admin_add_package.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response === 'success') {
                    $('#addPackageAlert').html(`
                        <div class="alert alert-success alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            Package added successfully! Reloading page...
                        </div>
                    `);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    $('#addPackageAlert').html(`
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            Error: ${response}
                        </div>
                    `);
                }
            },
            error: function() {
                $('#addPackageAlert').html(`
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        Failed to add package. Please try again.
                    </div>
                `);
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('Add Package');
            }
        });
    });
    
    // Edit Package - Show modal with data
    $(document).on('click', '.edit-package', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const price = $(this).data('price');
        const description = $(this).data('description');
        const active = $(this).data('active');
        
        $('#editPackageId').val(id);
        $('#editPackageName').val(name);
        $('#editBasePrice').val(price);
        $('#editPackageDescription').val(description);
        $('#editIsActive').prop('checked', active == 1);
        
        // Reset all requirements to 0
        $('#editMenuRequirements input[type="number"]').val(0);
        
        // Load package requirements from database
        $.ajax({
            url: 'admin_get_package_requirements.php',
            method: 'GET',
            data: { package_id: id },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    // Set requirement values
                    data.requirements.forEach(function(req) {
                        $(`#edit_req_${req.category_id}`).val(req.amount);
                    });
                }
            }
        });
        
        $('#editPackageModal').modal('show');
    });
    
    // Edit Package form submission
    $('#editPackageForm').submit(function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const formData = form.serialize();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        
        $.ajax({
            url: 'admin_update_package.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response === 'success') {
                    $('#editPackageAlert').html(`
                        <div class="alert alert-success alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            Package updated successfully! Reloading page...
                        </div>
                    `);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    $('#editPackageAlert').html(`
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            Error: ${response}
                        </div>
                    `);
                }
            },
            error: function() {
                $('#editPackageAlert').html(`
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        Failed to update package. Please try again.
                    </div>
                `);
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('Update Package');
            }
        });
    });
    
    // Toggle package status
    $(document).on('click', '.toggle-status', function() {
        const btn = $(this);
        const id = btn.data('id');
        const currentStatus = btn.data('status');
        const newStatus = currentStatus == 1 ? 0 : 1;
        const action = currentStatus == 1 ? 'deactivate' : 'activate';
        
        if (confirm(`Are you sure you want to ${action} this package?`)) {
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: 'admin_toggle_package_status.php',
                method: 'POST',
                data: {
                    package_id: id,
                    status: newStatus
                },
                success: function(response) {
                    if (response === 'success') {
                        location.reload();
                    } else {
                        alert('Error: ' + response);
                        btn.prop('disabled', false).html(`<i class="fas fa-${currentStatus == 1 ? 'toggle-off' : 'toggle-on'}"></i>`);
                    }
                },
                error: function() {
                    alert('Failed to update package status. Please try again.');
                    btn.prop('disabled', false).html(`<i class="fas fa-${currentStatus == 1 ? 'toggle-off' : 'toggle-on'}"></i>`);
                }
            });
        }
    });
    
    // Add Category form submission
    $('#addCategoryForm').submit(function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const formData = form.serialize();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: 'admin_add_category.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response === 'success') {
                    $('#manageCategoriesAlert').html(`
                        <div class="alert alert-success alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            Category added successfully! Reloading page...
                        </div>
                    `);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    $('#manageCategoriesAlert').html(`
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            Error: ${response}
                        </div>
                    `);
                }
            },
            error: function() {
                $('#manageCategoriesAlert').html(`
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        Failed to add category. Please try again.
                    </div>
                `);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('Add');
                $('#categoryName').val('');
            }
        });
    });
    
    // Edit Category - Open modal
    $(document).on('click', '.edit-category, .modal-edit-category', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        $('#editCategoryId').val(id);
        $('#editCategoryName').val(name);
        
        $('#editCategoryModal').modal('show');
        
        // If the manage categories modal is open, hide it temporarily
        if ($('#manageCategoriesModal').hasClass('show')) {
            $('#manageCategoriesModal').modal('hide');
            $('#editCategoryModal').on('hidden.bs.modal', function() {
                $('#manageCategoriesModal').modal('show');
                $('#editCategoryModal').off('hidden.bs.modal');
            });
        }
    });
    
    // Edit Category form submission
    $('#editCategoryForm').submit(function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const formData = form.serialize();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        
        $.ajax({
            url: 'admin_update_category.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response === 'success') {
                    $('#editCategoryAlert').html(`
                        <div class="alert alert-success alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            Category updated successfully! Reloading page...
                        </div>
                    `);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    $('#editCategoryAlert').html(`
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            Error: ${response}
                        </div>
                    `);
                }
            },
            error: function() {
                $('#editCategoryAlert').html(`
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        Failed to update category. Please try again.
                    </div>
                `);
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('Update');
            }
        });
    });
      // Delete Category
    $(document).on('click', '.delete-category, .modal-delete-category', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        if (confirm(`Are you sure you want to delete the category "${name}"? This cannot be undone.`)) {
            $.ajax({
                url: 'admin_delete_category.php',
                method: 'POST',
                data: {
                    category_id: id
                },
                success: function(response) {
                    if (response === 'success') {
                        location.reload();
                    } else {
                        alert('Error: ' + response);
                    }
                },
                error: function() {
                    alert('Failed to delete category. Please try again.');
                }
            });
        }
    });

    // Delete Package
    $(document).on('click', '#deletePackageBtn', function() {
        const id = $('#editPackageId').val();
        const name = $('#editPackageName').val();
        
        if (confirm(`Are you sure you want to delete the package "${name}"? This action cannot be undone.`)) {
            $.ajax({
                url: 'admin_delete_package.php',
                method: 'POST',
                data: {
                    package_id: id
                },
                success: function(response) {
                    if (response === 'success') {
                        $('#editPackageAlert').html(`
                            <div class="alert alert-success alert-dismissible fade show">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                Package deleted successfully! Reloading page...
                            </div>
                        `);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $('#editPackageAlert').html(`
                            <div class="alert alert-danger alert-dismissible fade show">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                Error: ${response}
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#editPackageAlert').html(`
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        Failed to delete package. Please try again.
                        </div>
                    `);
                }
            });
        }
    });
});
</script>
