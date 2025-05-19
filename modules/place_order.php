<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

include("dbconi.php");

// user id mula session
$user_id = $_SESSION['user_id'];

// Validate input data
if (empty($_POST['fullname']) || empty($_POST['phone']) || empty($_POST['address']) || empty($_POST['payment'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Common data
$fullname = $_POST['fullname'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$notes = $_POST['notes'] ?? '';
$payment_method = $_POST['payment'];
$total_amount = $_POST['total'];
$delivery_type = $_POST['delivery_type'];

$scheduled_delivery_str = null;
if ($delivery_type === 'same_day') {
    // Ensure timezone is consistent with your application's default
    // date_default_timezone_set('Asia/Manila'); // Example, set in config.php if global
    $date = new DateTime('today'); 
    $time = $_POST['same_day_time'];
    $scheduled_delivery_str = $date->format('Y-m-d') . ' ' . $time . ':00';
} else {
    $scheduled_delivery_str = $_POST['scheduled_date'] . ' ' . $_POST['scheduled_time'] . ':00';
}

$payment_ref = uniqid('order_', true); // Generate unique reference for all order types initially

try {
    mysqli_begin_transaction($dbc);

    if ($payment_method === 'gcash') {
        // Handle GCash payment - store in temp_orders
        $temp_order_query = "INSERT INTO temp_orders (
            user_id, full_name, phone, address, notes, payment_method, 
            total_amount, delivery_fee, scheduled_delivery, payment_reference, 
            status, expires_at 
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 50.00, ?, ?, 'awaiting_payment', NULL)";
        // Assuming delivery_fee is fixed at 50.00.

        $stmt_temp_order = mysqli_prepare($dbc, $temp_order_query);
        if (!$stmt_temp_order) {
            throw new Exception("Prepare statement failed (temp_orders): " . mysqli_error($dbc));
        }
        mysqli_stmt_bind_param(
            $stmt_temp_order, "isssssdss",
            $user_id, $fullname, $phone, $address, $notes, $payment_method,
            $total_amount, $scheduled_delivery_str, $payment_ref
        );

        if (!mysqli_stmt_execute($stmt_temp_order)) {
            throw new Exception("Error creating temporary order: " . mysqli_stmt_error($stmt_temp_order));
        }
        $temp_order_id = mysqli_insert_id($dbc); // Get the ID of the temp order

        // Get cart items
        $cart_query = "SELECT uc.product_id, uc.quantity, p.prod_price as price 
                       FROM user_cart uc 
                       JOIN products p ON uc.product_id = p.product_id 
                       WHERE uc.user_id = ?";
        $cart_stmt = mysqli_prepare($dbc, $cart_query);
        if (!$cart_stmt) {
            throw new Exception("Prepare statement failed (cart_query): " . mysqli_error($dbc));
        }
        mysqli_stmt_bind_param($cart_stmt, "i", $user_id);
        mysqli_stmt_execute($cart_stmt);
        $cart_result = mysqli_stmt_get_result($cart_stmt);

        if (mysqli_num_rows($cart_result) == 0) {
             throw new Exception("Cart is empty. Cannot place order.");
        }

        // Insert into temp_order_items
        while ($item = mysqli_fetch_assoc($cart_result)) {
            $temp_item_query = "INSERT INTO temp_order_items (
                temp_order_payment_reference, product_id, quantity, price
            ) VALUES (?, ?, ?, ?)";
            $stmt_temp_item = mysqli_prepare($dbc, $temp_item_query);
            if (!$stmt_temp_item) {
                throw new Exception("Prepare statement failed (temp_order_items): " . mysqli_error($dbc));
            }
            mysqli_stmt_bind_param(
                $stmt_temp_item, "siid",
                $payment_ref, 
                $item['product_id'],
                $item['quantity'],
                $item['price']
            );
            if (!mysqli_stmt_execute($stmt_temp_item)) {
                throw new Exception("Error adding items to temporary order: " . mysqli_stmt_error($stmt_temp_item));
            }
        }
        // DO NOT CLEAR user_cart for GCash until payment is confirmed.
        // DO NOT create order_checklist items here for temp_orders. Handle upon successful payment.
        // DO NOT update product qty_sold here for temp_orders. Handle upon successful payment.

        mysqli_commit($dbc);
        echo json_encode(['success' => true, 'payment_reference' => $payment_ref, 'temp_order_id' => $temp_order_id, 'message' => 'Order awaiting payment.']);

    } else { // For 'cash' or other direct payment methods
        $order_query = "INSERT INTO orders (
            user_id, full_name, phone, address, notes, payment_method, 
            total_amount, delivery_fee, scheduled_delivery, status, payment_reference, payment_status, delivery_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 50.00, ?, 'pending', ?, 'paid', ?)";
        // Added payment_status = 'paid' for cash orders & delivery_date
        
        $stmt_order = mysqli_prepare($dbc, $order_query);
        if (!$stmt_order) {
            throw new Exception("Prepare statement failed (orders): " . mysqli_error($dbc));
        }
        mysqli_stmt_bind_param(
            $stmt_order, "isssssdsss", // Corrected: 10 characters for 10 variables
            $user_id, $fullname, $phone, $address, $notes, $payment_method,
            $total_amount, $scheduled_delivery_str, $payment_ref, $scheduled_delivery_str // Using scheduled_delivery_str for delivery_date
        );

        if (!mysqli_stmt_execute($stmt_order)) {
            throw new Exception("Error creating order: " . mysqli_stmt_error($stmt_order));
        }
        $order_id = mysqli_insert_id($dbc);

        // Get cart items
        $cart_query = "SELECT uc.product_id, uc.quantity, p.prod_price as price 
                       FROM user_cart uc 
                       JOIN products p ON uc.product_id = p.product_id 
                       WHERE uc.user_id = ?";
        $cart_stmt = mysqli_prepare($dbc, $cart_query);
         if (!$cart_stmt) {
            throw new Exception("Prepare statement failed (cart_query for cash): " . mysqli_error($dbc));
        }
        mysqli_stmt_bind_param($cart_stmt, "i", $user_id);
        mysqli_stmt_execute($cart_stmt);
        $cart_result = mysqli_stmt_get_result($cart_stmt);

        if (mysqli_num_rows($cart_result) == 0) {
             throw new Exception("Cart is empty. Cannot place order.");
        }
        
        while ($item = mysqli_fetch_assoc($cart_result)) {
            // Insert order item
            $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                           VALUES (?, ?, ?, ?)";
            $item_stmt = mysqli_prepare($dbc, $item_query);
            if (!$item_stmt) {
                throw new Exception("Prepare statement failed (order_items for cash): " . mysqli_error($dbc));
            }
            mysqli_stmt_bind_param($item_stmt, "iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            if (!mysqli_stmt_execute($item_stmt)) {
                throw new Exception("Error adding order items: " . mysqli_stmt_error($item_stmt));
            }

            // Get product ingredients and create checklist items
            $ingredient_query = "SELECT pi.ingredient_id, pi.quantity as quantity_needed 
                               FROM product_ingredients pi 
                               WHERE pi.product_id = ?";
            $ingredient_stmt = mysqli_prepare($dbc, $ingredient_query);
            if (!$ingredient_stmt) {
                throw new Exception("Prepare statement failed (ingredient_query for cash): " . mysqli_error($dbc));
            }
            mysqli_stmt_bind_param($ingredient_stmt, "i", $item['product_id']);
            mysqli_stmt_execute($ingredient_stmt);
            $ingredient_result = mysqli_stmt_get_result($ingredient_stmt);

            while ($ingredient = mysqli_fetch_assoc($ingredient_result)) {
                $total_needed = $ingredient['quantity_needed'] * $item['quantity'];
                $checklist_query = "INSERT INTO order_checklist (order_id, ingredient_id, quantity_needed, is_ready) 
                                  VALUES (?, ?, ?, 0)"; // is_ready defaults to 0 (false)
                $checklist_stmt = mysqli_prepare($dbc, $checklist_query);
                if (!$checklist_stmt) {
                    throw new Exception("Prepare statement failed (checklist_query for cash): " . mysqli_error($dbc));
                }
                mysqli_stmt_bind_param($checklist_stmt, "iid", $order_id, $ingredient['ingredient_id'], $total_needed);
                if (!mysqli_stmt_execute($checklist_stmt)) {
                    throw new Exception("Error creating checklist items: " . mysqli_stmt_error($checklist_stmt));
                }
            }

            // Update product qty_sold
            $update_query = "UPDATE products SET qty_sold = qty_sold + ? WHERE product_id = ?";
            $update_stmt = mysqli_prepare($dbc, $update_query);
            if (!$update_stmt) {
                throw new Exception("Prepare statement failed (update_query for cash): " . mysqli_error($dbc));
            }
            mysqli_stmt_bind_param($update_stmt, "ii", $item['quantity'], $item['product_id']);
            if (!mysqli_stmt_execute($update_stmt)) {
                throw new Exception("Error updating product quantities: " . mysqli_stmt_error($update_stmt));
            }
        }

        // Clear the user's cart for cash orders
        $clear_cart_query = "DELETE FROM user_cart WHERE user_id = ?";
        $clear_stmt = mysqli_prepare($dbc, $clear_cart_query);
        if (!$clear_stmt) {
            throw new Exception("Prepare statement failed (clear_cart_query for cash): " . mysqli_error($dbc));
        }
        mysqli_stmt_bind_param($clear_stmt, "i", $user_id);
        if (!mysqli_stmt_execute($clear_stmt)) {
            throw new Exception("Error clearing cart: " . mysqli_stmt_error($clear_stmt));
        }

        mysqli_commit($dbc);
        echo json_encode(['success' => true, 'message' => 'Order placed successfully!', 'order_id' => $order_id, 'payment_reference' => $payment_ref]);
    }

} catch (Exception $e) {
    if (isset($dbc) && mysqli_ping($dbc)) { // Check if connection is still valid before rollback
       mysqli_rollback($dbc);
    }
    error_log("Place Order Error: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
    echo json_encode(['success' => false, 'message' => 'An error occurred while placing your order. Please try again. Details: ' . $e->getMessage()]);
} finally {
    if (isset($dbc)) {
        mysqli_close($dbc);
    }
}
?>