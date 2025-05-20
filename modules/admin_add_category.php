<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
    echo "Unauthorized";
    exit;
}

include("dbconi.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name']);
    
    // Validate input
    if (empty($category_name)) {
        echo "Category name is required";
        exit;
    }
    
    // Check if category already exists
    $check_query = "SELECT category_id FROM categories WHERE category_name = ?";
    $stmt = mysqli_prepare($dbc, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $category_name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo "A category with this name already exists";
        exit;
    }
    
    // Insert new category
    $insert_query = "INSERT INTO categories (category_name) VALUES (?)";
    $stmt = mysqli_prepare($dbc, $insert_query);
    mysqli_stmt_bind_param($stmt, "s", $category_name);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "success";
    } else {
        echo "Error creating category: " . mysqli_error($dbc);
    }
} else {
    echo "Invalid request";
}
?>
