<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/dbconi.php';

try {
    // Log incoming webhook data
    error_log('PayMongo Webhook received: ' . file_get_contents('php://input'));
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Invalid webhook payload');
    }

    // Extract payment reference from attributes
    $paymentRef = $data['data']['attributes']['reference'] ?? null;
    error_log('Payment Reference: ' . $paymentRef);
    
    if (!$paymentRef) {
        throw new Exception('No payment reference in webhook data');
    }

    mysqli_begin_transaction($dbc);

    // Handle different event types
    switch ($data['type']) {
        case 'payment.paid':
            error_log('Processing successful payment for ref: ' . $paymentRef);
            
            // Check and update delivery orders
            $query = "UPDATE orders 
                     SET status = 'processing', 
                         payment_status = 'paid',
                         updated_at = NOW(),
                         status_updates = CONCAT(COALESCE(status_updates, ''), 
                            IF(LENGTH(COALESCE(status_updates, '')) > 0, '\n', ''),
                            '[', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), '] Payment completed successfully')
                     WHERE payment_reference = ? AND payment_status != 'paid'";
            
            $stmt = mysqli_prepare($dbc, $query);
            mysqli_stmt_bind_param($stmt, "s", $paymentRef);
            mysqli_stmt_execute($stmt);
            $delivery_updated = mysqli_affected_rows($dbc);
            error_log('Delivery orders updated: ' . $delivery_updated);

            // If no delivery order was updated, check catering orders
            if ($delivery_updated === 0) {
                $query = "UPDATE catering_orders 
                         SET status = 'confirmed', 
                             payment_status = 'paid',
                             updated_at = NOW(),
                             status_updates = CONCAT(COALESCE(status_updates, ''), 
                                IF(LENGTH(COALESCE(status_updates, '')) > 0, '\n', ''),
                                '[', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), '] Payment completed successfully')
                         WHERE payment_reference = ? AND payment_status != 'paid'";
                
                $stmt = mysqli_prepare($dbc, $query);
                mysqli_stmt_bind_param($stmt, "s", $paymentRef);
                mysqli_stmt_execute($stmt);
                $catering_updated = mysqli_affected_rows($dbc);
                error_log('Catering orders updated: ' . $catering_updated);
            }
            break;

        case 'payment.failed':
            error_log('Processing failed payment for ref: ' . $paymentRef);
            
            // Update delivery orders
            $query = "UPDATE orders 
                     SET status = 'cancelled', 
                         payment_status = 'failed',
                         updated_at = NOW(),
                         status_updates = CONCAT(COALESCE(status_updates, ''), 
                            IF(LENGTH(COALESCE(status_updates, '')) > 0, '\n', ''),
                            '[', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), '] Payment failed')
                     WHERE payment_reference = ? AND payment_status = 'pending'";
            
            $stmt = mysqli_prepare($dbc, $query);
            mysqli_stmt_bind_param($stmt, "s", $paymentRef);
            mysqli_stmt_execute($stmt);
            $delivery_updated = mysqli_affected_rows($dbc);
            error_log('Failed delivery orders updated: ' . $delivery_updated);

            // Update catering orders
            $query = "UPDATE catering_orders 
                     SET status = 'cancelled', 
                         payment_status = 'failed',
                         updated_at = NOW(),
                         status_updates = CONCAT(COALESCE(status_updates, ''), 
                            IF(LENGTH(COALESCE(status_updates, '')) > 0, '\n', ''),
                            '[', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), '] Payment failed')
                     WHERE payment_reference = ? AND payment_status = 'pending'";
            
            $stmt = mysqli_prepare($dbc, $query);
            mysqli_stmt_bind_param($stmt, "s", $paymentRef);
            mysqli_stmt_execute($stmt);
            $catering_updated = mysqli_affected_rows($dbc);
            error_log('Failed catering orders updated: ' . $catering_updated);
            break;
    }

    mysqli_commit($dbc);
    http_response_code(200);
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log('Payment Webhook Error: ' . $e->getMessage());
    if (isset($dbc) && mysqli_connect_errno()) {
        mysqli_rollback($dbc);
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}