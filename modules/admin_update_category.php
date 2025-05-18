<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    echo "Unauthorized";
    exit;
}

include("dbconi.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id']);
    $category_name = trim($_POST['category_name']);
    
    // Validate input
    if (empty($category_name)) {
        echo "Category name is required";
        exit;
    }
    
    // Check if another category already exists with this name
    $check_query = "SELECT category_id FROM categories WHERE category_name = ? AND category_id != ?";
    $stmt = mysqli_prepare($dbc, $check_query);
    mysqli_stmt_bind_param($stmt, "si", $category_name, $category_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo "Another category with this name already exists";
        exit;
    }
    
    // Update category
    $update_query = "UPDATE categories SET category_name = ? WHERE category_id = ?";
    $stmt = mysqli_prepare($dbc, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $category_name, $category_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "success";
    } else {
        echo "Error updating category: " . mysqli_error($dbc);
    }
} else {
    echo "Invalid request";
}
?>
