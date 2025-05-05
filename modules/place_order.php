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

try {
    mysqli_begin_transaction($dbc);

    // IInput after placing order
    $query = "INSERT INTO orders (user_id, full_name, phone, address, notes, payment_method, total_amount, delivery_fee, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, 50.00, 'pending')";
              
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "issssdd", 
        $user_id,
        $_POST['fullname'],
        $_POST['phone'],
        $_POST['address'],
        $_POST['notes'],
        $_POST['payment'],
        $_POST['total']
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error creating order: " . mysqli_stmt_error($stmt));
    }

    $order_id = mysqli_insert_id($dbc);

    // Get cart items
    $cart_query = "SELECT uc.*, p.prod_name, p.prod_price as price FROM user_cart uc 
                   JOIN products p ON uc.product_id = p.product_id 
                   WHERE uc.user_id = ?";
    $cart_stmt = mysqli_prepare($dbc, $cart_query);
    mysqli_stmt_bind_param($cart_stmt, "i", $user_id);
    mysqli_stmt_execute($cart_stmt);
    $cart_result = mysqli_stmt_get_result($cart_stmt);

    // Insert order items and update product quantities
    while ($item = mysqli_fetch_assoc($cart_result)) {
        // Insert order item
        $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                      VALUES (?, ?, ?, ?)";
        $item_stmt = mysqli_prepare($dbc, $item_query);
        mysqli_stmt_bind_param($item_stmt, "iiid", 
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        );
        
        if (!mysqli_stmt_execute($item_stmt)) {
            throw new Exception("Error adding order items");
        }

        // Get product ingredients and create checklist items
        $ingredient_query = "SELECT pi.ingredient_id, pi.quantity as quantity_needed, i.unit 
                           FROM product_ingredients pi
                           JOIN ingredients i ON pi.ingredient_id = i.ingredient_id
                           WHERE pi.product_id = ?";
        $ingredient_stmt = mysqli_prepare($dbc, $ingredient_query);
        mysqli_stmt_bind_param($ingredient_stmt, "i", $item['product_id']);
        mysqli_stmt_execute($ingredient_stmt);
        $ingredient_result = mysqli_stmt_get_result($ingredient_stmt);

        // Create checklist items for each ingredient
        while ($ingredient = mysqli_fetch_assoc($ingredient_result)) {
            $total_needed = $ingredient['quantity_needed'] * $item['quantity'];
            
            $checklist_query = "INSERT INTO order_checklist 
                              (order_id, ingredient_id, quantity_needed, is_ready) 
                              VALUES (?, ?, ?, 0)";
            $checklist_stmt = mysqli_prepare($dbc, $checklist_query);
            mysqli_stmt_bind_param($checklist_stmt, "iid", 
                $order_id,
                $ingredient['ingredient_id'],
                $total_needed
            );
            
            if (!mysqli_stmt_execute($checklist_stmt)) {
                throw new Exception("Error creating checklist items");
            }
        }

        // Update product qty_sold
        $update_query = "UPDATE products SET qty_sold = qty_sold + ? WHERE product_id = ?";
        $update_stmt = mysqli_prepare($dbc, $update_query);
        mysqli_stmt_bind_param($update_stmt, "ii", $item['quantity'], $item['product_id']);
        
        if (!mysqli_stmt_execute($update_stmt)) {
            throw new Exception("Error updating product quantities");
        }
    }

    // Clear the user's cart after successful order
    $clear_cart = "DELETE FROM user_cart WHERE user_id = ?";
    $clear_stmt = mysqli_prepare($dbc, $clear_cart);
    mysqli_stmt_bind_param($clear_stmt, "i", $user_id);
    
    if (!mysqli_stmt_execute($clear_stmt)) {
        throw new Exception("Error clearing cart");
    }

    mysqli_commit($dbc);
    echo json_encode(['success' => true, 'message' => 'Order placed successfully!']);

} catch (Exception $e) {
    mysqli_rollback($dbc);
    error_log("Order Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    mysqli_close($dbc);
}
?>