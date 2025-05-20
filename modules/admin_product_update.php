<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
    echo "Unauthorized access";
    exit;
}

include("dbconi.php");

try {
    if (!isset($_POST['product_id'])) {
        throw new Exception("Product ID is required");
    }

    $product_id = mysqli_real_escape_string($dbc, $_POST['product_id']);
    $prod_name = mysqli_real_escape_string($dbc, $_POST['prod_name']);
    $prod_price = mysqli_real_escape_string($dbc, $_POST['prod_price']);
    $prod_desc = mysqli_real_escape_string($dbc, $_POST['prod_desc']);
    $prod_cat_id = mysqli_real_escape_string($dbc, $_POST['prod_cat_id']);

    // Start transaction
    mysqli_begin_transaction($dbc);

    // Handle image upload if new image is provided
    if (isset($_FILES['prod_img']) && $_FILES['prod_img']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['prod_img']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            throw new Exception("Invalid file format");
        }

        // Generate unique filename
        $newname = uniqid() . "." . $ext;
        $destination = "../images/Products/" . $newname;
        
        // Get old image filename
        $query = "SELECT prod_img FROM products WHERE product_id = ?";
        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        // Upload new image
        if (!move_uploaded_file($_FILES['prod_img']['tmp_name'], $destination)) {
            throw new Exception("Failed to upload image");
        }

        // Delete old image if exists
        if ($row && $row['prod_img']) {
            $old_image = "../images/Products/" . $row['prod_img'];
            if (file_exists($old_image)) {
                unlink($old_image);
            }
        }

        // Update product with new image
        $query = "UPDATE products SET 
                    prod_name = ?, 
                    prod_price = ?, 
                    prod_desc = ?, 
                    prod_cat_id = ?,
                    prod_img = ?
                 WHERE product_id = ?";
        
        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "sssssi", 
            $prod_name, $prod_price, $prod_desc, $prod_cat_id, $newname, $product_id);
    } else {
        // Update product without changing image
        $query = "UPDATE products SET 
                    prod_name = ?, 
                    prod_price = ?, 
                    prod_desc = ?, 
                    prod_cat_id = ?
                 WHERE product_id = ?";
        
        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", 
            $prod_name, $prod_price, $prod_desc, $prod_cat_id, $product_id);
    }

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to update product");
    }

    mysqli_commit($dbc);
    echo "success";

} catch (Exception $e) {
    mysqli_rollback($dbc);
    echo $e->getMessage();
} finally {
    mysqli_close($dbc);
}
?>