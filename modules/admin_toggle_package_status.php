<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
    echo "Unauthorized";
    exit;
}

include("dbconi.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['package_id']) && isset($_POST['status'])) {
    $package_id = intval($_POST['package_id']);
    $status = intval($_POST['status']) ? 1 : 0;
    
    $query = "UPDATE packages SET is_active = ? WHERE package_id = ?";
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "ii", $status, $package_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "success";
    } else {
        echo "Error updating package status: " . mysqli_error($dbc);
    }
} else {
    echo "Invalid request";
}
?>
