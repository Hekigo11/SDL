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
    $order_ids = array_map('intval', $order_ids);
    $order_ids = array_filter($order_ids);
    $is_ready = (int)$_POST['is_ready'];
    $checked_by = $is_ready ? $_SESSION['user_id'] : null;
    $checked_at = $is_ready ? gmdate('Y-m-d H:i:s') : null;

    if (empty($order_ids)) {
        throw new Exception('No valid order IDs');
    }

    // Update all checklist items for this ingredient across the specified orders
    $update_query = "UPDATE order_checklist 
                    SET is_ready = ?, 
                        checked_by = ?,
                        checked_at = ?
                    WHERE order_id IN (" . implode(',', $order_ids) . ") 
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

    // For each affected order, check if all ingredients are now ready
    $orders_to_update = [];
    foreach ($order_ids as $order_id) {
        $check_query = "SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN is_ready = 1 THEN 1 ELSE 0 END) as ready
                        FROM order_checklist
                        WHERE order_id = ?";
        
        $stmt = mysqli_prepare($dbc, $check_query);
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        // If all ingredients for this order are ready, update order status to "processing"
        if ($row['total'] > 0 && $row['total'] == $row['ready']) {
            $orders_to_update[] = $order_id;
        }
    }
    
    // Update the status of orders where all ingredients are ready
    if (!empty($orders_to_update)) {
        $update_order_query = "UPDATE orders 
                              SET status = 'processing', kitchen_status = 'in_kitchen' 
                              WHERE order_id IN (" . implode(',', $orders_to_update) . ") 
                              AND status = 'pending'";
        
        if (!mysqli_query($dbc, $update_order_query)) {
            throw new Exception("Failed to update order status: " . mysqli_error($dbc));
        }
    }

    mysqli_commit($dbc);
    echo json_encode([
        'success' => true,
        'orders_updated' => $orders_to_update
    ]);

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