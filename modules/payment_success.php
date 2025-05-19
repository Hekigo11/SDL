<?php
require_once('../config/db.php');
require_once('../config/paymongo.php');
session_start();

error_log('Payment Success Handler: Starting');
error_log('GET Params: ' . print_r($_GET, true));

$reference = $_GET['reference'] ?? null;
if (!$reference) {
    error_log('Payment Success Handler: No reference provided');
    header('Location: ../error.php?msg=No payment reference found');
    exit;
}

// Verify payment status directly with PayMongo
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paymongo.com/v1/links/" . urlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => [
        "Accept: application/json",
        "Authorization: Basic " . base64_encode(PAYMONGO_SECRET_KEY . ":")
    ]
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    error_log('Payment Success Handler: cURL Error: ' . $err);
    header('Location: ../error.php?msg=Error verifying payment');
    exit;
}

$result = json_decode($response, true);
error_log('PayMongo Response: ' . print_r($result, true));

// Check payment status
$status = $result['data']['attributes']['status'] ?? null;
$payment_id = $result['data']['attributes']['payments'][0]['id'] ?? null;

error_log('Payment Status: ' . $status);
error_log('Payment ID: ' . $payment_id);

if ($status === 'paid') {
    try {
        mysqli_begin_transaction($dbc);
        
        // Update delivery orders first
        $query = "UPDATE orders 
                 SET status = 'processing',
                     payment_status = 'paid',
                     updated_at = NOW(),
                     payment_id = ?,
                     status_updates = CONCAT(COALESCE(status_updates, ''), 
                        IF(LENGTH(COALESCE(status_updates, '')) > 0, '\n', ''),
                        '[', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), '] Payment verified and completed')
                 WHERE payment_reference = ? AND payment_status != 'paid'";
        
        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "ss", $payment_id, $reference);
        mysqli_stmt_execute($stmt);
        $delivery_updated = mysqli_affected_rows($dbc);
        error_log('Delivery orders updated: ' . $delivery_updated);

        // If no delivery order was updated, try updating catering orders
        if ($delivery_updated === 0) {
            $query = "UPDATE catering_orders 
                     SET status = 'confirmed',
                         payment_status = 'paid',
                         updated_at = NOW(),
                         payment_id = ?,
                         status_updates = CONCAT(COALESCE(status_updates, ''), 
                            IF(LENGTH(COALESCE(status_updates, '')) > 0, '\n', ''),
                            '[', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), '] Payment verified and completed')
                     WHERE payment_reference = ? AND payment_status != 'paid'";
            
            $stmt = mysqli_prepare($dbc, $query);
            mysqli_stmt_bind_param($stmt, "ss", $payment_id, $reference);
            mysqli_stmt_execute($stmt);
            $catering_updated = mysqli_affected_rows($dbc);
            error_log('Catering orders updated: ' . $catering_updated);
        }

        mysqli_commit($dbc);
        
        // Clear cart session after successful payment
        if (isset($_SESSION['cart'])) {
            unset($_SESSION['cart']);
        }
        
        header('Location: ../payment_success.php');
        exit;

    } catch (Exception $e) {
        error_log('Payment Success Handler Error: ' . $e->getMessage());
        mysqli_rollback($dbc);
        header('Location: ../error.php?msg=Error processing payment');
        exit;
    }
} else {
    error_log('Payment not completed. Status: ' . $status);
    header('Location: ../payment_pending.php?reference=' . urlencode($reference));
    exit;
}
?>