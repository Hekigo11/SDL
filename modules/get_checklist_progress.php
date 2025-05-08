<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

include("dbconi.php");

if (!isset($_GET['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

try {
    $order_id = intval($_GET['order_id']);
    
    $query = "SELECT 
        (SELECT COUNT(*) FROM order_checklist WHERE order_id = ?) as total,
        (SELECT COUNT(*) FROM order_checklist WHERE order_id = ? AND is_ready = 1) as completed";

    $stmt = mysqli_prepare($dbc, $query);
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . mysqli_error($dbc));
    }
    
    mysqli_stmt_bind_param($stmt, "ii", $order_id, $order_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Query execution failed: " . mysqli_error($dbc));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    echo json_encode([
        'success' => true,
        'total' => intval($row['total']),
        'completed' => intval($row['completed'])
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($stmt)) mysqli_stmt_close($stmt);
    if (isset($dbc)) mysqli_close($dbc);
}