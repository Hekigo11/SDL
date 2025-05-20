<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/paymongo.php';
require_once __DIR__ . '/dbconi.php';
session_start();

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    error_log('[create_payment_link] Received input: ' . json_encode($input));
    
    if (!$input || !isset($input['amount']) || !is_numeric($input['amount']) || !isset($input['reference'])) {
        throw new Exception('Valid amount and internal reference are required.');
    }

    $amount_in_cents = intval(floatval($input['amount']) * 100);
    $internal_order_reference = $input['reference']; // This is our payment_reference from temp_orders
    $description = $input['description'] ?? 'Payment for Order';

    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $domain = $protocol . $_SERVER['HTTP_HOST'];

    // Construct success and cancel URLs using the internal_order_reference
    $success_url = $domain . BASE_URL . '/modules/payment_success.php?reference=' . urlencode($internal_order_reference);
    $cancel_url = $domain . BASE_URL . '/modules/payment_failed.php?reference=' . urlencode($internal_order_reference);
    // The webhook URL should be general and not contain specific order references if possible,
    // as PayMongo might only allow one webhook URL per account or API key.
    // We will identify the order from the webhook payload (e.g., metadata or PayMongo link ID).
    $webhook_url = $domain . BASE_URL . '/modules/payment_webhook.php'; 

    // Calculate expires_at: current time + 3 minutes, in ISO8601 format for PayMongo
    $current_time = new DateTime("now", new DateTimeZone('Asia/Manila'));
    $current_time->add(new DateInterval('PT3M')); // Add 3 minutes
    $expires_at_iso8601 = $current_time->format(DateTime::ATOM); //ATOM is equivalent to Y-m-d\TH:i:sP (ISO8601)

    $payload = [
        'data' => [
            'attributes' => [
                'amount' => $amount_in_cents,
                'description' => $description,
                'currency' => 'PHP',
                'expires_at' => $expires_at_iso8601, // Add expires_at here
                // 'remarks' => 'Order Ref: ' . $internal_order_reference, // Optional: for your PayMongo dashboard
                'success_url' => $success_url,
                'cancel_url' => $cancel_url,
                // It's good practice to pass your internal reference in metadata for webhook reconciliation
                'metadata' => [
                    'internal_reference' => $internal_order_reference
                ]
                // 'webhook_url' => $webhook_url // Webhooks are typically configured at the account level in PayMongo dashboard
            ]
        ]
    ];
    // If you have configured webhooks at the link creation level, uncomment 'webhook_url' above.
    // Otherwise, rely on account-level webhooks.

    $encoded_payload = json_encode($payload);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('[create_payment_link] JSON encode error for payload: ' . json_last_error_msg());
        throw new Exception('Failed to encode payload for PayMongo API: ' . json_last_error_msg());
    }
    error_log('[create_payment_link] PayMongo API Payload for ref ' . $internal_order_reference . ': ' . $encoded_payload);

    $curl = curl_init('https://api.paymongo.com/v1/links');
    
    // Check if we're in local development environment
    $is_local = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);
    
    $curl_options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $encoded_payload,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':')
        ]
    ];
    
    // Only disable SSL verification in local environment
    if ($is_local) {
        $curl_options[CURLOPT_SSL_VERIFYPEER] = false;
        $curl_options[CURLOPT_SSL_VERIFYHOST] = 0;
        error_log('[create_payment_link] Running in local environment, SSL verification disabled');
    }
    
    curl_setopt_array($curl, $curl_options);

    $response_paymongo_api = curl_exec($curl);
    $curl_error = curl_error($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($curl_error) {
        error_log('[create_payment_link] PayMongo API cURL error for ref ' . $internal_order_reference . ": " . $curl_error);
        throw new Exception('PayMongo API connection error: ' . $curl_error);
    }

    $result_paymongo = json_decode($response_paymongo_api, true);
    error_log('[create_payment_link] PayMongo API Response (HTTP ' . $http_code . ') for ref ' . $internal_order_reference . ': ' . $response_paymongo_api);

    if ($http_code !== 200 || !$result_paymongo || json_last_error() !== JSON_ERROR_NONE) {
        $api_error_message = $result_paymongo['errors'][0]['detail'] ?? 'Invalid or malformed response from PayMongo API.';
        error_log('[create_payment_link] PayMongo API error for ref ' . $internal_order_reference . ": " . $api_error_message);
        throw new Exception('Payment link creation failed with PayMongo: ' . $api_error_message);
    }

    if (!isset($result_paymongo['data']['id']) || !isset($result_paymongo['data']['attributes']['checkout_url'])) {
        error_log('[create_payment_link] PayMongo response structure invalid for ref ' . $internal_order_reference . ". Missing ID or checkout_url.");
        throw new Exception('Invalid payment response structure from PayMongo (missing link ID or checkout URL).');
    }

    $paymongo_generated_link_id = $result_paymongo['data']['id'];
    $paymongo_checkout_url = $result_paymongo['data']['attributes']['checkout_url'];
    // Use the expires_at we sent to PayMongo, as they don't return it in the link creation response attributes.
    $paymongo_link_expires_at_to_store = $expires_at_iso8601; 

    // Update temp_orders with PayMongo link ID, checkout URL, and set status to 'pending_confirmation'
    $update_temp_order_sql = "UPDATE temp_orders 
                              SET paymongo_link_id = ?, 
                                  checkout_url = ?, 
                                  status = 'pending_confirmation', 
                                  expires_at = ?,
                                  updated_at = NOW()
                              WHERE payment_reference = ? AND status = 'awaiting_payment'";
    
    $stmt_update_temp = mysqli_prepare($dbc, $update_temp_order_sql);
    if (!$stmt_update_temp) {
        error_log('[create_payment_link] DB Prepare Error (update temp_orders) for ref ' . $internal_order_reference . ": " . mysqli_error($dbc));
        throw new Exception('Database error preparing to update temporary order with PayMongo details.');
    }
    mysqli_stmt_bind_param($stmt_update_temp, "ssss", $paymongo_generated_link_id, $paymongo_checkout_url, $paymongo_link_expires_at_to_store, $internal_order_reference);
    
    if (!mysqli_stmt_execute($stmt_update_temp)) {
        error_log('[create_payment_link] DB Execute Error (update temp_orders) for ref ' . $internal_order_reference . ": " . mysqli_stmt_error($stmt_update_temp));
        throw new Exception('Failed to store PayMongo link details in temporary order.');
    }
    $affected_rows = mysqli_stmt_affected_rows($stmt_update_temp);
    mysqli_stmt_close($stmt_update_temp);

    if ($affected_rows == 0) {
        error_log('[create_payment_link] Failed to update temp_order for ref ' . $internal_order_reference . ". It might have been already updated or status was not 'awaiting_payment'.");
        // This could be an issue if the order was somehow processed or status changed between place_order and here.
        // For now, we'll proceed to return the link, but this is a potential race condition or logic flaw to investigate if it happens frequently.
        // Consider if an exception should be thrown here if strict consistency is required.
    }

    // The client (cart.php) expects our internal reference for polling check_paymongo_status.php
    echo json_encode([
        'success' => true,
        'checkout_url' => $paymongo_checkout_url,
        'reference' => $internal_order_reference, // Crucial: return OUR internal reference
        'paymongo_link_id' => $paymongo_generated_link_id, // For debugging or if client needs it for some reason
        'paymongo_link_expires_at' => $paymongo_link_expires_at_to_store // Return the stored expiry for debugging
    ]);

} catch (Throwable $t) { // Catch Throwable to handle Errors and Exceptions
    error_log('[create_payment_link] Throwable for ref (' . ($internal_order_reference ?? 'N/A') . '): ' . $t->getMessage() . " at " . $t->getFile() . ":" . $t->getLine() . "\nStack trace: " . $t->getTraceAsString());
    
    if (ob_get_length()) { // Check if there's anything in the buffer
        ob_clean(); // Clear it to prevent HTML leakage
    }
    
    // Ensure headers are set for JSON, even if attempted before.
    if (!headers_sent()) {
        header('Content-Type: application/json', true); // 'true' to replace existing
        http_response_code(400); // Bad Request or appropriate error code
    } else {
        // Headers already sent, can't set response code, but error is logged.
        // The client will likely still parse this as an error due to malformed JSON if HTML was partially sent.
    }

    echo json_encode([
        'success' => false,
        'error' => 'Server error while creating payment link. Please check server logs. Ref: ' . ($internal_order_reference ?? 'N/A'),
        'debug_message' => $t->getMessage() // Provide actual message for debugging if needed on client for dev
    ]);
} finally {
    ob_end_flush();
    if (isset($dbc) && $dbc->thread_id) {
        // mysqli_close($dbc); // Manage connection centrally or let PHP handle it
    }
}
?>