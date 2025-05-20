<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
    http_response_code(403);
    echo 'Unauthorized';
    exit;
}

include("dbconi.php");

if (!isset($_POST['item_id']) || !isset($_POST['is_ready'])) {
    http_response_code(400);
    echo 'Missing required fields';
    exit;
}

try {
    if (!preg_match('/^(\d+)-(\d+)$/', $_POST['item_id'], $matches)) {
        throw new Exception('Invalid item ID format');
    }
    
    $order_id = $matches[1];
    $ingredient_id = $matches[2];
    $is_ready = (int)$_POST['is_ready'];
    $checked_by = $is_ready ? $_SESSION['user_id'] : null;
    $checked_at = $is_ready ? gmdate('Y-m-d H:i:s') : null;

    // Start transaction
    mysqli_begin_transaction($dbc);

    // First verify the order exists and is in a valid state
    $check_query = "SELECT status FROM orders WHERE order_id = ?";
    $check_stmt = mysqli_prepare($dbc, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $order_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) === 0) {
        throw new Exception('Order not found');
    }
    
    $order = mysqli_fetch_assoc($check_result);
    if ($order['status'] === 'cancelled') {
        throw new Exception('Cannot update checklist for cancelled orders');
    }

    // Update the checklist item
    $query = "UPDATE order_checklist 
              SET is_ready = ?, 
                  checked_by = ?,
                  checked_at = ?
              WHERE order_id = ? AND ingredient_id = ?";

    $stmt = mysqli_prepare($dbc, $query);
    if (!$stmt) {
        throw new Exception(mysqli_error($dbc));
    }
    
    mysqli_stmt_bind_param($stmt, "iisii", $is_ready, $checked_by, $checked_at, $order_id, $ingredient_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_error($dbc));
    }

    if (mysqli_affected_rows($dbc) === 0) {
        throw new Exception('Checklist item not found');
    }

    // Commit transaction
    mysqli_commit($dbc);
    
    echo 'success';

} catch (Exception $e) {
    if (isset($dbc) && mysqli_ping($dbc)) {
        mysqli_rollback($dbc);
    }
    http_response_code(500);
    echo $e->getMessage();
} finally {
    if (isset($stmt)) mysqli_stmt_close($stmt);
    if (isset($check_stmt)) mysqli_stmt_close($check_stmt);
    if (isset($dbc)) mysqli_close($dbc);
}
?>