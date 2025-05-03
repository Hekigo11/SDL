<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    echo "Unauthorized access";
    exit;
}

include("dbconi.php");

try {
    if (!isset($_POST['product_id'])) {
        throw new Exception("Product ID is required");
    }

    // Get product image filename
    $query = "SELECT prod_img FROM products WHERE product_id = ?";
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "i", $_POST['product_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    // Delete product
    $delete_query = "DELETE FROM products WHERE product_id = ?";
    $delete_stmt = mysqli_prepare($dbc, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "i", $_POST['product_id']);

    if (!mysqli_stmt_execute($delete_stmt)) {
        throw new Exception("Failed to delete product");
    }

    // Delete product image
    if ($row && $row['prod_img']) {
        $image_path = "../images/Products/" . $row['prod_img'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    echo "success";

} catch (Exception $e) {
    echo $e->getMessage();
} finally {
    mysqli_close($dbc);
}
?>