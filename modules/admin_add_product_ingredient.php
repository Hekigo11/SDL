<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {       
    echo "Unauthorized access";
    exit;
}

include("dbconi.php");

try {
    if (!isset($_POST['product_id']) || !isset($_POST['ingredient_id']) || !isset($_POST['quantity'])) {
        throw new Exception('All fields are required');
    }

    $product_id = mysqli_real_escape_string($dbc, $_POST['product_id']);
    $ingredient_id = mysqli_real_escape_string($dbc, $_POST['ingredient_id']);
    $quantity = mysqli_real_escape_string($dbc, $_POST['quantity']);

    // Check if ingredient already exists for this product
    $check_query = "SELECT * FROM product_ingredients WHERE product_id = ? AND ingredient_id = ?";
    $check_stmt = mysqli_prepare($dbc, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ii", $product_id, $ingredient_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) > 0) {
        throw new Exception('This ingredient is already added to the product');
    }

    // Add new ingredient
    $query = "INSERT INTO product_ingredients (product_id, ingredient_id, quantity) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "iid", $product_id, $ingredient_id, $quantity);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to add ingredient');
    }

    echo "success";

} catch (Exception $e) {
    echo $e->getMessage();
} finally {
    mysqli_close($dbc);
}
?>