<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
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
    list($order_id, $ingredient_id) = explode('-', $_POST['item_id']);
    $is_ready = (int)$_POST['is_ready'];
    $checked_by = $is_ready ? $_SESSION['user_id'] : null;
    $checked_at = $is_ready ? date('Y-m-d H:i:s') : null;

    $query = "UPDATE order_checklist 
              SET is_ready = ?, 
                  checked_by = ?,
                  checked_at = ?
              WHERE order_id = ? AND ingredient_id = ?";

    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "iisii", 
        $is_ready, 
        $checked_by, 
        $checked_at, 
        $order_id, 
        $ingredient_id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_error($dbc));
    }

    echo 'success';

} catch (Exception $e) {
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
} finally {
    mysqli_close($dbc);
}
?>