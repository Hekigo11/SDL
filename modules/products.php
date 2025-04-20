<?php 
require_once __DIR__ . '/../config.php';
// Try ko maglagay error handling --jas
if (session_status() === PHP_SESSION_NONE) {
    try {
        session_start();
    } catch (Exception $e) {
        error_log("Session start failed: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
		<!-- jQuery library -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
		<!-- Popper JS -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
		<!-- Latest compiled JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
		<link rel="stylesheet" href="<?php echo BASE_URL; ?>/vendor/style1.css">
		<title>Products - MARJ Food Delivery</title>
        <style>
            .card-img-top {
                aspect-ratio: 16/9;  
                object-fit: cover;
                width: 100%;
            }
            
            /* Mga search box styles */
            .input-group-text {
                background-color: var(--accent);
                border-color: var(--accent);
                color: white;
            }

            #searchBox:focus {
                box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
                border-color: var(--accent);
                outline: none;
            }

            .input-group {
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .nav-pills {
                position: relative;
                gap: 0.5rem;
            }

            .nav-pills .nav-link {
                position: relative;
                z-index: 1;
                transition: color 0.3s ease;
            }

            .nav-pills .nav-link::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: var(--accent);
                border-radius: 1rem;
                transform: scale(0);
                transition: transform 0.3s ease;
                z-index: -1;
            }

            .nav-pills .nav-link:hover::before,
            .nav-pills .nav-link.active::before {
                transform: scale(1);
            }
            .nav-pills .nav-link:hover {
                color: white !important;
            }
            .nav-pills .nav-link.active {
                color: white;
                background-color: transparent !important;
            }

            .nav-pills .nav-link:not(.active) {
                color: var(--primary1);
            }
            #prodnav a{
                font-family: Montserrat;
                font-weight: 600;
            }
        </style>
	</head>

	<body>
        <?php include("product_nav.php");?>
        <?php
            include("dbconi.php");
            $query = "SELECT p.*, 
                    CASE WHEN pi.ingredient_id = 1 THEN 0 ELSE 1 END as is_halal 
                    FROM products p 
                    LEFT JOIN product_ingredients pi ON p.product_id = pi.product_id";
            $result = mysqli_query($dbc, $query);
            if (!$result) {
                die("Query failed: " . mysqli_error($dbc));
            }
        ?>
		
		<main class="container py-5">
            <h1 class="text-center mb-5">Our Food Menu</h1>

            <!-- Search Box and Halal Filter, testing muna -->
            <div class="row mb-4">
                <div class="col-md-6 mx-auto">
                    <div class="input-group mb-3">
                        <input type="text" id="searchBox" class="form-control" placeholder="Search for food items...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                    <div class="custom-control custom-checkbox text-center">
                        <input type="checkbox" class="custom-control-input" id="halalOnly">
                        <label class="custom-control-label" for="halalOnly">Show Halal Items Only</label>
                    </div>
                </div>
            </div>
            
            <!-- Product Categories -->
            <div class="row mb-4">
                <div class="col-12">
                    <ul class="nav nav-pills justify-content-center" id="prodnav">
                        <div class="nav-indicator"></div>
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-category="all">All Items</a>
                        </li>
                        <?php
                        // Query to fetch categories
                        $cat_query = "SELECT category_id, category_name FROM categories";
                        $cat_result = mysqli_query($dbc, $cat_query);
                        
                        if (!$cat_result) {
                            die("Category query failed: " . mysqli_error($dbc));
                        }

                        while ($cat_row = mysqli_fetch_assoc($cat_result)) {
                            echo '<li class="nav-item">';
                            echo '<a class="nav-link" href="#" data-category="' . $cat_row['category_id'] . '">' . htmlspecialchars($cat_row['category_name']) . '</a>';
                            echo '</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="row" id="products-container">
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <div class="col-md-4 mb-4 product-item" data-category="<?php echo $row['prod_cat_id']; ?>" data-halal="<?php echo $row['is_halal']; ?>">
                        <div class="card h-100">
                            <img src="<?php echo BASE_URL; ?>/images/Products/<?php echo htmlspecialchars($row['prod_img']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['prod_name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['prod_name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($row['prod_desc']); ?></p>
                                <p class="card-text text-primary font-weight-bold">₱<?php echo number_format($row['prod_price'], 2); ?></p>
                                <?php if ($row['is_halal']) { ?>
                                    <span class="badge badge-success">Halal</span>
                                <?php } else { ?>
                                    <span class="badge badge-danger">Non-Halal</span>
                                <?php } ?>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <button class="btn btn-outline-primary view-product" 
                                            data-id="<?php echo $row['product_id']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['prod_name']); ?>"
                                            data-desc="<?php echo htmlspecialchars($row['prod_desc']); ?>"
                                            data-price="<?php echo $row['prod_price']; ?>"
                                            data-img="<?php echo BASE_URL; ?>/images/Products/<?php echo htmlspecialchars($row['prod_img']); ?>"
                                            data-halal="<?php echo $row['is_halal']; ?>">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <div class="input-group input-group-sm" style="width: 120px;">
                                        <div class="input-group-prepend">
                                            <button class="btn btn-outline-secondary decrease-qty" type="button">-</button>
                                        </div>
                                        <input type="text" class="form-control text-center item-qty" value="1">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary increase-qty" type="button">+</button>
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-primary btn-block add-to-cart" 
                                        data-id="<?php echo $row['product_id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($row['prod_name']); ?>" 
                                        data-price="<?php echo $row['prod_price']; ?>">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            
            <!-- Mini Cart Indicator -->
            <div class="fixed-bottom mb-4 mr-4 d-flex justify-content-end">
                <a href="<?php echo BASE_URL; ?>/modules/cart.php" class="btn btn-primary position-relative">
                    <i class="fas fa-shopping-cart"></i> View Cart
                    <span class="badge badge-light cart-count">0</span>
                </a>
            </div>
		</main>
		
		<footer class="bg-dark text-white text-center py-3">
            <p>&copy; 2023 MARJ Food Services. All rights reserved.</p>
        </footer>
		
		<!-- LOGIN MODAL -->
        <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
            <div class="modal-dialog" style="margin: 25vh auto;" role="document">
                <div class="modal-content" style="border-radius: 30px;">
                    <div class="modal-header text-center position-center" style="background-color:var(--accent); border-radius: 30px 30px 0 0;">
                        <h5 class="modal-title text-light" id="loginModalLabel">Login</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php include("login.php"); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- LOGOUT MODAL -->
        <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
            <div class="modal-dialog" style="margin: 25vh auto;" role="document">
                <div class="modal-content" style="border-radius: 30px;">
                    <div class="modal-header text-center position-center" style="background-color:var(--accent); border-radius: 30px 30px 0 0;">
                        <h5 class="modal-title text-light" id="logoutModalLabel">Logout</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <p>Are you sure you want to log out?</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <a href="<?php echo BASE_URL; ?>/modules/logout.php" class="btn btn-danger">Yes, Logout</a>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product View Modal -->
        <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content" style="border-radius: 30px;">
                    <div class="modal-header text-center position-center" style="background-color:var(--accent); border-radius: 30px 30px 0 0;">
                        <h5 class="modal-title text-light" id="productModalLabel">Product Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <img src="" alt="" class="product-modal-img img-fluid" style="max-height: 200px; object-fit: cover;">
                        </div>
                        <h4 class="product-modal-name"></h4>
                        <p class="product-modal-desc"></p>
                        <p class="product-modal-price font-weight-bold"></p>
                        <div class="halal-status mb-2">
                            <span class="badge badge-success halal-badge">Halal</span>
                            <span class="badge badge-danger non-halal-badge">Non-Halal</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="input-group input-group-sm" style="width: 120px;">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-secondary decrease-qty" type="button">-</button>
                                </div>
                                <input type="text" class="form-control text-center item-qty" value="1">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary increase-qty" type="button">+</button>
                                </div>
                            </div>
                            <button class="btn btn-primary add-to-cart">Add to Cart</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		
		<script>
            $(document).ready(function() {
                const isLoggedIn = <?php echo isset($_SESSION['loginok']) ? 'true' : 'false'; ?>;

                // Cart Functions
                function addToCart(productId, productName, quantity) {
                    if (!isLoggedIn) {
                        $('#productModal').modal('hide'); //para di magpatungan mga modal
                        $('#loginModal').modal('show');
                        return;
                    }

                    $.ajax({
                        url: 'add_to_cart.php',
                        method: 'POST',
                        data: {
                            product_id: productId,
                            quantity: quantity
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(`${productName} added to your cart!`);
                                updateCartCount();
                                $('#productModal').modal('hide');
                            } else {
                                alert('Failed to add to cart: ' + (response.message || 'Unknown error'));
                            }
                        },
                        error: function() {
                            alert('Error adding item to cart');
                        }
                    });
                }

                function updateCartCount() {
                    if (!isLoggedIn) {
                        $('.cart-count').text('0');
                        return;
                    }
                    
                    $.get('get_cart_count.php', function(response) {
                        if (response.success) {
                            $('.cart-count').text(response.count);
                        }
                    }, 'json');
                }

                // Quantity Controls
                $('.decrease-qty').click(function() {
                    const input = $(this).closest('.input-group').find('.item-qty');
                    const currentVal = parseInt(input.val());
                    if (currentVal > 1) {
                        input.val(currentVal - 1);
                    }
                });

                $('.increase-qty').click(function() {
                    const input = $(this).closest('.input-group').find('.item-qty');
                    const currentVal = parseInt(input.val());
                    input.val(currentVal + 1);
                });

                // Add to Cart Button Clicks
                $('.card-footer .add-to-cart').click(function() {
                    const productId = $(this).data('id');
                    const productName = $(this).data('name');
                    const quantity = parseInt($(this).closest('.card-footer').find('.item-qty').val());
                    addToCart(productId, productName, quantity);
                });

                $('#productModal .add-to-cart').click(function() {
                    const productId = $(this).data('id');
                    const productName = $(this).data('name');
                    const quantity = parseInt($('#productModal .item-qty').val());
                    addToCart(productId, productName, quantity);
                });

                // Filter function na pagsasama search, category, tas halal filters
                function filterProducts() {
                    const searchText = $('#searchBox').val().toLowerCase();
                    const showHalalOnly = $('#halalOnly').is(':checked');
                    const selectedCategory = $('.nav-pills .nav-link.active').data('category');
                    
                    $('.product-item').each(function() {
                        const $item = $(this);
                        const productName = $item.find('.card-title').text().toLowerCase();
                        const isHalal = $item.find('.badge-success').length > 0;
                        const itemCategory = $item.data('category');
                        
                        const matchesSearch = productName.includes(searchText);
                        const matchesHalal = !showHalalOnly || isHalal;
                        const matchesCategory = selectedCategory === 'all' || selectedCategory === itemCategory;
                        
                        $item.toggle(matchesSearch && matchesHalal && matchesCategory);
                    });
                }

                // Category filtering
                $('.nav-pills .nav-link').click(function(e) {
                    e.preventDefault();
                    $('.nav-pills .nav-link').removeClass('active');
                    $(this).addClass('active');
                    filterProducts();
                });

                // Halal filter functionality
                $('#halalOnly').change(function() {
                    filterProducts();
                });

                // Search functionality
                $('#searchBox').on('keyup', function() {
                    filterProducts();
                });

                // View Product Modal -- baka tanggalin if di kaidlangan
                $('.view-product').click(function() {
                    const $btn = $(this);
                    const modal = $('#productModal');
                    
                    modal.find('.product-modal-img').attr('src', $btn.data('img'));
                    modal.find('.product-modal-name').text($btn.data('name'));
                    modal.find('.product-modal-desc').text($btn.data('desc'));
                    modal.find('.product-modal-price').text('₱' + parseFloat($btn.data('price')).toFixed(2));
                    
                    if ($btn.data('halal') == 1) {
                        modal.find('.halal-badge').removeClass('d-none');
                        modal.find('.non-halal-badge').addClass('d-none');
                    } else {
                        modal.find('.halal-badge').addClass('d-none');
                        modal.find('.non-halal-badge').removeClass('d-none');
                    }

                    // Modal add to cart 
                    modal.find('.add-to-cart')
                        .data('id', $btn.data('id'))
                        .data('name', $btn.data('name'))
                        .data('price', $btn.data('price'));

                    modal.modal('show');
                });

                // update cart pag nagload
                updateCartCount();
            });
        </script>
	</body>
</html>