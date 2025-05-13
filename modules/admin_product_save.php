<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    echo "Unauthorized access";
    exit;
}

include("dbconi.php");

try {
    // Validate inputs
    if (empty($_POST['prod_name']) || empty($_POST['prod_price']) || empty($_POST['prod_cat_id'])) {
        throw new Exception("All fields are required");
    }

    // Handle file upload
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
        
        if (!move_uploaded_file($_FILES['prod_img']['tmp_name'], $destination)) {
            throw new Exception("Failed to upload image");
        }
    } else {
        throw new Exception("Product image is required");
    }

    // Start transaction
    mysqli_begin_transaction($dbc);

    // Insert product
    $query = "INSERT INTO products (prod_name, prod_price, prod_desc, prod_img, prod_cat_id, qty_sold) 
              VALUES (?, ?, ?, ?, ?, 0)";
    
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "sissi", 
        $_POST['prod_name'],
        $_POST['prod_price'],
        $_POST['prod_desc'],
        $newname,
        $_POST['prod_cat_id']
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Database error: " . mysqli_stmt_error($stmt));
    }
    
    // Get the newly inserted product ID
    $product_id = mysqli_insert_id($dbc);
    
    // Insert ingredients if any
    if (isset($_POST['ingredients']) && is_array($_POST['ingredients'])) {
        $ing_query = "INSERT INTO product_ingredients (product_id, ingredient_id, quantity) VALUES (?, ?, ?)";
        $ing_stmt = mysqli_prepare($dbc, $ing_query);
        
        foreach ($_POST['ingredients'] as $ingredient) {
            if (isset($ingredient['ingredient_id']) && isset($ingredient['quantity'])) {
                mysqli_stmt_bind_param($ing_stmt, "iid", 
                    $product_id, 
                    $ingredient['ingredient_id'], 
                    $ingredient['quantity']
                );
                
                if (!mysqli_stmt_execute($ing_stmt)) {
                    throw new Exception("Error adding ingredient: " . mysqli_stmt_error($ing_stmt));
                }
            }
        }
    }

    // Commit the transaction
    mysqli_commit($dbc);
    echo "success";

} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($dbc);
    echo $e->getMessage();
} finally {
    mysqli_close($dbc);
}
?>