<?php
session_start();
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
		<link rel="stylesheet" href="vendor/style1.css">
		<title>Shopping Cart - MARJ Food Delivery</title>
	</head>

	<body>
		<?php include("modules/cart_nav.php");?>
		
		<main class="container py-5">
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-4">Your Shopping Cart</h1>
                    
                    <!-- Cart Empty State -->
                    <div id="empty-cart" class="text-center py-5" style="display: none;">
                        <i class="fas fa-shopping-cart fa-4x mb-3 text-muted"></i>
                        <h3>Your cart is empty</h3>
                        <p class="text-muted">Looks like you haven't added any items to your cart yet.</p>
                        <a href="products.php" class="btn btn-primary mt-3">Browse Products</a>
                    </div>
                    
                    <!-- Cart Items Table -->
                    <div id="cart-content">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="cart-items">
                                    <!-- Cart items will be inserted here via JavaScript -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-right font-weight-bold">Total:</td>
                                        <td id="cart-total" class="font-weight-bold">₱0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="products.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left mr-2"></i>Continue Shopping
                            </a>
                            <button id="checkout-btn" class="btn btn-success">
                                Proceed to Checkout<i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Optional: Recently Viewed Products -->
            <div class="row mt-5">
                <div class="col-12">
                    <h3>You might also like</h3>
                    <div class="row mt-3">
                        <div class="col-md-3 col-6 mb-4">
                            <div class="card h-100">
                                <img src="images/dg.jpg" class="card-img-top" alt="Food Item">
                                <div class="card-body">
                                    <h5 class="card-title">Lechon Kawali</h5>
                                    <p class="card-text text-primary">₱190.00</p>
                                    <a href="products.php" class="btn btn-sm btn-outline-primary">View</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-6 mb-4">
                            <div class="card h-100">
                                <img src="images/dg.jpg" class="card-img-top" alt="Food Item">
                                <div class="card-body">
                                    <h5 class="card-title">Sisig</h5>
                                    <p class="card-text text-primary">₱170.00</p>
                                    <a href="products.php" class="btn btn-sm btn-outline-primary">View</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-6 mb-4">
                            <div class="card h-100">
                                <img src="images/dg.jpg" class="card-img-top" alt="Food Item">
                                <div class="card-body">
                                    <h5 class="card-title">Kare-Kare</h5>
                                    <p class="card-text text-primary">₱240.00</p>
                                    <a href="products.php" class="btn btn-sm btn-outline-primary">View</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-6 mb-4">
                            <div class="card h-100">
                                <img src="images/dg.jpg" class="card-img-top" alt="Food Item">
                                <div class="card-body">
                                    <h5 class="card-title">Bulalo</h5>
                                    <p class="card-text text-primary">₱260.00</p>
                                    <a href="products.php" class="btn btn-sm btn-outline-primary">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                        <?php include("modules/login.php"); ?>
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
                        <a href="modules/logout.php" class="btn btn-danger">Yes, Logout</a>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Modal -->
        <div class="modal fade" id="checkoutModal" tabindex="-1" role="dialog" aria-labelledby="checkoutModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" style="border-radius: 30px;">
                    <div class="modal-header text-center" style="background-color:var(--accent); border-radius: 30px 30px 0 0;">
                        <h5 class="modal-title text-light" id="checkoutModalLabel">Checkout</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="checkout-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>Delivery Information</h4>
                                    <div class="form-group">
                                        <label for="fullname">Full Name</label>
                                        <input type="text" class="form-control" id="fullname" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="address">Delivery Address</label>
                                        <textarea class="form-control" id="address" rows="3" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="notes">Delivery Instructions (Optional)</label>
                                        <textarea class="form-control" id="notes" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h4>Order Summary</h4>
                                    <div id="checkout-items">
                                        <!-- Order items will be listed here via JavaScript -->
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span>Subtotal:</span>
                                        <span id="checkout-subtotal"></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Delivery Fee:</span>
                                        <span>₱50.00</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between font-weight-bold">
                                        <span>Total:</span>
                                        <span id="checkout-total"></span>
                                    </div>
                                    <div class="form-group mt-4">
                                        <label for="payment">Payment Method</label>
                                        <select class="form-control" id="payment">
                                            <option value="cash">Cash on Delivery</option>
                                            <option value="gcash">GCash</option>
                                            <option value="card">Credit/Debit Card</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="place-order-btn">Place Order</button>
                    </div>
                </div>
            </div>
        </div>