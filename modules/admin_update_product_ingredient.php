<?php
require_once __DIR__ . '/../config.php';
session_start();

// Check if user is admin
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
    echo "Unauthorized access";
    exit;
}

include("dbconi.php");

// Verify that we have the required data
if (!isset($_POST['product_id']) || 
    !isset($_POST['original_ingredient_id']) || 
    !isset($_POST['ingredient_id']) || 
    !isset($_POST['quantity'])) {
    echo "Missing required fields";
    exit;
}

// Sanitize inputs
$product_id = mysqli_real_escape_string($dbc, $_POST['product_id']);
$original_ingredient_id = mysqli_real_escape_string($dbc, $_POST['original_ingredient_id']);
$ingredient_id = mysqli_real_escape_string($dbc, $_POST['ingredient_id']);
$quantity = mysqli_real_escape_string($dbc, $_POST['quantity']);

// Validate quantity
if (!is_numeric($quantity) || $quantity <= 0) {
    echo "Invalid quantity";
    exit;
}

try {
    // Start transaction
    mysqli_begin_transaction($dbc);
    
    // If the ingredient is being changed to a different one
    if ($original_ingredient_id != $ingredient_id) {
        // Delete the original ingredient
        $delete_query = "DELETE FROM product_ingredients 
                        WHERE product_id = ? AND ingredient_id = ?";
        $delete_stmt = mysqli_prepare($dbc, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, "ii", $product_id, $original_ingredient_id);
        mysqli_stmt_execute($delete_stmt);
        
        // Check if the new ingredient already exists for this product
        $check_query = "SELECT COUNT(*) as count FROM product_ingredients 
                       WHERE product_id = ? AND ingredient_id = ?";
        $check_stmt = mysqli_prepare($dbc, $check_query);
        mysqli_stmt_bind_param($check_stmt, "ii", $product_id, $ingredient_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $exists = mysqli_fetch_assoc($check_result)['count'] > 0;
        
        if ($exists) {
            // Update existing entry for the new ingredient
            $update_query = "UPDATE product_ingredients 
                            SET quantity = quantity + ? 
                            WHERE product_id = ? AND ingredient_id = ?";
            $update_stmt = mysqli_prepare($dbc, $update_query);
            mysqli_stmt_bind_param($update_stmt, "dii", $quantity, $product_id, $ingredient_id);
            mysqli_stmt_execute($update_stmt);
        } else {
            // Insert the new ingredient
            $insert_query = "INSERT INTO product_ingredients 
                            (product_id, ingredient_id, quantity) 
                            VALUES (?, ?, ?)";
            $insert_stmt = mysqli_prepare($dbc, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, "iid", $product_id, $ingredient_id, $quantity);
            mysqli_stmt_execute($insert_stmt);
        }
    } else {
        // Just update the quantity
        $update_query = "UPDATE product_ingredients 
                        SET quantity = ? 
                        WHERE product_id = ? AND ingredient_id = ?";
        $update_stmt = mysqli_prepare($dbc, $update_query);
        mysqli_stmt_bind_param($update_stmt, "dii", $quantity, $product_id, $original_ingredient_id);
        mysqli_stmt_execute($update_stmt);
    }
    
    // Commit transaction
    mysqli_commit($dbc);
    echo "success";
} catch (Exception $e) {
    // Roll back transaction on error
    mysqli_rollback($dbc);
    echo "Error: " . $e->getMessage();
} finally {
    mysqli_close($dbc);
}
?> 