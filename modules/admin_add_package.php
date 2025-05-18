<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    echo "Unauthorized";
    exit;
}

include("dbconi.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $base_price = floatval($_POST['base_price']);
    $description = trim($_POST['description']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $requirements = isset($_POST['requirements']) ? $_POST['requirements'] : [];

    // Validate input
    if (empty($name)) {
        echo "Package name is required";
        exit;
    }

    if ($base_price <= 0) {
        echo "Base price must be greater than zero";
        exit;
    }
    
    try {
        mysqli_begin_transaction($dbc);

        // Insert package
        $query = "INSERT INTO packages (name, base_price, description, is_active) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "sdsi", $name, $base_price, $description, $is_active);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error creating package: " . mysqli_error($dbc));
        }
        
        $package_id = mysqli_insert_id($dbc);
        
        // Insert requirements
        if (!empty($requirements)) {
            $req_query = "INSERT INTO package_products (package_id, category_id, amount) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($dbc, $req_query);
            
            foreach ($requirements as $category_id => $amount) {
                $amount = intval($amount);
                if ($amount > 0) {
                    mysqli_stmt_bind_param($stmt, "iii", $package_id, $category_id, $amount);
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Error setting requirements: " . mysqli_error($dbc));
                    }
                }
            }
        }
        
        mysqli_commit($dbc);
        echo "success";
        
    } catch (Exception $e) {
        mysqli_rollback($dbc);
        echo $e->getMessage();
    }
}
?>
