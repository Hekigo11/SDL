<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/dbconi.php';

header('Content-Type: application/json');

try {
    $reference = $_GET['reference'] ?? null;
    
    if (!$reference) {
        throw new Exception('Reference number required');
    }
    
    // Get payment status from PayMongo API
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.paymongo.com/v1/links/" . $reference,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Accept: application/json",
            "Authorization: Basic " . base64_encode(PAYMONGO_SECRET_KEY . ":")
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        throw new Exception('Error checking payment status: ' . $err);
    }

    $result = json_decode($response, true);
    
    if (!$result || !isset($result['data']['attributes']['status'])) {
        throw new Exception('Invalid response from payment provider');
    }

    $paymentStatus = $result['data']['attributes']['status'];
    
    // If payment is paid, update the order status
    if ($paymentStatus === 'paid') {
        mysqli_begin_transaction($dbc);

        // Try updating delivery orders first
        $query = "UPDATE orders 
                 SET status = 'processing',
                     payment_status = 'paid',
                     updated_at = NOW()
                 WHERE payment_reference = ?";
        
        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "s", $reference);
        mysqli_stmt_execute($stmt);
        
        if (mysqli_affected_rows($dbc) === 0) {
            // If no delivery order was updated, try catering orders
            $query = "UPDATE catering_orders 
                     SET status = 'confirmed',
                         payment_status = 'paid',
                         updated_at = NOW()
                     WHERE payment_reference = ?";
            
            $stmt = mysqli_prepare($dbc, $query);
            mysqli_stmt_bind_param($stmt, "s", $reference);
            mysqli_stmt_execute($stmt);
        }

        mysqli_commit($dbc);
    }

    echo json_encode([
        'success' => true,
        'status' => $paymentStatus
    ]);

} catch (Exception $e) {
    error_log('Payment Status Check Error: ' . $e->getMessage());
    if (isset($dbc) && mysqli_connect_errno()) {
        mysqli_rollback($dbc);
    }
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}