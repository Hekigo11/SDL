<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
    echo "Unauthorized";
    exit;
}

include("dbconi.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log the incoming data
    error_log("Received package data: " . print_r($_POST, true));
    
    $name = trim($_POST['name']);
    $base_price = floatval($_POST['base_price']);
    $description = trim($_POST['description']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $requirements = isset($_POST['requirements']) ? $_POST['requirements'] : [];

    // Validate input
    if (empty($name)) {
        error_log("Package name is empty");
        echo "Package name is required";
        exit;
    }

    if ($base_price <= 0) {
        error_log("Invalid base price: " . $base_price);
        echo "Base price must be greater than zero";
        exit;
    }
    
    try {
        mysqli_begin_transaction($dbc);

        // Insert package
        $query = "INSERT INTO packages (name, base_price, description, is_active) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($dbc, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare package insert statement: " . mysqli_error($dbc));
        }
        
        mysqli_stmt_bind_param($stmt, "sdsi", $name, $base_price, $description, $is_active);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error creating package: " . mysqli_error($dbc));
        }
        
        $package_id = mysqli_insert_id($dbc);
        error_log("Successfully inserted package with ID: " . $package_id);
        
        // Insert requirements
        if (!empty($requirements)) {
            $req_query = "INSERT INTO package_products (package_id, category_id, amount) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($dbc, $req_query);
            if (!$stmt) {
                throw new Exception("Failed to prepare requirements insert statement: " . mysqli_error($dbc));
            }
            
            foreach ($requirements as $category_id => $amount) {
                $amount = intval($amount);
                if ($amount > 0) {
                    mysqli_stmt_bind_param($stmt, "iii", $package_id, $category_id, $amount);
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Error setting requirements: " . mysqli_error($dbc));
                    }
                }
            }
            error_log("Successfully inserted package requirements");
        }
        
        mysqli_commit($dbc);
        echo "success";
        
    } catch (Exception $e) {
        mysqli_rollback($dbc);
        error_log("Error in admin_add_package.php: " . $e->getMessage());
        echo $e->getMessage();
    }
}
?>
