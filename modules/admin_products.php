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
        }
        .card {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
        }
        .main-content.expanded {
            margin-left: 70px;
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
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
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
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editProductForm" enctype="multipart/form-data">
                <input type="hidden" name="product_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Current Image</label>
                        <img id="currentProductImage" src="" alt="Current Product" class="img-fluid mb-2" style="max-height: 100px;">
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
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
    });
    </script>
</body>
</html>