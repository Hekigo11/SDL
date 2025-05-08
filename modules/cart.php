<?php
require_once __DIR__ . '/../config.php';
session_start();

// Get user details for autofill if user is logged in
$user_data = null;
if (isset($_SESSION['loginok']) && isset($_SESSION['user_id'])) {
    include("dbconi.php");
    $user_query = "SELECT fname, mname, lname, email_add, mobile_num FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($dbc, $user_query);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $user_result = mysqli_stmt_get_result($stmt);
    $user_data = mysqli_fetch_assoc($user_result);
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
                                        <input type="text" class="form-control" id="fullname" value="<?php echo $user_data['fname'] . ' ' . $user_data['mname'] . ' ' . $user_data['lname']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" value="<?php echo $user_data['mobile_num']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="address">Delivery Address</label>
                                        <textarea class="form-control" id="address" rows="3" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Delivery Options</label>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="delivery_type" id="same_day" value="same_day" checked>
                                            <label class="form-check-label" for="same_day">
                                                Same Day Delivery
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="radio" name="delivery_type" id="scheduled" value="scheduled">
                                            <label class="form-check-label" for="scheduled">
                                                Schedule for Later
                                            </label>
                                        </div>
                                        
                                        <!-- Same Day Delivery Time Selection -->
                                        <div id="same_day_options">
                                            <label for="same_day_time">Delivery Time</label>
                                            <select class="form-control" id="same_day_time" required>
                                                <?php
                                                date_default_timezone_set('Asia/Manila'); // Set to Philippines timezone
                                                $now = new DateTime();
                                                $now->modify('+1 hour'); // Minimum 1 hour lead time
                                                
                                                // Round up to next 30 minute interval
                                                $minutes = (int)$now->format('i');
                                                $minutes = $minutes > 30 ? 60 : 30;
                                                $now->setTime($now->format('H'), $minutes);
                                                
                                                $end = new DateTime('today 20:00'); // Last delivery at 8 PM
                                                
                                                if ($now >= $end) {
                                                    echo '<option value="">No more deliveries available today</option>';
                                                } else {
                                                    $timeSlot = clone $now;
                                                    while ($timeSlot <= $end) {
                                                        $formattedTime = $timeSlot->format('H:i');
                                                        $displayTime = $timeSlot->format('h:i A');
                                                        echo '<option value="' . $formattedTime . '">' . $displayTime . '</option>';
                                                        $timeSlot->modify('+30 minutes');
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <small class="form-text text-muted">
                                                Available delivery times for today (minimum 1 hour lead time)
                                            </small>
                                        </div>

                                        <!-- Scheduled Delivery Options -->
                                        <div id="scheduled_options" style="display: none;">
                                            <div class="form-group">
                                                <label for="scheduled_date">Delivery Date</label>
                                                <input type="date" 
                                                       class="form-control" 
                                                       id="scheduled_date"
                                                       min="<?php echo (new DateTime('tomorrow'))->format('Y-m-d'); ?>"
                                                       max="<?php echo (new DateTime('+7 days'))->format('Y-m-d'); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="scheduled_time">Delivery Time</label>
                                                <select class="form-control" id="scheduled_time">
                                                    <option value="09:00">9:00 AM</option>
                                                    <option value="09:30">9:30 AM</option>
                                                    <option value="10:00">10:00 AM</option>
                                                    <option value="10:30">10:30 AM</option>
                                                    <option value="11:00">11:00 AM</option>
                                                    <option value="11:30">11:30 AM</option>
                                                    <option value="13:00">1:00 PM</option>
                                                    <option value="13:30">1:30 PM</option>
                                                    <option value="14:00">2:00 PM</option>
                                                    <option value="14:30">2:30 PM</option>
                                                    <option value="15:00">3:00 PM</option>
                                                    <option value="15:30">3:30 PM</option>
                                                    <option value="16:00">4:00 PM</option>
                                                    <option value="16:30">4:30 PM</option>
                                                    <option value="17:00">5:00 PM</option>
                                                    <option value="17:30">5:30 PM</option>
                                                    <option value="18:00">6:00 PM</option>
                                                    <option value="18:30">6:30 PM</option>
                                                    <option value="19:00">7:00 PM</option>
                                                </select>
                                            </div>
                                            <small class="form-text text-muted">
                                                Schedule delivery up to 7 days in advance
                                            </small>
                                        </div>
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
                    showAlert('Please login to manage your cart', 'warning', true);
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
                    showAlert('Please login to manage your cart', 'warning', true);
                    return;
                }

                const id = $(this).data('id');
                
                $.post('remove_from_cart.php', {
                    product_id: id
                }, function(response) {
                    if (response.success) {
                        showAlert('Item removed from cart', 'success');
                        updateCart();
                    }
                });
            });
            
            // Handle checkout
            $('#checkout-btn').click(function() {
                if (!isLoggedIn) {
                    showAlert('Please login to proceed to checkout', 'warning', true);
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
                const $btn = $(this);
                
                // Disable button immediately
                $btn.prop('disabled', true)
                   .html('<i class="fas fa-spinner fa-spin"></i> Processing...');

                const formData = {
                    fullname: $('#fullname').val(),
                    phone: $('#phone').val(),
                    address: $('#address').val(),
                    delivery_type: $('input[name="delivery_type"]:checked').val(),
                    same_day_time: $('#same_day_time').val(),
                    scheduled_date: $('#scheduled_date').val(),
                    scheduled_time: $('#scheduled_time').val(),
                    notes: $('#notes').val() || '',
                    payment: $('#payment').val(),
                    total: parseFloat($('#checkout-total').text().replace('₱', ''))
                };

                if (!formData.fullname || !formData.phone || !formData.address || !formData.payment || 
                    (formData.delivery_type === 'same_day' && !formData.same_day_time) || 
                    (formData.delivery_type === 'scheduled' && (!formData.scheduled_date || !formData.scheduled_time))) {
                    showAlert('Please fill in all required fields', 'danger');
                    $btn.prop('disabled', false).text('Place Order');
                    return;
                }

                $.ajax({
                    url: 'place_order.php',
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showAlert(response.message || 'Order placed successfully!', 'success');
                            // Clear cart from database
                            $.post('remove_from_cart.php', { clear_all: true }, function() {
                                updateCart();
                            });
                            $('#checkoutModal').modal('hide');
                            setTimeout(() => {
                                window.location.href = '<?php echo BASE_URL; ?>/index.php';
                            }, 2000);
                        } else {
                            showAlert('Failed to place order: ' + (response.message || 'Unknown error'), 'danger');
                            $btn.prop('disabled', false).text('Place Order');
                        }
                    },
                    error: function() {
                        showAlert('Error placing order. Please try again.', 'danger');
                        $btn.prop('disabled', false).text('Place Order');
                    }
                });
            });

            // Show alert
            function showAlert(message, type, autoHide = false) {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;
                $('main.container').prepend(alertHtml);
                if (autoHide) {
                    setTimeout(() => {
                        $('.alert').alert('close');
                    }, 3000);
                }
            }

            // Toggle delivery options
            $('input[name="delivery_type"]').change(function() {
                const selectedType = $(this).val();
                if (selectedType === 'same_day') {
                    $('#same_day_options').show();
                    $('#scheduled_options').hide();
                } else {
                    $('#same_day_options').hide();
                    $('#scheduled_options').show();
                }
            });

            // Initialize cart
            updateCart();
        });
        </script>
        <?php include('authenticate.php')?>
	</body>
</html>