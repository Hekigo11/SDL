<?php
require_once __DIR__ . '/../config.php';
session_start();

if (isset($_SESSION['loginok']) && ($_SESSION['role'] == 1 || $_SESSION['role'] == 3)) {
    header('Location: ' . BASE_URL . '/modules/admindashboard.php');
	if (!headers_sent()) {
        header('Location: ' . BASE_URL . '/modules/admindashboard.php');
        exit;
    } else {
        echo '<script>window.location.href="' . BASE_URL . '/modules/admindashboard.php";</script>';
        exit;
    }
}

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
        <style>
        .modal-backdrop {
            z-index: 1040;
        }
        .modal {
            z-index: 1050;
        }
        .alert {
            z-index: 1060;
        }
        </style>
	</head>

	<body>
		<?php include("navigation.php");?>
		
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
                                        <select class="form-control mb-2" id="address_select" required>
                                            <option value="">Select a delivery address</option>
                                            <option value="new">+ Add New Address</option>
                                        </select>
                                        
                                        <!-- Address Form (initially hidden) -->
                                        <div id="new_address_form" style="display: none;">
                                            <div class="form-group">
                                                <input type="text" class="form-control mb-2" id="street_number" placeholder="Street Number">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" class="form-control mb-2" id="street_name" placeholder="Street Name">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" class="form-control mb-2" id="barangay" placeholder="Barangay">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" class="form-control mb-2" id="city" placeholder="City">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" class="form-control mb-2" id="province" placeholder="Province">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" class="form-control mb-2" id="zip_code" placeholder="ZIP Code">
                                            </div>
                                            <div class="form-group">
                                                <select class="form-control mb-2" id="label">
                                                    <option value="Home">Home</option>
                                                    <option value="Work">Work</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                            <div class="form-group" id="customLabelGroup" style="display: none;">
                                                <input type="text" class="form-control mb-2" id="customLabel" placeholder="Enter custom label">
                                            </div>
                                            <div class="form-check mb-3">
                                                <input type="checkbox" class="form-check-input" id="is_default">
                                                <label class="form-check-label" for="is_default">Set as default address</label>
                                            </div>
                                            <button type="button" class="btn btn-primary mb-3" id="save_new_address">Save Address</button>
                                        </div>

                                        <textarea class="form-control" id="address" rows="3" style="display: none;"></textarea>
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
                                            <option value="gcash">Cashless/E-wallet</option>
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
                        <form id="cartLoginForm" class="p-4" novalidate>
                            <div class="form-group">
                                <label for="txtemail_cart">Email</label>
                                <input type="email" class="form-control mb-2" id="txtemail_cart" name="txtemail" placeholder="Enter Email" required>
                            </div>
                            <div class="form-group">
                                <label for="txtpassword_cart">Password</label>
                                <input type="password" class="form-control mb-2" id="txtpassword_cart" name="txtpassword" placeholder="Enter Password" required>
                            </div>
                            <button type="submit" class="btn rounded-pill btn-primary btn-block" id="btnlogin_cart">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

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
                    address: $('#address').val(), // This should be the selected/entered address string
                    delivery_type: $('input[name="delivery_type"]:checked').val(),
                    same_day_time: $('#same_day_time').val(),
                    scheduled_date: $('#scheduled_date').val(),
                    scheduled_time: $('#scheduled_time').val(),
                    notes: $('#notes').val() || '',
                    payment: $('#payment').val(),
                    total: parseFloat($('#checkout-total').text().replace('₱', ''))
                };

                // Validate the address field from the textarea
                if (!formData.address && $('#address_select').val() !== 'new') {
                    const selectedAddress = $('#address_select').val();
                    if (selectedAddress) {
                        formData.address = selectedAddress;
                    }
                }
                
                // More robust validation
                let validationError = false;
                let alertMessage = 'Please fill in all required delivery and payment fields.';

                if (!formData.fullname || !formData.phone || !formData.address || !formData.payment) {
                    validationError = true;
                } else if (formData.delivery_type === 'same_day') {
                    if (!formData.same_day_time) { // This covers the "No more deliveries" case (empty value)
                        validationError = true;
                        alertMessage = 'No delivery times are available for same-day delivery. Please schedule for later or try again tomorrow.';
                    }
                } else if (formData.delivery_type === 'scheduled') {
                    if (!formData.scheduled_date || !formData.scheduled_time) {
                        validationError = true;
                        alertMessage = 'Please select a date and time for scheduled delivery.';
                    }
                }
                
                if (validationError) {
                    showAlert(alertMessage, 'danger');
                    $btn.prop('disabled', false).text('Place Order');
                    return;
                }

                // Clear existing alerts
                $('.alert').remove();

                if (formData.payment === 'gcash') {
                    // Step 1: Call place_order.php to create the order and get payment_reference
                    $.ajax({
                        url: 'place_order.php',
                        method: 'POST',
                        data: formData, // Send all collected form data
                        dataType: 'json',
                        success: function(orderResponse) {
                            if (orderResponse.success && orderResponse.payment_reference) {
                                // Step 2: Call create_payment_link.php with the reference
                                const paymentRequestData = {
                                    amount: formData.total,
                                    description: 'Payment for Order (Ref: ' + orderResponse.payment_reference + ')',
                                    reference: orderResponse.payment_reference, // Use reference from place_order.php
                                    type: 'delivery' 
                                };

                                fetch('create_payment_link.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify(paymentRequestData)
                                })
                                .then(response => response.json())
                                .then(linkData => {
                                    if (linkData.success && linkData.checkout_url) {
                                        // Open PayMongo checkout in new tab
                                        window.open(linkData.checkout_url, '_blank');
                                        
                                        // Start polling for payment status
                                        checkPaymentStatus(linkData.reference); // Use the same reference for polling
                                        
                                        showAlert('Payment window opened. Please complete your payment. Your order is pending confirmation.', 'info', true);
                                        // $btn.prop('disabled', false).text('Place Order'); // Keep disabled until payment status known or polling stops
                                    } else {
                                        throw new Error(linkData.error || 'Failed to create payment link.');
                                    }
                                })
                                .catch(error => {
                                    console.error('Create Payment Link Error:', error);
                                    showAlert(error.message || 'Payment link creation failed. Your order was created but payment was not initiated.', 'danger', true);
                                    $btn.prop('disabled', false).text('Place Order');
                                });

                            } else {
                                // Error from place_order.php
                                showAlert('Failed to create order: ' + (orderResponse.message || 'Unknown error during order creation.'), 'danger', true);
                                $btn.prop('disabled', false).text('Place Order');
                            }
                        },
                        error: function(xhr, status, error) {
                            // AJAX error for place_order.php
                            console.error('Place Order AJAX Error:', status, error, xhr.responseText);
                            showAlert('Error creating order. Please try again. ' + (xhr.responseJSON ? xhr.responseJSON.message : ''), 'danger', true);
                            $btn.prop('disabled', false).text('Place Order');
                        }
                    });
                } else { // For 'cash' or other non-GCash payments
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
                                    // Update cart count in navbar
                                    if (typeof updateCartCount === 'function') {
                                        updateCartCount();
                                    }
                                });
                                $('#checkoutModal').modal('hide');
                                setTimeout(() => {
                                    window.location.href = '<?php echo BASE_URL; ?>/index.php'; // Or to an order success page
                                }, 2000);
                            } else {
                                showAlert('Failed to place order: ' + (response.message || 'Unknown error'), 'danger');
                                $btn.prop('disabled', false).text('Place Order');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Place Order (Cash) AJAX Error:', status, error, xhr.responseText);
                            showAlert('Error placing order. Please try again. ' + (xhr.responseJSON ? xhr.responseJSON.message : ''), 'danger');
                            $btn.prop('disabled', false).text('Place Order');
                        }
                    });
                }
            });

            // Replace your existing checkPaymentStatus function with this:
            function checkPaymentStatus(reference) {
                console.log('Checking payment status for:', reference); // Debug log
                
                function updateStatus() {
                    if (!$('#checkoutModal').is(':visible')) {
                        console.log('Checkout modal closed, stopping payment status check for:', reference);
                        return; // Stop polling if modal is closed
                    }

                    $.ajax({
                        url: 'check_paymongo_status.php',
                        method: 'GET',
                        data: { reference: reference },
                        dataType: 'json', // Expect JSON response
                        success: function(response) {
                            console.log('Payment status response:', response); // Debug log
                            
                            if (response.success) {
                                if (response.status === 'paid') {
                                    console.log("UI update triggered for paid status in cart.php polling:", response); // Added for debugging
                                    // Clear alerts and modal
                                    $('.alert').remove();
                                    $('#checkoutModal').modal('hide');
                                    
                                    showAlert('Payment successful! Your order is being processed. Redirecting...', 'success', true);
                                    
                                    // Optionally, clear cart from client-side if not already handled by server or page reload
                                    // updateCart(); 
                                    if (typeof updateCartCount === 'function') {
                                        updateCartCount(0); // Or fetch new count
                                    }

                                    // Redirect to orders page
                                    setTimeout(() => {
                                        window.location.href = '<?php echo BASE_URL; ?>/modules/orders.php?order_ref=' + reference;
                                    }, 2500);
                                    
                                    return; // Stop checking
                                } else if (response.status === 'expired' || response.status === 'failed') {
                                    $('.alert').remove();
                                    showAlert('Payment link has expired or payment failed. Please try placing the order again.', 'danger', true);
                                    $('#place-order-btn').prop('disabled', false).text('Place Order'); // Re-enable button
                                    return; // Stop checking
                                }
                                
                                // Continue checking every 5 seconds if pending
                                setTimeout(updateStatus, 5000);
                            } else {
                                // success: false from check_paymongo_status.php
                                console.error('Payment check failed (API error):', response.error);
                                showAlert('Error checking payment status: ' + response.error + '. Will retry.', 'warning');
                                setTimeout(updateStatus, 7000); // Retry after a slightly longer delay
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Payment check AJAX error:', status, error, xhr.responseText);
                            // Only continue polling if modal is still visible, otherwise could be an error after success
                            if ($('#checkoutModal').is(':visible')) {
                                showAlert('Could not retrieve payment status. Checking again shortly.', 'warning');
                                setTimeout(updateStatus, 7000); // Retry after a slightly longer delay
                            }
                        }
                    });
                }

                // Start checking only if modal is visible
                if ($('#checkoutModal').is(':visible')) {
                    updateStatus();
                } else {
                    console.log('Checkout modal not visible, not starting payment status check for:', reference);
                }
            }

            // Show alert
            function showAlert(message, type, persistent = false) {
                // Remove any existing alerts
                $('.alert').remove();
                
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" 
                         style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 99999; min-width: 300px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>${message}</span>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                `;
                
                $('body').append(alertHtml);
                
                // Auto dismiss after 5 seconds if not persistent
                if (!persistent) {
                    setTimeout(() => {
                        $('.alert').alert('close');
                    }, 5000);
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

            // Toggle new address form
            $('#address_select').change(function() {
                const selectedValue = $(this).val();
                if (selectedValue === 'new') {
                    $('#new_address_form').show();
                    $('#address').hide();
                } else {
                    $('#new_address_form').hide();
                    $('#address').show();
                }
            });

            // Show custom label input
            $('#label').change(function() {
                const selectedLabel = $(this).val();
                if (selectedLabel === 'Other') {
                    $('#customLabelGroup').show();
                } else {
                    $('#customLabelGroup').hide();
                }
            });

            // Load saved addresses
            function loadAddresses() {
                $.post("<?php echo BASE_URL; ?>/modules/manage_address.php", {
                    action: "get"
                })
                .done(function(response) {
                    if (response.success) {
                        const addressSelect = $("#address_select");
                        // Keep the first two options (Select and Add New)
                        addressSelect.find('option:gt(1)').remove();
                        
                        response.addresses.forEach(function(address) {
                            const addressText = `${address.label}: ${address.street_number} ${address.street_name}, ${address.barangay}, ${address.city}, ${address.province} ${address.zip_code}`;
                            const option = new Option(addressText, addressText);
                            if (address.is_default == 1) {
                                $(option).prop('selected', true);
                                $('#address').val(addressText);
                            }
                            addressSelect.append(option);
                        });
                    }
                });
            }

            // Handle new address save
            $('#save_new_address').click(function() {
                const formData = {
                    action: 'add',
                    street_number: $('#street_number').val(),
                    street_name: $('#street_name').val(),
                    barangay: $('#barangay').val(),
                    city: $('#city').val(),
                    province: $('#province').val(),
                    zip_code: $('#zip_code').val(),
                    label: $('#label').val(),
                    customLabel: $('#customLabel').val(),
                    is_default: $('#is_default').prop('checked') ? 1 : 0
                };

                $.post("<?php echo BASE_URL; ?>/modules/manage_address.php", formData)
                    .done(function(response) {
                        if (response.success) {
                            showAlert('Address saved successfully', 'success');
                            loadAddresses();
                            $('#new_address_form').hide();
                            $('#address').show();
                            // Reset form
                            $('#street_number, #street_name, #barangay, #city, #province, #zip_code, #customLabel').val('');
                            $('#is_default').prop('checked', false);
                            $('#label').val('Home');
                            $('#customLabelGroup').hide();
                        } else {
                            showAlert(response.message || 'Error saving address', 'danger');
                        }
                    })
                    .fail(function() {
                        showAlert('An error occurred while saving the address', 'danger');
                    });
            });

            // Update delivery address when selecting from dropdown
            $('#address_select').change(function() {
                const selectedValue = $(this).val();
                if (selectedValue && selectedValue !== 'new') {
                    $('#address').val(selectedValue);
                }
            });

            // Load addresses when checkout modal is shown
            $('#checkoutModal').on('show.bs.modal', function() {
                loadAddresses();
            });

            // Add this near the top of your script section
            window.addEventListener('message', function(event) {
                const data = event.data;
                if (typeof data === 'object') {
                    // Remove any existing alerts first
                    $('.alert').remove();
                    
                    switch(data.type) {
                        case 'payment_success':
                            // Hide checkout modal
                            $('#checkoutModal').modal('hide');
                            // Clear any existing alerts
                            $('.alert').remove();
                            // Show success message
                            showAlert('Payment successful! Redirecting to orders...', 'success', true);
                            // Clear cart and redirect
                            $.post('remove_from_cart.php', { clear_all: true }, function() {
                                setTimeout(() => {
                                    window.location.href = '<?php echo BASE_URL; ?>/modules/orders.php';
                                }, 2000);
                            });
                            break;
                            
                        case 'payment_error':
                            // Hide checkout modal
                            $('#checkoutModal').modal('hide');
                            // Show error message
                            showAlert('Payment failed: ' + data.message, 'danger', true);
                            break;
                    }
                }
            });

            // Initialize cart
            updateCart();
        });
        </script>
        <?php include('authenticate.php')?>
	</body>
</html>