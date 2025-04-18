<?php
require_once __DIR__ . '/../config.php';
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
		<link rel="stylesheet" href="<?php echo BASE_URL; ?>/vendor/style1.css">
		<title>Shopping Cart - MARJ Food Delivery</title>
	</head>

	<body>
		<?php include("cart_nav.php");?>
		
		<main class="container py-5">
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-4">Your Shopping Cart</h1>
                    
                    <!-- Cart Empty State -->
                    <div id="empty-cart" class="text-center py-5" style="display: none;">
                        <i class="fas fa-shopping-cart fa-4x mb-3 text-muted"></i>
                        <h3>Your cart is empty</h3>
                        <p class="text-muted">Looks like you haven't added any items to your cart yet.</p>
                        <a href="<?php echo BASE_URL; ?>/modules/products.php" class="btn btn-primary mt-3">Browse Products</a>
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
                            <a href="<?php echo BASE_URL; ?>/modules/products.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left mr-2"></i>Continue Shopping
                            </a>
                            <button id="checkout-btn" class="btn btn-success">
                                Proceed to Checkout<i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
		</main>
		
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
                                    <div class="form-group">
                                        <label for="payment">Payment Method</label>
                                        <select class="form-control" id="payment" required>
                                            <option value="cash">Cash on Delivery</option>
                                            <option value="gcash">GCash</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h4>Order Summary</h4>
                                    <div id="checkout-items">
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

        <!-- Login Modal -->
        <?php if (!isset($_SESSION['loginok'])) { ?>
        <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginModalLabel">Login Required</h5>
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
        <?php } ?>

		<script>
        $(document).ready(function() {
            const isLoggedIn = <?php echo isset($_SESSION['loginok']) ? 'true' : 'false'; ?>;
            
            function updateCart() {
                // Only proceed if user is logged in
                if (!isLoggedIn) {
                    $('#cart-content').hide();
                    $('#empty-cart').show();
                    return;
                }
                
                // Fetch cart items from database
                $.get('get_cart_items.php', function(response) {
                    if (response.success) {
                        displayCartItems(response.items);
                    } else {
                        $('#cart-content').hide();
                        $('#empty-cart').show();
                    }
                });
            }

            function displayCartItems(items) {
                if (!items || items.length === 0) {
                    $('#cart-content').hide();
                    $('#empty-cart').show();
                    return;
                }

                let total = 0;
                let cartHtml = '';
                
                items.forEach(item => {
                    const subtotal = item.price * item.quantity;
                    total += subtotal;
                    
                    cartHtml += `
                        <tr>
                            <td>${item.name}</td>
                            <td>₱${parseFloat(item.price).toFixed(2)}</td>
                            <td>
                                <div class="input-group input-group-sm" style="width: 100px;">
                                    <div class="input-group-prepend">
                                        <button class="btn btn-outline-secondary update-qty" data-id="${item.id}" data-action="decrease">-</button>
                                    </div>
                                    <input type="text" class="form-control text-center item-qty" value="${item.quantity}" readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary update-qty" data-id="${item.id}" data-action="increase">+</button>
                                    </div>
                                </div>
                            </td>
                            <td>₱${subtotal.toFixed(2)}</td>
                            <td>
                                <button class="btn btn-danger btn-sm remove-item" data-id="${item.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                $('#cart-items').html(cartHtml);
                $('#cart-total').text(`₱${total.toFixed(2)}`);
                $('#cart-content').show();
                $('#empty-cart').hide();
            }
            
            // Handle quantity updates
            $(document).on('click', '.update-qty', function() {
                if (!isLoggedIn) {
                    $('#loginModal').modal('show');
                    return;
                }

                const id = $(this).data('id');
                const action = $(this).data('action');
                
                $.post('update_cart_quantity.php', {
                    product_id: id,
                    action: action
                }, function(response) {
                    if (response.success) {
                        updateCart();
                    }
                });
            });
            
            // Handle item removal
            $(document).on('click', '.remove-item', function() {
                if (!isLoggedIn) {
                    $('#loginModal').modal('show');
                    return;
                }

                const id = $(this).data('id');
                
                $.post('remove_from_cart.php', {
                    product_id: id
                }, function(response) {
                    if (response.success) {
                        updateCart();
                    }
                });
            });
            
            // Handle checkout
            $('#checkout-btn').click(function() {
                if (!isLoggedIn) {
                    $('#loginModal').modal('show');
                    return;
                }
                
                $('#checkoutModal').modal('show');
                updateCheckoutSummary();
            });
            
            function updateCheckoutSummary() {
                if (!isLoggedIn) {
                    return;
                }
                
                $.get('get_cart_items.php', function(response) {
                    if (response.success) {
                        displayCheckoutSummary(response.items);
                    }
                });
            }
            
            function displayCheckoutSummary(items) {
                let itemsHtml = '';
                let subtotal = 0;
                
                items.forEach(item => {
                    const itemTotal = item.price * item.quantity;
                    subtotal += itemTotal;
                    itemsHtml += `
                        <div class="d-flex justify-content-between mb-2">
                            <span>${item.name} x ${item.quantity}</span>
                            <span>₱${itemTotal.toFixed(2)}</span>
                        </div>
                    `;
                });
                
                $('#checkout-items').html(itemsHtml);
                $('#checkout-subtotal').text(`₱${subtotal.toFixed(2)}`);
                $('#checkout-total').text(`₱${(subtotal + 50).toFixed(2)}`);
            }

            // Handle place order
            $('#place-order-btn').click(function() {
                const formData = {
                    fullname: $('#fullname').val(),
                    phone: $('#phone').val(),
                    address: $('#address').val(),
                    notes: $('#notes').val() || '',
                    payment: $('#payment').val(),
                    total: parseFloat($('#checkout-total').text().replace('₱', ''))
                };

                if (!formData.fullname || !formData.phone || !formData.address || !formData.payment) {
                    alert('Please fill in all required fields');
                    return;
                }

                $.ajax({
                    url: 'place_order.php',
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            // Clear cart from database
                            $.post('remove_from_cart.php', { clear_all: true }, function() {
                                updateCart();
                            });
                            $('#checkoutModal').modal('hide');
                            window.location.href = '<?php echo BASE_URL; ?>/index.php';
                        } else {
                            alert('Failed to place order: ' + (response.message || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Order Error:', error);
                        alert('Failed to place order. Please try again.');
                    }
                });
            });

            // Initialize cart
            updateCart();
        });
        </script>
	</body>
</html>