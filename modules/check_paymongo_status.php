<?php
ob_start();
// error_reporting(0); // Enable for debugging, disable for production
// ini_set('display_errors', 0); // Enable for debugging, disable for production
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/paymongo.php'; // Ensure this path is correct and defines PAYMONGO_SECRET_KEY
require_once __DIR__ . '/dbconi.php'; // Ensure $dbc is available and connected
session_start();

ob_clean(); // Clean any previous output
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable mysqli exceptions

try {
    $internal_reference = $_GET['reference'] ?? null;
    if (!$internal_reference) {
        throw new Exception('Internal reference number is required.');
    }
    error_log("[check_paymongo_status] Received internal reference: " . $internal_reference);

    // Fetch details from temp_orders using the internal_reference
    $temp_order_query_sql = "SELECT * FROM temp_orders WHERE payment_reference = ? AND status != 'paid' AND status != 'completed'";
    $stmt_temp_order = mysqli_prepare($dbc, $temp_order_query_sql);
    if (!$stmt_temp_order) {
        throw new Exception("DB Prepare Error (temp_orders): " . mysqli_error($dbc));
    }
    mysqli_stmt_bind_param($stmt_temp_order, "s", $internal_reference);
    mysqli_stmt_execute($stmt_temp_order);
    $result_temp_order = mysqli_stmt_get_result($stmt_temp_order);
    $temp_order_data = mysqli_fetch_assoc($result_temp_order);
    mysqli_stmt_close($stmt_temp_order);

    if (!$temp_order_data) {
        // It's possible the order was already processed by a webhook or another poll.
        // Check if it exists in the main 'orders' table with 'paid' status.
        $main_order_query_sql = "SELECT payment_status FROM orders WHERE payment_reference = ?";
        $stmt_main_order_check = mysqli_prepare($dbc, $main_order_query_sql);
        mysqli_stmt_bind_param($stmt_main_order_check, "s", $internal_reference);
        mysqli_stmt_execute($stmt_main_order_check);
        $result_main_order = mysqli_stmt_get_result($stmt_main_order_check);
        $main_order_status_data = mysqli_fetch_assoc($result_main_order);
        mysqli_stmt_close($stmt_main_order_check);

        if ($main_order_status_data && $main_order_status_data['payment_status'] === 'paid') {
            error_log("[check_paymongo_status] Order " . $internal_reference . " already marked as paid in main orders table.");
            echo json_encode(['success' => true, 'status' => 'paid', 'reference' => $internal_reference, 'order_updated' => true, 'message' => 'Order already processed.']);
            exit;
        }
        error_log("[check_paymongo_status] Temp order not found or already processed for reference: " . $internal_reference);
        throw new Exception('Order not found for the provided reference or already processed.');
    }

    $paymongo_link_id_from_db = $temp_order_data['paymongo_link_id'];
    $current_temp_order_status = $temp_order_data['status'];
    $user_id_for_cart = $temp_order_data['user_id'];

    if (empty($paymongo_link_id_from_db)) {
        error_log("[check_paymongo_status] PayMongo Link ID not found in temp_orders for reference: " . $internal_reference);
        echo json_encode([
            'success' => true, 
            'status' => $current_temp_order_status, // Reflect current DB status if no link ID
            'reference' => $internal_reference,
            'order_updated' => false,
            'error_message' => 'PayMongo Link ID missing, cannot verify status with payment gateway.'
        ]);
        exit;
    }

    // Query PayMongo API for the payment link status
    error_log("[check_paymongo_status] Querying PayMongo with Link ID: " . $paymongo_link_id_from_db . " for internal ref: " . $internal_reference);
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.paymongo.com/v1/links/" . urlencode($paymongo_link_id_from_db),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true, // Should be true in production
        CURLOPT_HTTPHEADER => [
            "Accept: application/json",
            "Authorization: Basic " . base64_encode(PAYMONGO_SECRET_KEY . ":")
        ]
    ]);
    $response_paymongo_api = curl_exec($curl);
    $curl_error = curl_error($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($curl_error) {
        error_log("[check_paymongo_status] PayMongo API cURL error for " . $paymongo_link_id_from_db . ": " . $curl_error);
        // Even with cURL error, tell client to stop for this attempt if it's not a transient network blip handled by retry
        echo json_encode([
            'success' => true, // So client doesn't show generic AJAX error, but specific status
            'status' => 'failed', // Treat as failed for UI
            'reference' => $internal_reference,
            'order_updated' => false,
            'message' => 'Error communicating with payment gateway. Please try again.',
             'debug' => ['curl_error' => $curl_error]
        ]);
        exit;
    }

    $result_paymongo = json_decode($response_paymongo_api, true);
    error_log("[check_paymongo_status] PayMongo API Response (HTTP " . $http_code . ") for Link ID " . $paymongo_link_id_from_db . ": " . print_r($result_paymongo, true));

    if ($http_code !== 200 || !isset($result_paymongo['data']['attributes']['status'])) {
        $api_error_message = $result_paymongo['errors'][0]['detail'] ?? 'Unknown PayMongo API error or malformed response.';
        // If PayMongo says the link is not found (e.g., 404), it might have expired or been wrong.
        if ($http_code === 404) {
             // Update temp_orders to 'expired' or 'failed'
            $update_temp_failed_sql = "UPDATE temp_orders SET status = 'expired', updated_at = NOW() WHERE payment_reference = ? AND status != 'expired'";
            $stmt_update_temp_failed = mysqli_prepare($dbc, $update_temp_failed_sql);
            mysqli_stmt_bind_param($stmt_update_temp_failed, "s", $internal_reference);
            mysqli_stmt_execute($stmt_update_temp_failed);
            mysqli_stmt_close($stmt_update_temp_failed);
            error_log("[check_paymongo_status] PayMongo Link ID " . $paymongo_link_id_from_db . " not found (404). Marked temp_order as expired for ref: " . $internal_reference);
            echo json_encode(['success' => true, 'status' => 'expired', 'reference' => $internal_reference, 'order_updated' => false, 'message' => 'Payment link not found or expired.']);
            exit;
        }
        error_log("[check_paymongo_status] PayMongo API query failed or malformed response for link ID " . $paymongo_link_id_from_db . ". HTTP: " . $http_code . ". Detail: " . $api_error_message);
        $update_temp_error_sql = "UPDATE temp_orders SET status = 'failed', updated_at = NOW() WHERE payment_reference = ? AND status NOT IN ('paid', 'completed', 'expired')";
        $stmt_update_temp_error = mysqli_prepare($dbc, $update_temp_error_sql);
        if ($stmt_update_temp_error) {
            mysqli_stmt_bind_param($stmt_update_temp_error, "s", $internal_reference);
            mysqli_stmt_execute($stmt_update_temp_error);
            mysqli_stmt_close($stmt_update_temp_error);
            error_log("[check_paymongo_status] Marked temp_order status to 'failed' due to API error for ref: " . $internal_reference);
        }
        
        echo json_encode([
            'success' => true, 
            'status' => 'failed', 
            'reference' => $internal_reference,
            'order_updated' => false,
            'message' => 'Could not determine payment status from payment gateway: ' . $api_error_message,
            'debug' => [
                'paymongo_link_id_used' => $paymongo_link_id_from_db,
                'http_code' => $http_code,
                'api_response' => $result_paymongo // Include actual API response for debugging
            ]
        ]);
        exit;
    }
    
    $paymongo_reported_status = $result_paymongo['data']['attributes']['status'];
    $paymongo_checkout_url = $result_paymongo['data']['attributes']['checkout_url'] ?? $temp_order_data['checkout_url']; // Fallback to stored
    $paymongo_payment_id = null; // This is for direct payments/sources, link API might not provide this directly in the same way
    
    // If PayMongo link has a payment intent associated and it's paid
    if (isset($result_paymongo['data']['attributes']['payments']) && !empty($result_paymongo['data']['attributes']['payments'])) {
        // For links, the status of the link itself is what matters primarily.
        // If the link is 'paid', then one of its payments should be successful.
        // We might want to record the PayMongo payment ID.
        // Example: $paymongo_payment_id = $result_paymongo['data']['attributes']['payments'][0]['id'];
    }

    // Determine the effective status
    $effective_status = $paymongo_reported_status; // Initialize with PayMongo's status

    if ($temp_order_data['expires_at'] !== null &&
        !in_array($effective_status, ['paid', 'failed', 'expired']) // Check only if not already terminal by PayMongo
    ) {
        // Ensure correct timezone handling for comparison
        // Assuming PHP default timezone is Asia/Manila or set accordingly elsewhere
        $current_datetime = new DateTime("now", new DateTimeZone('Asia/Manila'));
        // temp_order_data['expires_at'] is assumed to be a string in 'Y-m-d H:i:s' format (Manila time)
        $expires_at_datetime = new DateTime($temp_order_data['expires_at'], new DateTimeZone('Asia/Manila'));

        if ($current_datetime >= $expires_at_datetime) {
            error_log("[check_paymongo_status] Link for ref " . $internal_reference . " is past its stored expires_at (" . $temp_order_data['expires_at'] . "). Original PayMongo status: '" . $paymongo_reported_status . "'. Forcing effective status to 'expired'.");
            $effective_status = 'expired';
        }
    }

    $order_processed_successfully = false;
    $system_error_message = null; // For paid_system_error

    // Use $effective_status for all subsequent logic
    if ($effective_status === 'paid') {
        mysqli_begin_transaction($dbc);
        try {
            // 1. Insert into main 'orders' table from 'temp_orders'
            $insert_order_sql = "INSERT INTO orders (
                user_id, full_name, phone, address, notes, payment_method, total_amount, delivery_fee, 
                scheduled_delivery, status, payment_reference, payment_status, delivery_date, 
                paymongo_link_id, checkout_url, updated_at, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'processing', ?, 'paid', ?, ?, ?, NOW(), ?)";
            
            $stmt_insert_order = mysqli_prepare($dbc, $insert_order_sql);
            if (!$stmt_insert_order) throw new Exception("DB Prepare Error (insert_order): " . mysqli_error($dbc));
            
            mysqli_stmt_bind_param($stmt_insert_order, "isssssddssssss",
                $temp_order_data['user_id'], $temp_order_data['full_name'], $temp_order_data['phone'],
                $temp_order_data['address'], $temp_order_data['notes'], $temp_order_data['payment_method'],
                $temp_order_data['total_amount'], $temp_order_data['delivery_fee'], $temp_order_data['scheduled_delivery'],
                $internal_reference, // payment_reference
                $temp_order_data['scheduled_delivery'], // delivery_date (using scheduled_delivery for simplicity)
                $paymongo_link_id_from_db, $paymongo_checkout_url, $temp_order_data['created_at'] // Use temp_order creation time
            );
            mysqli_stmt_execute($stmt_insert_order);
            $new_main_order_id = mysqli_insert_id($dbc);
            mysqli_stmt_close($stmt_insert_order);

            // 2. Fetch items from 'temp_order_items'
            $temp_items_sql = "SELECT * FROM temp_order_items WHERE temp_order_payment_reference = ?";
            $stmt_temp_items = mysqli_prepare($dbc, $temp_items_sql);
            mysqli_stmt_bind_param($stmt_temp_items, "s", $internal_reference);
            mysqli_stmt_execute($stmt_temp_items);
            $result_temp_items = mysqli_stmt_get_result($stmt_temp_items);
            
            while ($item = mysqli_fetch_assoc($result_temp_items)) {
                // 3. Insert into 'order_items'
                $insert_item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt_insert_item = mysqli_prepare($dbc, $insert_item_sql);
                mysqli_stmt_bind_param($stmt_insert_item, "iiid", $new_main_order_id, $item['product_id'], $item['quantity'], $item['price']);
                mysqli_stmt_execute($stmt_insert_item);
                mysqli_stmt_close($stmt_insert_item);

                // 4. Create 'order_checklist' entries
                $ingredient_query = "SELECT pi.ingredient_id, pi.quantity as quantity_needed 
                                   FROM product_ingredients pi 
                                   WHERE pi.product_id = ?";
                $ingredient_stmt = mysqli_prepare($dbc, $ingredient_query);
                mysqli_stmt_bind_param($ingredient_stmt, "i", $item['product_id']);
                mysqli_stmt_execute($ingredient_stmt);
                $ingredient_result = mysqli_stmt_get_result($ingredient_stmt);
                while ($ingredient = mysqli_fetch_assoc($ingredient_result)) {
                    $total_needed = $ingredient['quantity_needed'] * $item['quantity'];
                    $checklist_query = "INSERT INTO order_checklist (order_id, ingredient_id, quantity_needed, is_ready) 
                                      VALUES (?, ?, ?, 0)";
                    $checklist_stmt = mysqli_prepare($dbc, $checklist_query);
                    mysqli_stmt_bind_param($checklist_stmt, "iid", $new_main_order_id, $ingredient['ingredient_id'], $total_needed);
                    mysqli_stmt_execute($checklist_stmt);
                    mysqli_stmt_close($checklist_stmt);
                }
                mysqli_stmt_close($ingredient_stmt);

                // 5. Update 'products.qty_sold'
                $update_prod_sql = "UPDATE products SET qty_sold = qty_sold + ? WHERE product_id = ?";
                $stmt_update_prod = mysqli_prepare($dbc, $update_prod_sql);
                mysqli_stmt_bind_param($stmt_update_prod, "ii", $item['quantity'], $item['product_id']);
                mysqli_stmt_execute($stmt_update_prod);
                mysqli_stmt_close($stmt_update_prod);
            }
            mysqli_stmt_close($stmt_temp_items);

            // 6. Clear 'user_cart'
            $clear_cart_sql = "DELETE FROM user_cart WHERE user_id = ?";
            $stmt_clear_cart = mysqli_prepare($dbc, $clear_cart_sql);
            mysqli_stmt_bind_param($stmt_clear_cart, "i", $user_id_for_cart);
            mysqli_stmt_execute($stmt_clear_cart);
            mysqli_stmt_close($stmt_clear_cart);

            // 7. Update 'temp_orders' status to 'completed' or 'paid'
            $update_temp_sql = "UPDATE temp_orders SET status = 'paid', updated_at = NOW() WHERE payment_reference = ?";
            $stmt_update_temp = mysqli_prepare($dbc, $update_temp_sql);
            mysqli_stmt_bind_param($stmt_update_temp, "s", $internal_reference);
            mysqli_stmt_execute($stmt_update_temp);
            mysqli_stmt_close($stmt_update_temp);

            // 8. Delete from temp_order_items
            $delete_temp_items_sql = "DELETE FROM temp_order_items WHERE temp_order_payment_reference = ?";
            $stmt_delete_temp_items = mysqli_prepare($dbc, $delete_temp_items_sql);
            mysqli_stmt_bind_param($stmt_delete_temp_items, "s", $internal_reference);
            mysqli_stmt_execute($stmt_delete_temp_items);
            mysqli_stmt_close($stmt_delete_temp_items);

            // 9. Delete from temp_orders (optional, could also just rely on 'paid' status if you want to keep a record for a bit)
            // For cleanest approach, we delete it.
            $delete_temp_order_sql = "DELETE FROM temp_orders WHERE payment_reference = ?";
            $stmt_delete_temp_order = mysqli_prepare($dbc, $delete_temp_order_sql);
            mysqli_stmt_bind_param($stmt_delete_temp_order, "s", $internal_reference);
            mysqli_stmt_execute($stmt_delete_temp_order);
            mysqli_stmt_close($stmt_delete_temp_order);

            mysqli_commit($dbc);
            $order_processed_successfully = true;
            error_log("[check_paymongo_status] Successfully processed PAID order " . $internal_reference . ". New order ID: " . $new_main_order_id);

        } catch (Exception $db_ex) {
            mysqli_rollback($dbc);
            error_log("[check_paymongo_status] DB transaction failed for PAID order " . $internal_reference . ": " . $db_ex->getMessage());
            // If DB transaction fails, the payment is made but order not in system. Critical error.
            $effective_status = 'paid_system_error'; // Update effective_status to indicate this specific failure
            $system_error_message = 'Payment confirmed, but an error occurred processing your order. Please contact support with reference: ' . $internal_reference;
            // Do not re-throw, send JSON response
        }
    } elseif ($effective_status === 'failed' || $effective_status === 'expired' || $effective_status === 'unpaid') {
        // Update temp_orders status based on $effective_status
        // For 'unpaid', the client will continue polling, but we ensure DB state is consistent if it became 'expired' by our logic
        $update_temp_status_sql = "UPDATE temp_orders SET status = ?, updated_at = NOW() WHERE payment_reference = ? AND status NOT IN ('paid', 'completed')";
        $stmt_update_temp_status = mysqli_prepare($dbc, $update_temp_status_sql);
        if ($stmt_update_temp_status) {
            mysqli_stmt_bind_param($stmt_update_temp_status, "ss", $effective_status, $internal_reference);
            mysqli_stmt_execute($stmt_update_temp_status);
            mysqli_stmt_close($stmt_update_temp_status);
            error_log("[check_paymongo_status] PayMongo/Effective status " . $effective_status . " for ref: " . $internal_reference . ". Temp order status updated.");
        } else {
            error_log("[check_paymongo_status] Failed to prepare statement to update temp_order status to " . $effective_status . " for ref: " . $internal_reference . ". Error: " . mysqli_error($dbc));
        }
    } else {
        // Status is still pending at PayMongo (e.g., 'awaiting_next_action')
        // $effective_status will reflect this, client will continue polling
        error_log("[check_paymongo_status] PayMongo/Effective status for " . $internal_reference . " is: " . $effective_status . " (treated as pending by client)");
    }

    echo json_encode([
        'success' => true,
        'status' => $effective_status, // Use the determined effective_status
        'reference' => $internal_reference,
        'order_updated' => $order_processed_successfully,
        'message' => $system_error_message, // Will be null unless paid_system_error occurred
        'debug' => [
            'paymongo_link_id_used' => $paymongo_link_id_from_db,
            'paymongo_api_reported_status' => $paymongo_reported_status, // Original from API
            'initial_temp_order_status' => $current_temp_order_status,
            'final_client_status' => $effective_status // Status sent to client
        ]
    ]);

} catch (Exception $e) {
    error_log("[check_paymongo_status] General Error for ref (" . ($_GET['reference'] ?? 'N/A') . "): " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'status' => 'error' // General error status for client
    ]);
} finally {
    if (isset($dbc) && $dbc->thread_id) { // Check if connection is still alive
       // mysqli_close($dbc); // Removed explicit close, let PHP handle it or manage centrally
    }
}
?>