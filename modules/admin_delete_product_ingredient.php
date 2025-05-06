<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    echo "Unauthorized access";
    exit;
}

include("dbconi.php");

try {
    if (!isset($_POST['product_id']) || !isset($_POST['ingredient_id'])) {
        throw new Exception('Product ID and Ingredient ID are required');
    }

    $product_id = mysqli_real_escape_string($dbc, $_POST['product_id']);
    $ingredient_id = mysqli_real_escape_string($dbc, $_POST['ingredient_id']);

    $query = "DELETE FROM product_ingredients WHERE product_id = ? AND ingredient_id = ?";
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "ii", $product_id, $ingredient_id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to delete ingredient');
    }

    echo "success";

} catch (Exception $e) {
    echo $e->getMessage();
} finally {
    mysqli_close($dbc);
}
?>