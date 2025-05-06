<?php
require_once __DIR__ . '/../config.php';

// Check if user is admin
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

include("dbconi.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <style>
        .product-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
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
        }
        .modal-header {
            padding: 1.5rem;
            border-bottom: none;
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
        /* Image preview */
        #currentProductImage {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        /* Modal animations */
        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
        }
        .modal.fade.show .modal-dialog {
            transform: none;
        }

        #details-tab, #ingredients-tab {
            font-weight: 600;
            color: var(--primary1);
            border: solid 2px var(--primary1);
            margin: 0 10px;
        }
    </style>
</head>
<body>

    <div class="main-content">
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
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 30px;">
                <div class="modal-header text-center position-center" style="background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark, #007bff) 100%); border-radius: 30px 30px 0 0;">
                    <h5 class="modal-title text-light" style="font-weight: 600;">Add New Product</h5>
                    <button type="button" class="close" data-dismiss="modal" style="opacity: 1;">
                        <span aria-hidden="true" style="color: white; text-shadow: none;">&times;</span>
                    </button>
                </div>
                <form id="addProductForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Product Name</label>
                            <input type="text" class="form-control" name="prod_name" required>
                        </div>
                        <div class="form-group">
                            <label>Price</label>
                            <input type="number" class="form-control" name="prod_price" required>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select class="form-control" name="prod_cat_id" required>
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
                            <select class="form-control" name="ingredient_id" required>
                                <?php
                                $ing_query = "SELECT i.*, t.type_name 
                                            FROM ingredients i 
                                            JOIN ingredient_types t ON i.type_id = t.type_id 
                                            ORDER BY t.type_name, i.name";
                                $ing_result = mysqli_query($dbc, $ing_query);
                                while ($ing = mysqli_fetch_assoc($ing_result)) {
                                    echo "<option value='" . $ing['ingredient_id'] . "' data-unit='" . htmlspecialchars($ing['unit']) . "'>" 
                                         . htmlspecialchars($ing['type_name'] . ' - ' . $ing['name']) . "</option>";
                                }
                                ?>
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

    <script>
    $(document).ready(function() {
        // Removed redundant sidebar toggle code, nagbubug siya kamo

        // Add Product Form Submit
        $('#addProductForm').submit(function(e) {
            e.preventDefault();
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

        // Handle ingredient unit display
        $('select[name="ingredient_id"]').change(function() {
            var unit = $(this).find('option:selected').data('unit');
            $('.ingredient-unit').text(unit);
        }).trigger('change');

        // Load ingredients when tab is shown
        $('#ingredients-tab').on('show.bs.tab', function() {
            var productId = $('#editProductForm input[name="product_id"]').val();
            loadProductIngredients(productId);
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

        // Handle ingredient unit display
        $('select[name="ingredient_id"]').change(function() {
            var unit = $(this).find('option:selected').data('unit');
            $('.ingredient-unit').text(unit);
        }).trigger('change');

        // Load ingredients when tab is shown
        $('#ingredients-tab').on('show.bs.tab', function() {
            var productId = $('#editProductForm input[name="product_id"]').val();
            loadProductIngredients(productId);
        });
    });
    </script>
</body>
</html>
