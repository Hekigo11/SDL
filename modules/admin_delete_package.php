<?php
session_start();
require_once __DIR__ . '/../config.php';

// Check if user is admin
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
    echo "Unauthorized access";
    exit;
}

include("dbconi.php");

// Verify we have a package ID
if (!isset($_POST['package_id']) || empty($_POST['package_id'])) {
    echo "Missing package ID";
    exit;
}

// Sanitize the input
$package_id = mysqli_real_escape_string($dbc, $_POST['package_id']);

try {
    // Start a transaction for safety
    mysqli_begin_transaction($dbc);
    
    // First, check if the package exists
    $check_query = "SELECT * FROM packages WHERE package_id = ?";
    $check_stmt = mysqli_prepare($dbc, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $package_id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($result) == 0) {
        throw new Exception("Package not found");
    }
    
    // Delete menu requirements first (foreign key constraint)
    $req_delete_query = "DELETE FROM package_products WHERE package_id = ?";
    $req_delete_stmt = mysqli_prepare($dbc, $req_delete_query);
    mysqli_stmt_bind_param($req_delete_stmt, "i", $package_id);
    mysqli_stmt_execute($req_delete_stmt);
    
    // Now delete the package itself
    $package_delete_query = "DELETE FROM packages WHERE package_id = ?";
    $package_delete_stmt = mysqli_prepare($dbc, $package_delete_query);
    mysqli_stmt_bind_param($package_delete_stmt, "i", $package_id);
    mysqli_stmt_execute($package_delete_stmt);
    
    // Check if the package was actually deleted
    if (mysqli_affected_rows($dbc) == 0) {
        throw new Exception("Failed to delete package");
    }
    
    // Commit the transaction
    mysqli_commit($dbc);
    echo "success";
    
} catch (Exception $e) {
    // Roll back the transaction if there was an error
    mysqli_rollback($dbc);
    echo $e->getMessage();
} finally {
    // Close statements
    if (isset($check_stmt)) mysqli_stmt_close($check_stmt);
    if (isset($req_delete_stmt)) mysqli_stmt_close($req_delete_stmt);
    if (isset($package_delete_stmt)) mysqli_stmt_close($package_delete_stmt);
}
?>
