<?php
require_once __DIR__ . '/../config.php';

// Check if user is admin
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

include("dbconi.php");

// Helper function to render ingredient options with groups
function renderIngredientOptions($dbc) {
    $ing_query = "SELECT i.*, t.type_name 
                FROM ingredients i 
                JOIN ingredient_types t ON i.type_id = t.type_id 
                ORDER BY t.type_name, i.name";
    $ing_result = mysqli_query($dbc, $ing_query);
    $current_type = '';
    
    echo '<option value="">Select Ingredient</option>';
    while ($ing = mysqli_fetch_assoc($ing_result)) {
        if ($current_type != $ing['type_name']) {
            if ($current_type != '') {
                echo "</optgroup>";
            }
            echo "<optgroup label='" . htmlspecialchars($ing['type_name']) . "'>";
            $current_type = $ing['type_name'];
        }
        echo "<option value='" . $ing['ingredient_id'] . "' data-unit='" . htmlspecialchars($ing['unit']) . "'>" 
             . htmlspecialchars($ing['name']) . "</option>";
    }
    if ($current_type != '') {
        echo "</optgroup>";
    }
}

// Add custom styles for the ingredient dropdown
// This line will be added in the JavaScript section only
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Manage Products</h2>
        </div>
        <div class="col-md-6 text-right">
            <button class="btn btn-primary" data-toggle="modal" data-target="#addProductModal">
                <i class="fas fa-plus"></i> Add Product
            </button>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT p.*, c.category_name 
                                 FROM products p 
                                 LEFT JOIN categories c ON p.prod_cat_id = c.category_id";
                        $result = mysqli_query($dbc, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td>
                                <img src="<?php echo BASE_URL; ?>/images/Products/<?php echo htmlspecialchars($row['prod_img']); ?>" 
                                     class="product-image" 
                                     alt="<?php echo htmlspecialchars($row['prod_name']); ?>">
                            </td>
                            <td><?php echo htmlspecialchars($row['prod_name']); ?></td>
                            <td>₱<?php echo number_format($row['prod_price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['prod_desc']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-product" 
                                        data-id="<?php echo $row['product_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($row['prod_name']); ?>"
                                        data-price="<?php echo $row['prod_price']; ?>"
                                        data-category="<?php echo $row['prod_cat_id']; ?>"
                                        data-desc="<?php echo htmlspecialchars($row['prod_desc']); ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-product" 
                                        data-id="<?php echo $row['product_id']; ?>">
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

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="max-width: 900px; min-width: 700px;">
        <div class="modal-content" style="border-radius: 30px;">
            <div class="modal-header text-center position-center" style="background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark, #007bff) 100%); border-radius: 30px 30px 0 0;">
                <h5 class="modal-title text-light" style="font-weight: 600;">Add New Product</h5>
                <button type="button" class="close" data-dismiss="modal" style="opacity: 1;">
                    <span aria-hidden="true" style="color: white; text-shadow: none;">&times;</span>
                </button>
            </div>
            <form id="addProductForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <!-- Left: Product Details -->
                        <div class="col-md-6 pr-md-4 border-right">
                            <h5 class="mb-3 font-weight-bold">Product Details</h5>
                            <div class="form-group">
                                <label>Product Name</label>
                                <input type="text" class="form-control" name="prod_name" required>
                            </div>
                            <div class="form-group">
                                <label>Price</label>
                                <input type="number" class="form-control" name="prod_price" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select class="form-control custom-select" name="prod_cat_id" required>
                                    <?php
                                    $cat_query = "SELECT * FROM categories";
                                    $cat_result = mysqli_query($dbc, $cat_query);
                                    while ($cat = mysqli_fetch_assoc($cat_result)) {
                                        echo "<option value='" . $cat['category_id'] . "'>" . htmlspecialchars($cat['category_name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" name="prod_desc" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Product Image</label>
                                <input type="file" class="form-control-file" name="prod_img" accept="image/*" required>
                            </div>
                        </div>
                        <!-- Right: Ingredients Checklist -->
                        <div class="col-md-6 pl-md-4">
                            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                                <div class="card-header bg-light border-0" style="border-radius: 12px 12px 0 0; font-weight: 600; font-size: 1.1rem;">
                                    <i class="fas fa-clipboard-list mr-2"></i>Ingredients Checklist
                                </div>
                                <div class="card-body pb-2 pt-3">
                                    <div id="addProductIngredientModalAlert"></div>
                                    <div class="form-row align-items-end mb-3">
                                        <div class="col-12 mb-2">
                                            <div class="ingredient-search-container position-relative">
                                                <input type="text" class="form-control" id="ingredientSearch" placeholder="Search for ingredients...">
                                                <div id="ingredientSearchResults" class="dropdown-menu w-100" style="max-height: 250px; overflow-y: auto;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-7">
                                            <label for="addProductIngredientQuantity" class="mb-1">Quantity</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="addProductIngredientQuantity" placeholder="Quantity" min="0.01" step="0.01">
                                                <div class="input-group-append">
                                                    <span class="input-group-text" id="addProductIngredientUnit"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-5 text-right">
                                            <button type="button" class="btn btn-success btn-block mt-2" id="addProductIngredientToList" style="border-radius: 8px;">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0" id="newProductIngredientsTable" style="background: #fff;">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Ingredient</th>
                                                    <th>Quantity</th>
                                                    <th>Unit</th>
                                                    <th style="width: 90px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 30px;">
            <div class="modal-header text-center position-center" style="background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark, #007bff) 100%); border-radius: 30px 30px 0 0;">
                <h5 class="modal-title text-light" style="font-weight: 600;">Edit Product</h5>
                <button type="button" class="close" data-dismiss="modal" style="opacity: 1;">
                    <span aria-hidden="true" style="color: white; text-shadow: none;">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Product Edit Tabs -->
                <ul class="nav nav-tabs mb-3" id="productEditTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="details-tab" data-toggle="tab" href="#details" role="tab">Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="ingredients-tab" data-toggle="tab" href="#ingredients" role="tab">Ingredients</a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Details Tab -->
                    <div class="tab-pane fade show active" id="details" role="tabpanel">
                        <form id="editProductForm" enctype="multipart/form-data">
                            <input type="hidden" name="product_id">
                            <div class="form-group">
                                <div class="d-flex align-content-center justify-content-between">
                                    <label>Current Image</label>
                                    <img id="currentProductImage" src="" alt="Current Product" class="img-fluid mb-2" style="max-height: 100px;">
                                </div>
                                <input type="file" class="form-control-file" name="prod_img" accept="image/*">
                                <small class="form-text text-muted">Leave empty to keep current image</small>
                            </div>
                            <div class="form-group">
                                <label>Product Name</label>
                                <input type="text" class="form-control" name="prod_name" required>
                            </div>
                            <div class="form-group">
                                <label>Price</label>
                                <input type="number" class="form-control" name="prod_price" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select class="form-control" name="prod_cat_id" required>
                                    <?php
                                    $cat_query = "SELECT * FROM categories";
                                    $cat_result = mysqli_query($dbc, $cat_query);
                                    while ($cat = mysqli_fetch_assoc($cat_result)) {
                                        echo "<option value='" . $cat['category_id'] . "'>" . 
                                             htmlspecialchars($cat['category_name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" name="prod_desc" rows="3" required></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>

                    <!-- Ingredients Tab -->
                    <div class="tab-pane fade" id="ingredients" role="tabpanel">
                        <div class="mb-3">
                            <button class="btn btn-sm btn-primary" id="addIngredientToProduct">
                                <i class="fas fa-plus"></i> Add Ingredient
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm" id="productIngredientsTable">
                                <thead>
                                    <tr>
                                        <th>Ingredient</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Ingredient Modal -->
<div class="modal fade" id="addProductIngredientModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content" style="border-radius: 30px;">
            <div class="modal-header text-center position-center" style="background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark, #007bff) 100%); border-radius: 30px 30px 0 0;">
                <h5 class="modal-title text-light" style="font-weight: 600;">Add Ingredient</h5>
                <button type="button" class="close" data-dismiss="modal" style="opacity: 1;">
                    <span aria-hidden="true" style="color: white; text-shadow: none;">&times;</span>
                </button>
            </div>
            <form id="addProductIngredientForm">
                <div class="modal-body">
                    <input type="hidden" name="product_id">
                    <div class="form-group">
                        <label>Ingredient</label>
                        <select class="form-control ingredient-select" name="ingredient_id" required>
                            <?php renderIngredientOptions($dbc); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quantity</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="quantity" step="0.01" required>
                            <div class="input-group-append">
                                <span class="input-group-text ingredient-unit"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Ingredient Modal -->
<div class="modal fade" id="editProductIngredientModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content" style="border-radius: 30px;">
            <div class="modal-header text-center position-center" style="background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark, #007bff) 100%); border-radius: 30px 30px 0 0;">
                <h5 class="modal-title text-light" style="font-weight: 600;">Edit Ingredient</h5>
                <button type="button" class="close" data-dismiss="modal" style="opacity: 1;">
                    <span aria-hidden="true" style="color: white; text-shadow: none;">&times;</span>
                </button>
            </div>
            <form id="editProductIngredientForm">
                <div class="modal-body">
                    <div id="editProductIngredientModalAlert"></div>
                    <input type="hidden" name="product_id">
                    <input type="hidden" name="original_ingredient_id">
                    <div class="form-group">
                        <label>Ingredient</label>
                        <select class="form-control ingredient-select" name="ingredient_id" required>
                            <?php renderIngredientOptions($dbc); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quantity</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="quantity" step="0.01" required>
                            <div class="input-group-append">
                                <span class="input-group-text ingredient-unit"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize ingredients list
    let newProductIngredients = [];
    
    // Add custom styles for the ingredient dropdown
    $('<style>').text(`
        #ingredientSearchResults {
            max-height: 300px;
            overflow-y: auto;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border: 1px solid #ddd;
            border-radius: 0.25rem;
            z-index: 1050;
            background: white;
        }
        #ingredientSearchResults.show {
            display: block;
        }
        .ingredient-item {
            cursor: pointer;
        }
        .ingredient-item:hover {
            background-color: #f5f5f5;
        }
    `).appendTo('head');
    
    // Simple ingredient search functionality
    $('#ingredientSearch').on('input', function() {
        let term = $(this).val().trim();
        let $results = $('#ingredientSearchResults');
        
        if (!term) {
            $results.empty().removeClass('show');
            return;
        }
        
        // Load the results panel with a loading message
        $results.html('<div class="p-3 text-center"><i class="fas fa-spinner fa-spin mr-2"></i>Loading...</div>').addClass('show');
        
        // Fetch ingredients that match the search term
        $.get('admin_get_ingredients.php', { term: term }, function(data) {
            $results.html(data).addClass('show');
            
            // Add click handler for ingredient selection
            $results.find('.ingredient-item').on('click', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const unit = $(this).data('unit');
                
                // Update the input fields
                $('#ingredientSearch').val(name);
                $('#addProductIngredientUnit').text(unit);
                $('#ingredientSearch').data('selected-id', id);
                
                // Hide the results
                $results.removeClass('show');
                
                // Focus quantity field
                $('#addProductIngredientQuantity').focus();
            });
        }).fail(function() {
            $results.html('<div class="p-3 text-center text-danger"><i class="fas fa-exclamation-circle mr-2"></i>Error loading ingredients</div>').addClass('show');
        });
    });
    
    // Close results dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.ingredient-search-container').length) {
            $('#ingredientSearchResults').removeClass('show');
        }
    });
    
    // Add ingredient to list
    $('#addProductIngredientToList').click(function() {
        const selectedId = $('#ingredientSearch').data('selected-id');
        const ingredientName = $('#ingredientSearch').val();
        const quantity = $('#addProductIngredientQuantity').val();
        const unit = $('#addProductIngredientUnit').text();
        
        if (!selectedId || !quantity || quantity <= 0) {
            $('#addProductIngredientModalAlert').html(`
                <div class="alert alert-danger">
                    Please select an ingredient and enter a valid quantity.
                </div>
            `);
            return;
        }
        
        // Check for duplicate
        if (newProductIngredients.some(i => i.ingredient_id == selectedId)) {
            $('#addProductIngredientModalAlert').html(`
                <div class="alert alert-danger">
                    This ingredient is already added.
                </div>
            `);
            return;
        }
        
        // Add to list
        newProductIngredients.push({
            ingredient_id: selectedId,
            ingredient_name: ingredientName,
            quantity: quantity,
            unit: unit
        });
        
        // Update table
        renderNewProductIngredients();
        
        // Clear inputs
        $('#ingredientSearch').val('').data('selected-id', '');
        $('#addProductIngredientQuantity').val('');
        $('#addProductIngredientUnit').text('');
        $('#addProductIngredientModalAlert').empty();
    });
    
    // Render ingredients table
    function renderNewProductIngredients() {
        let tbody = '';
        newProductIngredients.forEach((item, idx) => {
            tbody += `<tr>
                <td>${item.ingredient_name}</td>
                <td>${item.quantity}</td>
                <td>${item.unit}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary edit-new-product-ingredient" data-idx="${idx}"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-sm btn-danger delete-new-product-ingredient" data-idx="${idx}"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
        $('#newProductIngredientsTable tbody').html(tbody);
    }
    
    // Edit ingredient in new product
    $(document).on('click', '.edit-new-product-ingredient', function() {
        const idx = $(this).data('idx');
        const item = newProductIngredients[idx];
        $('#ingredientSearch').val(item.ingredient_name).data('selected-id', item.ingredient_id);
        $('#addProductIngredientQuantity').val(item.quantity);
        $('#addProductIngredientUnit').text(item.unit);
        // Remove so re-adding will update
        newProductIngredients.splice(idx, 1);
        renderNewProductIngredients();
    });
    
    // Delete ingredient in new product
    $(document).on('click', '.delete-new-product-ingredient', function() {
        const idx = $(this).data('idx');
        newProductIngredients.splice(idx, 1);
        renderNewProductIngredients();
    });
    
    // Add Product Form Submit
    $('#addProductForm').submit(function(e) {
        e.preventDefault();
        
        // Remove any previous hidden fields
        $(this).find('input[name^="ingredients["]').remove();
        
        // Add ingredients to the form
        newProductIngredients.forEach((item, idx) => {
            $(this).append(`<input type="hidden" name="ingredients[${idx}][ingredient_id]" value="${item.ingredient_id}">`);
            $(this).append(`<input type="hidden" name="ingredients[${idx}][quantity]" value="${item.quantity}">`);
        });
        
        // Create FormData and submit
        var formData = new FormData(this);
        
        $.ajax({
            url: 'admin_product_save.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response === 'success') {
                    location.reload();
                } else {
                    alert('Error: ' + response);
                }
            },
            error: function() {
                alert('Error: Could not connect to server');
            }
        });
    });

    // Edit Product
    $('.edit-product').click(function() {
        var button = $(this);
        var id = button.data('id');
        var name = button.data('name');
        var price = button.data('price');
        var category = button.data('category');
        var desc = button.data('desc');
        var imgSrc = button.closest('tr').find('.product-image').attr('src');

        $('#editProductModal').modal('show');
        var form = $('#editProductForm');
        form.find('input[name="product_id"]').val(id);
        form.find('input[name="prod_name"]').val(name);
        form.find('input[name="prod_price"]').val(price);
        form.find('select[name="prod_cat_id"]').val(category);
        form.find('textarea[name="prod_desc"]').val(desc);
        $('#currentProductImage').attr('src', imgSrc);
        
        // Load ingredients immediately when opening the modal
        loadProductIngredients(id);
    });

    // Edit Product Form Submit
    $('#editProductForm').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: 'admin_product_update.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response === 'success') {
                    $('#editProductModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response);
                }
            },
            error: function() {
                alert('Error: Could not connect to server');
            }
        });
    });

    // Delete Product
    $('.delete-product').click(function() {
        if(confirm('Are you sure you want to delete this product?')) {
            var id = $(this).data('id');
            $.post('admin_product_delete.php', {
                product_id: id
            }, function(response) {
                if(response === 'success') {
                    location.reload();
                } else {
                    alert('Error: ' + response);
                }
            });
        }
    });

    // Load product ingredients
    function loadProductIngredients(productId) {
        $.get('admin_get_product_ingredients.php', { product_id: productId }, function(response) {
            $('#productIngredientsTable tbody').html(response);
        });
    }

    // Add ingredient to product
    $('#addProductIngredientForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.post('admin_add_product_ingredient.php', formData, function(response) {
            if (response === 'success') {
                $('#addProductIngredientModal').modal('hide');
                var productId = $('#editProductForm input[name="product_id"]').val();
                loadProductIngredients(productId);
                $('#addProductIngredientForm')[0].reset();
            } else {
                alert('Error: ' + response);
            }
        });
    });

    // Open Add Ingredient Modal
    $('#addIngredientToProduct').click(function() {
        var productId = $('#editProductForm input[name="product_id"]').val();
        $('#addProductIngredientForm input[name="product_id"]').val(productId);
        $('#addProductIngredientModal').modal('show');
    });

    // Delete ingredient from product
    $(document).on('click', '.delete-ingredient', function() {
        if (confirm('Are you sure you want to remove this ingredient?')) {
            var ingredientId = $(this).data('id');
            var productId = $('#editProductForm input[name="product_id"]').val();

            $.post('admin_delete_product_ingredient.php', {
                product_id: productId,
                ingredient_id: ingredientId
            }, function(response) {
                if (response === 'success') {
                    loadProductIngredients(productId);
                } else {
                    alert('Error: ' + response);
                }
            });
        }
    });

    // Add click handler for edit product ingredient
    $(document).on('click', '.edit-product-ingredient', function() {
        const ingredientId = $(this).data('id');
        const productId = $('#editProductForm input[name="product_id"]').val();
        const ingredientName = $(this).closest('tr').find('td:first').text();
        const quantity = $(this).closest('tr').find('td:nth-child(2)').text();
        
        // Populate the edit form
        $('#editProductIngredientForm input[name="product_id"]').val(productId);
        $('#editProductIngredientForm input[name="original_ingredient_id"]').val(ingredientId);
        $('#editProductIngredientForm input[name="quantity"]').val(quantity);
        
        // Find and select the ingredient in the dropdown
        const $select = $('#editProductIngredientForm select[name="ingredient_id"]');
        $select.val(ingredientId);
        
        // Update ingredient unit display
        const unit = $(this).closest('tr').find('td:nth-child(3)').text();
        $('#editProductIngredientForm .ingredient-unit').text(unit);
        
        // Show the modal
        $('#editProductIngredientModal').modal('show');
    });

    // Save edit product ingredient
    $('#editProductIngredientForm').submit(function(e) {
        e.preventDefault();
        $('#editProductIngredientModalAlert').empty();
        var formData = $(this).serialize();
        $.post('admin_update_product_ingredient.php', formData, function(response) {
            if (response === 'success') {
                $('#editProductIngredientModal').modal('hide');
                var productId = $('#editProductForm input[name="product_id"]').val();
                loadProductIngredients(productId);
            } else {
                const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ${response}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>`);
                $('#editProductIngredientModalAlert').append(alert);
            }
        });
    });
});
</script>
