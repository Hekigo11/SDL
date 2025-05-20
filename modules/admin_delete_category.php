<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
    echo "Unauthorized";
    exit;
}

include("dbconi.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_id'])) {
    $category_id = intval($_POST['category_id']);
      // Check if category has associated products
    $product_check = "SELECT COUNT(*) as count FROM products WHERE prod_cat_id = ?";
    $stmt = mysqli_prepare($dbc, $product_check);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] > 0) {
        echo "Cannot delete category: It has associated products";
        exit;
    }
    
    // Check if category has associated packages
    $package_check = "SELECT COUNT(*) as count FROM package_products WHERE category_id = ?";
    $stmt = mysqli_prepare($dbc, $package_check);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] > 0) {
        echo "Cannot delete category: It is used in catering packages";
        exit;
    }
    
    // All checks passed, delete the category
    $delete_query = "DELETE FROM categories WHERE category_id = ?";
    $stmt = mysqli_prepare($dbc, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "success";
    } else {
        echo "Error deleting category: " . mysqli_error($dbc);
    }
} else {
    echo "Invalid request";
}
?>
