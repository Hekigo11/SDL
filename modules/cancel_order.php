<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

include("dbconi.php");

if (!isset($_POST['order_id']) || !isset($_POST['reason'])) {
    $_SESSION['error'] = 'Missing required fields';
    header('Location: ' . BASE_URL . '/modules/orders.php');
    exit;
}

$order_id = intval($_POST['order_id']);
$reason = $_POST['reason'] === 'Other' ? $_POST['other_reason'] : $_POST['reason'];

try {
    // First verify that the order belongs to this user and is in a cancellable state
    $verify_query = "SELECT status FROM orders WHERE order_id = ? AND user_id = ?";
    $stmt = $dbc->prepare($verify_query);
    $stmt->bind_param('ii', $order_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Order not found or unauthorized');
    }
    
    $order = $result->fetch_assoc();
    if ($order['status'] === 'delivering' || $order['status'] === 'completed') {
        throw new Exception('Orders that are being delivered or completed cannot be cancelled');
    }

    // Update the order status and add cancellation reason
    $update_query = "UPDATE orders SET 
                    status = 'cancelled',
                    cancellation_reason = ?,
                    cancelled_at = NOW()
                    WHERE order_id = ? AND user_id = ?";
    
    $stmt = $dbc->prepare($update_query);
    $stmt->bind_param('sii', $reason, $order_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Order cancelled successfully';
    } else {
        throw new Exception('Failed to cancel order');
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ' . BASE_URL . '/modules/orders.php');
exit;