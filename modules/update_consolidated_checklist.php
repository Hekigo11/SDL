<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

include("dbconi.php");

if (!isset($_POST['ingredient_id']) || !isset($_POST['order_ids']) || !isset($_POST['is_ready'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    mysqli_begin_transaction($dbc);

    $ingredient_id = intval($_POST['ingredient_id']);
    $order_ids = explode(',', $_POST['order_ids']);
    $is_ready = (int)$_POST['is_ready'];
    $checked_by = $is_ready ? $_SESSION['user_id'] : null;
    $checked_at = $is_ready ? gmdate('Y-m-d H:i:s') : null;

    // Verify orders exist and are in valid state
    $order_ids_str = implode(',', array_map('intval', $order_ids));
    $check_query = "SELECT order_id, status FROM orders WHERE order_id IN ($order_ids_str)";
    $check_result = mysqli_query($dbc, $check_query);

    while ($order = mysqli_fetch_assoc($check_result)) {
        if ($order['status'] === 'cancelled') {
            throw new Exception('Cannot update checklist for cancelled orders');
        }
    }

    // Update all checklist items for this ingredient across the specified orders
    $update_query = "UPDATE order_checklist 
                    SET is_ready = ?, 
                        checked_by = ?,
                        checked_at = ?
                    WHERE order_id IN ($order_ids_str) 
                    AND ingredient_id = ?";

    $stmt = mysqli_prepare($dbc, $update_query);
    if (!$stmt) {
        throw new Exception(mysqli_error($dbc));
    }

    mysqli_stmt_bind_param($stmt, "iisi", $is_ready, $checked_by, $checked_at, $ingredient_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_error($dbc));
    }

    if (mysqli_affected_rows($dbc) === 0) {
        throw new Exception('No checklist items were updated');
    }

    // For each affected order, update its kitchen_status to 'processing' if not already
    foreach ($order_ids as $order_id) {
        $update_order = "UPDATE orders 
                        SET kitchen_status = 'processing' 
                        WHERE order_id = ? 
                        AND kitchen_status = 'pending'";
        $order_stmt = mysqli_prepare($dbc, $update_order);
        if (!$order_stmt) {
            throw new Exception(mysqli_error($dbc));
        }
        mysqli_stmt_bind_param($order_stmt, "i", $order_id);
        mysqli_stmt_execute($order_stmt);
        mysqli_stmt_close($order_stmt);
    }

    mysqli_commit($dbc);
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($dbc) && mysqli_ping($dbc)) {
        mysqli_rollback($dbc);
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) mysqli_stmt_close($stmt);
    if (isset($dbc)) mysqli_close($dbc);
}
?>