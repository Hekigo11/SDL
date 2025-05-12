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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Popper.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css" rel="stylesheet">
    
    <style>
        .select2-container {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
        }
        .select2-dropdown {
            min-width: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
        }
        .select2-container--bootstrap4 .select2-selection--single {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
        }
        .modal .select2-container {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            z-index: 1056 !important; /* Higher than modal backdrop */
        }
        .select2-container .select2-selection--single {
            height: calc(1.5em + .75rem + 2px);
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
            color: #6c757d;
            line-height: calc(1.5em + .75rem);
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + .75rem);
        }
        /* Custom Select2 Styles */
        .select2-container--bootstrap {
            width: 100% !important;
        }
        .select2-container--bootstrap .select2-selection {
            border-radius: 8px;
            border: 1px solid rgba(0,0,0,0.1);
            height: auto;
            padding: 0.4rem 0.8rem;
        }
        .select2-container--bootstrap .select2-selection--single {
            height: calc(1.5em + 1.2rem + 2px);
        }
        .select2-container--bootstrap .select2-selection--single .select2-selection__rendered {
            padding: 0;
            line-height: 1.5;
        }
        .select2-container--bootstrap .select2-selection--multiple {
            min-height: calc(1.5em + 1.2rem + 2px);
        }
        .select2-container--bootstrap .select2-selection--multiple .select2-selection__rendered {
            padding: 0;
        }
        .select2-container--bootstrap .select2-selection--multiple .select2-selection__choice {
            background-color: var(--accent);
            border: none;
            color: white;
            border-radius: 4px;
            padding: 2px 8px;
            margin: 2px 4px 2px 0;
        }
        .select2-container--bootstrap .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            margin-right: 5px;
        }
        .select2-container--bootstrap .select2-search--dropdown .select2-search__field {
            border-radius: 4px;
            border: 1px solid rgba(0,0,0,0.1);
            padding: 0.4rem 0.8rem;
        }
        .select2-container--bootstrap .select2-results__option--highlighted[aria-selected] {
            background-color: var(--accent);
        }
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
        /* Select2 Custom Styles */
        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            color: #495057;
            line-height: 1.5;
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
            color: #6c757d;
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + .75rem);
        }
        .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da;
            border-radius: .25rem;
        }
        .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
            background-color: var(--accent);
        }
        .select2-container--bootstrap4 .select2-results__group {
            padding: 6px;
            color: #6c757d;
            font-weight: 600;
            background: #f8f9fa;
        }
        /* Additional Select2 custom styles */
        .select2-container--bootstrap4 .select2-results__group {
            padding: 6px 12px;
            margin-top: 4px;
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .ingredient-option {
            padding: 6px 12px;
            margin: 2px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .ingredient-group {
            padding: 8px 12px;
            color: #495057;
        }

        .select2-container--bootstrap4 .select2-results__option--highlighted .ingredient-option {
            color: #fff;
        }

        .select2-container--bootstrap4 .select2-results__option--highlighted .text-muted {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .select2-container--bootstrap4.select2-container--open .select2-selection {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* --- Select2 Modal Fixes --- */
        .modal .select2-container {
            width: 100% !important;
            min-width: 0 !important;
        }
        .modal .select2-dropdown {
            z-index: 1056 !important; /* Match container z-index */
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
        }
        .modal .select2-search {
            width: 100% !important;
            padding: 8px;
        }
        .modal .select2-results {
            max-height: 200px;
            overflow-y: auto;
        }
        /* Ensure dropdown stays within modal */
        .modal-dialog {
            transform: none !important;
        }
        /* Fix dropdown position in modal */
        .modal-open .select2-container--open .select2-dropdown {
            left: 0 !important;
        }
        /* Improve dropdown appearance */
        .select2-container--bootstrap4 .select2-dropdown {
            border-color: #dee2e6;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        @media (max-width: 576px) {
            .modal .select2-dropdown {
                max-width: 98vw !important;
                left: 1vw !important;
            }
        }

        /* --- Select2 Modal Searchbox Fixes --- */
        .modal .select2-container {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
        }
        .modal .select2-dropdown {
            min-width: 0 !important;
            width: 100% !important;
            max-width: 100vw !important;
            left: 0 !important;
        }
        .modal .select2-search--dropdown .select2-search__field {
            width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box;
        }
        @media (max-width: 576px) {
            .modal .select2-dropdown {
                left: 0 !important;
                right: 0 !important;
                width: 98vw !important;
                margin: 0 auto;
            }
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
                                                <select class="form-control ingredient-select" id="addProductIngredientSelect">
                                                    <?php renderIngredientOptions($dbc); ?>
                                                </select>
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

        // --- Add/Edit/Delete Ingredients in Add Product Modal ---
        let newProductIngredients = [];
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
        // Add ingredient to new product
        $('#addProductIngredientToList').click(function() {
            $('#addProductIngredientModalAlert').empty();
            const ingredientId = $('#addProductIngredientSelect').val();
            const ingredientName = $('#addProductIngredientSelect option:selected').text();
            const unit = $('#addProductIngredientSelect option:selected').data('unit');
            const quantity = $('#addProductIngredientQuantity').val();
            if (!ingredientId || !quantity || quantity <= 0) {
                const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Please select an ingredient and enter a valid quantity.
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>`);
                $('#addProductIngredientModalAlert').append(alert);
                return;
            }
            // Prevent duplicate ingredient
            if (newProductIngredients.some(i => i.ingredient_id == ingredientId)) {
                const alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    This ingredient is already added.
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>`);
                $('#addProductIngredientModalAlert').append(alert);
                return;
            }
            newProductIngredients.push({
                ingredient_id: ingredientId,
                ingredient_name: ingredientName,
                unit: unit,
                quantity: quantity
            });
            renderNewProductIngredients();
            $('#addProductIngredientSelect').val('');
            $('#addProductIngredientQuantity').val('');
        });
        // Edit ingredient in new product
        $(document).on('click', '.edit-new-product-ingredient', function() {
            const idx = $(this).data('idx');
            const item = newProductIngredients[idx];
            $('#addProductIngredientSelect').val(item.ingredient_id);
            $('#addProductIngredientQuantity').val(item.quantity);
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
        // On submit, append ingredients to form
        $('#addProductForm').submit(function(e) {
            // Remove any previous hidden fields
            $(this).find('input[name^="ingredients["]').remove();
            newProductIngredients.forEach((item, idx) => {
                $(this).append(`<input type="hidden" name="ingredients[${idx}][ingredient_id]" value="${item.ingredient_id}">`);
                $(this).append(`<input type="hidden" name="ingredients[${idx}][quantity]" value="${item.quantity}">`);
            });
        });
        // Ingredient unit display for add modal
        $('#addProductIngredientSelect').change(function() {
            var unit = $(this).find('option:selected').data('unit');
            $('#addProductIngredientUnit').text(unit);
        });
        // --- Edit/Replace Product Ingredient ---
        $(document).on('click', '.edit-product-ingredient', function() {
            const row = $(this).closest('tr');
            const ingredientId = $(this).data('id');
            const productId = $('#editProductForm input[name="product_id"]').val();
            const quantity = row.find('td').eq(1).text();
            const unit = row.find('td').eq(2).text();
            $('#editProductIngredientForm input[name="product_id"]').val(productId);
            $('#editProductIngredientForm input[name="original_ingredient_id"]').val(ingredientId);
            $('#editProductIngredientForm select[name="ingredient_id"]').val(ingredientId);
            $('#editProductIngredientForm input[name="quantity"]').val(quantity);
            $('#editProductIngredientModal .ingredient-unit').text(unit);
            $('#editProductIngredientModalAlert').empty();
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
        });        // Enhanced Select2 initialization
        function initSelect2() {
            $('.ingredient-select').each(function() {
                var $select = $(this);
                var $modal = $select.closest('.modal');
                
                // Destroy existing Select2 instance if any
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }
                
                // Configure Select2
                var config = {
                    theme: 'bootstrap4',
                    width: '100%',
                    dropdownAutoWidth: true,
                    allowClear: true,
                    placeholder: 'Search for an ingredient...',
                    minimumInputLength: 1,
                    templateResult: formatIngredientOption,
                    templateSelection: formatIngredientSelection,
                    escapeMarkup: function(markup) { return markup; },
                    dropdownPosition: 'below',
                    dropdownParent: $modal.find('.modal-content'),
                    closeOnSelect: true
                };

                // Initialize Select2
                $select.select2(config)
                    .on('select2:open', function() {
                        // Ensure proper positioning
                        setTimeout(function() {
                            $('.select2-dropdown').css('width', $select.width());
                            $('.select2-search__field').focus();
                        }, 0);
                    });

                // Handle modal events
                $modal.on('shown.bs.modal', function() {
                    $select.select2(config);
                }).on('hidden.bs.modal', function() {
                    if ($select.hasClass('select2-hidden-accessible')) {
                        $select.select2('destroy');
                    }
                });
            });
        }

        // Format dropdown options with group headers
        function formatIngredientOption(ingredient) {
            if (!ingredient.id || !ingredient.element) {
                return ingredient.text;
            }

            if ($(ingredient.element).is('optgroup')) {
                return $('<div class="ingredient-group">' +
                        '<strong><i class="fas fa-layer-group mr-1"></i>' + ingredient.text + '</strong>' +
                        '</div>');
            }

            var $ingredient = $(
                '<div class="ingredient-option">' +
                '<span class="ingredient-name">' + ingredient.text + '</span>' +
                '<small class="text-muted ml-2">(' + 
                $(ingredient.element).data('unit') + ')</small>' +
                '</div>'
            );

            return $ingredient;
        }

        // Format selected option
        function formatIngredientSelection(ingredient) {
            if (!ingredient.id || !ingredient.element) {
                return ingredient.text;
            }

            return ingredient.text + ' (' + $(ingredient.element).data('unit') + ')';
        }

        // Initialize Select2 on page load
        initSelect2();

        // Handle ingredient selection change
        $('.ingredient-select').on('select2:select', function(e) {
            var unit = $(e.params.data.element).data('unit');
            var $modal = $(this).closest('.modal');
            $modal.find('.ingredient-unit').text(unit || '');
        });

        // Re-initialize when any modal is shown
        $('.modal').on('shown.bs.modal', function() {
            initSelect2();
        });

        // Clean up when modal is hidden
        $('.modal').on('hidden.bs.modal', function() {
            $('.ingredient-select', this).select2('destroy');
        });
    });
    </script>
</body>
</html>
