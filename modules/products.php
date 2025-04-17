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
            $query = "SELECT * FROM products";
            $result = mysqli_query($dbc, $query);
            if (!$result) {
                die("Query failed: " . mysqli_error($dbc));
            }
        ?>
		
		<main class="container py-5">
            <h1 class="text-center mb-5">Our Food Menu</h1>
            
            <!-- Product Categories -->
            <!-- <div class="row mb-4">
                <div class="col-12">
                    <ul class="nav nav-pills justify-content-center">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-category="all">All Items</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-category="1">Main Dishes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-category="2">Sides</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-category="3">Desserts</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-category="4">Beverages</a>
                        </li>
                    </ul>
                </div>
            </div> -->
            <div class="row mb-4">
    <div class="col-12">
        <ul class="nav nav-pills justify-content-center" id="prodnav">
            <div class="nav-indicator">            </div>
            <li class="nav-item">
                <a class="nav-link active" href="#" data-category="all">All Items</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-category="1">Main Dishes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-category="2">Sides</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-category="3">Desserts</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-category="4">Beverages</a>
            </li>

        </ul>
    </div>
</div>
            <!-- Products Grid -->
            <div class="row" id="products-container">
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <div class="col-md-4 mb-4 product-item" data-category="<?php echo $row['prod_cat_id']; ?>">
                        <div class="card h-100">
                            <img src="<?php echo BASE_URL; ?>/images/<?php echo htmlspecialchars($row['prod_img']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['prod_name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['prod_name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($row['prod_desc']); ?></p>
                                <p class="card-text text-primary font-weight-bold">₱<?php echo number_format($row['prod_price'], 2); ?></p>
                            </div>
                            <div class="card-footer bg-white border-top-0">
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
                                    <button class="btn btn-primary add-to-cart" 
                                            data-id="<?php echo $row['product_id']; ?>" 
                                            data-name="<?php echo htmlspecialchars($row['prod_name']); ?>" 
                                            data-price="<?php echo $row['prod_price']; ?>">
                                        Add to Cart
                                    </button>
                                </div>
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
		
		<script>
            $(document).ready(function() {
                // Category filtering
                $('.nav-pills a').click(function(e) {
                    e.preventDefault();
                    $('.nav-pills a').removeClass('active');
                    $(this).addClass('active');
                    
                    const category = $(this).data('category');
                    if (category === 'all') {
                        $('.product-item').show();
                    } else {
                        $('.product-item').hide();
                        $(`.product-item[data-category="${category}"]`).show();
                    }
                });
                
                // Quantity controls
                $('.increase-qty').click(function() {
                    let input = $(this).closest('.input-group').find('.item-qty');
                    let value = parseInt(input.val()) + 1;
                    input.val(value);
                });
                
                $('.decrease-qty').click(function() {
                    let input = $(this).closest('.input-group').find('.item-qty');
                    let value = parseInt(input.val());
                    if (value > 1) {
                        input.val(value - 1);
                    }
                });
                
                // Shopping cart functionality
                $('.add-to-cart').click(function() {
                    const productId = $(this).data('id');
                    const productName = $(this).data('name');
                    const productPrice = $(this).data('price');
                    const quantity = parseInt($(this).closest('.card-footer').find('.item-qty').val());
                    
                    // Get existing cart from localStorage or create new one
                    let cart = JSON.parse(localStorage.getItem('cart')) || [];
                    
                    // Check if product already in cart
                    const existingItem = cart.find(item => item.id === productId);
                    
                    if (existingItem) {
                        existingItem.quantity += quantity;
                    } else {
                        cart.push({
                            id: productId,
                            name: productName,
                            price: productPrice,
                            quantity: quantity
                        });
                    }
                    
                    // Save cart to localStorage
                    localStorage.setItem('cart', JSON.stringify(cart));
                    
                    // Update cart count
                    updateCartCount();
                    
                    // Show notification
                    alert(`${productName} added to your cart!`);
                });
                
                // Initialize cart count
                updateCartCount();
                
                function updateCartCount() {
                    let cart = JSON.parse(localStorage.getItem('cart')) || [];
                    let count = 0;
                    
                    cart.forEach(item => {
                        count += item.quantity;
                    });
                    
                    $('.cart-count').text(count);
                }
            });
		</script>
	</body>
</html>